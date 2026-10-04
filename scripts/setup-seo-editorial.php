<?php

/** @file Additive, idempotent local editorial configuration and migration. */
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\taxonomy\Entity\Term;
use Drupal\metatag\Entity\MetatagDefaults;
use Drupal\schema_metatag\SchemaMetatagManager;
use Drupal\views\Entity\View;
use Drupal\block\Entity\Block;

if (!in_array(\Drupal::request()->getHost(), ['localhost', '127.0.0.1'], TRUE)) { throw new RuntimeException('Local only.'); }
if (!is_file(dirname(__DIR__) . '/tmp/seo-content-apoio/before/manifest.json')) { throw new RuntimeException('Logical backup required.'); }
$changes = [];
$add = static function (string $entity, string $bundle, string $name, string $type, string $label, bool $required = FALSE, array $storage_settings = [], array $settings = [], int $cardinality = 1, array $default = []) use (&$changes): void {
  $storage = FieldStorageConfig::loadByName($entity, $name);
  if (!$storage) {
    $storage = FieldStorageConfig::create(['entity_type' => $entity, 'field_name' => $name, 'type' => $type, 'cardinality' => $cardinality, 'settings' => $storage_settings, 'translatable' => TRUE]);
    $storage->save();
    $changes[] = 'field.storage.' . $entity . '.' . $name;
  }
  if ($storage->getType() !== $type || $storage->getCardinality() !== $cardinality) { throw new RuntimeException('Incompatible existing storage: ' . $name); }
  $field = FieldConfig::loadByName($entity, $bundle, $name) ?: FieldConfig::create(['entity_type' => $entity, 'bundle' => $bundle, 'field_name' => $name]);
  $field->setLabel($label)->setDescription('Campo editorial administrável: ' . $label . '. Preencha somente com informações verificadas.')->setRequired($required)->setSettings($settings)->setDefaultValue($default)->save();
  $changes[] = $field->getConfigDependencyName();
  $form = EntityFormDisplay::load("$entity.$bundle.default") ?: EntityFormDisplay::create(['targetEntityType' => $entity, 'bundle' => $bundle, 'mode' => 'default', 'status' => TRUE]);
  $widget = ['string' => 'string_textfield', 'string_long' => 'string_textarea', 'text_long' => 'text_textarea', 'datetime' => 'datetime_default', 'list_string' => 'options_select', 'boolean' => 'boolean_checkbox', 'entity_reference' => 'entity_reference_autocomplete', 'link' => 'link_default', 'metatag' => 'metatag_firehose'][$type] ?? 'string_textfield';
  if ($type === 'entity_reference' && ($storage_settings['target_type'] ?? '') === 'media') { $widget = 'media_library_widget'; }
  $form->setComponent($name, ['type' => $widget, 'weight' => 15, 'settings' => []])->save();
  $view = EntityViewDisplay::load("$entity.$bundle.default") ?: EntityViewDisplay::create(['targetEntityType' => $entity, 'bundle' => $bundle, 'mode' => 'default', 'status' => TRUE]);
  // New fields are explicitly hidden until a reviewed presentation is assigned.
  $view->removeComponent($name)->save();
};
$vocab = static function (string $id, string $label, string $description, array $terms = []): array {
  (Vocabulary::load($id) ?: Vocabulary::create(['vid' => $id]))->set('name', $label)->set('description', $description)->save();
  $result = [];
  foreach ($terms as $name) {
    $existing = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['vid' => $id, 'name' => $name]);
    $term = $existing ? reset($existing) : Term::create(['vid' => $id, 'name' => $name, 'langcode' => 'pt-br']);
    $term->save();
    $result[$name] = $term;
  }
  return $result;
};
$ref = static fn(string $target, array $bundles): array => ['handler' => 'default:' . $target, 'handler_settings' => ['target_bundles' => array_combine($bundles, $bundles), 'auto_create' => FALSE]];
$vocab('editorial_category', 'Categoria editorial', 'Organização temática das notícias; criar termos conforme os conteúdos reais.');
$vocab('tags', 'Tags', 'Palavras-chave administráveis para organizar conteúdos publicados, sem preenchimento automático fictício.');
$vocab('event_type', 'Tipo de atividade', 'Formato de cada atividade ou evento institucional.', ['Apresentação', 'Encontro', 'Oficina', 'Roda de conversa', 'Seminário', 'Formação', 'Cortejo', 'Festival', 'Feira', 'Assembleia', 'Outro']);
$areas = $vocab('work_area', 'Área de atuação', 'Frentes comprovadas de atuação dos projetos.', ['Cultura', 'Música', 'Comunicação', 'Formação', 'Educação', 'Saúde', 'Redução de danos', 'Direitos humanos', 'Participação social', 'Pesquisa']);
$authors = $vocab('editorial_author', 'Autoria editorial', 'Autoria pública institucional ou pessoal; não confundir com a conta Drupal que cadastrou a publicação.', ['Associação Cultural Antiproibicionista']);
$add('taxonomy_term', 'editorial_author', 'field_author_kind', 'list_string', 'Tipo de autoria', TRUE, ['allowed_values' => ['organization' => 'Institucional', 'person' => 'Pessoa']], [], 1, [['value' => 'organization']]);
$author_id = $authors['Associação Cultural Antiproibicionista']->id();
\Drupal::service('entity_field.manager')->clearCachedFieldDefinitions();
\Drupal::entityTypeManager()->getStorage('taxonomy_term')->resetCache([$author_id]);
$author = Term::load($author_id);
$author->set('field_author_kind', 'organization')->save();

