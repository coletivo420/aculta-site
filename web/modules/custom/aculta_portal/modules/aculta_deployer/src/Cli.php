<?php

declare(strict_types=1);

namespace AcultaDeployer;

/** Comandos da ferramenta. Não usa Drupal, Drush nem vendor. */
final class Cli {

  public const VERSION = '0.1.5';

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
      'robots' => $this->robots($options),
      'sitemap' => $this->sitemap($options),
      'secrets' => $this->secrets((string) ($argv[2] ?? ''), $options),
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

  /**
   * Política de indexação por ambiente: produção indexável em todos os hosts;
   * teste com noindex. GET somente leitura.
   *
   * @param array<string, string|bool> $o
   */
  private function robots(array $o): int {
    $env = is_string($o['env'] ?? null) ? $o['env'] : 'production';
    $policy = $this->json($this->toolRoot . '/config/deploy.json')['robots_policy'][$env] ?? null;
    if ($policy === null) {
      $this->err("robots: ambiente desconhecido: $env");
      return 1;
    }
    $code = 0;
    foreach ($policy['hosts'] as $url) {
      $res = Verify::fetchWithHeaders((string) $url);
      if ($res === null) {
        $this->say("FAIL $url: sem resposta");
        $code = 1;
        continue;
      }
      $value = Verify::robotsHeader($res['headers']);
      $noindex = Verify::isNoindex($value);
      $ok = $env === 'production' ? !$noindex : $noindex;
      $this->say(sprintf('%s %s: X-Robots-Tag=%s', $ok ? 'PASS' : 'FAIL', $url, $value ?? '(ausente)'));
      $code = $ok ? $code : 1;
    }
    if ($env === 'production') {
      $indexUrl = (string) ($this->json($this->toolRoot . '/config/deploy.json')['sitemap']['production']['index_url'] ?? '');
      foreach ($policy['hosts'] as $url) {
        $res = Verify::fetchWithHeaders(rtrim((string) $url, '/') . '/robots.txt');
        if ($res === null || Verify::statusCode($res['headers']) !== 200) {
          $this->say("FAIL $url robots.txt: sem resposta 200");
          $code = 1;
          continue;
        }
        $advertised = in_array($indexUrl, Verify::sitemapDirectives($res['body']), true);
        $blocked = Verify::disallowsRoot($res['body']);
        $ok = $advertised && !$blocked;
        $this->say(sprintf('%s %s robots.txt: Sitemap=%s, Disallow: / %s', $ok ? 'PASS' : 'FAIL', $url, $advertised ? 'índice' : 'ausente', $blocked ? 'presente' : 'ausente'));
        $code = $ok ? $code : 1;
      }
    }
    foreach ($policy['private_probes'] ?? [] as $url) {
      $res = Verify::fetchWithHeaders((string) $url);
      if ($res === null) {
        $this->say("FAIL $url: sem resposta (caminho privado)");
        $code = 1;
        continue;
      }
      $value = Verify::robotsHeader($res['headers']);
      $status = Verify::statusCode($res['headers']);
      $meta = Verify::metaNoindex($res['body']);
      // Privado: noindex no cabeçalho ou no HTML, ou status que não expõe conteúdo.
      $ok = Verify::isNoindex($value) || $meta || Verify::isRefusedStatus($status);
      $this->say(sprintf('%s %s (privado): status=%s X-Robots-Tag=%s meta=%s',
        $ok ? 'PASS' : 'FAIL', $url, $status ?? '?', $value ?? '(ausente)', $meta ? 'noindex' : 'ausente'));
      $code = $ok ? $code : 1;
    }
    $this->say("robots ($env): " . ($code === 0 ? 'PASS' : 'FAIL'));
    return $code;
  }

