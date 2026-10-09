<?php

declare(strict_types=1);

namespace AcultaDeployer;

/**
 * Transformação de build de produção: remove aliases de teste e substitui o
 * sufixo de host de teste pelo de produção, somente nos arquivos do escopo.
 */
final class Transform {

  /** @param array<string, mixed> $config Conteúdo de config/deploy.json. */
  public function __construct(private readonly array $config) {}

  public function inScope(string $relPath): bool {
    foreach ($this->config['scope'] as $pattern) {
      if (preg_match('#' . $pattern . '#', $relPath) === 1) {
        return true;
      }
    }
    return false;
  }

  public function isDropped(string $relPath): bool {
    foreach ($this->config['drop'] as $pattern) {
      if (preg_match('#' . $pattern . '#', $relPath) === 1) {
        return true;
      }
    }
    return false;
  }

  /** Aplica as regras de host ao conteúdo. Retorna [conteúdo, nº de substituições]. */
  public function applyHosts(string $content): array {
    $total = 0;
    foreach ($this->config['host_rules'] as $rule) {
      $from = preg_quote((string) $rule['from'], '#');
      // Host boundary: no letter, digit, hyphen or '.label' may follow, so
      // 'aculta.toca.net.br.evil.com' is not rewritten.
      $pattern = '#(?<![A-Za-z0-9.-])((?:[a-z0-9-]+\.)*)' . $from . '(?![A-Za-z0-9-]|\.[A-Za-z0-9-])#i';
      $content = preg_replace($pattern, '${1}' . $rule['to'], $content, -1, $count);
      $total += $count;
    }
    return [$content, $total];
  }

  /** Conta ocorrências de hosts de teste em um conteúdo, sem alterá-lo. */
  public function countTestHosts(string $content): int {
    return $this->applyHosts($content)[1];
  }

}
