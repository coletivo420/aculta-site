<?php

declare(strict_types=1);

/** Utilities for immutable SQLite Estado files and mutable Runtime copies. */

require dirname(__DIR__, 2) . '/vendor/autoload.php';

const ESTADO_ROOT = __DIR__ . '/../../estados';
const MANIFEST_PATH = ESTADO_ROOT . '/manifesto.yml';
const DEFAULT_RUNTIME = __DIR__ . '/../../var/database/aculta-runtime.sqlite';

function fail(string $message, int $code = 1): never {
  fwrite(STDERR, $message . PHP_EOL);
  exit($code);
}

function sqlite(string $path, bool $readOnly = FALSE): PDO {
  if (!is_file($path)) {
    fail('SQLite file not found.');
  }
  $pdo = class_exists(\Pdo\Sqlite::class)
    ? new \Pdo\Sqlite('sqlite:' . $path)
    : new PDO('sqlite:' . $path);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  if (method_exists($pdo, 'createCollation')) {
    $pdo->createCollation('NOCASE_UTF8', [Drupal\Component\Utility\Unicode::class, 'strcasecmp']);
  }
  else {
    $pdo->sqliteCreateCollation('NOCASE_UTF8', [Drupal\Component\Utility\Unicode::class, 'strcasecmp']);
  }
  $pdo->exec('PRAGMA busy_timeout = 10000');
  if ($readOnly) {
    $pdo->exec('PRAGMA query_only = ON');
  }
  return $pdo;
}

function manifestStates(): array {
  if (!is_file(MANIFEST_PATH)) {
    return [];
  }
  $lines = file(MANIFEST_PATH, FILE_IGNORE_NEW_LINES) ?: [];
  $states = [];
  $current = NULL;
  foreach ($lines as $line) {
    if (preg_match('/^  - file:\s*(\S+)\s*$/', $line, $match)) {
      if ($current !== NULL) {
        $states[$current['file']] = $current;
      }
      $current = ['file' => $match[1]];
    }
    elseif ($current !== NULL && preg_match('/^    ([a-z0-9_]+):\s*(.*?)\s*$/', $line, $match)) {
      $current[$match[1]] = trim($match[2], " \t\"'");
    }
  }
  if ($current !== NULL) {
    $states[$current['file']] = $current;
  }
  return $states;
}

function validateState(string $path, bool $requireManifest = TRUE): array {
  $real = realpath($path);
  if ($real === FALSE || !is_file($real)) {
    fail('Estado file does not exist.');
  }
  $base = basename($real);
  $entry = manifestStates()[$base] ?? NULL;
  if ($requireManifest && !is_array($entry)) {
    fail('Estado is not listed in estados/manifesto.yml.');
  }
  $hash = hash_file('sha256', $real);
  if ($requireManifest && filesize($real) > 50000000) {
    fail('Estado exceeds the 50 MB Git size gate.');
  }
  if (is_array($entry) && !hash_equals(strtolower((string) ($entry['sha256'] ?? '')), strtolower($hash))) {
    fail('Estado SHA-256 does not match the manifest.');
  }
  $pdo = sqlite($real, TRUE);
  foreach (['quick_check', 'integrity_check'] as $pragma) {
    if ($pdo->query('PRAGMA ' . $pragma)->fetchColumn() !== 'ok') {
      fail('SQLite ' . $pragma . ' failed.');
    }
  }
  $tableNames = $pdo->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'")->fetchAll(PDO::FETCH_COLUMN);
  $tables = count($tableNames);
  $rows = 0;
  foreach ($tableNames as $table) {
    $quoted = '"' . str_replace('"', '""', $table) . '"';
    $rows += (int) $pdo->query('SELECT COUNT(*) FROM ' . $quoted)->fetchColumn();
  }
  if (is_array($entry) && ((int) ($entry['table_count'] ?? $tables) !== $tables || (int) ($entry['row_count'] ?? $rows) !== $rows)) {
    fail('Estado table/row counts do not match the manifest.');
  }
  return ['file' => $base, 'sha256' => $hash, 'bytes' => filesize($real), 'tables' => $tables, 'rows' => $rows];
}