  /**
   * Descoberta e sitemaps por ambiente (GET somente leitura):
   * 1) o índice responde e cada filho está na base do ambiente (sem host de teste
   *    em produção, e sem host de produção no servidor de testes além do canônico);
   * 2) as URLs dos filhos pertencem aos hosts de produção da política e esses hosts
   *    respondem (cross-host, por exemplo apoio.aculta.org).
   *
   * @param array<string, string|bool> $o
   */
  private function sitemap(array $o): int {
    $env = is_string($o['env'] ?? null) ? $o['env'] : 'production';
    $cfg = $this->json($this->toolRoot . '/config/deploy.json');
    $policy = $cfg['sitemap'][$env] ?? null;
    if ($policy === null) {
      $this->err("sitemap: ambiente desconhecido: $env");
      return 1;
    }
    $allowed = [];
    foreach ($cfg['robots_policy']['production']['hosts'] ?? [] as $url) {
      $allowed[] = Verify::hostOf((string) $url);
    }
    $base = Verify::hostOf((string) $policy['index_base']);
    $code = 0;
    $index = Verify::fetchWithHeaders((string) $policy['index_url']);
    $indexLocs = $index === null ? null : Verify::xmlLocs($index['body']);
    if ($indexLocs === null || $indexLocs === []) {
      $this->say("FAIL {$policy['index_url']}: índice ausente, sem resposta ou sem <loc>");
      $this->say("sitemap ($env): FAIL");
      return 1;
    }
    $this->say("PASS {$policy['index_url']}: índice com " . count($indexLocs) . ' sitemap(s)');
    $contentHosts = [];
    foreach ($indexLocs as $child) {
      $sameBase = Verify::hostOf($child) === $base;
      if (!$sameBase) {
        $code = 1;
      }
      $this->say(sprintf('%s índice aponta para %s (base do ambiente: %s)', $sameBase ? 'PASS' : 'FAIL', $child, $base));
      $res = Verify::fetchWithHeaders($child);
      $locs = $res === null || Verify::statusCode($res['headers']) !== 200 ? null : Verify::xmlLocs($res['body']);
      if ($locs === null || $locs === []) {
        $this->say("FAIL $child: filho sem resposta 200 ou sem <loc>");
        $code = 1;
        continue;
      }
      foreach ($locs as $u) {
        $h = Verify::hostOf($u);
        if ($h !== null && !in_array($h, $allowed, true)) {
          $this->say("FAIL $u: host fora da política de produção");
          $code = 1;
        }
        $contentHosts[$h] = true;
      }
      $this->say(sprintf('PASS %s: %d URL(s) de conteúdo', $child, count($locs)));
    }
    foreach (array_keys($contentHosts) as $h) {
      // No servidor de testes, o host de conteúdo é verificado pelo equivalente de teste.
      $target = $env === 'test' ? Verify::testEquivalent((string) $h) : (string) $h;
      $probe = Verify::fetchWithHeaders("https://$target/");
      $ok = $probe !== null && (Verify::statusCode($probe['headers']) ?? 500) < 400;
      $this->say(sprintf('%s host de conteúdo %s (%s) responde %s', $ok ? 'PASS' : 'FAIL', $h, $target, $probe === null ? 'sem resposta' : (string) Verify::statusCode($probe['headers'])));
      $code = $ok ? $code : 1;
    }
    $this->say("sitemap ($env): " . ($code === 0 ? 'PASS' : 'FAIL'));
    return $code;
  }

  /**
   * Arquivo local de credenciais (ACULTA Secrets Contract). Nunca imprime valores: só nomes e
   * motivos. Padrão do arquivo: <repo>/secrets/aculta.secrets.env (ignorado pelo Git, fora de web/).
   *   secrets check  [--env=production|test] [--file=PATH]
   *   secrets export --env=production|test --out=PATH [--file=PATH]
   *
   * @param array<string, string|bool> $o
   */
  private function secrets(string $sub, array $o): int {
    $env = is_string($o['env'] ?? null) ? $o['env'] : 'production';
    if (!in_array($sub, ['check', 'export'], true)) {
      $this->err('uso: secrets check|export [--env=production|test] [--file=PATH] [--out=PATH]');
      return 1;
    }
    if (!in_array($env, ['production', 'test'], true)) {
      $this->err("secrets: ambiente desconhecido: $env");
      return 1;
    }
    $contract = $this->json($this->repoRoot . '/config/secrets-contract.json');
    if (($problems = Secrets::contractProblems($contract)) !== []) {
      foreach ($problems as $p) {
        $this->err("secrets: contrato inválido: $p");
      }
      return 1;
    }
    $file = is_string($o['file'] ?? null) ? $o['file'] : $this->repoRoot . '/secrets/aculta.secrets.env';
    $values = $this->secretsValidate($file, $env, $contract);
    if ($values === null) {
      $this->say("secrets ($env): FAIL");
      return 1;
    }
    if ($sub === 'check') {
      $this->say("secrets ($env): PASS (" . count($values) . ' variável(eis) no arquivo; valores não exibidos)');
      return 0;
    }
    return $this->secretsExport($o, $env, $contract, $values);
  }

