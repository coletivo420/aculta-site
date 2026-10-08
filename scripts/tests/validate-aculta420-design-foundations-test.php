<?php

declare(strict_types=1);

/**
 * Negative regression fixtures for the ACULTA420 design-foundation validator.
 * All fixtures use a copied stylesheet and synthetic source files in a temp dir.
 */

$repository = dirname(__DIR__, 2);
$validator = $repository . '/scripts/validate-aculta420-design-foundations.php';
$theme_source = $repository . '/web/themes/custom/aculta420';
$temporary_root = sys_get_temp_dir() . '/aculta420-design-gate-' . bin2hex(random_bytes(6));
$temporary_theme = $temporary_root . '/web/themes/custom/aculta420';
$fixtures = 0;
$failures = [];

$copy_file = static function (string $source, string $destination): void {
  if (!is_dir(dirname($destination)) && !mkdir(dirname($destination), 0700, TRUE) && !is_dir(dirname($destination))) {
    throw new RuntimeException('Could not create fixture directory.');
  }
  if (!copy($source, $destination)) {
    throw new RuntimeException('Could not copy fixture file.');
  }
};
$remove_tree = static function (string $path) use (&$remove_tree): void {
  if (!is_dir($path)) {
    if (file_exists($path) || is_link($path)) {
      unlink($path);
    }
    return;
  }
  foreach (new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS) as $entry) {
    $remove_tree($entry->getPathname());
  }
  rmdir($path);
};

