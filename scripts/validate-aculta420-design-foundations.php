<?php

declare(strict_types=1);

/**
 * Static checks for the ACULTA420 0.2-A semantic design foundations.
 *
 * This validator deliberately does not bootstrap Drupal and does not test
 * Portal behavior. It checks the theme's token and ownership boundaries only.
 */

$options = getopt('', ['root:']);
$root_argument = $options['root'] ?? dirname(__DIR__);
if (!is_string($root_argument) || !str_starts_with($root_argument, DIRECTORY_SEPARATOR) || !is_dir($root_argument)) {
  fwrite(STDERR, "Validator root must be an existing absolute directory.\n");
  exit(2);
}
$root = realpath($root_argument) ?: $root_argument;
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

$tokens_css_without_comments = is_string($tokens_css)
  ? preg_replace('~/\*.*?\*/~s', '', $tokens_css)
  : '';
$extract_blocks = static function (string $css, string $wanted_selector): array {
  preg_match_all('/([^{}]+)\{([^{}]*)\}/s', $css, $matches, PREG_SET_ORDER);
  $normalize = static fn (string $selector): string => strtolower(preg_replace('/\s+/', '', trim($selector)) ?? trim($selector));
  $blocks = [];
  foreach ($matches as $match) {
    if ($normalize($match[1]) === $normalize($wanted_selector)) {
      $blocks[] = $match[2];
    }
  }
  return $blocks;
};
$light_blocks = is_string($tokens_css_without_comments)
  ? $extract_blocks($tokens_css_without_comments, ':root, [data-bs-theme="light"]')
  : [];
$dark_blocks = is_string($tokens_css_without_comments)
  ? $extract_blocks($tokens_css_without_comments, '[data-bs-theme="dark"]')
  : [];
$check($light_blocks !== [], 'Light token mode is declared.');
$check($dark_blocks !== [], 'Dark token mode is declared.');

$parse_declarations = static function (array $blocks): array {
  $declarations = [];
  foreach ($blocks as $block) {
    preg_match_all('/(?:^|;)\s*(--[a-zA-Z0-9_-]+)\s*:\s*([^;]*?)\s*(?=;|$)/m', $block, $matches, PREG_SET_ORDER);
    foreach ($matches as $match) {
      $declarations[$match[1]][] = trim($match[2]);
    }
  }
  return $declarations;
};
$light_declarations = $parse_declarations($light_blocks);
$dark_declarations = $parse_declarations($dark_blocks);
$supported_token_selectors = [':root, [data-bs-theme="light"]', '[data-bs-theme="dark"]'];
if (is_string($tokens_css_without_comments)) {
  preg_match_all('/([^{}]+)\{([^{}]*)\}/s', $tokens_css_without_comments, $token_rules, PREG_SET_ORDER);
  foreach ($token_rules as $rule) {
    $selector = strtolower(preg_replace('/\s+/', '', trim($rule[1])) ?? trim($rule[1]));
    if (in_array($selector, array_map(static fn (string $value): string => strtolower(preg_replace('/\s+/', '', $value) ?? $value), $supported_token_selectors), TRUE)) {
      continue;
    }
    if (preg_match('/--(?:aculta|bs)-[a-z0-9_-]+\s*:/i', $rule[2]) === 1) {
      $check(FALSE, 'ACULTA and Bootstrap tokens are declared only in the supported root/light and dark mode blocks.');
    }
  }
}
foreach ($required_tokens as $token) {
  $check(isset($light_declarations[$token]), $token . ' has a light value.');
  $check(isset($dark_declarations[$token]), $token . ' has a dark value.');
}

$check(is_string($tokens_css_without_comments) && preg_match('/--bs-body-bg\s*:\s*var\(--aculta-surface-page\)/', $tokens_css_without_comments) === 1, 'Bootstrap body background maps to the semantic page surface.');
$check(is_string($tokens_css_without_comments) && preg_match('/--bs-body-color\s*:\s*var\(--aculta-text-primary\)/', $tokens_css_without_comments) === 1, 'Bootstrap body text maps to semantic primary text.');

