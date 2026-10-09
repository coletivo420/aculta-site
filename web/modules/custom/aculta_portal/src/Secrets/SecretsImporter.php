<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\Secrets;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Site\Settings;
use Drupal\key\KeyRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Importação de credenciais do ambiente pelo painel administrativo.
 *
 * Fluxo: o operador copia um arquivo NAME=value para a pasta de importação (fora de web/,
 * modo 0600). A importação valida o arquivo, grava o arquivo de credenciais de forma atômica
 * e apaga a origem com sobrescrita antes de remover. Nenhum valor é exibido, gravado no banco
 * ou escrito em log: só nomes e contagens.
 */
final class SecretsImporter {

  /** Tamanho do bloco de sobrescrita na exclusão segura. */
  private const SHRED_CHUNK = 8192;

  private readonly LoggerInterface $logger;

  public function __construct(
    private readonly KeyRepositoryInterface $keys,
    private readonly Settings $settings,
    LoggerChannelFactoryInterface $loggerFactory,
    private readonly string $appRoot,
  ) {
    $this->logger = $loggerFactory->get('aculta_portal');
  }

  /** Arquivo de credenciais lido pelo bootstrap (ver aculta_secrets_file em settings.local.php). */
  public function storePath(): string {
    return (string) $this->settings->get('aculta_secrets_file', dirname($this->appRoot) . '/secrets/aculta.secrets.env');
  }

  /** Pasta de origem da importação (fora de web/). */
  public function importDir(): string {
    return dirname($this->storePath()) . DIRECTORY_SEPARATOR . 'import';
  }

  /** Ambiente lido de aculta_secrets_environment (production|test). Padrão: production. */
  public function environment(): string {
    $env = (string) $this->settings->get('aculta_secrets_environment', 'production');
    return in_array($env, ['production', 'test'], TRUE) ? $env : 'production';
  }

  /** @return array<string, mixed> */
  public function contract(): array {
    $file = dirname($this->appRoot) . '/config/secrets-contract.json';
    $data = is_file($file) ? json_decode((string) file_get_contents($file), TRUE) : NULL;
    return is_array($data) ? $data : [];
  }

  /**
   * Estado por variável: se a chave tem valor no ambiente. Nunca retorna o valor.
   *
   * @return array<string, array{key_id: string, required: bool, present: bool}>
   */
  public function status(): array {
    $contract = $this->contract();
    $required = SecretsFormat::requiredNames($contract, $this->environment());
    $rows = [];
    foreach ($contract['variables'] ?? [] as $variable) {
      $name = (string) $variable['name'];
      $rows[$name] = [
        'key_id' => (string) ($variable['key_id'] ?? ''),
        'required' => in_array($name, $required, TRUE),
        'present' => $this->hasValue((string) ($variable['key_id'] ?? '')),
      ];
    }
    return $rows;
  }

  /** @return string[] nomes obrigatórios no ambiente que não têm valor. */
  public function missingRequired(): array {
    $missing = [];
    foreach ($this->status() as $name => $row) {
      if ($row['required'] && !$row['present']) {
        $missing[] = $name;
      }
    }
    return $missing;
  }

  /** @return string[] nomes dos arquivos .env prontos para importar (sem conteúdo). */
  public function stagedFiles(): array {
    $dir = $this->importDir();
    if (!is_dir($dir)) {
      return [];
    }
    $names = [];
    foreach (scandir($dir) ?: [] as $entry) {
      if (preg_match('/^[A-Za-z0-9_.-]+\.env$/', $entry) === 1 && is_file($dir . DIRECTORY_SEPARATOR . $entry)) {
        $names[] = $entry;
      }
    }
    return $names;
  }

