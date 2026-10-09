<?php

declare(strict_types=1);

namespace AcultaDeployer;

/**
 * Verificação pós-deploy somente leitura: uma requisição GET por entrada com
 * "probe" e "expect". Não grava nada e não segue redirecionamentos.
 */
final class Verify {

  public const TIMEOUT_SECONDS = 10;
  public const MAX_BYTES = 1048576;

  /** Somente HTTPS, com host e sem credenciais na URL. */
  public static function isAllowedUrl(string $url): bool {
    if (!str_starts_with($url, 'https://') || filter_var($url, FILTER_VALIDATE_URL) === false) {
      return false;
    }
    $parts = parse_url($url);
    return isset($parts['host']) && !isset($parts['user']) && !isset($parts['pass']);
  }

  /** O texto esperado precisa aparecer na resposta. */
  public static function evaluate(string $body, string $expect): bool {
    return $expect !== '' && str_contains($body, $expect);
  }

  /** GET sem seguir redirecionamentos; devolve corpo e cabeçalhos ou null. */
  public static function fetchWithHeaders(string $url): ?array {
    if (!self::isAllowedUrl($url)) {
      return null;
    }
    $context = self::context();
    $body = @file_get_contents($url, false, $context, 0, self::MAX_BYTES);
    if ($body === false) {
      return null;
    }
    // file_get_contents grava os cabeçalhos da resposta neste escopo.
    return ['body' => $body, 'headers' => $http_response_header ?? []];
  }

  private static function context() {
    return stream_context_create(['http' => [
      'method' => 'GET',
      'timeout' => self::TIMEOUT_SECONDS,
      'follow_location' => 0,
      'ignore_errors' => true,
      'user_agent' => 'aculta-deployer/' . Cli::VERSION,
      'max_redirects' => 0,
    ]]);
  }

  /** Valor do cabeçalho X-Robots-Tag, ou null se ausente. */
  public static function robotsHeader(array $headers): ?string {
    foreach ($headers as $line) {
      if (preg_match('/^x-robots-tag:\s*(.+)$/i', trim((string) $line), $m) === 1) {
        return trim($m[1]);
      }
    }
    return null;
  }

  /** Verdadeiro quando o valor do cabeçalho impede indexação. */
  public static function isNoindex(?string $value): bool {
    return $value !== null && stripos($value, 'noindex') !== false;
  }

  /** GET sem seguir redirecionamentos, com tempo limite e tamanho máximo. */
  public static function fetch(string $url): ?string {
    if (!self::isAllowedUrl($url)) {
      return null;
    }
    $context = stream_context_create(['http' => [
      'method' => 'GET',
      'timeout' => self::TIMEOUT_SECONDS,
      'follow_location' => 0,
      'ignore_errors' => true,
      'user_agent' => 'aculta-deployer/' . Cli::VERSION,
      'max_redirects' => 0,
    ]]);
    $body = @file_get_contents($url, false, $context, 0, self::MAX_BYTES);
    return $body === false ? null : $body;
  }

}
