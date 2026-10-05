<?php

declare(strict_types=1);

use Drupal\Core\Database\Database;

/**
 * One-time, lossless table copier from the bootstrapped Drupal source DB to SQLite.
 *
 * Run with the source MariaDB site bootstrapped:
 *   drush php:script scripts/estados/importar-mariadb-sqlite.php -- <sqlite-path>
 *
 * This deliberately copies every source table and row, including active config,
 * caches, sessions, queues, logs, and persisted credentials. The destination is
 * never the immutable Estado file; use a disposable staging/runtime SQLite file.
 */

$targetPath = $extra[0] ?? NULL;
if (!is_string($targetPath) || $targetPath === '') {
  fwrite(STDERR, "Usage: drush php:script scripts/estados/importar-mariadb-sqlite.php -- <sqlite-path>\n");
  exit(2);
}

$source = Database::getConnection();
$driver = $source->driver();
if (!in_array($driver, ['mysql', 'mariadb'], TRUE)) {
  throw new RuntimeException('Source database must be MariaDB/MySQL.');
}
if (!extension_loaded('pdo_sqlite')) {
  throw new RuntimeException('pdo_sqlite is required.');
}
$targetPath = realpath(dirname($targetPath)) . DIRECTORY_SEPARATOR . basename($targetPath);
if (!is_dir(dirname($targetPath))) {
  throw new RuntimeException('Target directory does not exist.');
}

$sqlite = class_exists(\Pdo\Sqlite::class)
  ? new \Pdo\Sqlite('sqlite:' . $targetPath)
  : new PDO('sqlite:' . $targetPath);
$sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$sqlite->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
$sqlite->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, FALSE);
if (method_exists($sqlite, 'createCollation')) {
  $sqlite->createCollation('NOCASE_UTF8', [Drupal\Component\Utility\Unicode::class, 'strcasecmp']);
}
else {
  $sqlite->sqliteCreateCollation('NOCASE_UTF8', [Drupal\Component\Utility\Unicode::class, 'strcasecmp']);
}
$sqlite->exec('PRAGMA foreign_keys = OFF');
$sqlite->exec('PRAGMA busy_timeout = 10000');

$quoteSqlite = static fn(string $name): string => '"' . str_replace('"', '""', $name) . '"';
$canonicalValue = static function (mixed $value, string $type): ?string {
  if ($value === NULL) {
    return NULL;
  }
  $value = (string) $value;
  if (preg_match('/^(?:tinyint|smallint|mediumint|int|integer|bigint|bit)/i', $type)) {
    $negative = str_starts_with($value, '-');
    $digits = ltrim($negative ? substr($value, 1) : $value, '0');
    $digits = $digits === '' ? '0' : $digits;
    return ($negative && $digits !== '0' ? '-' : '') . $digits;
  }
  return $value;
};
$sourceTables = $source->query('SHOW TABLES')->fetchCol();
sort($sourceTables, SORT_STRING);
$targetTables = $sqlite->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'")->fetchAll(PDO::FETCH_COLUMN);
$targetTables = array_fill_keys($targetTables, TRUE);

$sourceMetadata = [];
$allRows = [];
$hashRows = [];

