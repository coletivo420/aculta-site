<?php
/** Assertions and public-content inventory, excluding private users and form submissions. */
if (!in_array(\Drupal::request()->getHost(), ['localhost', '127.0.0.1'], TRUE)) throw new RuntimeException('Local only.');
$assert = static function(bool $condition, string $message): void { if (!$condition) throw new RuntimeException($message); echo "$message: OK\n"; };
$assert(\Drupal::config('system.site')->get('mail') === '4e20coletivo@gmail.com', 'Institutional site mail');
$assert(\Drupal::config('system.site')->get('name') === 'Associação Cultural Antiproibicionista', 'Public identity');
$assert(!isset(\Drupal::config('core.extension')->get('theme')['olivero']), 'Olivero uninstalled');
$active = \Drupal::service('config.storage'); $sync = new \Drupal\Core\Config\FileStorage(dirname(__DIR__) . '/config/sync');
$differences = [];
foreach (array_unique(array_merge([''], $active->getAllCollectionNames(), $sync->getAllCollectionNames())) as $collection) {
  $a = $collection === '' ? $active : $active->createCollection($collection); $s = $collection === '' ? $sync : $sync->createCollection($collection);
  foreach (array_unique(array_merge($a->listAll(), $s->listAll())) as $name) {
    // Mail is environment-bound: the Homelab Runtime has no SMTP credentials and
    // keeps PHP mail on purpose. The versioned values are asserted below instead.
    if ($collection === '' && in_array($name, ['smtp.settings', 'system.mail'], TRUE)) continue;
    if ($a->read($name) != $s->read($name)) $differences[] = "$collection:$name";
  }
}
$assert(!$differences, 'Active configuration matches config/sync (all collections, excluding environment-bound mail)');
$versioned_mail = $sync->read('smtp.settings');
$assert(($versioned_mail['smtp_on'] ?? FALSE) === TRUE, 'Versioned SMTP is enabled (smtp.settings smtp_on).');
$assert(($sync->read('system.mail')['interface']['default'] ?? NULL) === 'SMTPMailSystem', 'Versioned default mailer is SMTPMailSystem.');
$assert(($sync->read('system.mail')['interface']['webform'] ?? NULL) === 'SMTPMailSystem', 'Versioned webform mailer is SMTPMailSystem.');
$assert(($versioned_mail['smtp_allowhtml'] ?? FALSE) === TRUE, 'Versioned SMTP allows HTML (webform notifications).');
$manifest = ['note' => 'Content is NOT included in config export. Preserve IDs/UUIDs during the separately planned secure migration; this is an inventory, not a database dump.', 'entities' => []];
foreach (['node', 'block_content', 'menu_link_content', 'path_alias', 'redirect', 'media', 'file'] as $type) {
  foreach (\Drupal::entityTypeManager()->getStorage($type)->loadMultiple() as $entity) $manifest['entities'][$type][] = ['id' => $entity->id(), 'uuid' => $entity->uuid(), 'bundle' => $entity->bundle(), 'label' => $entity->label(), 'published' => $entity instanceof \Drupal\Core\Entity\EntityPublishedInterface ? $entity->isPublished() : NULL];
}
foreach (['node_type','block_content_type','menu'] as $type) foreach (\Drupal::entityTypeManager()->getStorage($type)->loadMultiple() as $entity) {
  $assert((bool) $entity->getDescription(), 'Administrative description ' . $type . ':' . $entity->id());
}
$templates = glob(DRUPAL_ROOT . '/themes/custom/aculta420/templates/*.html.twig');
$twig = \Drupal::service('twig');
foreach ($templates as $path) {
  $name = str_replace('\\', '/', substr($path, strlen(DRUPAL_ROOT) + 1));
  $twig->compileSource(new \Twig\Source(file_get_contents($path), $name, $path));
}
echo count($templates) . " custom Twig templates compile: OK\n";
$assets = \Drupal::entityTypeManager()->getStorage('file')->loadMultiple();
$assert(!$assets, 'No managed images/PDFs/logos require transfer');
file_put_contents(__DIR__ . '/institution/FINAL-CONTENT-MANIFEST.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n");
echo "Content manifest written without credentials or submissions.\n";