foreach (['article', 'activity', 'project'] as $bundle) {
  $add('node', $bundle, 'field_summary', 'string_long', 'Resumo', TRUE);
  $add('node', $bundle, 'field_media_image', 'entity_reference', 'Imagem editorial', $bundle === 'article', ['target_type' => 'media'], $ref('media', ['image']));
  // Existing Metatag storage/name is deliberately reused.
  $add('node', $bundle, 'field_meta_tags', 'metatag', 'SEO e compartilhamento');
  $body = FieldConfig::loadByName('node', $bundle, 'body');
  $body->setRequired($bundle !== 'activity')->setDescription('Texto completo. A Home e as listagens utilizam o resumo editorial.')->save();
  $sitemap = \Drupal::configFactory()->getEditable("simple_sitemap.bundle_settings.default.node.$bundle");
  $sitemap->set('index', TRUE)->set('priority', '0.5')->set('changefreq', '')->set('include_images', TRUE)->save();
}
$add('node', 'article', 'field_subtitle', 'string', 'Subtítulo');
$add('node', 'article', 'field_editorial_category', 'entity_reference', 'Categoria editorial', TRUE, ['target_type' => 'taxonomy_term'], $ref('taxonomy_term', ['editorial_category']));
$add('node', 'article', 'field_tags', 'entity_reference', 'Tags', FALSE, ['target_type' => 'taxonomy_term'], $ref('taxonomy_term', ['tags']), -1);
$add('node', 'article', 'field_editorial_author', 'entity_reference', 'Autoria editorial', TRUE, ['target_type' => 'taxonomy_term'], $ref('taxonomy_term', ['editorial_author']), 1, [['target_id' => $author->id()]]);
$add('node', 'article', 'field_credits', 'string_long', 'Créditos');
$add('node', 'article', 'field_references', 'link', 'Referências', FALSE, [], ['link_type' => 16, 'title' => 1], -1);
$add('node', 'article', 'field_related_activity', 'entity_reference', 'Atividade relacionada', FALSE, ['target_type' => 'node'], $ref('node', ['activity']));
$add('node', 'article', 'field_published_at', 'datetime', 'Data efetiva de publicação', FALSE, ['datetime_type' => 'datetime']);
FieldConfig::loadByName('node', 'article', 'field_published_at')->setDescription('Registrada automaticamente na primeira publicação. Altere somente para reproduzir uma data de publicação efetivamente comprovada.')->save();

