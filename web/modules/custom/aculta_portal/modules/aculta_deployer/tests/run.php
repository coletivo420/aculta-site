<?php

declare(strict_types=1);

// Testes determinísticos da CLI. Não usam Drupal, Drush nem vendor.
$toolRoot = dirname(__DIR__);
foreach (['Registry', 'Transform', 'Boundary', 'Verify', 'Cli'] as $class) {
  require_once $toolRoot . '/src/' . $class . '.php';
}

use AcultaDeployer\Boundary;
use AcultaDeployer\Registry;
use AcultaDeployer\Transform;

$failures = 0;
$assert = static function (bool $ok, string $name) use (&$failures): void {
  echo ($ok ? 'PASS ' : 'FAIL ') . $name . PHP_EOL;
  if (!$ok) {
    $failures++;
  }
};

$config = json_decode((string) file_get_contents($toolRoot . '/config/deploy.json'), true, 512, JSON_THROW_ON_ERROR);
$t = new Transform($config);

// Substituição de host: preserva o prefixo e troca o sufixo.
[$out, $n] = $t->applyHosts('https://apoio.aculta.toca.net.br/');
$assert($out === 'https://apoio.aculta.org/' && $n === 1, 'host de teste com prefixo vira produção');
[$out] = $t->applyHosts('aculta.toca.net.br');
$assert($out === 'aculta.org', 'host raiz vira produção');
[$out, $n] = $t->applyHosts('aculta.toca.net.br.evil.com');
$assert($out === 'aculta.toca.net.br.evil.com' && $n === 0, 'host com rótulo extra não é reescrito');
[$out, $n] = $t->applyHosts('notaculta.toca.net.br');
$assert($n === 0, 'host que termina no sufixo sem fronteira não é reescrito');
[$out, $n] = $t->applyHosts('aculta.toca.net.brx');
$assert($n === 0, 'sufixo seguido de letra não é reescrito');

// Escopo e remoção de aliases de teste.
$assert($t->isDropped('config/sync/domain_alias.alias.apoio_aculta_toca_net_br.yml'), 'alias de homologação é removido no build');
$assert($t->isDropped('config/sync/domain_alias.alias.aculta_test_8080.yml'), 'alias de porta 8080 é removido no build');
$assert(!$t->isDropped('config/sync/domain_alias.settings.yml'), 'configuração geral de aliases é mantida');
$assert($t->inScope('config/sync/system.site.yml') && $t->inScope('config/sync/domain/apoio_aculta_org/system.site.yml'), 'config/sync entra no escopo');
$assert(!$t->inScope('docs/architecture/multidomain.md') && !$t->inScope('web/modules/custom/aculta_portal/x.php'), 'documentação e código ficam fora do escopo');

// Registro: validação e geração de id.
$tmp = sys_get_temp_dir() . '/aculta-deployer-test-' . getmypid() . '.json';
file_put_contents($tmp, json_encode(['schema' => 1, 'entries' => [[
  'id' => 'DEP-0001', 'kind' => 'canonical', 'status' => 'open', 'blocking' => true,
  'page' => 'p', 'current' => 'c', 'expected_production' => 'e', 'reason' => 'r', 'owner' => 'o', 'decision' => 'pendente',
]]]));
$reg = new Registry($tmp);
$assert($reg->validate() === [], 'registro válido não gera erros');
$assert(count($reg->openBlocking()) === 1, 'entrada bloqueante aberta é contada');
$id = $reg->add(['kind' => 'sitemap', 'page' => 'p2', 'current' => 'c', 'expected_production' => 'e', 'reason' => 'r', 'owner' => 'o', 'blocking' => false]);
$assert($id === 'DEP-0002', 'novo registro recebe o próximo id');
file_put_contents($tmp, json_encode(['schema' => 1, 'entries' => [['id' => 'X1', 'kind' => 'bogus', 'status' => 'open']]]));
$assert(count((new Registry($tmp))->validate()) >= 3, 'registro com id, kind e campos inválidos é reprovado');
try {
  (new Registry($tmp))->add(['kind' => 'canonical', 'page' => 'p', 'current' => 'c', 'expected_production' => 'e', 'reason' => 'r', 'owner' => 'o']);
  $assert(false, 'add recusa registro inválido');
} catch (\InvalidArgumentException) {
  $assert(true, 'add recusa registro inválido');
}
@unlink($tmp);