// Create only source tables absent from the Drupal-generated SQLite schema.
// This accommodates Drupal's runtime-created cache/queue/state tables while
// retaining their columns, primary keys, and indexes from source metadata.
foreach ($sourceTables as $table) {
  $quoted = $source->escapeTable($table);
  $columns = $source->query("SHOW FULL COLUMNS FROM {$quoted}")->fetchAll(PDO::FETCH_ASSOC);
  $indexes = $source->query("SHOW INDEX FROM {$quoted}")->fetchAll(PDO::FETCH_ASSOC);
  $sourceMetadata[$table] = ['columns' => $columns, 'indexes' => $indexes];

  if (isset($targetTables[$table])) {
    $targetColumns = $sqlite->query('PRAGMA table_info(' . $quoteSqlite($table) . ')')->fetchAll(PDO::FETCH_ASSOC);
    $sourceNames = array_column($columns, 'Field');
    $targetNames = array_column($targetColumns, 'name');
    if ($sourceNames !== $targetNames) {
      throw new RuntimeException("Column mismatch in existing SQLite table {$table}; no rows were copied.");
    }
    continue;
  }

  $primary = [];
  foreach ($indexes as $index) {
    if ($index['Key_name'] === 'PRIMARY') {
      $primary[(int) $index['Seq_in_index']] = $index['Column_name'];
    }
  }
  ksort($primary);
  $primary = array_values($primary);
  $definitions = [];
  foreach ($columns as $column) {
    $name = $column['Field'];
    $type = strtolower((string) $column['Type']);
    $affinity = preg_match('/(?:blob|binary)/', $type) ? 'BLOB'
      : (preg_match('/(?:int|bit)/', $type) ? 'INTEGER'
        : (preg_match('/(?:decimal|numeric|float|double|real)/', $type) ? 'NUMERIC' : 'TEXT'));
    $definition = $quoteSqlite($name) . ' ' . $affinity;
    $auto = stripos((string) $column['Extra'], 'auto_increment') !== FALSE;
    if ($auto && count($primary) === 1 && $primary[0] === $name) {
      $definition .= ' PRIMARY KEY AUTOINCREMENT';
    }
    elseif (($column['Null'] ?? '') === 'NO') {
      $definition .= ' NOT NULL';
    }
    if ($column['Default'] !== NULL && !$auto) {
      $default = (string) $column['Default'];
      if (preg_match('/^(CURRENT_TIMESTAMP|CURRENT_DATE|CURRENT_TIME)$/i', $default)) {
        $definition .= ' DEFAULT ' . strtoupper($default);
      }
      elseif (is_numeric($default)) {
        $definition .= ' DEFAULT ' . $default;
      }
      else {
        $definition .= ' DEFAULT ' . $sqlite->quote($default);
      }
    }
    $definitions[] = $definition;
  }
  if ($primary && !(count($primary) === 1 && stripos((string) $columns[array_search($primary[0], array_column($columns, 'Field'))]['Extra'], 'auto_increment') !== FALSE)) {
    $definitions[] = 'PRIMARY KEY (' . implode(', ', array_map($quoteSqlite, $primary)) . ')';
  }
  $sqlite->exec('CREATE TABLE ' . $quoteSqlite($table) . ' (' . implode(', ', $definitions) . ')');

  $groups = [];
  foreach ($indexes as $index) {
    if ($index['Key_name'] === 'PRIMARY') {
      continue;
    }
    $key = (string) $index['Key_name'];
    $groups[$key]['unique'] = (int) $index['Non_unique'] === 0;
    $groups[$key]['columns'][(int) $index['Seq_in_index']] = (string) $index['Column_name'];
  }
  foreach ($groups as $name => $group) {
    ksort($group['columns']);
    $indexName = $table . '__' . $name;
    $sqlite->exec('CREATE ' . ($group['unique'] ? 'UNIQUE ' : '') . 'INDEX ' . $quoteSqlite($indexName)
      . ' ON ' . $quoteSqlite($table) . ' (' . implode(', ', array_map($quoteSqlite, array_values($group['columns']))) . ')');
  }
  $targetTables[$table] = TRUE;
}