foreach (['article', 'activity'] as $bundle) {
  $add('node', $bundle, 'field_project', 'entity_reference', 'Projetos relacionados', FALSE, ['target_type' => 'node'], $ref('node', ['project']), -1);
}
$type = \Drupal\node\Entity\NodeType::load('activity');
$type->set('name', 'Atividade / evento')->set('description', 'Atividades reais com data e horário, local ou participação online. Alimenta próximas atividades e registros realizados.')->save();
$add('node', 'activity', 'field_event_type', 'entity_reference', 'Tipo de atividade', TRUE, ['target_type' => 'taxonomy_term'], $ref('taxonomy_term', ['event_type']));
$add('node', 'activity', 'field_event_start', 'datetime', 'Início', TRUE, ['datetime_type' => 'datetime']);
$add('node', 'activity', 'field_event_end', 'datetime', 'Término', FALSE, ['datetime_type' => 'datetime']);
$add('node', 'activity', 'field_modality', 'list_string', 'Modalidade', TRUE, ['allowed_values' => ['presencial' => 'Presencial', 'online' => 'Online', 'hibrido' => 'Híbrido']]);
foreach (['field_place_name' => 'Nome do local', 'field_event_street' => 'Logradouro, número e complemento', 'field_event_city' => 'Cidade da atividade', 'field_event_region' => 'Estado da atividade', 'field_event_postal' => 'CEP da atividade', 'field_event_country' => 'País da atividade'] as $field => $label) { $add('node', 'activity', $field, 'string', $label); }
$add('node', 'activity', 'field_online_url', 'link', 'Participação online', FALSE, [], ['link_type' => 16, 'title' => 0]);
$add('node', 'activity', 'field_organizer', 'string', 'Organizador', TRUE, [], [], 1, [['value' => 'Associação Cultural Antiproibicionista']]);
$add('node', 'activity', 'field_free', 'boolean', 'Atividade gratuita', TRUE, [], ['on_label' => 'Sim', 'off_label' => 'Não'], 1, [['value' => 1]]);
$add('node', 'activity', 'field_registration_url', 'link', 'Inscrição', FALSE, [], ['link_type' => 16, 'title' => 1]);
$add('node', 'activity', 'field_event_status', 'list_string', 'Situação da atividade', TRUE, ['allowed_values' => ['agendado' => 'Agendado', 'reagendado' => 'Reagendado', 'adiado' => 'Adiado', 'cancelado' => 'Cancelado', 'concluido' => 'Concluído']], [], 1, [['value' => 'agendado']]);
$legacy = FieldConfig::loadByName('node', 'activity', 'field_activity_date');
$legacy->setRequired(FALSE)->setLabel('Data anterior (legado)')->setDescription('Campo antigo preservado. Novas atividades devem utilizar Início e Término com horário.')->save();
EntityFormDisplay::load('node.activity.default')->removeComponent('field_activity_date')->save();
EntityViewDisplay::load('node.activity.default')->removeComponent('field_activity_date')->save();

$add('node', 'project', 'field_callout', 'string', 'Chamada');
$add('node', 'project', 'field_gallery', 'entity_reference', 'Galeria', FALSE, ['target_type' => 'media'], $ref('media', ['image']), -1);
$add('node', 'project', 'field_work_area', 'entity_reference', 'Áreas de atuação', TRUE, ['target_type' => 'taxonomy_term'], $ref('taxonomy_term', ['work_area']), -1);
$add('node', 'project', 'field_project_status', 'list_string', 'Situação do projeto', TRUE, ['allowed_values' => ['nao_informado' => 'Não informado — confirmar antes de divulgar', 'ativo' => 'Ativo', 'concluido' => 'Concluído', 'pausado' => 'Pausado']], [], 1, [['value' => 'nao_informado']]);
foreach (['field_project_start' => 'Data de início do projeto', 'field_project_end' => 'Data de término do projeto'] as $field => $label) { $add('node', 'project', $field, 'datetime', $label, FALSE, ['datetime_type' => 'date']); }
foreach (['field_territory' => 'Território', 'field_audience' => 'Público'] as $field => $label) { $add('node', 'project', $field, 'string_long', $label); }
$add('node', 'project', 'field_results', 'string_long', 'Resultados verificados');
$add('node', 'project', 'field_link', 'link', 'Link do projeto', FALSE, [], ['link_type' => 17, 'title' => 1]);
// Existing objectives/history/activity/records fields remain, with no duplication.
$storage = \Drupal::entityTypeManager()->getStorage('node');
foreach ($storage->loadByProperties(['type' => 'project']) as $project) {
  if ($project->get('field_summary')->isEmpty() || preg_match('/<[^>]+>/', (string) $project->get('field_summary')->value)) {
    $source = $project->get('field_summary')->isEmpty() ? (string) $project->get('body')->summary : (string) $project->get('field_summary')->value;
    $project->set('field_summary', trim(\Drupal\Component\Render\PlainTextOutput::renderFromHtml($source)));
  }
  if ($project->get('field_work_area')->isEmpty()) {
    $names = str_contains($project->label(), 'PodPlant') ? ['Cultura', 'Comunicação'] : ['Cultura', 'Música'];
    $project->set('field_work_area', array_map(static fn($name) => ['target_id' => $areas[$name]->id()], $names));
  }
  if ($project->get('field_project_status')->isEmpty()) { $project->set('field_project_status', 'nao_informado'); }
  $project->save();
}

