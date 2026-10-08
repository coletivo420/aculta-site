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

$token_value = static function (string $block, string $name): ?string {
  if (preg_match('/(?:^|\n)\s*' . preg_quote($name, '/') . '\s*:\s*([^;]+);/m', $block, $matches) !== 1) {
    return NULL;
  }
  return trim($matches[1]);
};
$resolve_hex = static function (string $block, string $name) use ($token_value, $light_block): ?array {
  $active_block = $block;
  for ($depth = 0; $depth < 8; $depth++) {
    $value = $token_value($active_block, $name);
    if ($value === NULL) {
      if ($active_block !== $light_block) {
        $active_block = $light_block;
        continue;
      }
      return NULL;
    }
    if (preg_match('/^var\((--[a-z0-9-]+)\)$/i', $value, $matches) === 1) {
      $name = $matches[1];
      $active_block = $block;
      continue;
    }
    if (preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $value, $matches) !== 1) {
      return NULL;
    }
    $hex = strtolower($matches[1]);
    if (strlen($hex) === 3) {
      $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
  }
  return NULL;
};
$contrast = static function (array $foreground, array $background): float {
  $luminance = static function (array $rgb): float {
    $channels = array_map(static function (int $channel): float {
      $value = $channel / 255;
      return $value <= 0.04045 ? $value / 12.92 : (($value + 0.055) / 1.055) ** 2.4;
    }, $rgb);
    return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
  };
  $first = $luminance($foreground);
  $second = $luminance($background);
  return (max($first, $second) + 0.05) / (min($first, $second) + 0.05);
};

$dark_surface_tokens = [
  '--aculta-surface-page',
  '--aculta-surface-raised',
  '--aculta-surface-muted',
  '--aculta-surface-header',
  '--aculta-surface-interactive',
  '--aculta-shell-institution-bg',
  '--aculta-shell-domain-bg',
];
foreach ($dark_surface_tokens as $token) {
  $value = $token_value($dark_block, $token) ?? '';
  $check($value !== '' && !str_contains($value, '--aculta-green'), $token . ' uses a neutral dark surface, not a green palette surface.');
}

$dark_contrast_pairs = [
  ['--aculta-text-primary', '--aculta-surface-page'],
  ['--aculta-text-primary', '--aculta-surface-raised'],
  ['--aculta-text-secondary', '--aculta-surface-raised'],
  ['--aculta-text-muted', '--aculta-surface-raised'],
  ['--aculta-text-accent', '--aculta-surface-raised'],
  ['--aculta-interactive-text', '--aculta-interactive-bg'],
  ['--aculta-interactive-hover-text', '--aculta-interactive-hover-bg'],
  ['--aculta-interactive-active-text', '--aculta-interactive-active-bg'],
  ['--aculta-shell-institution-text', '--aculta-shell-institution-bg'],
  ['--aculta-shell-domain-text', '--aculta-shell-domain-bg'],
];
$dark_contrast_results = [];
foreach ($dark_contrast_pairs as [$foreground_token, $background_token]) {
  $foreground = $resolve_hex($dark_block, $foreground_token);
  $background = $resolve_hex($dark_block, $background_token);
  $ratio = $foreground !== NULL && $background !== NULL ? $contrast($foreground, $background) : 0.0;
  $dark_contrast_results[$foreground_token . ' / ' . $background_token] = $ratio;
  $check($ratio >= 4.5, $foreground_token . ' / ' . $background_token . ' meets WCAG AA for normal text.');
}
$focus_surfaces = ['--aculta-surface-page', '--aculta-surface-raised', '--aculta-surface-muted', '--aculta-surface-header', '--aculta-surface-interactive'];
$focus_ring = $resolve_hex($dark_block, '--aculta-focus-ring');
$focus_contrast_results = [];
foreach ($focus_surfaces as $surface_token) {
  $surface = $resolve_hex($dark_block, $surface_token);
  $ratio = $focus_ring !== NULL && $surface !== NULL ? $contrast($focus_ring, $surface) : 0.0;
  $focus_contrast_results[$surface_token] = $ratio;
  $check($ratio >= 3.0, '--aculta-focus-ring has at least 3:1 contrast against ' . $surface_token . '.');
}