$command = $argv[1] ?? '';
if ($command === 'validate') {
  $path = $argv[2] ?? '';
  if ($path === '') {
    fail('Usage: php estado.php validate <estado.sqlite>');
  }
  $result = validateState($path);
  printf("VALID file=%s bytes=%d tables=%d sha256=%s\n", $result['file'], $result['bytes'], $result['tables'], $result['sha256']);
  exit(0);
}

if ($command === 'validate-runtime') {
  $path = $argv[2] ?? DEFAULT_RUNTIME;
  $result = validateState($path, FALSE);
  printf("RUNTIME_VALID file=%s bytes=%d tables=%d sha256=%s\n", $result['file'], $result['bytes'], $result['tables'], $result['sha256']);
  exit(0);
}

if ($command === 'restore') {
  $statePath = $argv[2] ?? '';
  $runtimePath = $argv[3] ?? DEFAULT_RUNTIME;
  if ($statePath === '') {
    fail('Usage: php estado.php restore <estado.sqlite> [runtime.sqlite]');
  }
  $state = validateState($statePath);
  $stateReal = realpath($statePath);
  $runtimeDir = dirname($runtimePath);
  if (!is_dir($runtimeDir) && !mkdir($runtimeDir, 0770, TRUE) && !is_dir($runtimeDir)) {
    fail('Unable to create Runtime directory.');
  }
  if (realpath($runtimePath) === $stateReal) {
    fail('Drupal Runtime cannot point directly at an Estado file.');
  }
  if (is_file($runtimePath)) {
    $current = sqlite($runtimePath);
    $checkpoint = $current->query('PRAGMA wal_checkpoint(TRUNCATE)')->fetch(PDO::FETCH_NUM);
    if (is_array($checkpoint) && (int) ($checkpoint[0] ?? 0) !== 0) {
      fail('Runtime WAL is busy; close Drupal requests and retry restore.');
    }
    $current->query('PRAGMA journal_mode=DELETE')->fetchColumn();
    $current = NULL;
  }
  foreach ([$runtimePath . '-wal', $runtimePath . '-shm'] as $sidecar) {
    if (is_file($sidecar) && filesize($sidecar) > 0) {
      fail('Runtime SQLite sidecar remains after checkpoint; close Drupal and retry.');
    }
    if (is_file($sidecar) && !unlink($sidecar)) {
      fail('Unable to remove an empty SQLite sidecar.');
    }
  }
  if (is_file($runtimePath)) {
    $backup = $runtimePath . '.pre-restore-' . gmdate('Ymd-His') . '.bak';
    if (!rename($runtimePath, $backup)) {
      fail('Unable to preserve the previous Runtime.');
    }
    printf("RUNTIME_BACKUP=%s\n", $backup);
  }
  $temporary = $runtimePath . '.restore-tmp';
  if (!copy($stateReal, $temporary)) {
    fail('Unable to copy Estado to Runtime.');
  }
  if (!hash_equals($state['sha256'], hash_file('sha256', $temporary))) {
    @unlink($temporary);
    fail('Copied Runtime does not match the Estado SHA-256.');
  }
  if (!rename($temporary, $runtimePath)) {
    @unlink($temporary);
    fail('Unable to activate Runtime copy.');
  }
  @chmod($runtimePath, 0660);
  $runtime = validateState($runtimePath, FALSE);
  printf("RESTORED runtime=%s bytes=%d tables=%d rows=%d estado_sha256=%s\n", $runtimePath, $runtime['bytes'], $runtime['tables'], $runtime['rows'], $state['sha256']);
  printf("NEXT: run Drush status, config:status and updatedb:status before any mutating command.\n");
  exit(0);
}

if ($command === 'list') {
  foreach (manifestStates() as $entry) {
    $path = ESTADO_ROOT . '/' . $entry['file'];
    if (!is_file($path)) {
      printf("MISSING file=%s\n", $entry['file']);
      continue;
    }
    printf("%s\t%s\t%d bytes\t%s\n", $entry['file'], $entry['milestone'] ?? 'unknown', filesize($path), hash_file('sha256', $path));
  }
  exit(0);
}

fail('Usage: php estado.php {validate|validate-runtime|restore|list} ...', 2);