// Select public presentation deliberately; inherited raw legacy fields stay hidden.
foreach (['article', 'activity', 'project'] as $bundle) {
  $view = EntityViewDisplay::load("node.$bundle.default");
  $view->setComponent('field_media_image', ['type' => 'entity_reference_entity_view', 'label' => 'hidden', 'weight' => -20, 'settings' => ['view_mode' => 'default', 'link' => FALSE]]);
  $view->setComponent('field_summary', ['type' => 'basic_string', 'label' => 'hidden', 'weight' => -10]);
  if ($bundle === 'project') { $view->removeComponent('field_summary'); } // Body already carries complete content.
  if ($bundle === 'activity') {
    foreach (['field_event_start' => 1, 'field_event_end' => 2] as $name => $weight) { $view->setComponent($name, ['type' => 'datetime_default', 'label' => 'above', 'weight' => $weight]); }
    foreach (['field_place_name', 'field_event_street', 'field_event_city'] as $weight => $name) { $view->setComponent($name, ['type' => 'string', 'label' => 'above', 'weight' => $weight + 3]); }
    foreach (['field_online_url', 'field_registration_url'] as $name) { $view->setComponent($name, ['type' => 'link', 'label' => 'above', 'weight' => 8]); }
    $view->setComponent('field_event_status', ['type' => 'list_default', 'label' => 'above', 'weight' => 9]);
  }
  if ($bundle === 'article') { $view->setComponent('field_editorial_author', ['type' => 'entity_reference_label', 'label' => 'above', 'weight' => 25, 'settings' => ['link' => FALSE]]); }
  $view->save();
}

