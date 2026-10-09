<?php

declare(strict_types=1);

namespace AcultaDeployer;

/** Comandos da ferramenta. Não usa Drupal, Drush nem vendor. */
final class Cli {

  public const VERSION = '0.1.0';

  private readonly string $toolRoot;
  private readonly string $repoRoot;

  public function __construct(string $toolRoot) {
    $this->toolRoot = $toolRoot;
    // Submódulo em web/modules/custom/aculta_portal/modules/aculta_deployer: seis níveis até a raiz.
    $this->repoRoot = dirname($toolRoot, 6);
  }

  /** @param string[] $argv */
  public function run(array $argv): int {
    $command = $argv[1] ?? 'help';
    $options = $this->options(array_slice($argv, 2));
    return match ($command) {
      'version', '--version' => $this->say('aculta-deployer ' . self::VERSION),
      'check' => $this->check(!empty($options['strict'])),
      'boundaries' => $this->boundaries(),
      'list' => $this->list(),
      'register' => $this->register($options),
      'build' => $this->build($options),
      default => $this->help(),
    };
  }

  private function check(bool $strict): int {
    $code = 0;
    $registry = $this->registry();
    $errors = $registry->validate();
    foreach ($errors as $e) {
      $this->err("registro: $e");
      $code = 1;
    }
    $boundary = $this->boundaryViolations();
    foreach ($boundary as $v) {
      $this->err("fronteira: $v");
      $code = 1;
    }
    $transform = $this->transform();
    $stats = $this->scan($transform);
    $this->say(sprintf('escopo: %d arquivos; %d com host de teste; %d removidos no build de produção',
      $stats['scope'], $stats['with_test_host'], $stats['dropped']));
    foreach ($stats['outside_scope'] as $rel) {
      $this->say("  fora do escopo (documentação ou teste; não transformado): $rel");
    }
    $open = $registry->open();
    $blocking = $registry->openBlocking();
    $this->say(sprintf('registro: %d entradas abertas, %d bloqueantes', count($open), count($blocking)));
    foreach ($blocking as $e) {
      $this->say("  bloqueante {$e['id']} ({$e['kind']}): {$e['page']}");
    }
    if ($code === 0 && $strict && $blocking !== []) {
      $this->err('--strict: existem entradas bloqueantes abertas');
      return 2;
    }
    $this->say($code === 0 ? 'check: PASS' : 'check: FAIL');
    return $code;
  }

  private function boundaries(): int {
    $violations = $this->boundaryViolations();
    foreach ($violations as $v) {
      $this->err("fronteira: $v");
    }
    if ($violations !== []) {
      $this->say('boundaries: FAIL');
      return 1;
    }
    $this->say('boundaries: PASS (Portal e tema não referenciam a ferramenta; a ferramenta não depende deles)');
    return 0;
  }

  private function list(): int {
    foreach ($this->registry()->entries() as $e) {
      $this->say(sprintf('%s [%s] %s%s — %s', $e['id'], $e['status'], $e['kind'], ($e['blocking'] ?? false) ? ' (bloqueante)' : '', $e['page']));
    }
    return 0;
  }

  /** @param array<string, string|bool> $o */
  private function register(array $o): int {
    $required = ['kind', 'page', 'current', 'expected', 'reason', 'owner'];
    foreach ($required as $k) {
      if (!isset($o[$k]) || $o[$k] === true || trim((string) $o[$k]) === '') {
        $this->err("register: --$k é obrigatório");
        return 1;
      }
    }
    try {
      $id = $this->registry()->add([
        'kind' => (string) $o['kind'],
        'page' => (string) $o['page'],
        'current' => (string) $o['current'],
        'expected_production' => (string) $o['expected'],
        'reason' => (string) $o['reason'],
        'owner' => (string) $o['owner'],
        'blocking' => isset($o['blocking']),
      ]);
    } catch (\InvalidArgumentException $e) {
      $this->err('register: ' . $e->getMessage());
      return 1;
    }
    $this->say("registrado: $id");
    return 0;
  }

