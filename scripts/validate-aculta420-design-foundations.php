<?php

declare(strict_types=1);

/**
 * Static checks for the ACULTA420 0.2-A semantic design foundations.
 *
 * This validator deliberately does not bootstrap Drupal and does not test
 * Portal behavior. It checks the theme's token and ownership boundaries only.
 */

$root = dirname(__DIR__);
$theme = $root . '/web/themes/custom/aculta420';
$failures = [];
$checks = 0;

$check = static function (bool $condition, string $message) use (&$checks, &$failures): void {
  $checks++;
  if (!$condition) {
    $failures[] = $message;
  }
};

$tokens_path = $theme . '/css/tokens.css';
$tokens_css = is_file($tokens_path) ? file_get_contents($tokens_path) : FALSE;
$check(is_string($tokens_css), 'Semantic token stylesheet exists and is readable.');

$required_tokens = [
  '--aculta-surface-page',
  '--aculta-surface-raised',
  '--aculta-surface-muted',
  '--aculta-surface-header',
  '--aculta-surface-interactive',
  '--aculta-text-primary',
  '--aculta-text-secondary',
  '--aculta-text-muted',
  '--aculta-text-inverse',
  '--aculta-text-accent',
  '--aculta-border-subtle',
  '--aculta-border-default',
  '--aculta-border-strong',
  '--aculta-border-accent',
  '--aculta-interactive-bg',
  '--aculta-interactive-text',
  '--aculta-interactive-border',
  '--aculta-interactive-hover-bg',
  '--aculta-interactive-hover-text',
  '--aculta-interactive-active-bg',
  '--aculta-interactive-active-text',
  '--aculta-focus-ring',
  '--aculta-shell-institution-bg',
  '--aculta-shell-institution-text',
  '--aculta-shell-domain-bg',
  '--aculta-shell-domain-text',
  '--aculta-shell-border',
  '--aculta-shell-active-bg',
  '--aculta-shell-active-text',
];

$extract_block = static function (string $css, string $selector_pattern): string {
  if (!preg_match('/' . $selector_pattern . '\s*\{([^}]*)\}/s', $css, $matches)) {
    return '';
  }
  return $matches[1];
};

$light_block = is_string($tokens_css)
  ? $extract_block($tokens_css, ':root,\s*\[data-bs-theme="light"\]')
  : '';
$dark_block = is_string($tokens_css)
  ? $extract_block($tokens_css, '\[data-bs-theme="dark"\]')
  : '';
$check($light_block !== '', 'Light token mode is declared.');
$check($dark_block !== '', 'Dark token mode is declared.');

foreach ($required_tokens as $token) {
  $check(preg_match('/' . preg_quote($token, '/') . '\s*:\s*[^;]+;/', $light_block) === 1, $token . ' has a light value.');
  $check(preg_match('/' . preg_quote($token, '/') . '\s*:\s*[^;]+;/', $dark_block) === 1, $token . ' has a dark value.');
}

$check(is_string($tokens_css) && preg_match('/--bs-body-bg\s*:\s*var\(--aculta-surface-page\)/', $tokens_css) === 1, 'Bootstrap body background maps to the semantic page surface.');
$check(is_string($tokens_css) && preg_match('/--bs-body-color\s*:\s*var\(--aculta-text-primary\)/', $tokens_css) === 1, 'Bootstrap body text maps to semantic primary text.');

$runtime_extensions = ['php', 'module', 'inc', 'theme', 'twig', 'yml', 'yaml', 'js', 'css'];
$runtime_files = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($theme, FilesystemIterator::SKIP_DOTS));
foreach ($iterator as $file) {
  if (!$file->isFile() || !in_array(strtolower($file->getExtension()), $runtime_extensions, TRUE)) {
    continue;
  }
  $path = $file->getPathname();
  if (str_contains($path, DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR)) {
    continue;
  }
  $runtime_files[] = $path;
}