$serialize = static fn(array $value) => SchemaMetatagManager::serialize($value);
$organization = ['@type' => 'Organization', '@id' => 'https://aculta.org/#organization', 'name' => '[institution:name]', 'url' => '[institution:url]'];
$global = MetatagDefaults::load('global');
$tags = $global->get('tags');
$tags += [
  'schema_organization_type' => 'Organization', 'schema_organization_id' => 'https://aculta.org/#organization',
  'schema_organization_name' => '[institution:name]', 'schema_organization_legal_name' => '[institution:name]', 'schema_organization_alternate_name' => '[institution:alternate]',
  'schema_organization_tax_id' => '[institution:tax-id]', 'schema_organization_email' => '[institution:email]', 'schema_organization_telephone' => '[institution:phone]', 'schema_organization_url' => '[institution:url]',
  'schema_organization_description' => 'Associação sem fins lucrativos de Goiânia que desenvolve projetos de cultura, comunicação, cuidado, direitos e participação social.',
  'schema_organization_address' => $serialize(['@type' => 'PostalAddress', 'streetAddress' => '[institution:street]', 'addressLocality' => '[institution:city]', 'addressRegion' => '[institution:region]', 'postalCode' => '[institution:postal]', 'addressCountry' => '[institution:country]']),
  'schema_web_site_type' => 'WebSite', 'schema_web_site_id' => 'https://aculta.org/#website', 'schema_web_site_name' => '[institution:name]', 'schema_web_site_url' => '[institution:url]', 'schema_web_site_publisher' => $serialize($organization),
  'schema_web_page_type' => 'WebPage', 'schema_web_page_name' => '[current-page:title]', 'schema_web_page_url' => '[current-page:url]',
];
$global->set('tags', $tags)->save();
foreach (['article', 'activity', 'project'] as $bundle) {
  $id = 'node__' . $bundle;
  $default = MetatagDefaults::load($id) ?: MetatagDefaults::create(['id' => $id, 'label' => 'Conteúdo: ' . $bundle]);
  $tags = ['title' => '[node:title] | Associação Cultural Antiproibicionista', 'description' => '[node:editorial-summary]', 'og_title' => '[node:title]', 'og_description' => '[node:editorial-summary]', 'og_url' => '[node:canonical]', 'og_image' => '[node:image]', 'canonical_url' => '[node:canonical]', 'schema_web_page_type' => 'WebPage', 'schema_web_page_name' => '[node:title]', 'schema_web_page_description' => '[node:editorial-summary]', 'schema_web_page_url' => '[node:canonical]'];
  if ($bundle === 'article') {
    $tags += ['og_type' => 'article', 'schema_article_type' => 'NewsArticle', 'schema_article_headline' => '[node:title]', 'schema_article_description' => '[node:editorial-summary]', 'schema_article_image' => $serialize(['@type' => 'ImageObject', 'url' => '[node:image]']), 'schema_article_date_published' => '[node:published]', 'schema_article_date_modified' => '[node:modified]', 'schema_article_author' => $serialize(['@type' => '[node:author-type]', 'name' => '[node:author-name]']), 'schema_article_publisher' => $serialize($organization), 'schema_article_main_entity_of_page' => $serialize(['@type' => 'WebPage', '@id' => '[node:canonical]'])];
  }
  if ($bundle === 'activity') {
    $tags += ['schema_event_type' => 'Event', 'schema_event_name' => '[node:title]', 'schema_event_description' => '[node:editorial-summary]', 'schema_event_image' => $serialize(['@type' => 'ImageObject', 'url' => '[node:image]']), 'schema_event_start_date' => '[node:start]', 'schema_event_end_date' => '[node:end]', 'schema_event_event_status' => '[node:event-status]', 'schema_event_event_attendance_mode' => '[node:attendance]', 'schema_event_is_accessible_for_free' => '[node:free]', 'schema_event_url' => '[node:canonical]', 'schema_event_organizer' => $serialize(['@type' => 'Organization', 'name' => '[node:organizer]'])];
  }
  $default->set('tags', $tags)->save();
}