$sqlite->beginTransaction();
try {
  foreach ($sourceTables as $table) {
    $sqlite->exec('DELETE FROM ' . $quoteSqlite($table));
  }

  foreach ($sourceTables as $table) {
    $columns = $sourceMetadata[$table]['columns'];
    $columnNames = array_column($columns, 'Field');
    $quotedColumns = implode(', ', array_map($quoteSqlite, $columnNames));
    $orderColumns = [];
    foreach ($sourceMetadata[$table]['indexes'] as $index) {
      if ($index['Key_name'] === 'PRIMARY') {
        $orderColumns[(int) $index['Seq_in_index']] = (string) $index['Column_name'];
      }
    }
    if ($orderColumns) {
      ksort($orderColumns);
      $orderColumns = array_values($orderColumns);
    }
    else {
      $orderColumns = $columnNames;
    }
    $sourceTypes = [];
    foreach ($sourceMetadata[$table]['columns'] as $column) {
      $sourceTypes[$column['Field']] = strtolower((string) $column['Type']);
    }
    $orderSql = implode(', ', array_map(static function (string $name) use ($source, $sourceTypes): string {
      $field = $source->escapeField($name);
      return preg_match('/(?:char|text|binary|blob|enum|set|json)/i', $sourceTypes[$name] ?? '') ? 'BINARY ' . $field : $field;
    }, $orderColumns));
    $statement = $source->query("SELECT * FROM {$source->escapeTable($table)} ORDER BY {$orderSql}");
    $insert = $sqlite->prepare('INSERT INTO ' . $quoteSqlite($table) . ' (' . $quotedColumns . ') VALUES (' . implode(', ', array_fill(0, count($columnNames), '?')) . ')');
    $blobColumns = [];
    foreach ($columns as $column) {
      // Drupal's SQLite schema maps MariaDB DECIMAL to SQLite FLOAT. Binding
      // these values as BLOB preserves the exact decimal text instead of
      // silently round-tripping it through IEEE-754.
      $blobColumns[$column['Field']] = preg_match('/(?:blob|binary|decimal|numeric)/i', (string) $column['Type']) === 1;
    }
    $sourceDigests = [];
    $count = 0;
    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
      $values = [];
      $rowHash = hash_init('sha256');
      foreach ($columnNames as $columnName) {
        $value = $row[$columnName];
        if ($value === NULL) {
          $values[] = NULL;
          hash_update($rowHash, "N\0");
        }
        else {
          $value = $canonicalValue($value, $sourceTypes[$columnName] ?? '');
          $values[] = $value;
          hash_update($rowHash, "V" . pack('N', strlen($value)) . $value);
        }
      }
      foreach ($values as $position => $value) {
        $columnName = $columnNames[$position];
        $insert->bindValue($position + 1, $value, $value === NULL ? PDO::PARAM_NULL : ($blobColumns[$columnName] ? PDO::PARAM_LOB : PDO::PARAM_STR));
      }
      $insert->execute();
      $sourceDigests[] = hash_final($rowHash);
      $count++;
    }
    sort($sourceDigests, SORT_STRING);
    $sourceTableHash = hash_init('sha256');
    foreach ($sourceDigests as $digest) {
      hash_update($sourceTableHash, $digest);
    }
    $sourceHash = hash_final($sourceTableHash);
    $allRows[$table] = $count;

    // Re-hash the SQLite rows using identical byte/NULL framing.
    $targetOrder = implode(', ', array_map(static fn(string $name): string => $quoteSqlite($name) . ' COLLATE BINARY', $orderColumns));
    $targetRows = $sqlite->query('SELECT ' . $quotedColumns . ' FROM ' . $quoteSqlite($table) . ' ORDER BY ' . $targetOrder);
    $targetCount = 0;
    $targetDigests = [];
    while ($targetRow = $targetRows->fetch(PDO::FETCH_ASSOC)) {
      $rowHash = hash_init('sha256');
      foreach ($columnNames as $columnName) {
        $value = $targetRow[$columnName];
        if ($value === NULL) {
          hash_update($rowHash, "N\0");
        }
        else {
          $value = $canonicalValue($value, $sourceTypes[$columnName] ?? '');
          hash_update($rowHash, "V" . pack('N', strlen($value)) . $value);
        }
      }
      $targetDigests[] = hash_final($rowHash);
      $targetCount++;
    }
    sort($targetDigests, SORT_STRING);
    $targetTableHash = hash_init('sha256');
    foreach ($targetDigests as $digest) {
      hash_update($targetTableHash, $digest);
    }
    $targetHash = hash_final($targetTableHash);
    $hashRows[$table] = [
      'source_rows' => $count,
      'target_rows' => $targetCount,
      'source_sha256' => $sourceHash,
      'target_sha256' => $targetHash,
      'match' => $count === $targetCount && hash_equals($sourceHash, $targetHash),
    ];
    if (!$hashRows[$table]['match']) {
      throw new RuntimeException("Row/hash mismatch in table {$table}; transaction rolled back.");
    }
  }
  $sqlite->commit();
}
catch (Throwable $error) {
  if ($sqlite->inTransaction()) {
    $sqlite->rollBack();
  }
  throw $error;
}

$reportPath = $targetPath . '.conversion-report.json';
file_put_contents($reportPath, json_encode([
  'source_driver' => $driver,
  'target_driver' => 'sqlite',
  'tables' => $hashRows,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
fwrite(STDOUT, 'tables=' . count($sourceTables) . ' rows=' . array_sum($allRows) . ' table_hashes=match report=' . $reportPath . PHP_EOL);