$rgb_mappings = [
  ['--bs-body-bg-rgb', '--aculta-surface-page'],
  ['--bs-body-color-rgb', '--aculta-text-primary'],
  ['--bs-secondary-bg-rgb', '--aculta-surface-raised'],
  ['--bs-tertiary-bg-rgb', '--aculta-surface-muted'],
  ['--bs-light-rgb', '--aculta-surface-muted'],
  ['--bs-dark-rgb', '--aculta-surface-page'],
  ['--bs-secondary-color-rgb', '--aculta-text-secondary'],
  ['--bs-tertiary-color-rgb', '--aculta-text-muted'],
  ['--bs-emphasis-color-rgb', '--aculta-text-primary'],
  ['--bs-link-color-rgb', '--aculta-text-primary'],
  ['--bs-link-hover-color-rgb', '--aculta-text-primary'],
];
foreach ($rgb_mappings as [$rgb_token, $source_token]) {
  $rgb = $resolve_hex($dark_block, $source_token);
  $actual = $token_value($dark_block, $rgb_token);
  $expected = $rgb !== NULL ? implode(',', $rgb) : '';
  if (is_string($actual) && preg_match('/^var\((--[a-z0-9-]+)\)$/i', $actual, $matches) === 1) {
    $actual = $token_value($dark_block, $matches[1]);
  }
  $actual_normalized = is_string($actual) ? preg_replace('/\s+/', '', $actual) : '';
  $check($expected !== '' && $actual_normalized === $expected, $rgb_token . ' exactly matches ' . $source_token . '.');
}
$check(preg_match('/--bs-border-color\s*:\s*var\(--aculta-border-default\)/', $dark_block) === 1, 'Bootstrap border color maps to the semantic default border.');

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
$mode_contract_pattern = '/data-bs-theme|prefers-color-scheme|theme-dark|dark-mode|color[-_ ]mode/i';
$structural_dark_overrides = 0;
$dark_twig_branches = 0;
$dark_php_branches = 0;
$dark_js_layout_behavior = 0;
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
  if ($extension === 'css' && $path !== $tokens_path && preg_match($mode_contract_pattern, $runtime_source) === 1) {
    $structural_dark_overrides++;
  }
  if ($extension === 'twig' && preg_match($mode_contract_pattern, $runtime_source) === 1) {
    $dark_twig_branches++;
  }
  if (in_array($extension, ['php', 'module', 'inc', 'theme'], TRUE) && preg_match($mode_contract_pattern, $runtime_source) === 1) {
    $dark_php_branches++;
  }
  if ($extension === 'js' && preg_match($mode_contract_pattern, $runtime_source) === 1) {
    $dark_js_layout_behavior++;
  }
  $check(preg_match($auth_pattern, $runtime_source) !== 1, 'No auth/anti-bot integration reference in ' . $relative . '.');
  $check(preg_match($domain_pattern, $runtime_source) !== 1, 'No Domain resolution or Portal service reference in ' . $relative . '.');
}
$check($structural_dark_overrides === 0, 'Color mode is token-only: no dark selector or mode query exists outside tokens.css.');
$check($dark_twig_branches === 0, 'Twig has no color-mode branch.');
$check($dark_php_branches === 0, 'PHP has no color-mode branch.');
$check($dark_js_layout_behavior === 0, 'JavaScript has no color-mode layout behavior.');

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
fwrite(STDOUT, "COLOR MODE TOKEN-ONLY: PASS\n");
fwrite(STDOUT, "STRUCTURAL DARK OVERRIDES: $structural_dark_overrides\n");
fwrite(STDOUT, "DARK TWIG BRANCHES: $dark_twig_branches\n");
fwrite(STDOUT, "DARK PHP BRANCHES: $dark_php_branches\n");
fwrite(STDOUT, "DARK JS LAYOUT BEHAVIOR: $dark_js_layout_behavior\n");
foreach ($dark_contrast_results as $pair => $ratio) {
  fwrite(STDOUT, sprintf("DARK CONTRAST %s: %.2f:1\n", $pair, $ratio));
}
foreach ($focus_contrast_results as $surface => $ratio) {
  fwrite(STDOUT, sprintf("DARK FOCUS CONTRAST %s: %.2f:1\n", $surface, $ratio));
}
