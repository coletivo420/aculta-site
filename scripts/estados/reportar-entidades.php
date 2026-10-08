<?php

declare(strict_types=1);

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Database\Database;

$output = $extra[0] ?? NULL;
$manager = \Drupal::entityTypeManager();
$database = Database::getConnection();
$report = [];
foreach ($manager->getDefinitions() as $type => $definition) {
  $class = $definition->getClass();
  if (!is_string($class) || !is_subclass_of($class, ContentEntityInterface::class)) {
    continue;
  }
  $storage = $manager->getStorage($type);
  $ids = $storage->getQuery()->accessCheck(FALSE)->execute();
  $idValues = array_map(static fn($id): string => (string) $id, array_values($ids));
  sort($idValues, SORT_STRING);
  $uuidValues = [];
  foreach ($storage->loadMultiple($ids) as $entity) {
    if ($entity->hasField('uuid') && !$entity->get('uuid')->isEmpty()) {
      $uuidValues[] = (string) $entity->get('uuid')->value;
    }
  }
  sort($uuidValues, SORT_STRING);
  $revisionTable = method_exists($definition, 'getRevisionTable') ? $definition->getRevisionTable() : NULL;
  $revisionCount = NULL;
  if (is_string($revisionTable) && $revisionTable !== '' && $database->schema()->tableExists($revisionTable)) {
    $revisionCount = (int) $database->select($revisionTable, 'r')->countQuery()->execute()->fetchField();
  }
  $report[$type] = [
    'count' => count($idValues),
    'id_sha256' => hash('sha256', implode("\n", $idValues)),
    'uuid_count' => count($uuidValues),
    'uuid_sha256' => hash('sha256', implode("\n", $uuidValues)),
    'revision_table' => $revisionTable,
    'revision_count' => $revisionCount,
  ];
}
ksort($report, SORT_STRING);
$json = json_encode(['entity_types' => $report], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
if (is_string($output) && $output !== '') {
  if (file_put_contents($output, $json . PHP_EOL) === FALSE) {
    throw new RuntimeException('Unable to write the entity inventory.');
  }
}
else {
  print($json . PHP_EOL);
}