$collapse_declarations = static function (array $declarations): array {
  $effective = [];
  foreach ($declarations as $name => $values) {
    if ($values !== []) {
      $effective[$name] = $values[array_key_last($values)];
    }
  }
  return $effective;
};
$light_effective = $collapse_declarations($light_declarations);
$dark_effective = array_replace($light_effective, $collapse_declarations($dark_declarations));

$protected_tokens = array_values(array_unique(array_merge(
  $required_tokens,
  ['--aculta-surface-page-rgb', '--aculta-button-primary-bg', '--aculta-button-primary-text', '--aculta-button-secondary-bg', '--aculta-button-secondary-text', '--aculta-button-secondary-border', '--aculta-button-hover-bg', '--aculta-button-hover-text', '--aculta-nav-current-bg', '--aculta-nav-current-text', '--aculta-nav-current-underline', '--aculta-nav-hover-bg', '--aculta-nav-hover-text', '--aculta-nav-hover-underline', '--aculta-link-editorial', '--aculta-link-editorial-hover', '--aculta-link', '--aculta-border', '--aculta-control-border', '--aculta-focus', '--aculta-shadow-hover', '--aculta-motion-fast', '--aculta-ease-standard']
)));
foreach (['light' => $light_declarations, 'dark' => $dark_declarations] as $mode => $declarations) {
  foreach ($declarations as $name => $values) {
    if (preg_match('/^--(?:aculta|bs)-/', $name) === 1) {
      $check(count($values) === 1, ucfirst($mode) . ' token block has no duplicate declaration for ' . $name . '.');
    }
  }
}

$resolve_token = NULL;
$resolve_token = static function (string $name, array $effective, array $stack = []) use (&$resolve_token): array {
  if (in_array($name, $stack, TRUE)) {
    return ['value' => NULL, 'error' => 'circular token reference at ' . $name];
  }
  if (!array_key_exists($name, $effective)) {
    return ['value' => NULL, 'error' => 'unresolved token reference ' . $name];
  }
  $stack[] = $name;
  $value = trim($effective[$name]);
  $error = NULL;
  $resolved = preg_replace_callback('/var\(\s*(--[a-zA-Z0-9_-]+)\s*\)/', static function (array $matches) use (&$resolve_token, $effective, $stack, &$error): string {
    $result = $resolve_token($matches[1], $effective, $stack);
    if ($result['error'] !== NULL) {
      $error = $result['error'];
      return '';
    }
    return $result['value'];
  }, $value);
  if ($error !== NULL) {
    return ['value' => NULL, 'error' => $error];
  }
  if (str_contains((string) $resolved, 'var(')) {
    return ['value' => NULL, 'error' => 'unsupported or unresolved var() syntax in ' . $name];
  }
  return ['value' => trim((string) $resolved), 'error' => NULL];
};

$all_custom_tokens = array_values(array_unique(array_merge(array_keys($light_declarations), array_keys($dark_declarations))));
foreach (['light' => $light_effective, 'dark' => $dark_effective] as $mode => $effective) {
  foreach (array_intersect($protected_tokens, $all_custom_tokens) as $token) {
    $resolved = $resolve_token($token, $effective);
    $check($resolved['error'] === NULL, ucfirst($mode) . ' token ' . $token . ' resolves without missing or circular references.');
  }
}

$resolve_hex = static function (array $effective, string $name) use ($resolve_token): ?array {
  $resolved = $resolve_token($name, $effective);
  if ($resolved['error'] !== NULL || preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $resolved['value'] ?? '') !== 1) {
    return NULL;
  }
  preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $resolved['value'], $matches);
  $hex = strtolower($matches[1]);
  if (strlen($hex) === 3) {
    $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
  }
  return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
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