  /**
   * Valida o arquivo: existência, permissões, fora do document root, ignorado pelo Git (se
   * estiver no repositório), sintaxe, nomes do contrato e obrigatórios do ambiente.
   *
   * @param array<string, mixed> $contract
   * @return array<string, string>|null valores lidos, ou null se alguma verificação falhou
   */
  private function secretsValidate(string $file, string $env, array $contract): ?array {
    $real = realpath($file);
    if ($real === false || !is_file($real) || !is_readable($real)) {
      $this->say("FAIL arquivo ausente ou ilegível: $file");
      return null;
    }
    $ok = true;
    $mode = fileperms($real) & 0777;
    if (($m = Secrets::modeProblem($mode)) !== null) {
      $this->say(sprintf('FAIL permissões %04o: %s', $mode, $m));
      $ok = false;
    }
    $webRoot = realpath($this->repoRoot . '/web');
    if ($webRoot !== false && Secrets::isInside($real, $webRoot)) {
      $this->say('FAIL o arquivo está dentro do document root (web/)');
      $ok = false;
    }
    $repoReal = realpath($this->repoRoot);
    if ($repoReal !== false && Secrets::isInside($real, $repoReal)) {
      $cmd = 'git -C ' . escapeshellarg($repoReal) . ' check-ignore -q ' . escapeshellarg($real) . ' 2>/dev/null';
      exec($cmd, $unused, $rc);
      if ($rc !== 0) {
        $this->say('FAIL o arquivo dentro do repositório não é ignorado pelo Git');
        $ok = false;
      }
    }
    $values = Secrets::parse((string) file_get_contents($real));
    if ($values === null) {
      $this->say('FAIL linhas fora do formato NAME=value (conteúdo não exibido)');
      return null;
    }
    $allowed = Secrets::allowedNames($contract);
    foreach (array_keys($values) as $name) {
      if (!in_array($name, $allowed, true)) {
        $this->say("FAIL variável fora do contrato: $name");
        $ok = false;
      }
    }
    foreach (Secrets::requiredNames($contract, $env) as $name) {
      if (!isset($values[$name])) {
        $this->say("FAIL obrigatória ausente em $env: $name");
        $ok = false;
      } elseif ($values[$name] === '') {
        $this->say("FAIL obrigatória vazia em $env: $name");
        $ok = false;
      }
    }
    return $ok ? $values : null;
  }

  /**
   * Grava as variáveis do contrato presentes no arquivo em um arquivo novo, 0600, fora do
   * repositório. Não sobrescreve. Serve à importação manual pós-deploy.
   *
   * @param array<string, string|bool> $o
   * @param array<string, mixed> $contract
   * @param array<string, string> $values
   */
  private function secretsExport(array $o, string $env, array $contract, array $values): int {
    $out = is_string($o['out'] ?? null) ? $o['out'] : '';
    if ($out === '') {
      $this->err('secrets export: informe --out=PATH (arquivo novo, fora do repositório)');
      return 1;
    }
    if (file_exists($out)) {
      $this->err('secrets export: o destino já existe; escolha outro caminho (não sobrescreve)');
      return 1;
    }
    $dirReal = realpath(dirname($out));
    $repoReal = realpath($this->repoRoot);
    if ($dirReal === false) {
      $this->err('secrets export: o diretório de destino não existe');
      return 1;
    }
    if ($repoReal !== false && Secrets::isInside($dirReal, $repoReal)) {
      $this->err('secrets export: o destino não pode ficar dentro do repositório');
      return 1;
    }
    $lines = [];
    foreach (Secrets::allowedNames($contract) as $name) {
      if (isset($values[$name])) {
        $lines[] = $name . '=' . $values[$name];
      }
    }
    $target = $dirReal . DIRECTORY_SEPARATOR . basename($out);
    $fh = @fopen($target, 'xb');
    if ($fh === false) {
      $this->err('secrets export: não foi possível criar o destino');
      return 1;
    }
    fwrite($fh, implode("\n", $lines) . "\n");
    fclose($fh);
    chmod($target, 0600);
    $this->say("secrets export ($env): " . count($lines) . " variável(eis) gravada(s) em $target (0600); valores não exibidos");
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
    $robots = $this->json($this->toolRoot . '/config/deploy.json')['robots_policy']['production'] ?? [];
    if (Verify::isNoindex($robots['x_robots_tag'] ?? null)) {
      $this->err('build: a política de produção envia noindex; o build de produção não é gerado');
      return 1;
    }
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
    file_put_contents($outAbs . '/deploy-policy.json', json_encode(['robots' => $robots], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
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
  aculta-deployer robots [--env=production|test]  GET somente leitura: X-Robots-Tag, caminhos privados e robots.txt (Sitemap)
  aculta-deployer secrets check [--env=production|test] [--file=PATH]  valida o arquivo local de credenciais (sem valores)
  aculta-deployer secrets export --env=production|test --out=PATH  grava cópia 0600 fora do repositório, para importação manual
  aculta-deployer sitemap [--env=production|test]  GET somente leitura: índice, filhos e hosts de conteúdo (cross-host)
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