$auth_pattern = '/social_auth(?:_google)?|social_auth_login|captcha(?:\.settings)?|turnstile|social_auth_google\.settings|GOOGLE_OAUTH_CLIENT_(?:ID|SECRET)|\boauth\b/i';
$domain_pattern = '/DomainInterface|DomainPurposeManager|domain\.negotiator|aculta_portal|HTTP_HOST|SERVER_NAME|\bhostname\b|\bgetHost\s*\(|\bgetHostname\s*\(|(?:getStorage|storage)\s*\(\s*[\'"]domain|\\Drupal\s*::/i';
$theme_info = file_get_contents($theme . '/aculta420.info.yml') ?: '';
$theme_libraries = file_get_contents($theme . '/aculta420.libraries.yml') ?: '';
$check(preg_match($auth_pattern, $theme_info . "\n" . $theme_libraries) !== 1, 'Theme metadata and libraries have no authentication or anti-bot dependencies.');

foreach ($runtime_files as $path) {
  $source = file_get_contents($path);
  if (!is_string($source)) {
    $check(FALSE, 'Runtime file is readable: ' . substr($path, strlen($theme) + 1));
    continue;
  }
  $relative = substr($path, strlen($theme) + 1);
  $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
  $runtime_source = preg_replace('~/\*.*?\*/|<!--.*?-->|\{#.*?#\}~s', '', $source) ?? $source;
  if (in_array($extension, ['php', 'module', 'inc', 'theme', 'js'], TRUE)) {
    $runtime_source = preg_replace('/^[ \t]*(?:\/\/|#).*$/m', '', $runtime_source) ?? $runtime_source;
  }
  elseif (in_array($extension, ['yml', 'yaml'], TRUE)) {
    $runtime_source = preg_replace('/^[ \t]*#.*$/m', '', $runtime_source) ?? $runtime_source;
  }
  $check(preg_match($auth_pattern, $runtime_source) !== 1, 'No auth/anti-bot integration reference in ' . $relative . '.');
  $check(preg_match($domain_pattern, $runtime_source) !== 1, 'No Domain resolution or Portal service reference in ' . $relative . '.');
}

$css_files = array_filter($runtime_files, static fn (string $path): bool => strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'css' && $path !== $tokens_path);
foreach ($css_files as $path) {
  $source = file_get_contents($path);
  if (!is_string($source)) {
    continue;
  }
  $relative = substr($path, strlen($theme) + 1);
  $check(preg_match('/#[0-9a-f]{3,8}\b|\b(?:rgb|rgba|hsl|hsla)\s*\(/i', $source) !== 1, 'CSS literals outside tokens.css need semantic tokens: ' . $relative . '.');
}

$catch_all = [];
foreach (['style.css', 'responsive.css'] as $name) {
  if (is_file($theme . '/css/' . $name)) {
    $catch_all[] = $name;
  }
}
$check($catch_all === [], 'No catch-all CSS file has been introduced.');

$bootstrap_copies = [];
foreach ($iterator as $file) {
  if ($file->isFile() && preg_match('/^bootstrap(?:\.min)?\.(?:css|js)$/i', $file->getFilename()) === 1) {
    $bootstrap_copies[] = $file->getPathname();
  }
}
$check($bootstrap_copies === [], 'The theme does not bundle a duplicate Bootstrap asset.');

$js_files = array_filter($runtime_files, static fn (string $path): bool => strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'js');
foreach ($js_files as $path) {
  $source = file_get_contents($path);
  if (!is_string($source)) {
    continue;
  }
  $relative = substr($path, strlen($theme) + 1);
  $check(preg_match('/localStorage|sessionStorage|prefers-color-scheme|setAttribute\s*\(\s*[\'"]data-bs-theme|color[-_ ]mode/i', $source) !== 1, 'No premature color-mode script in ' . $relative . '.');
}

if ($failures !== []) {
  fwrite(STDERR, "ACULTA420 DESIGN FOUNDATIONS: FAIL (" . count($failures) . "/$checks checks)\n");
  foreach ($failures as $failure) {
    fwrite(STDERR, "- $failure\n");
  }
  exit(1);
}

fwrite(STDOUT, "ACULTA420 DESIGN FOUNDATIONS: PASS ($checks checks)\n");
