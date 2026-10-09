<?php

declare(strict_types=1);

// Verificações estáticas da fase 0.1.0-E. Não usam Drupal.
$root = dirname(__DIR__);
$failures = 0;
$assert = static function (bool $ok, string $name) use (&$failures): void {
  echo ($ok ? 'PASS ' : 'FAIL ') . $name . PHP_EOL;
  if (!$ok) {
    $failures++;
  }
};
$plugin = (string) file_get_contents($root . '/src/Plugin/simple_sitemap/UrlGenerator/PurposeNodeUrlGenerator.php');
$assert(!str_contains($plugin, '\\Drupal::'), 'plugin usa injeção de dependências, sem \\Drupal:: estático');
$assert(str_contains($plugin, "const ROOT_PURPOSES = ['support'];"), 'raiz do SUPPORT é a única raiz por rota');
$assert(str_contains($plugin, "'page', 'project', 'activity', 'document', 'wiki_entry', 'article'"), 'bundles elegíveis conforme a matriz da ADR-009');
$assert(str_contains($plugin, "field_domain_source"), 'origem do conteúdo vem de field_domain_source (R1)');
$assert(str_contains($plugin, 'id = "aculta_purpose_node"'), 'id do plugin declarado na anotação');
$assert(str_contains($plugin, 'canonicalRouteUrl') || str_contains($plugin, 'canonicalPathUrl'), 'URLs canônicas vêm do DomainPurposeManager (R7)');
$assert(str_contains($plugin, 'AnonymousUserSession'), 'elegibilidade verificada como anônimo (R4)');
echo $failures === 0 ? "tests: PASS\n" : "tests: FAIL ($failures)\n";
exit($failures === 0 ? 0 : 1);