  /** @param array<string, string|bool> $o */
  private function build(array $o): int {
    $out = $o['out'] ?? null;
    if (!is_string($out) || $out === '') {
      $this->err('build: --out=DIR é obrigatório');
      return 1;
    }
    $outAbs = $this->absolute($out);
    if (str_starts_with($outAbs . '/', $this->repoRoot . '/')) {
      $this->err('build: o diretório de saída não pode ficar dentro do repositório');
      return 1;
    }
    $blocking = $this->registry()->openBlocking();
    if ($blocking !== [] && !isset($o['allow-open-blocking'])) {
      $this->err('build: existem entradas bloqueantes abertas; resolva-as ou use --allow-open-blocking para um build de ensaio');
      foreach ($blocking as $e) {
        $this->err("  {$e['id']} ({$e['kind']}): {$e['page']}");
      }
      return 2;
    }
    $transform = $this->transform();
    $report = ['tool' => 'aculta-deployer ' . self::VERSION, 'target' => 'production', 'files' => [], 'dropped' => [], 'replacements' => 0];
    foreach ($this->scopeFiles() as $rel) {
      if ($transform->isDropped($rel)) {
        $report['dropped'][] = $rel;
        continue;
      }
      [$content, $count] = $transform->applyHosts((string) file_get_contents($this->repoRoot . '/' . $rel));
      $target = $outAbs . '/' . $rel;
      if (!is_dir(dirname($target))) {
        mkdir(dirname($target), 0775, true);
      }
      file_put_contents($target, $content);
      $report['files'][] = ['path' => $rel, 'replacements' => $count];
      $report['replacements'] += $count;
    }
    file_put_contents($outAbs . '/deploy-report.json', json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
    $this->say(sprintf('build: %d arquivos gravados, %d removidos, %d substituições de host em %s',
      count($report['files']), count($report['dropped']), $report['replacements'], $out));
    return 0;
  }

  /** @return string[] */
  private function boundaryViolations(): array {
    $rules = $this->json($this->toolRoot . '/config/boundary.json');
    return (new Boundary($this->repoRoot, $this->toolRoot, $rules))->check();
  }

  private function transform(): Transform {
    return new Transform($this->json($this->toolRoot . '/config/deploy.json'));
  }

  private function registry(): Registry {
    return new Registry($this->toolRoot . '/registry/deploy-registry.json');
  }

  /** @return string[] Caminhos relativos ao repositório, dentro do escopo. */
  private function scopeFiles(): array {
    $files = [];
    foreach (['config/sync', 'scripts/content'] as $dir) {
      $abs = $this->repoRoot . '/' . $dir;
      if (!is_dir($abs)) {
        continue;
      }
      $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($abs, \FilesystemIterator::SKIP_DOTS));
      foreach ($it as $f) {
        if ($f->isFile()) {
          $rel = ltrim(str_replace($this->repoRoot, '', $f->getPathname()), '/');
          if ($this->transform()->inScope($rel)) {
            $files[] = $rel;
          }
        }
      }
    }
    sort($files);
    return $files;
  }

  /** @return array{scope: int, with_test_host: int, dropped: int, outside_scope: string[]} */
  private function scan(Transform $transform): array {
    $scope = $this->scopeFiles();
    $withTest = 0;
    $dropped = 0;
    foreach ($scope as $rel) {
      if ($transform->isDropped($rel)) {
        $dropped++;
      }
      if ($transform->countTestHosts((string) file_get_contents($this->repoRoot . '/' . $rel)) > 0) {
        $withTest++;
      }
    }
    $outside = [];
    $docs = $this->repoRoot . '/docs';
    if (is_dir($docs)) {
      $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($docs, \FilesystemIterator::SKIP_DOTS));
      foreach ($it as $f) {
        if ($f->isFile() && $transform->countTestHosts((string) file_get_contents($f->getPathname())) > 0) {
          $outside[] = ltrim(str_replace($this->repoRoot, '', $f->getPathname()), '/');
        }
      }
    }
    return ['scope' => count($scope), 'with_test_host' => $withTest, 'dropped' => $dropped, 'outside_scope' => $outside];
  }

  /** @return array<string, mixed> */
  private function json(string $path): array {
    return json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
  }

  private function absolute(string $path): string {
    return str_starts_with($path, '/') ? rtrim($path, '/') : rtrim(getcwd() . '/' . $path, '/');
  }

  /** @param string[] $args @return array<string, string|bool> */
  private function options(array $args): array {
    $out = [];
    foreach ($args as $a) {
      if (str_starts_with($a, '--')) {
        [$k, $v] = array_pad(explode('=', substr($a, 2), 2), 2, true);
        $out[$k] = $v;
      }
    }
    return $out;
  }

  private function help(): int {
    $this->say(<<<TXT
aculta-deployer {version} — substitui hosts de teste (*.aculta.toca.net.br) por produção (*.aculta.org) no build de deploy.

Uso:
  aculta-deployer check [--strict]     valida registro, fronteiras e escopo; --strict falha com bloqueantes abertas
  aculta-deployer boundaries           verifica a separação com Portal e tema
  aculta-deployer list                 lista as correções de deploy registradas
  aculta-deployer register --kind=K --page=P --current=C --expected=E --reason=R --owner=O [--blocking]
  aculta-deployer build --out=DIR [--allow-open-blocking]
  aculta-deployer version

Guia completo: o submódulo aculta_deployer/docs/USO.md
TXT . "\n", ['{version}' => self::VERSION]);
    return 0;
  }

  private function say(string $line): int {
    fwrite(STDOUT, $line . "\n");
    return 0;
  }

  private function err(string $line): void {
    fwrite(STDERR, $line . "\n");
  }

}