try {
  foreach ([
    'css/tokens.css',
    'aculta420.info.yml',
    'aculta420.libraries.yml',
  ] as $relative) {
    $copy_file($theme_source . '/' . $relative, $temporary_theme . '/' . $relative);
  }
  $copy_file($validator, $temporary_root . '/scripts/validate-aculta420-design-foundations.php');

  $run_validator = static function () use ($temporary_root): array {
    $command = [PHP_BINARY, $temporary_root . '/scripts/validate-aculta420-design-foundations.php', '--root=' . $temporary_root];
    $process = proc_open($command, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    if (!is_resource($process)) {
      throw new RuntimeException('Could not start validator fixture process.');
    }
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    return [proc_close($process), (string) $stdout . (string) $stderr];
  };
  // A clean baseline proves each failure below is caused by its injected fixture.
  [$baseline_status, $baseline_output] = $run_validator();
  $fixtures++;
  if ($baseline_status !== 0) {
    $failures[] = 'clean fixture baseline did not pass';
  }

  $cases = [
    ['.dark selector layout rule', 'css/fixtures/dark.css', '.dark .card { padding: 1rem; }', 'Color mode is token-only'],
    ['.dark-theme selector layout rule', 'css/fixtures/dark.css', '.dark-theme .card { padding: 1rem; }', 'Color mode is token-only'],
    ['data-theme selector layout rule', 'css/fixtures/dark.css', '[data-theme="dark"] .card { display: none; }', 'Color mode is token-only'],
    ['data-bs-theme selector layout rule', 'css/fixtures/dark.css', '[data-bs-theme="dark"] .card { display: none; }', 'Color mode is token-only'],
    ['light selector layout rule', 'css/fixtures/light.css', '.light-theme .card { padding: 2rem; }', 'Color mode is token-only'],
    ['Twig color-mode branch', 'templates/fixture.html.twig', "{% if theme == 'dark' %}dark markup{% endif %}", 'Twig has no color-mode branch'],
    ['Twig color-mode ternary', 'templates/ternary.html.twig', "{{ theme == 'light' ? 'light' : 'dark' }}", 'Twig has no color-mode branch'],
    ['PHP color-mode branch', 'src/Fixture.php', "<?php if (\$theme === 'dark') { echo 'different'; }", 'PHP has no color-mode branch'],
    ['PHP color-mode match', 'src/MatchFixture.php', "<?php \$variant = match (\$theme) { 'dark' => 'compact', default => 'standard' };", 'PHP has no color-mode branch'],
    ['JavaScript color-mode layout branch', 'js/fixture.js', "if (theme === 'dark') { card.style.display = 'none'; }", 'JavaScript has no color-mode layout behavior'],
    ['JavaScript boolean mode branch', 'js/boolean-mode.js', "if (isDarkMode) { card.hidden = true; }", 'JavaScript has no color-mode layout behavior'],
  ];
  foreach ($cases as [$name, $relative, $contents, $expected_message]) {
    $fixture_path = $temporary_theme . '/' . $relative;
    if (!is_dir(dirname($fixture_path)) && !mkdir(dirname($fixture_path), 0700, TRUE) && !is_dir(dirname($fixture_path))) {
      throw new RuntimeException('Could not create source fixture directory.');
    }
    file_put_contents($fixture_path, $contents);
    [$status, $output] = $run_validator();
    $fixtures++;
    if ($status === 0 || !str_contains($output, $expected_message)) {
      $failures[] = $name . ' was not rejected for the expected reason: ' . trim(preg_replace('/\s+/', ' ', $output) ?? $output);
    }
    unlink($fixture_path);
  }

  $tokens_path = $temporary_theme . '/css/tokens.css';
  $original_tokens = file_get_contents($tokens_path);
  if (!is_string($original_tokens)) {
    throw new RuntimeException('Could not read token fixture.');
  }
  $dark_start = strpos($original_tokens, '[data-bs-theme="dark"]');
  if ($dark_start === FALSE) {
    throw new RuntimeException('Dark token block is missing from fixture.');
  }
  $replace_dark = static function (string $old, string $new) use (&$original_tokens, $dark_start, $tokens_path): void {
    $current_tokens = file_get_contents($tokens_path);
    if (!is_string($current_tokens)) {
      throw new RuntimeException('Could not read current token fixture.');
    }
    $position = strpos($current_tokens, $old, $dark_start);
    if ($position === FALSE) {
      throw new RuntimeException('Expected dark token fixture declaration was not found.');
    }
    $updated = substr_replace($current_tokens, $new, $position, strlen($old));
    file_put_contents($tokens_path, $updated);
  };
  $restore_tokens = static function () use (&$original_tokens, $tokens_path): void {
    file_put_contents($tokens_path, $original_tokens);
  };
  $token_cases = [
    ['approved page surface rejected when green legacy value returns', '--aculta-surface-page: #171513;', '--aculta-surface-page: #121a16;', 'resolves to approved neutral dark surface'],
    ['intermediate alias to prohibited green surface rejected', '--aculta-surface-page: #171513;', "--aculta-surface-page: var(--fixture-surface);\n  --fixture-surface: #1b2720;", 'resolves to approved neutral dark surface'],
    ['conflicting duplicate token rejected', '--aculta-surface-page: #171513;', "--aculta-surface-page: #171513;\n  --aculta-surface-page: #121a16;", 'no duplicate declaration'],
    ['last duplicate is used for WCAG evaluation', '--aculta-text-primary: #f4efe8;', "--aculta-text-primary: #f4efe8;\n  --aculta-text-primary: #171513;", 'meets WCAG AA'],
    ['circular token references rejected', "--aculta-text-primary: #f4efe8;\n  --aculta-text-secondary: #d8d0c8;", "--aculta-text-primary: var(--aculta-text-secondary);\n  --aculta-text-secondary: var(--aculta-text-primary);", 'resolves without missing or circular references'],
    ['missing required token rejected', '--aculta-shell-active-text: var(--aculta-interactive-active-text);', '', 'has a dark value'],
  ];
  foreach ($token_cases as [$name, $old, $new, $expected_message]) {
    $replace_dark($old, $new);
    [$status, $output] = $run_validator();
    $fixtures++;
    if ($status === 0 || !str_contains($output, $expected_message)) {
      $failures[] = $name . ' was not rejected for the expected reason: ' . trim(preg_replace('/\s+/', ' ', $output) ?? $output);
    }
    $restore_tokens();
  }

  if ($failures === []) {
    fwrite(STDOUT, 'DESIGN FOUNDATION NEGATIVE FIXTURES: PASS (' . $fixtures . " expected outcomes)\n");
  }
}
finally {
  $remove_tree($temporary_root);
}

if ($failures !== []) {
  fwrite(STDERR, 'DESIGN FOUNDATION NEGATIVE FIXTURES: FAIL (' . count($failures) . '/' . $fixtures . " expected outcomes)\n");
  foreach ($failures as $failure) {
    fwrite(STDERR, '- ' . $failure . "\n");
  }
  exit(1);
}
