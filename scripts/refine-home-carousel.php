<?php

/** Local-only, one-time editorial carousel setup and targeted config export. */
use Drupal\node\Entity\NodeType;
use Drupal\node\Entity\Node;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\views\Entity\View;
use Drupal\block\Entity\Block;
use Symfony\Component\Yaml\Yaml;

if (!in_array(\Drupal::request()->getHost(), ['localhost', '127.0.0.1'], TRUE)) {
  throw new RuntimeException('Local site only.');
}
if (\Drupal::state()->get('aculta.home_carousel_ready')) {
  echo "Already configured; edit through Drupal.\n";
  return;
}
if (!\Drupal::moduleHandler()->moduleExists('vvjb')) {
  throw new RuntimeException('Enable VVJB first.');
}
$names = ['core.extension'];
$type = 'editorial_highlight';
NodeType::create(['type' => $type, 'name' => 'Destaque editorial', 'description' => 'Destaques administráveis do carrossel da Home.', 'new_revision' => TRUE, 'display_submitted' => FALSE])->save();
$names[] = 'node.type.' . $type;
$fields = [
  'field_category' => ['string', 'Categoria', 'string_textfield', 'string'],
  'field_summary' => ['string_long', 'Resumo', 'string_textarea', 'basic_string'],
  'field_complement' => ['string_long', 'Complemento', 'string_textarea', 'basic_string'],
  'field_link' => ['link', 'Link e texto do CTA', 'link_default', 'link'],
  'field_weight' => ['integer', 'Ordem', 'number', 'number_integer'],
];
$form = EntityFormDisplay::create(['targetEntityType' => 'node', 'bundle' => $type, 'mode' => 'default', 'status' => TRUE]);
$display = EntityViewDisplay::create(['targetEntityType' => 'node', 'bundle' => $type, 'mode' => 'default', 'status' => TRUE]);
$form->setComponent('title', ['type' => 'string_textfield', 'weight' => -10]);
$form->setComponent('status', ['type' => 'boolean_checkbox', 'weight' => 20]);
$form->setComponent('revision_log', ['type' => 'string_textarea', 'weight' => 25]);
foreach ($fields as $name => [$kind, $label, $widget, $formatter]) {
  if (!FieldStorageConfig::loadByName('node', $name)) {
    FieldStorageConfig::create(['entity_type' => 'node', 'field_name' => $name, 'type' => $kind, 'cardinality' => 1])->save();
    $names[] = 'field.storage.node.' . $name;
  }
  $config = ['entity_type' => 'node', 'bundle' => $type, 'field_name' => $name, 'label' => $label, 'required' => in_array($name, ['field_category', 'field_summary', 'field_weight']), 'default_value' => $name === 'field_weight' ? [['value' => 0]] : []];
  if ($name === 'field_link') $config['settings'] = ['link_type' => 1, 'title' => 2];
  FieldConfig::create($config)->save();
  $names[] = 'field.field.node.' . $type . '.' . $name;
  $form->setComponent($name, ['type' => $widget, 'weight' => array_search($name, array_keys($fields))]);
  $display->setComponent($name, ['type' => $formatter, 'label' => 'hidden', 'weight' => array_search($name, array_keys($fields)), 'settings' => $name === 'field_link' ? ['trim_length' => 0, 'url_only' => FALSE, 'url_plain' => FALSE, 'rel' => '', 'target' => ''] : []]);
}
$display->removeComponent('field_weight');
$display->removeComponent('links');
$form->save(); $display->save();
$names[] = 'core.entity_form_display.node.' . $type . '.default';
$names[] = 'core.entity_view_display.node.' . $type . '.default';
$items = [
  ['COMUNICAÇÃO • CULTURA', 'INFORMAÇÃO TAMBÉM É CULTURA', 'Produzir cultura também significa produzir conhecimento. Entrevistas, publicações e projetos como o PodPlant420 aproximam diferentes experiências e saberes.', 'Nossa comunicação cria espaços de escuta e diálogo sobre cultura, saúde, direitos e sociedade.', '/noticias', 'ACOMPANHE AS PUBLICAÇÕES'],
  ['CUIDADO • DIREITOS', 'CUIDADO, INFORMAÇÃO E DIREITOS', 'A redução de danos faz parte da atuação social da Associação, com informação, autonomia e cuidado em atividades culturais e comunitárias.', 'Também acompanhamos debates sobre direitos humanos e políticas públicas, com respeito à dignidade das pessoas.', '/institucional', 'CONHEÇA NOSSA ATUAÇÃO'],
  ['CULTURA • CONHECIMENTO', 'CULTURA, CUIDADO E PRODUÇÃO DE CONHECIMENTO', 'Em 2026, a experiência do Bloco Sativa no Carnaval de Goiânia foi objeto de trabalho na Escola de Governo da Fiocruz, abordando percussão popular, redução de danos e redes de cuidado.', 'Práticas culturais comunitárias também podem produzir conhecimento, vínculos e cuidado.', '/institucional', 'CONHEÇA NOSSA HISTÓRIA'],
];
$ids = [];
foreach ($items as $weight => [$category, $title, $summary, $complement, $path, $cta]) {
  $node = Node::create(['type' => $type, 'title' => $title, 'langcode' => 'pt-br', 'status' => 1, 'uid' => 1, 'field_category' => $category, 'field_summary' => $summary, 'field_complement' => $complement, 'field_weight' => $weight, 'field_link' => ['uri' => 'internal:' . $path, 'title' => $cta]]);
  $node->save(); $ids[] = $node->id();
}
$definition = View::load('aculta_projects')->toArray();
unset($definition['uuid'], $definition['dependencies']);
$definition['id'] = 'home_editorial_highlights';
$definition['label'] = 'Destaques editoriais da Home';
$options = &$definition['display']['default']['display_options'];
$options['title'] = 'Cultura, informação e cuidado';
$options['filters']['type']['value'] = [$type => $type];
$options['sorts'] = [
  'field_weight_value' => ['id' => 'field_weight_value', 'table' => 'node__field_weight', 'field' => 'field_weight_value', 'plugin_id' => 'standard', 'order' => 'ASC'],
  'nid' => ['id' => 'nid', 'table' => 'node_field_data', 'field' => 'nid', 'entity_type' => 'node', 'entity_field' => 'nid', 'plugin_id' => 'standard', 'order' => 'ASC'],
];
$options['row']['options']['view_mode'] = 'default';
$options['css_class'] = 'view-home-editorial-highlights';
$options['style'] = ['type' => 'views_vvjb', 'options' => ['orientation' => 'horizontal', 'items_small' => 1, 'items_big' => 3, 'gap' => 24, 'item_width' => 0, 'looping' => TRUE, 'slide_time' => 7000, 'navigation' => 'both', 'dot_style' => 'circle', 'breakpoints' => '768', 'show_play_pause' => TRUE, 'show_progress_bar' => FALSE, 'show_page_counter' => FALSE, 'enable_keyboard_nav' => TRUE, 'enable_touch_swipe' => TRUE, 'enable_pause_on_hover' => TRUE, 'pause_on_reduced_motion' => TRUE, 'enable_deeplink' => FALSE]];
$options['header']['area']['content']['value'] = '<h2>CULTURA, INFORMAÇÃO E CUIDADO</h2><p class="aculta-page-intro">Diferentes frentes de uma atuação que conecta produção cultural, conhecimento, direitos e cuidado coletivo.</p>';
$definition['display']['block_1']['display_options']['block_description'] = 'Destaques editoriais da Home';
View::create($definition)->save();
$names[] = 'views.view.home_editorial_highlights';
Block::create(['id' => 'aculta_home_editorial_highlights', 'theme' => 'aculta420', 'region' => 'content', 'weight' => 50, 'plugin' => 'views_block:home_editorial_highlights-block_1', 'settings' => ['id' => 'views_block:home_editorial_highlights-block_1', 'label' => 'Destaques editoriais da Home', 'label_display' => '0', 'provider' => 'views', 'views_label' => '', 'items_per_page' => 'none'], 'visibility' => ['request_path' => ['id' => 'request_path', 'negate' => FALSE, 'pages' => '<front>']]])->save();
$names[] = 'block.block.aculta_home_editorial_highlights';
foreach (['knowledge', 'care', 'research'] as $old) {
  Block::load('aculta_home_' . $old)->disable()->save();
  $names[] = 'block.block.aculta_home_' . $old;
}
// Export only the enumerated configuration affected by this feature.
$directory = dirname(__DIR__) . '/config/sync/';
foreach ($names as $name) {
  file_put_contents($directory . $name . '.yml', Yaml::dump(\Drupal::service('config.storage')->read($name), 12, 2));
}
file_put_contents(dirname(__DIR__) . '/tmp/home-carousel-config.json', json_encode($names, JSON_PRETTY_PRINT));
\Drupal::state()->set('aculta.home_carousel_ready', ['nodes' => $ids, 'config' => $names]);
echo json_encode(['nodes' => $ids, 'config' => $names], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
