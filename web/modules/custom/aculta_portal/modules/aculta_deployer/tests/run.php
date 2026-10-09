<?php

declare(strict_types=1);

// Testes determinísticos da CLI. Não usam Drupal, Drush nem vendor.
$toolRoot = dirname(__DIR__);
foreach (['Registry', 'Transform', 'Boundary', 'Cli'] as $class) {
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

echo $failures === 0 ? "tests: PASS\n" : "tests: FAIL ($failures)\n";
exit($failures === 0 ? 0 : 1);
