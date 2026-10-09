<?php

declare(strict_types=1);

namespace AcultaDeployer;

/**
 * Lógica pura do arquivo local de credenciais (ACULTA Secrets Contract).
 *
 * Nunca imprime valores. O contrato (nomes e ambientes) fica em config/secrets-contract.json;
 * os valores ficam só no arquivo local ignorado pelo Git, fora do document root.
 */
final class Secrets {

  /** Nome de variável no formato NAME=value, em maiúsculas (como no loader do Drupal). */
  private const NAME_PATTERN = '/^[A-Z][A-Z0-9_]*$/';

  /**
   * Lê o conteúdo NAME=value. Linhas vazias e comentários (#) são ignoradas. O valor é
   * literal, inclusive quando contém "=". Retorna null se alguma linha não tem essa forma.
   *
   * @return array<string, string>|null
   */
  public static function parse(string $content): ?array {
    $values = [];
    foreach (preg_split('/\R/', $content) ?: [] as $line) {
      $trim = trim($line);
      if ($trim === '' || str_starts_with($trim, '#')) {
        continue;
      }
      $pos = strpos($line, '=');
      if ($pos === false) {
        return null;
      }
      $name = substr($line, 0, $pos);
      if (preg_match(self::NAME_PATTERN, $name) !== 1) {
        return null;
      }
      $values[$name] = substr($line, $pos + 1);
    }
    return $values;
  }

  /**
   * Nomes permitidos pelo contrato, na ordem do arquivo de configuração.
   *
   * @param array<string, mixed> $contract
   * @return string[]
   */
  public static function allowedNames(array $contract): array {
    return array_values(array_map(static fn(array $v): string => (string) $v['name'], $contract['variables'] ?? []));
  }

  /**
   * Nomes obrigatórios num ambiente.
   *
   * @param array<string, mixed> $contract
   * @return string[]
   */
  public static function requiredNames(array $contract, string $env): array {
    $names = [];
    foreach ($contract['variables'] ?? [] as $v) {
      if (in_array($env, (array) ($v['required_in'] ?? []), true)) {
        $names[] = (string) $v['name'];
      }
    }
    return $names;
  }

  /**
   * Regra de permissão do arquivo de credenciais (alinhada ao loader do Drupal):
   * recusa escrita de grupo (0020) e qualquer acesso de outros (0007). Leitura de grupo é
   * permitida, porque o processo web lê o arquivo por ACL de leitura.
   */
  public static function modeProblem(int $perms): ?string {
    if (($perms & 0020) !== 0) {
      return 'escrita de grupo (exigido: sem escrita para grupo e sem acesso para outros)';
    }
    return ($perms & 0007) !== 0 ? 'acesso de outros (exigido: sem acesso para outros)' : null;
  }

  /** Verdadeiro se $path está dentro de $dir (ambos já resolvidos com realpath). */
  public static function isInside(string $path, string $dir): bool {
    $dir = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    return $path === rtrim($dir, DIRECTORY_SEPARATOR) || str_starts_with($path, $dir);
  }

  /**
   * Valida o contrato: nomes válidos, sem duplicatas e obrigatórios contidos na lista permitida.
   *
   * @param array<string, mixed> $contract
   * @return string[] problemas encontrados (vazio se o contrato é válido)
   */
  public static function contractProblems(array $contract): array {
    $problems = [];
    $names = self::allowedNames($contract);
    if (count($names) !== count(array_unique($names))) {
      $problems[] = 'nomes duplicados no contrato';
    }
    foreach ($names as $n) {
      if (preg_match(self::NAME_PATTERN, $n) !== 1) {
        $problems[] = "nome inválido no contrato: $n";
      }
    }
    foreach (['production', 'test'] as $env) {
      foreach (self::requiredNames($contract, $env) as $n) {
        if (!in_array($n, $names, true)) {
          $problems[] = "obrigatório fora da lista permitida: $n ($env)";
        }
      }
    }
    return $problems;
  }

}