$dark_surface_contract = [
  '--aculta-surface-page' => '#171513',
  '--aculta-surface-raised' => '#211e1b',
  '--aculta-surface-muted' => '#2b2723',
  '--aculta-surface-interactive' => '#302b27',
  '--aculta-surface-header' => '#211e1b',
  '--aculta-shell-institution-bg' => '#2b2723',
  '--aculta-shell-domain-bg' => '#211e1b',
];
foreach ($dark_surface_contract as $token => $expected) {
  $resolved = $resolve_token($token, $dark_effective);
  $actual = strtolower($resolved['value'] ?? '');
  $check($resolved['error'] === NULL && $actual === $expected, $token . ' resolves to approved neutral dark surface ' . $expected . '.');
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
  $foreground = $resolve_hex($dark_effective, $foreground_token);
  $background = $resolve_hex($dark_effective, $background_token);
  $ratio = $foreground !== NULL && $background !== NULL ? $contrast($foreground, $background) : 0.0;
  $dark_contrast_results[$foreground_token . ' / ' . $background_token] = $ratio;
  $check($ratio >= 4.5, $foreground_token . ' / ' . $background_token . ' meets WCAG AA for normal text.');
}
$focus_surfaces = ['--aculta-surface-page', '--aculta-surface-raised', '--aculta-surface-muted', '--aculta-surface-header', '--aculta-surface-interactive'];
$focus_ring = $resolve_hex($dark_effective, '--aculta-focus-ring');
$focus_contrast_results = [];
foreach ($focus_surfaces as $surface_token) {
  $surface = $resolve_hex($dark_effective, $surface_token);
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
  $rgb = $resolve_hex($dark_effective, $source_token);
  $actual_resolved = $resolve_token($rgb_token, $dark_effective);
  $actual = $actual_resolved['error'] === NULL ? $actual_resolved['value'] : NULL;
  $expected = $rgb !== NULL ? implode(',', $rgb) : '';
  $actual_normalized = is_string($actual) ? preg_replace('/\s+/', '', $actual) : '';
  $check($expected !== '' && $actual_normalized === $expected, $rgb_token . ' exactly matches ' . $source_token . '.');
}
$border_mapping = $resolve_token('--bs-border-color', $dark_effective);
$check($border_mapping['error'] === NULL && $border_mapping['value'] === ($resolve_token('--aculta-border-default', $dark_effective)['value'] ?? NULL), 'Bootstrap border color maps to the semantic default border.');

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
  if ($extension === 'css' && $path !== $tokens_path) {
    preg_match_all('/([^{}]+)\{/s', $runtime_source, $selector_matches);
    foreach ($selector_matches[1] as $selector) {
      if (preg_match('/\[\s*data-(?:(?:bs-)?theme|color-mode|color-scheme)\s*=\s*(["\']?)(?:dark|light)\1\s*\]/i', $selector) === 1
        || preg_match('/\.(?:dark|light|dark-theme|light-theme|theme-dark|theme-light|dark-mode|light-mode|is-dark|is-light|color-mode-dark|color-mode-light)(?![a-zA-Z0-9_-])/i', $selector) === 1
        || preg_match('/prefers-color-scheme\s*:\s*(?:dark|light)/i', $selector) === 1) {
        $structural_dark_overrides++;
      }
    }
  }
  if ($extension === 'twig') {
    preg_match_all('/\{%\s*(?:if|elseif)\s+(.+?)\s*%\}|\{\{\s*(.+?\?.+?)\s*\}\}/s', $runtime_source, $twig_conditions, PREG_SET_ORDER);
    foreach ($twig_conditions as $condition_match) {
      $condition = ($condition_match[1] ?? '') !== '' ? $condition_match[1] : ($condition_match[2] ?? '');
      $mode_reference = preg_match('/\b(?:theme|mode)\b|color[_-]?mode/i', $condition) === 1;
      $mode_literal = preg_match('/(?:["\'](?:dark|light)["\']|\b(?:dark|light)\b)/i', $condition) === 1;
      $boolean_mode = preg_match('/\b(?:is[_-]?(?:dark|light)(?:[_-]?mode)?|(?:dark|light)[_-]?mode)\b/i', $condition) === 1;
      if ($condition !== '' && (($mode_reference && $mode_literal) || $boolean_mode)) {
        $dark_twig_branches++;
      }
    }
  }
  if (in_array($extension, ['php', 'module', 'inc', 'theme'], TRUE)) {
    $php_tokens = token_get_all($runtime_source);
    foreach ($php_tokens as $token_index => $token) {
      if (!is_array($token) || !in_array($token[0], [T_IF, T_ELSEIF, T_SWITCH, T_MATCH], TRUE)) {
        continue;
      }
      $condition = '';
      for ($next = $token_index + 1, $depth = 0, $started = FALSE; isset($php_tokens[$next]); $next++) {
        $part = $php_tokens[$next];
        $text = is_array($part) ? $part[1] : $part;
        if (!$started && $text !== '(') {
          continue;
        }
        if ($text === '(') {
          $depth++;
          $started = TRUE;
          if ($depth === 1) {
            continue;
          }
        }
        if ($text === ')') {
          $depth--;
          if ($depth === 0) {
            break;
          }
        }
        $condition .= $text;
      }
      $branch_expression = $condition;
      if ($token[0] === T_MATCH) {
        $brace_depth = 0;
        $body_started = FALSE;
        for ($arm = $next + 1; isset($php_tokens[$arm]); $arm++) {
          $part = $php_tokens[$arm];
          $text = is_array($part) ? $part[1] : $part;
          if ($text === '{') {
            $brace_depth++;
            $body_started = TRUE;
          }
          elseif ($text === '}') {
            $brace_depth--;
            if ($body_started && $brace_depth === 0) {
              break;
            }
          }
          if ($body_started) {
            $branch_expression .= $text;
          }
        }
      }
      $mode_reference = preg_match('/\$?(?:theme|mode)\b|color[_-]?mode/i', $branch_expression) === 1;
      $mode_literal = preg_match('/(?:["\'](?:dark|light)["\']|\b(?:dark|light)\b)/i', $branch_expression) === 1;
      $boolean_mode = preg_match('/\$?(?:is[_-]?(?:dark|light)(?:[_-]?mode)?|(?:dark|light)[_-]?mode)\b/i', $branch_expression) === 1;
      if (($mode_reference && $mode_literal) || $boolean_mode) {
        $dark_php_branches++;
      }
    }
  }
  if ($extension === 'js') {
    preg_match_all('/\b(?:if|switch)\s*\(([^)]*)\)|([^;{}?]+\?[^:;{}]+:[^;{}]+)/s', $runtime_source, $js_conditions, PREG_SET_ORDER);
    foreach ($js_conditions as $condition_match) {
      $condition = ($condition_match[1] ?? '') !== '' ? $condition_match[1] : ($condition_match[2] ?? '');
      $mode_reference = preg_match('/\b(?:theme|mode|colorMode|color_mode|color-mode)\b/i', $condition) === 1;
      $mode_literal = preg_match('/(?:["\'](?:dark|light)["\']|\b(?:dark|light)\b)/i', $condition) === 1;
      $boolean_mode = preg_match('/\b(?:is[_-]?(?:dark|light)(?:[_-]?mode)?|(?:dark|light)[_-]?mode)\b/i', $condition) === 1;
      if (($mode_reference && $mode_literal) || $boolean_mode) {
        $dark_js_layout_behavior++;
      }
    }
    if (preg_match('/(?:classList\s*\.\s*(?:add|toggle|remove)\s*\([^)]*["\'](?:dark|light|dark-theme|light-theme|theme-dark|theme-light|dark-mode|light-mode)["\']|(?:setAttribute|dataset\s*\.)\s*\(?\s*["\']?(?:data-(?:bs-)?theme|theme)["\']?\s*,?\s*["\'](?:dark|light))/i', $runtime_source) === 1) {
      $dark_js_layout_behavior++;
    }
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
