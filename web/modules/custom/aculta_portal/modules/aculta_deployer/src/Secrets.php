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

}