// Fronteiras: o código atual passa, e referência proibida é detectada.
$repo = dirname($toolRoot, 6);
$rules = json_decode((string) file_get_contents($toolRoot . '/config/boundary.json'), true, 512, JSON_THROW_ON_ERROR);
$assert((new Boundary($repo, $toolRoot, $rules))->check() === [], 'fronteiras atuais passam');
$probe = $toolRoot . '/src/__probe.php';
// The probe is assembled at runtime so this file never contains the forbidden term.
$needle = 'Dru' . 'pal' . '::';
file_put_contents($probe, "<?php \$x = '" . $needle . "service';\n");
$assert(count((new Boundary($repo, $toolRoot, $rules))->check()) >= 1, 'dependência de Drupal no código da ferramenta é detectada');
unlink($probe);

// Fase 1: configuração, registro corrompido e escrita atômica.
$bad = new Transform(['scope' => ['(unclosed'], 'drop' => [], 'host_rules' => []]);
$assert(count($bad->validate()) >= 2, 'configuração com regex inválido e sem host_rules é reprovada');
$assert((new Transform($config))->validate() === [], 'configuração atual é válida');
$tmpDir = sys_get_temp_dir() . '/aculta-deployer-p1-' . getmypid();
@mkdir($tmpDir);
$corrupt = $tmpDir . '/reg.json';
file_put_contents($corrupt, '{not json');
$threw = false;
try {
  new Registry($corrupt);
} catch (\JsonException) {
  $threw = true;
}
$assert($threw, 'registro com JSON corrompido gera erro explícito');
file_put_contents($corrupt, json_encode(['schema' => 1, 'entries' => []]));
$reg2 = new Registry($corrupt);
$reg2->add(['kind' => 'other', 'page' => 'p', 'current' => 'c', 'expected_production' => 'e', 'reason' => 'r', 'owner' => 'o']);
$assert(!file_exists($corrupt . '.tmp-' . getmypid()), 'gravação atômica não deixa arquivo temporário');
$assert((new Registry($corrupt))->entries()[0]['id'] === 'DEP-0001', 'registro gravado é relido com o id criado');
array_map('unlink', glob($tmpDir . '/*') ?: []);
@rmdir($tmpDir);

// Fase 2: cobertura e segurança do build (executa a CLI real em diretórios temporários).
$base = sys_get_temp_dir() . '/aculta-deployer-p2-' . getmypid();
@mkdir($base);
$cli = new AcultaDeployer\Cli($toolRoot);
$quiet = static function (callable $fn): int {
  ob_start();
  try {
    return $fn();
  } finally {
    ob_end_clean();
  }
};
$rc1 = $quiet(fn() => $cli->run(['x', 'build', '--out=' . $base . '/a', '--allow-open-blocking']));
$rc2 = $quiet(fn() => $cli->run(['x', 'build', '--out=' . $base . '/b', '--allow-open-blocking']));
$assert($rc1 === 0 && $rc2 === 0, 'build de ensaio gera saída em dois diretórios novos');
$ra = json_decode((string) file_get_contents($base . '/a/deploy-report.json'), true);
$rb = json_decode((string) file_get_contents($base . '/b/deploy-report.json'), true);
$assert($ra['files'] === $rb['files'] && $ra['dropped'] === $rb['dropped'], 'build é idempotente: hashes e remoções iguais');
$leak = false;
foreach ($ra['files'] as $f) {
  if (str_contains((string) file_get_contents($base . '/a/' . $f['path']), 'toca.net.br')) {
    $leak = true;
  }
}
$assert(!$leak, 'nenhum arquivo gerado contém host de teste');
$assert($quiet(fn() => $cli->run(['x', 'build', '--out=' . $base . '/a', '--allow-open-blocking'])) === 1, 'build não sobrescreve saída existente');
$assert($quiet(fn() => $cli->run(['x', 'build', '--out=' . dirname($toolRoot) . '/dentro-do-repo', '--allow-open-blocking'])) === 1, 'build recusa saída dentro do repositório');
@symlink($repo . '/config', $base . '/link-para-repo');
$assert($quiet(fn() => $cli->run(['x', 'build', '--out=' . $base . '/link-para-repo/saida', '--allow-open-blocking'])) === 1, 'build recusa saída por link simbólico para o repositório');
$assert($quiet(fn() => $cli->run(['x', 'build', '--out=' . $base . '/c'])) === 2, 'build com entradas bloqueantes exige --allow-open-blocking');
// Limpeza.
foreach (['a', 'b'] as $d) {
  $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base . '/' . $d, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
  foreach ($it as $f) {
    $f->isDir() ? rmdir($f->getPathname()) : unlink($f->getPathname());
  }
  rmdir($base . '/' . $d);
}
@unlink($base . '/link-para-repo');
@rmdir($base);

