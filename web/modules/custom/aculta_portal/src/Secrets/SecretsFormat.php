<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\Secrets;

/**
 * Formato do arquivo de credenciais (ACULTA Secrets Contract), sem I/O.
 *
 * Duplica de propósito a lógica da ferramenta de deploy: nenhum dos dois depende do outro.
 * Os nomes vêm de config/secrets-contract.json.
 */
final class SecretsFormat {

  private const NAME_PATTERN = '/^[A-Z][A-Z0-9_]*$/';

  /**
   * Lê NAME=value. Ignora linhas vazias e comentários (#). O valor é literal (pode conter "=").
   *
   * @return array<string, string>|null null se alguma linha não tem essa forma.
   */
  public static function parse(string $content): ?array {
    $values = [];
    foreach (preg_split('/\R/', $content) ?: [] as $line) {
      $trim = trim($line);
      if ($trim === '' || str_starts_with($trim, '#')) {
        continue;
      }
      $pos = strpos($line, '=');
      if ($pos === FALSE) {
        return NULL;
      }
      $name = substr($line, 0, $pos);
      if (preg_match(self::NAME_PATTERN, $name) !== 1) {
        return NULL;
      }
      $values[$name] = substr($line, $pos + 1);
    }
    return $values;
  }

  /**
   * @param array<string, mixed> $contract
   * @return string[]
   */
  public static function allowedNames(array $contract): array {
    return array_values(array_map(static fn(array $v): string => (string) $v['name'], $contract['variables'] ?? []));
  }

  /**
   * @param array<string, mixed> $contract
   * @return string[]
   */
  public static function requiredNames(array $contract, string $environment): array {
    $names = [];
    foreach ($contract['variables'] ?? [] as $variable) {
      if (in_array($environment, (array) ($variable['required_in'] ?? []), TRUE)) {
        $names[] = (string) $variable['name'];
      }
    }
    return $names;
  }

  /** Mensagem se há bits de acesso para grupo ou outros (exigido: 0600 ou mais restrito). */
  public static function modeProblem(int $perms): ?string {
    return ($perms & 0077) !== 0 ? 'permissões amplas demais (exigido: 0600)' : NULL;
  }

  /** Verdadeiro se $path está dentro de $dir (ambos resolvidos com realpath). */
  public static function isInside(string $path, string $dir): bool {
    $prefix = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    return $path === rtrim($dir, DIRECTORY_SEPARATOR) || str_starts_with($path, $prefix);
  }

}
