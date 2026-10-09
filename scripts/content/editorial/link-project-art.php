<?php
/**
 * Vínculo editorial das artes de identidade aos nós de projeto.
 *
 * Para cada projeto, cria (ou reutiliza) uma entidade de mídia do bundle `image`
 * com o arquivo do kit, ligada a `field_media_image`, e preenche `field_image`
 * com o mesmo arquivo, que é o campo renderizado pelo teaser e pela página do
 * projeto. Idempotente: a mídia é localizada pelo nome.
 *
 * Por padrão, apenas mostra o plano. Para gravar: ACULTA_APPLY=1.
 * Execução: php vendor/drush/drush/drush.php --uri=<host> scr scripts/content/editorial/link-project-art.php
 * Requer escrita em public:// (o usuário do processo web).
 */
use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;
use Drupal\file\FileRepositoryInterface;

$theme = 'web/themes/custom/aculta420/assets/branding/';
$map = [
  // node id => [source relative to theme branding dir, alt, media name]
  2 => ['bloco-sativa420/web/square/bloco-sativa420-square-512w.webp',
        'Bloco Sativa 420 — arte com participantes mascarados, folhagens e as cores verde, amarelo e vermelho',
        'Carnareggae Bloco Sativa — arte do Bloco Sativa 420'],
  3 => ['baque-sativa/web/baque-sativa-red-square-512.webp',
        'Logomarca do Grupo de Percussão Baque Sativa',
        'Grupo de Percussão Baque Sativa — logomarca'],
  4 => ['podplant420/web/stacked/podplant420-stacked-on-light-512w.webp',
        'Podplant420',
        'PodPlant420 — logomarca'],
];
$fs = \Drupal::service('file_system');
$repo = \Drupal::service(FileRepositoryInterface::class);
$media_storage = \Drupal::entityTypeManager()->getStorage('media');
$apply = getenv('ACULTA_APPLY') === '1';
echo $apply ? "Modo gravação\n" : "Dry-run: nada será gravado (ACULTA_APPLY=1 grava)\n";
foreach ($map as $nid => [$rel, $alt, $name]) {
  $node = Node::load($nid);
  $src = DRUPAL_ROOT . '/../' . $theme . $rel;
  if (!$node || !is_file($src)) { echo "SKIP node $nid: missing node or source $src\n"; continue; }
  $dest = 'public://editorial/projetos/' . basename($rel);
  if (!$apply) { echo "PLAN node $nid ({$node->label()}): $rel -> $dest\n"; continue; }
  $dir = dirname($dest); $fs->prepareDirectory($dir, $fs::CREATE_DIRECTORY);
  $file = $repo->writeData(file_get_contents($src), $dest, $fs::EXISTS_REPLACE);
  $existing = $media_storage->loadByProperties(['name' => $name, 'bundle' => 'image']);
  $media = $existing ? reset($existing) : Media::create(['bundle' => 'image', 'name' => $name, 'uid' => 1, 'status' => 1]);
  $media->set('field_media_image', ['target_id' => $file->id(), 'alt' => $alt, 'title' => $name]);
  $media->save();
  $node->set('field_media_image', ['target_id' => $media->id()]);
  $node->set('field_image', ['target_id' => $file->id(), 'alt' => $alt, 'title' => $name]);
  $node->save();
  echo "node $nid ({$node->label()}): media={$media->id()} file={$file->id()} ", basename($dest), "\n";
}