// Fase 3: importação de achados (tudo ou nada).
$regFile = sys_get_temp_dir() . '/aculta-deployer-p3-' . getmypid() . '.json';
file_put_contents($regFile, json_encode(['schema' => 1, 'entries' => []]));
$good = [
  ['kind' => 'other', 'page' => 'p1', 'current' => 'c', 'expected_production' => 'e', 'reason' => 'r', 'owner' => 'o', 'extra' => 'ignorado'],
  ['kind' => 'link', 'page' => 'p2', 'current' => 'c', 'expected_production' => 'e', 'reason' => 'r', 'owner' => 'o'],
];
$gReg = new Registry($regFile);
$ids = $gReg->addMany($good);
$assert($ids === ['DEP-0001', 'DEP-0002'] && !array_key_exists('extra', (new Registry($regFile))->entries()[0]), 'importação grava achados válidos e descarta campos extras');
$badFindings = [$good[0], ['kind' => 'canonical', 'page' => 'p3']];
$before = file_get_contents($regFile);
$threwBad = false;
try {
  (new Registry($regFile))->addMany($badFindings);
} catch (\InvalidArgumentException) {
  $threwBad = true;
}
$assert($threwBad && file_get_contents($regFile) === $before, 'importação com um achado inválido não grava nada');
unlink($regFile);

// Fase 5: verificação pós-deploy somente leitura.
$assert(AcultaDeployer\Verify::isAllowedUrl('https://apoio.aculta.org/'), 'verify aceita URL https válida');
$assert(!AcultaDeployer\Verify::isAllowedUrl('http://apoio.aculta.org/'), 'verify recusa http');
$assert(!AcultaDeployer\Verify::isAllowedUrl('https://user:senha@apoio.aculta.org/'), 'verify recusa URL com credenciais');
$assert(!AcultaDeployer\Verify::isAllowedUrl('https://'), 'verify recusa URL sem host');
$assert(AcultaDeployer\Verify::evaluate('<link rel="canonical" href="https://apoio.aculta.org/">', '<link rel="canonical" href="https://apoio.aculta.org/">'), 'verify encontra o valor esperado no corpo');
$assert(!AcultaDeployer\Verify::evaluate('<link rel="canonical" href="https://apoio.aculta.toca.net.br/">', '<link rel="canonical" href="https://apoio.aculta.org/">'), 'verify reprova canonical de teste');
$assert(!AcultaDeployer\Verify::evaluate('qualquer coisa', ''), 'verify reprova expectativa vazia');
$regPath = $toolRoot . '/registry/deploy-registry.json';
$before = hash_file('sha256', $regPath);
ob_start();
$rcVerify = (new AcultaDeployer\Cli($toolRoot))->run(['x', 'verify']);
ob_end_clean();
$assert($rcVerify === 1 || $rcVerify === 0, 'verify executa e retorna código de saída válido');
$assert(hash_file('sha256', $regPath) === $before, 'verify não altera o registro (somente leitura)');

echo $failures === 0 ? "tests: PASS\n" : "tests: FAIL ($failures)\n";
exit($failures === 0 ? 0 : 1);
