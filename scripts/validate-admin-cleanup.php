<?php
/** Read-only checks of local cleanup and the retained administrative contract. */
use Symfony\Component\Yaml\Yaml;
// Reviewed inventory of Views. Portal and enabled modules provide the course,
// Wiki, Commerce, LMS and Social Auth views; any other Views fail the check.
$expected = ['aculta_activities','aculta_documents','aculta_news','aculta_projects','home_editorial_highlights','block_content','content','files','media','media_library','redirect','user_admin_people','watchdog','webform_submissions','activities_selection','aculta_related_activities','aculta_related_news','agreements','commerce_cart_block','commerce_cart_form','commerce_carts','commerce_checkout_order_summary','commerce_order_item_table','commerce_order_item_table_admin','commerce_order_payments','commerce_orders','commerce_stores','commerce_user_orders','courses','courses_admin','courses_catalog','group_members','lessons_selection','locked_content','moderated_content','profiles','social_auth_profiles','user_agreements','wiki_categories','wiki_entries'];
$views = \Drupal\views\Entity\View::loadMultiple();
sort($expected); $actual = array_keys($views); sort($actual);
if ($actual !== $expected) throw new RuntimeException('Unexpected retained Views.');
// Curated administrative Views must document their purpose. Views shipped by
// Commerce, LMS, Wiki and Social Auth are owned by those modules; their
// descriptions are not part of this cleanup contract.
$curated = ['aculta_activities','aculta_documents','aculta_news','aculta_projects','home_editorial_highlights','block_content','content','files','media','media_library','redirect','user_admin_people','watchdog','webform_submissions'];
foreach ($views as $view) {
  if (in_array($view->id(), $curated, TRUE) && !$view->get('description')) throw new RuntimeException('Missing View description: ' . $view->id());
  $view->getExecutable()->initDisplay();
}
foreach (['admin','content','navigation-user-links','account','main','aculta-footer-content','aculta-footer-institution','aculta-footer-participation'] as $id) {
  $menu = \Drupal\system\Entity\Menu::load($id);
  if (!$menu || !$menu->getDescription()) throw new RuntimeException('Missing menu/reference: ' . $id);
}
if (\Drupal::moduleHandler()->moduleExists('contact')) throw new RuntimeException('Old Contact module remains.');
if (!\Drupal\webform\Entity\Webform::load('aculta_contact') || \Drupal\webform\Entity\Webform::load('contact')) throw new RuntimeException('Unexpected Webforms.');
foreach (['image','document','remote_video'] as $id) if (!\Drupal\media\Entity\MediaType::load($id)) throw new RuntimeException('Missing active editor media type.');
$media = array_keys(\Drupal\media\Entity\MediaType::loadMultiple()); sort($media);
if ($media !== ['document','image','remote_video']) throw new RuntimeException('Unexpected media types.');
$before = json_decode(file_get_contents(dirname(__DIR__) . '/tmp/admin-structure-audit.json'), TRUE, 512, JSON_THROW_ON_ERROR);
foreach (['node','block_content','menu_link_content'] as $type) {
  $entities = \Drupal::entityTypeManager()->getStorage($type)->loadMultiple();
  if (count($entities) !== count($before['content'][$type])) throw new RuntimeException('Existing content count changed: ' . $type);
}
$config = \Drupal::service('config.storage');
$names = $config->listAll();
foreach ($names as $name) foreach ($config->read($name)['dependencies']['config'] ?? [] as $dependency) if (!$config->exists($dependency)) throw new RuntimeException("Missing dependency $dependency in $name");
$report = \Drupal::state()->get('aculta.admin_cleanup_done');
foreach ($report['deleted'] as $name) if ($config->exists($name)) throw new RuntimeException('Removed config still present.');
$count = 0;
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(dirname(__DIR__) . '/config/sync', FilesystemIterator::SKIP_DOTS));
foreach ($iterator as $file) if ($file->getExtension() === 'yml') {Yaml::parseFile($file->getPathname()); $count++;}
echo "14 required Views, 8 menus, 3 editor media types; contact migration intact.\n";
echo "Editorial entity counts preserved; configuration dependencies intact; $count YAML files parsed.\n";
$switcher = \Drupal::service('account_switcher');
$switcher->switchTo(\Drupal\user\Entity\User::load(1));
try {
  foreach (['/admin/content','/admin/content/block','/admin/content/media','/admin/content/files','/admin/people','/admin/reports/dblog','/admin/config/search/redirect','/admin/structure/webform','/admin/structure/views','/admin/structure/menu'] as $path) {
    $routes = \Drupal::service('router.route_provider')->getRoutesByPattern($path);
    if (!$routes->count()) throw new RuntimeException('Required administrative route missing: ' . $path);
  }
} finally {$switcher->switchBack();}
$changes = ['changed' => [], 'deleted' => []];
foreach (array_unique(array_merge(array_keys($before['configuration']), $config->listAll())) as $name) {
  $value = $config->read($name);
  if ($value === ($before['configuration'][$name] ?? FALSE)) continue;
  $changes[$value === FALSE ? 'deleted' : 'changed'][] = $name;
}
file_put_contents(dirname(__DIR__) . '/tmp/admin-cleanup-final-diff.json', json_encode($changes, JSON_PRETTY_PRINT));
echo '10 administrative routes present; ' . count($changes['deleted']) . ' configurations removed, ' . count($changes['changed']) . ' updated.' . PHP_EOL;