// Reuse the existing View/block; add upcoming display and date sorting.
$view = View::load('aculta_activities');
$displays = $view->get('display');
$options = &$displays['default']['display_options'];
$options['title'] = 'Atividades realizadas';
$options['sorts'] = ['field_event_start_value' => ['id' => 'field_event_start_value', 'table' => 'node__field_event_start', 'field' => 'field_event_start_value', 'plugin_id' => 'date', 'order' => 'DESC']];
$date_filter = ['id' => 'field_event_start_value', 'table' => 'node__field_event_start', 'field' => 'field_event_start_value', 'plugin_id' => 'date', 'operator' => '<', 'value' => ['value' => 'now', 'type' => 'offset']];
$options['filters']['field_event_start_value'] = $date_filter;
$options['empty'] = ['area' => ['id' => 'area', 'table' => 'views', 'field' => 'area', 'plugin_id' => 'text', 'empty' => TRUE, 'content' => ['value' => '<p>Os registros de atividades serão publicados neste espaço conforme sua organização editorial.</p>', 'format' => 'full_html']]];
$displays['block_upcoming'] = ['id' => 'block_upcoming', 'display_title' => 'Próximas atividades', 'display_plugin' => 'block', 'position' => 2, 'display_options' => ['defaults' => ['title' => FALSE, 'filters' => FALSE, 'sorts' => FALSE, 'empty' => FALSE], 'title' => 'Próximas atividades', 'filters' => $options['filters'], 'sorts' => $options['sorts'], 'empty' => ['area' => ['id' => 'area', 'table' => 'views', 'field' => 'area', 'plugin_id' => 'text', 'empty' => TRUE, 'content' => ['value' => '<p>Acompanhe este espaço para conhecer as próximas atividades abertas à comunidade.</p>', 'format' => 'full_html']]], 'block_description' => 'Próximas atividades']];
$displays['block_upcoming']['display_options']['filters']['field_event_start_value']['operator'] = '>=';
$displays['block_upcoming']['display_options']['sorts']['field_event_start_value']['order'] = 'ASC';
$view->set('display', $displays)->set('description', 'Atividades publicadas organizadas por data de início; sem dependência de IDs locais.')->save();
$block = Block::load('aculta_upcoming_activities') ?: Block::create(['id' => 'aculta_upcoming_activities', 'theme' => 'aculta', 'region' => 'content', 'weight' => 25, 'plugin' => 'views_block:aculta_activities-block_upcoming']);
$block->set('settings', ['id' => 'views_block:aculta_activities-block_upcoming', 'label' => 'Próximas atividades', 'label_display' => 'visible', 'provider' => 'views', 'views_label' => '', 'items_per_page' => NULL])->set('visibility', ['request_path' => ['id' => 'request_path', 'negate' => FALSE, 'pages' => '/atividades']])->save();
$old = Block::load('aculta_activities');
$settings = $old->get('settings'); $settings['label'] = 'Atividades realizadas'; $settings['label_display'] = 'visible'; $old->set('settings', $settings)->save();

// Related content displays are ready for placement, without fixed project IDs.
foreach (['aculta_news' => 'aculta_related_news', 'aculta_activities' => 'aculta_related_activities'] as $source_id => $id) {
  if (View::load($id)) { continue; }
  $data = View::load($source_id)->toArray();
  unset($data['uuid']); $data['id'] = $id; $data['label'] = $source_id === 'aculta_news' ? 'Notícias relacionadas ao projeto' : 'Atividades relacionadas ao projeto';
  $data['description'] = 'Relacionamento pelo campo Projetos relacionados; argumento obtido do node da rota, sem IDs hardcoded.';
  $data['display']['default']['display_options']['arguments'] = ['field_project_target_id' => ['id' => 'field_project_target_id', 'table' => 'node__field_project', 'field' => 'field_project_target_id', 'plugin_id' => 'numeric', 'default_action' => 'default', 'default_argument_type' => 'node', 'default_argument_options' => [], 'break_phrase' => FALSE, 'validate' => ['type' => 'none', 'fail' => 'not found']]];
  View::create($data)->save();
}

// Existing path patterns may already target these bundles. Never regenerate aliases.
foreach (['article' => 'noticias', 'activity' => 'atividades', 'project' => 'projetos'] as $bundle => $prefix) {
  $id = 'aculta_' . $bundle;
  $pattern = \Drupal\pathauto\Entity\PathautoPattern::load($id);
  if (!$pattern) {
    $pattern = \Drupal\pathauto\Entity\PathautoPattern::create(['id' => $id, 'label' => 'URL editorial: ' . $bundle, 'type' => 'canonical_entities:node', 'pattern' => '/' . $prefix . '/[node:title]', 'selection_criteria' => ['bundle' => ['id' => 'entity_bundle:node', 'bundles' => [$bundle => $bundle], 'negate' => FALSE, 'context_mapping' => ['node' => 'node']]], 'selection_logic' => 'and', 'weight' => -20, 'status' => TRUE]);
    $pattern->save();
  }
  $redundant = \Drupal\pathauto\Entity\PathautoPattern::load('editorial_' . $bundle);
  if ($redundant) { $redundant->delete(); } // Only configuration created by this script, never existing content.
}
file_put_contents(dirname(__DIR__) . '/tmp/seo-content-apoio/editorial-fields.json', json_encode($changes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "Editorial fields and Schema defaults configured; existing projects preserved.\n";

