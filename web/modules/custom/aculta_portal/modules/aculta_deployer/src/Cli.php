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
      'verify' => $this->verify(),
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
    foreach ($transform->validate() as $e) {
      $this->err("configuração: $e");
      $code = 1;
    }
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

  /** Fase 5: GET somente leitura nas entradas com probe e expect. */
  private function verify(): int {
    $code = 0;
    $checked = 0;
    foreach ($this->registry()->entries() as $e) {
      if (!isset($e['probe'], $e['expect'])) {
        continue;
      }
      $checked++;
      $body = Verify::fetch((string) $e['probe']);
      if ($body === null) {
        $this->say("FAIL {$e['id']}: não foi possível ler {$e['probe']} (URL inválida ou sem resposta)");
        $code = 1;
        continue;
      }
      if (Verify::evaluate($body, (string) $e['expect'])) {
        $this->say("PASS {$e['id']}: {$e['probe']} contém o valor esperado");
      } else {
        $this->say("FAIL {$e['id']}: {$e['probe']} não contém o valor esperado");
        $code = 1;
      }
    }
    $this->say($checked === 0 ? 'verify: nenhuma entrada com probe' : ($code === 0 ? 'verify: PASS' : 'verify: FAIL'));
    return $code;
  }

  /** @param array<string, string|bool> $o */
  private function register(array $o): int {
    if (isset($o['from-json'])) {
      return $this->importFindings((string) $o['from-json']);
    }
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

  /** Fase 3: importa achados de um JSON (lista de objetos), tudo ou nada. */
  private function importFindings(string $path): int {
    if ($path === '' || !is_file($path) || is_link($path)) {
      $this->err('register: --from-json precisa apontar para um arquivo regular');
      return 1;
    }
    if (filesize($path) > 262144) {
      $this->err('register: arquivo de achados acima de 256 KiB');
      return 1;
    }
    try {
      $findings = json_decode((string) file_get_contents($path), true, 64, JSON_THROW_ON_ERROR);
    } catch (\JsonException) {
      $this->err('register: JSON de achados inválido');
      return 1;
    }
    if (!is_array($findings) || !array_is_list($findings)) {
      $this->err('register: o arquivo deve conter uma lista de achados');
      return 1;
    }
    try {
      $ids = $this->registry()->addMany($findings);
    } catch (\InvalidArgumentException $e) {
      $this->err('register: nenhum achado foi gravado: ' . $e->getMessage());
      return 1;
    }
    $this->say('importados: ' . implode(', ', $ids));
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
    // Resolve symlinks do ancestral existente antes de comparar com o repositório.
    $probe = $outAbs;
    while (!file_exists($probe) && dirname($probe) !== $probe) {
      $probe = dirname($probe);
    }
    $real = realpath($probe) ?: $probe;
    $repoReal = realpath($this->repoRoot) ?: $this->repoRoot;
    if ($real === $repoReal || str_starts_with($real . '/', $repoReal . '/')) {
      $this->err('build: o diretório de saída não pode ficar dentro do repositório (inclusive via link)');
      return 1;
    }
    // Nunca sobrescreve: cada build vai para um diretório novo, preservando o anterior.
    if (file_exists($outAbs) && (!is_dir($outAbs) || count(scandir($outAbs)) > 2)) {
      $this->err('build: o diretório de saída já existe e não está vazio; use um diretório novo (builds anteriores são preservados)');
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
    if ($transform->validate() !== []) {
      $this->err('build: configuração de escopo inválida; rode check para detalhes');
      return 1;
    }
    $maxBytes = (int) ($this->json($this->toolRoot . '/config/deploy.json')['max_bytes'] ?? 2097152);
    $report = ['tool' => 'aculta-deployer ' . self::VERSION, 'target' => 'production', 'files' => [], 'dropped' => [], 'skipped_binary' => [], 'replacements' => 0];
    if (!is_dir($outAbs) && !mkdir($outAbs, 0775, true)) {
      $this->err('build: não foi possível criar o diretório de saída');
      return 1;
    }
    try {
      foreach ($this->scopeFiles() as $rel) {
        if ($transform->isDropped($rel)) {
          $report['dropped'][] = $rel;
          continue;
        }
        $src = $this->repoRoot . '/' . $rel;
        if (filesize($src) > $maxBytes) {
          throw new \RuntimeException("arquivo acima do limite de $maxBytes bytes: $rel");
        }
        $raw = (string) file_get_contents($src);
        if (str_contains($raw, "\0")) {
          // Binário: não é reescrito. Se contiver host de teste, o build falha.
          if ($transform->countTestHosts($raw) > 0) {
            throw new \RuntimeException("binário com host de teste no escopo: $rel");
          }
          $content = $raw;
          $count = 0;
          $report['skipped_binary'][] = $rel;
        } else {
          [$content, $count] = $transform->applyHosts($raw);
        }
        $target = $outAbs . '/' . $rel;
        if (!is_dir(dirname($target)) && !mkdir(dirname($target), 0775, true)) {
          throw new \RuntimeException("não foi possível criar diretório para $rel");
        }
        file_put_contents($target, $content);
        $report['files'][] = ['path' => $rel, 'replacements' => $count, 'sha256' => hash('sha256', $content)];
        $report['replacements'] += $count;
      }
      // Autoverificação: nenhum host de teste pode restar nos arquivos gerados.
      foreach ($report['files'] as $f) {
        if (in_array($f['path'], $report['skipped_binary'], true)) {
          continue;
        }
        if ($transform->countTestHosts((string) file_get_contents($outAbs . '/' . $f['path'])) > 0) {
          throw new \RuntimeException('host de teste restou no arquivo gerado: ' . $f['path']);
        }
      }
    } catch (\RuntimeException $e) {
      $this->removeTree($outAbs);
      $this->err('build: ' . $e->getMessage() . ' (saída removida)');
      return 1;
    }
    file_put_contents($outAbs . '/deploy-report.json', json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
    $this->say(sprintf('build: %d arquivos gravados, %d removidos, %d binários preservados, %d substituições de host em %s',
      count($report['files']), count($report['dropped']), count($report['skipped_binary']), $report['replacements'], $out));
    return 0;
  }

  private function removeTree(string $dir): void {
    if (!is_dir($dir)) {
      return;
    }
    $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($it as $f) {
      $f->isDir() && !$f->isLink() ? rmdir($f->getPathname()) : unlink($f->getPathname());
    }
    rmdir($dir);
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
        if ($f->isFile() && !$f->isLink()) {
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

Códigos de saída: 0 sucesso; 1 erro de uso, validação ou fronteira; 2 bloqueado (entrada bloqueante aberta).

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
