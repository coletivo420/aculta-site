<?php

declare(strict_types=1);

namespace AcultaDeployer;

/**
 * Barreiras rígidas de separação: o Portal e o tema não podem referenciar a
 * ferramenta, e a ferramenta não pode depender deles nem do Drupal. As listas
 * ficam em config/boundary.json para que este arquivo não contenha os termos.
 */
final class Boundary {

  /** @param array<string, mixed> $rules Conteúdo de config/boundary.json. */
  public function __construct(
    private readonly string $repoRoot,
    private readonly string $toolRoot,
    private readonly array $rules,
  ) {}

  /** @return string[] Violações encontradas. */
  public function check(): array {
    $violations = [];
    foreach ($this->rules['consumers'] as $dir) {
      $abs = $this->repoRoot . '/' . $dir;
      if (!is_dir($abs)) {
        continue;
      }
      foreach ($this->files($abs) as $file) {
        $rel = $this->rel($file);
        // The submodule itself is the tool; it may name itself.
        if (array_filter($this->rules['consumer_exclude'] ?? [], static fn(string $x): bool => str_starts_with($rel, $x . '/'))) {
          continue;
        }
        $text = (string) file_get_contents($file);
        foreach ($this->rules['tool_terms'] as $term) {
          if (str_contains($text, $term)) {
            $violations[] = $this->rel($file) . " referencia a ferramenta ('$term'); consumidores não podem depender de o submódulo aculta_deployer";
          }
        }
      }
    }
    foreach ($this->rules['tool_dirs'] as $dir) {
      $abs = $this->toolRoot . '/' . $dir;
      if (!is_dir($abs)) {
        continue;
      }
      foreach ($this->files($abs) as $file) {
        // The rules file lists the forbidden terms by design; it is data, not a dependency.
        if (realpath($file) === realpath($this->toolRoot . '/config/boundary.json')) {
          continue;
        }
        $text = (string) file_get_contents($file);
        foreach ($this->rules['forbidden_dependencies'] as $dep) {
          if (str_contains($text, $dep)) {
            $violations[] = $this->rel($file) . " depende de '$dep'; a ferramenta é standalone e não usa Drupal, Portal, tema ou vendor";
          }
        }
      }
    }
    return $violations;
  }

  /** @return string[] */
  private function files(string $dir): array {
    $out = [];
    $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) {
      if ($f->isFile() && !str_ends_with($f->getFilename(), '.md')) {
        $out[] = $f->getPathname();
      }
    }
    return $out;
  }

  private function rel(string $abs): string {
    return ltrim(str_replace($this->repoRoot, '', $abs), '/');
  }

}