  /**
   * Importa um arquivo da pasta de origem.
   *
   * @return array{ok: bool, message: string, count: int, source_deleted: bool}
   */
  public function import(string $fileName, bool $overwrite): array {
    $fail = static fn(string $message): array => ['ok' => FALSE, 'message' => $message, 'count' => 0, 'source_deleted' => FALSE];
    if (preg_match('/^[A-Za-z0-9_.-]+\.env$/', $fileName) !== 1) {
      return $fail('Nome de arquivo inválido.');
    }
    $appReal = realpath($this->appRoot);
    $dirReal = realpath($this->importDir());
    $source = $dirReal === FALSE ? FALSE : realpath($dirReal . DIRECTORY_SEPARATOR . $fileName);
    if ($source === FALSE || $dirReal === FALSE || !SecretsFormat::isInside($source, $dirReal) || !is_file($source)) {
      return $fail('Arquivo de origem não encontrado na pasta de importação.');
    }
    if ($appReal !== FALSE && SecretsFormat::isInside($dirReal, $appReal)) {
      return $fail('A pasta de importação está dentro do document root.');
    }
    if (($problem = SecretsFormat::modeProblem(fileperms($source) & 0777)) !== NULL) {
      return $fail('Arquivo de origem recusado: ' . $problem . '.');
    }
    $values = SecretsFormat::parse((string) file_get_contents($source));
    if ($values === NULL) {
      return $fail('Arquivo fora do formato NAME=value (conteúdo não exibido).');
    }
    $contract = $this->contract();
    $allowed = SecretsFormat::allowedNames($contract);
    $unknown = array_values(array_diff(array_keys($values), $allowed));
    if ($unknown !== []) {
      return $fail('Variáveis fora do contrato: ' . implode(', ', $unknown) . '.');
    }
    $environment = $this->environment();
    $missing = [];
    foreach (SecretsFormat::requiredNames($contract, $environment) as $name) {
      if (!isset($values[$name]) || $values[$name] === '') {
        $missing[] = $name;
      }
    }
    if ($missing !== []) {
      return $fail('Obrigatórias ausentes ou vazias em ' . $environment . ': ' . implode(', ', $missing) . '.');
    }
    $store = $this->storePath();
    if (file_exists($store) && !$overwrite) {
      return $fail('Já existe um arquivo de credenciais. Confirme a substituição para importar.');
    }
    $lines = [];
    foreach ($allowed as $name) {
      if (isset($values[$name])) {
        $lines[] = $name . '=' . $values[$name];
      }
    }
    if (!$this->writeAtomic($store, implode("\n", $lines) . "\n")) {
      return $fail('Não foi possível gravar o arquivo de credenciais.');
    }
    $deleted = self::shred($source);
    $this->logger->notice('Importação de credenciais: @count variável(eis) gravada(s) para o ambiente @env; origem apagada: @deleted.', [
      '@count' => count($lines),
      '@env' => $environment,
      '@deleted' => $deleted ? 'sim' : 'não',
    ]);
    return [
      'ok' => TRUE,
      'message' => $deleted
        ? 'Importação concluída; arquivo de origem apagado.'
        : 'Importação concluída, mas o arquivo de origem NÃO pôde ser apagado. Remova-o manualmente.',
      'count' => count($lines),
      'source_deleted' => $deleted,
    ];
  }

  /**
   * Sobrescreve o arquivo com bytes aleatórios (duas passadas), sincroniza e remove.
   * Limite: em sistemas de arquivos com cópia-na-escrita, journaling ou SSD, a sobrescrita não
   * garante a remoção física dos blocos antigos. Por isso o arquivo de origem deve ficar só no
   * tempo da importação.
   */
  public static function shred(string $path): bool {
    $size = @filesize($path);
    $fh = $size === FALSE ? FALSE : @fopen($path, 'r+b');
    if ($fh === FALSE) {
      return FALSE;
    }
    for ($pass = 0; $pass < 2; $pass++) {
      rewind($fh);
      $left = $size;
      while ($left > 0) {
        $chunk = random_bytes(min(self::SHRED_CHUNK, $left));
        fwrite($fh, $chunk);
        $left -= strlen($chunk);
      }
      fflush($fh);
      fsync($fh);
    }
    fclose($fh);
    return unlink($path);
  }

  private function hasValue(string $keyId): bool {
    if ($keyId === '') {
      return FALSE;
    }
    $key = $this->keys->getKey($keyId);
    $value = $key?->getKeyValue();
    return is_string($value) && $value !== '';
  }

  private function writeAtomic(string $target, string $content): bool {
    $dir = dirname($target);
    if (!is_dir($dir) && !mkdir($dir, 0700, TRUE)) {
      return FALSE;
    }
    $temp = $dir . DIRECTORY_SEPARATOR . '.aculta-import-' . bin2hex(random_bytes(8)) . '.tmp';
    $fh = @fopen($temp, 'xb');
    if ($fh === FALSE) {
      return FALSE;
    }
    chmod($temp, 0600);
    $ok = fwrite($fh, $content) === strlen($content);
    fflush($fh);
    fsync($fh);
    fclose($fh);
    if (!$ok || !rename($temp, $target)) {
      @unlink($temp);
      return FALSE;
    }
    chmod($target, 0600);
    return TRUE;
  }

}
