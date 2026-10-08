<?php

declare(strict_types=1);

/**
 * Small, dependency-free analyzers for the ACULTA420 design-foundation gate.
 *
 * Supported CSS is the flat token stylesheet used by ACULTA420: top-level
 * rules, nested selectors/at-rules for mode-selector discovery, custom
 * properties, hex/rgb/rgba colors, and var(--token) aliases without fallbacks.
 * This is intentionally not a complete CSS, Twig, PHP, or JavaScript parser.
 */
final class Aculta420DesignFoundationsAnalyzer {

  private const LIGHT_SELECTOR = ':root, [data-bs-theme="light"]';

  private const DARK_SELECTOR = '[data-bs-theme="dark"]';

  private const REQUIRED_TOKENS = [
    '--aculta-surface-page', '--aculta-surface-raised', '--aculta-surface-muted',
    '--aculta-surface-header', '--aculta-surface-interactive',
    '--aculta-text-primary', '--aculta-text-secondary', '--aculta-text-muted',
    '--aculta-text-inverse', '--aculta-text-accent',
    '--aculta-border-subtle', '--aculta-border-default', '--aculta-border-strong',
    '--aculta-border-accent', '--aculta-interactive-bg', '--aculta-interactive-text',
    '--aculta-interactive-border', '--aculta-interactive-hover-bg',
    '--aculta-interactive-hover-text', '--aculta-interactive-active-bg',
    '--aculta-interactive-active-text', '--aculta-focus-ring',
    '--aculta-shell-institution-bg', '--aculta-shell-institution-text',
    '--aculta-shell-domain-bg', '--aculta-shell-domain-text', '--aculta-shell-border',
    '--aculta-shell-active-bg', '--aculta-shell-active-text',
  ];

  private const DARK_SURFACES = [
    '--aculta-surface-page' => '#171513',
    '--aculta-surface-raised' => '#211e1b',
    '--aculta-surface-muted' => '#2b2723',
    '--aculta-surface-interactive' => '#302b27',
    '--aculta-surface-header' => '#211e1b',
    '--aculta-shell-institution-bg' => '#2b2723',
    '--aculta-shell-domain-bg' => '#211e1b',
  ];

  private const COLOR_TOKENS = [
    '--aculta-green', '--aculta-green-dark', '--aculta-yellow', '--aculta-red',
    '--aculta-cream', '--aculta-white', '--aculta-button-primary-bg',
    '--aculta-button-primary-text', '--aculta-button-secondary-bg',
    '--aculta-button-secondary-text', '--aculta-button-secondary-border',
    '--aculta-button-hover-bg', '--aculta-button-hover-text', '--aculta-nav-current-bg',
    '--aculta-nav-current-text', '--aculta-nav-current-underline', '--aculta-nav-hover-bg',
    '--aculta-nav-hover-text', '--aculta-nav-hover-underline', '--aculta-link-editorial',
    '--aculta-link-editorial-hover', '--aculta-link', '--aculta-border',
    '--aculta-control-border', '--aculta-focus',
    '--aculta-surface-page', '--aculta-surface-raised', '--aculta-surface-muted',
    '--aculta-surface-header', '--aculta-surface-interactive', '--aculta-text-primary',
    '--aculta-text-secondary', '--aculta-text-muted', '--aculta-text-inverse',
    '--aculta-text-accent', '--aculta-border-subtle', '--aculta-border-default',
    '--aculta-border-strong', '--aculta-border-accent', '--aculta-interactive-bg',
    '--aculta-interactive-text', '--aculta-interactive-border',
    '--aculta-interactive-hover-bg', '--aculta-interactive-hover-text',
    '--aculta-interactive-active-bg', '--aculta-interactive-active-text',
    '--aculta-focus-ring', '--aculta-shell-institution-bg',
    '--aculta-shell-institution-text', '--aculta-shell-domain-bg',
    '--aculta-shell-domain-text', '--aculta-shell-border', '--aculta-shell-active-bg',
    '--aculta-shell-active-text',
    '--bs-primary', '--bs-secondary', '--bs-success', '--bs-info', '--bs-warning',
    '--bs-danger', '--bs-light', '--bs-dark', '--bs-black', '--bs-white', '--bs-gray',
    '--bs-gray-dark', '--bs-gray-100', '--bs-gray-200', '--bs-gray-300', '--bs-gray-400',
    '--bs-gray-500', '--bs-gray-600', '--bs-gray-700', '--bs-gray-800', '--bs-gray-900',
    '--bs-code-color', '--bs-highlight-color', '--bs-highlight-bg', '--bs-heading-color',
    '--bs-emphasis-color', '--bs-secondary-color', '--bs-tertiary-color',
    '--bs-secondary-bg', '--bs-tertiary-bg', '--bs-link-color', '--bs-link-hover-color',
    '--bs-border-color', '--bs-border-color-translucent', '--bs-focus-ring-color',
    '--bs-form-valid-color', '--bs-form-valid-border-color', '--bs-form-invalid-color',
    '--bs-form-invalid-border-color', '--bs-primary-text-emphasis',
    '--bs-secondary-text-emphasis', '--bs-success-text-emphasis', '--bs-info-text-emphasis',
    '--bs-warning-text-emphasis', '--bs-danger-text-emphasis', '--bs-light-text-emphasis',
    '--bs-dark-text-emphasis', '--bs-primary-bg-subtle', '--bs-secondary-bg-subtle',
    '--bs-success-bg-subtle', '--bs-info-bg-subtle', '--bs-warning-bg-subtle',
    '--bs-danger-bg-subtle', '--bs-light-bg-subtle', '--bs-dark-bg-subtle',
    '--bs-primary-border-subtle', '--bs-secondary-border-subtle', '--bs-success-border-subtle',
    '--bs-info-border-subtle', '--bs-warning-border-subtle', '--bs-danger-border-subtle',
    '--bs-light-border-subtle', '--bs-dark-border-subtle',
  ];

  private const RGB_TOKENS = [
    '--aculta-surface-page-rgb', '--bs-body-color-rgb', '--bs-body-bg-rgb',
    '--bs-primary-rgb', '--bs-secondary-rgb', '--bs-success-rgb', '--bs-info-rgb',
    '--bs-warning-rgb', '--bs-danger-rgb', '--bs-light-rgb', '--bs-dark-rgb',
    '--bs-black-rgb', '--bs-white-rgb', '--bs-emphasis-color-rgb',
    '--bs-secondary-color-rgb', '--bs-tertiary-color-rgb', '--bs-secondary-bg-rgb',
    '--bs-tertiary-bg-rgb', '--bs-link-color-rgb', '--bs-link-hover-color-rgb',
  ];

  private const RGB_MAPPINGS = [
    '--bs-body-bg-rgb' => '--aculta-surface-page',
    '--bs-body-color-rgb' => '--aculta-text-primary',
    '--bs-secondary-bg-rgb' => '--aculta-surface-raised',
    '--bs-tertiary-bg-rgb' => '--aculta-surface-muted',
    '--bs-light-rgb' => '--aculta-surface-muted',
    '--bs-dark-rgb' => '--aculta-surface-page',
    '--bs-secondary-color-rgb' => '--aculta-text-secondary',
    '--bs-tertiary-color-rgb' => '--aculta-text-muted',
    '--bs-emphasis-color-rgb' => '--aculta-text-primary',
    '--bs-link-color-rgb' => '--aculta-text-primary',
    '--bs-link-hover-color-rgb' => '--aculta-text-primary',
  ];

  /** Check absolute paths using the selected platform's root syntax. */
  public static function isAbsolutePath(string $path, ?string $platform = NULL): bool {
    if ($path === '' || str_contains($path, "\0")) {
      return FALSE;
    }
    $platform ??= PHP_OS_FAMILY;
    $windows = strtolower($platform) === 'windows';
    $absolute = $windows
      ? (preg_match('/^[a-z]:[\\\\\/]/i', $path) === 1
        || preg_match('/^(?:\\\\\\\\|\/\/)[^\\\\\/]+[\\\\\/][^\\\\\/]+/', $path) === 1)
      : str_starts_with($path, '/');
    if (!$absolute) {
      return FALSE;
    }
    $segments = preg_split('~[\\\\/]+~', $path) ?: [];
    return !in_array('..', $segments, TRUE);
  }

  /** Analyze the two supported token blocks and their effective values. */
  public static function analyzeTokens(string $css): array {
    $assertions = [];
    $assert = static function (bool $condition, string $message) use (&$assertions): void {
      $assertions[] = [$condition, $message];
    };
    $clean = self::stripCssComments($css);
    [$rules, $parse_errors] = self::parseCssRules($clean);
    foreach ($parse_errors as $error) {
      $assert(FALSE, 'Token CSS parses within the supported flat-rule subset: ' . $error . '.');
    }

    $light_rules = [];
    $dark_rules = [];
    $unsupported_token_rules = [];
    $walk = static function (array $nodes, bool $top_level = TRUE) use (&$walk, &$light_rules, &$dark_rules, &$unsupported_token_rules): void {
      foreach ($nodes as $node) {
        $selector = self::normalizeSelector($node['selector']);
        if ($top_level && $selector === self::normalizeSelector(self::LIGHT_SELECTOR)) {
          $light_rules[] = $node;
        }
        elseif ($top_level && $selector === self::normalizeSelector(self::DARK_SELECTOR)) {
          $dark_rules[] = $node;
        }
        else {
          foreach (self::parseDeclarations($node['body']) as $declaration) {
            if (preg_match('/^--(?:aculta|bs)-/i', $declaration['name']) === 1) {
              $unsupported_token_rules[] = $node['selector'];
              break;
            }
          }
        }
        if (self::containsModeSelector($node['selector'])) {
          $is_supported_root = $top_level && ($selector === self::normalizeSelector(self::LIGHT_SELECTOR)
            || $selector === self::normalizeSelector(self::DARK_SELECTOR));
          if (!$is_supported_root) {
            $unsupported_token_rules[] = $node['selector'];
          }
          elseif ($node['children'] !== []) {
            $unsupported_token_rules[] = $node['selector'] . ' contains a nested selector';
          }
        }
        $walk($node['children'], FALSE);
      }
    };
    $walk($rules);
    $assert(count($light_rules) === 1, 'Exactly one supported root/light token block exists.');
    $assert(count($dark_rules) === 1, 'Exactly one supported dark token block exists.');
    $assert($unsupported_token_rules === [], 'Mode selectors and ACULTA/Bootstrap tokens occur only in the two supported top-level token blocks, with no nested rules.');

    $parse_mode = static function (array $mode_rules, string $mode) use ($assert): array {
      $declarations = [];
      foreach ($mode_rules as $rule) {
        foreach (self::parseDeclarations($rule['body']) as $declaration) {
          if (!str_starts_with($declaration['name'], '--')) {
            $assert(FALSE, ucfirst($mode) . ' token block contains custom properties only (found ' . $declaration['name'] . ').');
            continue;
          }
          $declarations[$declaration['name']][] = $declaration['value'];
        }
      }
      foreach ($declarations as $name => $values) {
        $assert(count($values) === 1, ucfirst($mode) . ' token block has one declaration for ' . $name . '.');
      }
      return array_map(static fn (array $values): string => $values[array_key_last($values)], $declarations);
    };
    $light = $parse_mode($light_rules, 'light');
    $dark = $parse_mode($dark_rules, 'dark');

    foreach (self::REQUIRED_TOKENS as $token) {
      $assert(array_key_exists($token, $light), $token . ' has a light value.');
      $assert(array_key_exists($token, $dark), $token . ' has a dark value.');
    }
    $light_effective = $light;
    $dark_effective = array_replace($light, $dark);

    $resolve = static fn (string $token, array $map): array => self::resolveToken($token, $map);
    foreach (['light' => $light_effective, 'dark' => $dark_effective] as $mode => $effective) {
      foreach ($effective as $token => $_value) {
        if (preg_match('/^--(?:aculta|bs)-/', $token) !== 1) {
          continue;
        }
        $result = $resolve($token, $effective);
        $assert($result['error'] === NULL, ucfirst($mode) . ' token ' . $token . ' resolves without missing references or cycles.');
      }
      foreach (self::COLOR_TOKENS as $token) {
        $result = $resolve($token, $effective);
        $assert($result['error'] === NULL && self::parseColor($result['value'] ?? '') !== NULL, ucfirst($mode) . ' color token ' . $token . ' resolves to a supported color value.');
      }
      foreach (self::RGB_TOKENS as $token) {
        $result = $resolve($token, $effective);
        $assert($result['error'] === NULL && self::parseRgbTriplet($result['value'] ?? '') !== NULL, ucfirst($mode) . ' RGB token ' . $token . ' resolves to a valid channel triplet.');
      }
    }

    foreach (self::DARK_SURFACES as $token => $expected) {
      $result = $resolve($token, $dark_effective);
      $assert($result['error'] === NULL && strtolower($result['value'] ?? '') === $expected, $token . ' resolves to approved neutral dark surface ' . $expected . '.');
    }

    $dark_pairs = [
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
    $dark_contrast = [];
    foreach ($dark_pairs as [$foreground, $background]) {
      $fg = self::resolvedColor($foreground, $dark_effective);
      $bg = self::resolvedColor($background, $dark_effective);
      $assert($bg !== NULL && ($bg[3] ?? 1.0) === 1.0, $background . ' is opaque for contrast evaluation.');
      $ratio = $fg !== NULL && $bg !== NULL ? self::contrast(self::composite($fg, $bg), $bg) : 0.0;
      $dark_contrast[$foreground . ' / ' . $background] = $ratio;
      $assert($ratio >= 4.5, $foreground . ' / ' . $background . ' meets WCAG AA for normal text in dark mode.');
    }
    $focus_contrast = [];
    foreach (['--aculta-surface-page', '--aculta-surface-raised', '--aculta-surface-muted', '--aculta-surface-header', '--aculta-surface-interactive'] as $surface) {
      $fg = self::resolvedColor('--aculta-focus-ring', $dark_effective);
      $bg = self::resolvedColor($surface, $dark_effective);
      $assert($bg !== NULL && ($bg[3] ?? 1.0) === 1.0, $surface . ' is opaque for focus contrast evaluation.');
      $ratio = $fg !== NULL && $bg !== NULL ? self::contrast(self::composite($fg, $bg), $bg) : 0.0;
      $focus_contrast[$surface] = $ratio;
      $assert($ratio >= 3.0, '--aculta-focus-ring has at least 3:1 contrast against ' . $surface . '.');
    }

    $mode_contrast = [];
    foreach (['light' => $light_effective, 'dark' => $dark_effective] as $mode => $effective) {
      $mode_contrast[$mode] = [];
      foreach ([
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
      ] as [$foreground, $background]) {
        $fg = self::resolvedColor($foreground, $effective);
        $bg = self::resolvedColor($background, $effective);
        $assert($bg !== NULL && ($bg[3] ?? 1.0) === 1.0, $background . ' is opaque for ' . $mode . ' contrast evaluation.');
        $ratio = $fg !== NULL && $bg !== NULL ? self::contrast(self::composite($fg, $bg), $bg) : 0.0;
        $mode_contrast[$mode][$foreground . ' / ' . $background] = $ratio;
        $assert($ratio >= 4.5, $foreground . ' / ' . $background . ' meets WCAG AA for normal text in ' . $mode . ' mode.');
      }
    }

    $mapping_values = [
      '--bs-body-bg' => '--aculta-surface-page',
      '--bs-body-color' => '--aculta-text-primary',
      '--bs-secondary-bg' => '--aculta-surface-raised',
      '--bs-tertiary-bg' => '--aculta-surface-muted',
      '--bs-light' => '--aculta-surface-muted',
      '--bs-secondary-color' => '--aculta-text-secondary',
      '--bs-tertiary-color' => '--aculta-text-muted',
      '--bs-emphasis-color' => '--aculta-text-primary',
      '--bs-link-color' => '--aculta-link',
      '--bs-link-hover-color' => '--aculta-text-primary',
    ];
    $bootstrap_results = [];
    foreach (['light' => $light_effective, 'dark' => $dark_effective] as $mode => $effective) {
      $mode_mappings = $mapping_values;
      $mode_mappings['--bs-dark'] = $mode === 'dark' ? '--aculta-surface-page' : '--aculta-green-dark';
      foreach ($mode_mappings as $bootstrap_token => $source_token) {
        $actual = $resolve($bootstrap_token, $effective);
        $expected = $resolve($source_token, $effective);
        $matches = $actual['error'] === NULL && $expected['error'] === NULL
          && self::sameColor($actual['value'] ?? '', $expected['value'] ?? '');
        $bootstrap_results[$mode][$bootstrap_token] = $matches;
        $assert($matches, ucfirst($mode) . ' ' . $bootstrap_token . ' effectively maps to ' . $source_token . '.');
      }
      $mode_rgb_mappings = self::RGB_MAPPINGS;
      $mode_rgb_mappings['--bs-dark-rgb'] = $mode === 'dark' ? '--aculta-surface-page' : '--aculta-green-dark';
      foreach ($mode_rgb_mappings as $rgb_token => $source_token) {
        $actual = $resolve($rgb_token, $effective);
        $expected_color = self::resolvedColor($source_token, $effective);
        $actual_rgb = $actual['error'] === NULL ? self::parseRgbTriplet($actual['value'] ?? '') : NULL;
        $expected_rgb = $expected_color !== NULL ? array_slice($expected_color, 0, 3) : NULL;
        $matches = $actual_rgb !== NULL && $expected_rgb !== NULL && $actual_rgb == $expected_rgb;
        $bootstrap_results[$mode][$rgb_token] = $matches;
        $assert($matches, ucfirst($mode) . ' ' . $rgb_token . ' matches ' . $source_token . '.');
      }
      $border = $resolve('--bs-border-color', $effective);
      $border_source = $resolve('--aculta-border-default', $effective);
      $matches = $border['error'] === NULL && $border_source['error'] === NULL
        && self::normalizeColorValue($border['value'] ?? '') === self::normalizeColorValue($border_source['value'] ?? '');
      $bootstrap_results[$mode]['--bs-border-color'] = $matches;
      $assert($matches, ucfirst($mode) . ' --bs-border-color maps to --aculta-border-default.');
    }

    return [
      'assertions' => $assertions,
      'dark_contrast' => $dark_contrast,
      'focus_contrast' => $focus_contrast,
      'mode_contrast' => $mode_contrast,
      'bootstrap' => $bootstrap_results,
    ];
  }

  /** Count prohibited color-mode selectors, including inside tokens.css. */
  public static function countModeSelectors(string $css, bool $tokens_file = FALSE): int {
    [$rules] = self::parseCssRules(self::stripCssComments($css));
    $count = 0;
    $walk = static function (array $nodes, bool $top_level = TRUE) use (&$walk, &$count, $tokens_file): void {
      foreach ($nodes as $node) {
        if (self::containsModeSelector($node['selector'])) {
          $selector = self::normalizeSelector($node['selector']);
          $allowed = $tokens_file && $top_level
            && in_array($selector, [self::normalizeSelector(self::LIGHT_SELECTOR), self::normalizeSelector(self::DARK_SELECTOR)], TRUE)
            && $node['children'] === []
            && self::onlyCustomPropertyDeclarations($node['body']);
          if (!$allowed) {
            $count++;
          }
        }
        $walk($node['children'], FALSE);
      }
    };
    $walk($rules);
    return $count;
  }

  public static function countTwigModeBranches(string $source): int {
    $source = preg_replace('/\{#.*?#\}/s', '', $source) ?? $source;
    preg_match_all('/\{\{(.*?)\}\}|\{%([^%]*?)%\}/s', $source, $tags, PREG_SET_ORDER);
    $count = 0;
    foreach ($tags as $tag) {
      $expression = trim(($tag[1] ?? '') !== '' ? $tag[1] : ($tag[2] ?? ''));
      $is_branch = preg_match('/^(?:if|elseif)\b/i', $expression) === 1;
      $is_ternary = str_contains($expression, '?') && str_contains($expression, ':');
      if (($is_branch || $is_ternary) && self::hasModeDecision($expression)) {
        $count++;
      }
    }
    return $count;
  }

  public static function countPhpModeBranches(string $source): int {
    $tokens = token_get_all($source);
    $count = 0;
    foreach ($tokens as $index => $token) {
      if (!is_array($token) || !in_array($token[0], [T_IF, T_ELSEIF, T_SWITCH, T_MATCH], TRUE)) {
        continue;
      }
      [$condition, $after_condition] = self::readPhpParenthesized($tokens, $index + 1);
      $expression = $condition;
      if ($token[0] === T_SWITCH) {
        $expression .= ' ' . self::readPhpDecisionArms($tokens, $after_condition, TRUE);
      }
      elseif ($token[0] === T_MATCH) {
        $expression .= ' ' . self::readPhpDecisionArms($tokens, $after_condition);
      }
      if (self::hasModeDecision($expression)) {
        $count++;
      }
    }
    return $count;
  }

  public static function countJsModeBranches(string $source): int {
    $source = self::stripJsComments($source);
    $count = 0;
    preg_match_all('/\bif\s*\(([^()]*(?:\([^()]*\)[^()]*)*)\)/s', $source, $ifs, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
    foreach ($ifs as $match) {
      if (self::hasModeDecision($match[1][0])) {
        $count++;
      }
    }
    preg_match_all('/\bswitch\s*\(/s', $source, $switches, PREG_OFFSET_CAPTURE);
    foreach ($switches[0] as [$switch_text, $switch_offset]) {
      $open = $switch_offset + strlen($switch_text) - 1;
      [$discriminant, $after_discriminant] = self::readBalancedJsParentheses($source, $open);
      $brace = strpos($source, '{', $after_discriminant);
      if ($brace === FALSE) {
        continue;
      }
      $body_start = $brace + 1;
      $body = self::readBalancedJsBlock($source, $body_start);
      if (self::hasModeDecision($discriminant . ' ' . $body)) {
        $count++;
      }
    }
    preg_match_all('/([^;{}\n?]+\?[^;{}\n:]+:[^;{}\n]+)/', $source, $ternaries);
    foreach ($ternaries[0] as $expression) {
      if (self::hasModeDecision($expression)) {
        $count++;
      }
    }
    if (preg_match('/\.dataset(?:\s*\.\s*(?:theme|bsTheme|colorMode|colorScheme)|\s*\[\s*["\'](?:theme|bsTheme|colorMode|colorScheme)["\']\s*\])\s*=/i', $source) === 1
      || preg_match('/(?:setAttribute)\s*\(\s*["\'](?:data-(?:bs-)?theme|data-color-(?:mode|scheme))["\']\s*,\s*["\'](?:dark|light)["\']/i', $source) === 1
      || preg_match('/classList\s*\.\s*(?:add|toggle|remove)\s*\([^)]*["\'](?:dark|light|dark-theme|light-theme|theme-dark|theme-light|dark-mode|light-mode)["\']/i', $source) === 1) {
      $count++;
    }
    return $count;
  }

  public static function hasPrematureColorModeScript(string $source): bool {
    $source = self::stripJsComments($source);
    return preg_match('/localStorage|sessionStorage|prefers-color-scheme|setAttribute\s*\(\s*[\'\"]data-bs-theme|color[-_ ]mode/i', $source) === 1;
  }

  private static function normalizeSelector(string $selector): string {
    return strtolower(preg_replace('/\s+/', '', trim($selector)) ?? trim($selector));
  }

  private static function stripCssComments(string $source): string {
    return preg_replace_callback('~/\*.*?\*/~s', static function (array $match): string {
      return preg_replace('/[^\r\n]/', ' ', $match[0]) ?? '';
    }, $source) ?? $source;
  }

  /** Parse nested CSS blocks while respecting strings, comments and parens. */
  private static function parseCssRules(string $css): array {
    $rules = [];
    $errors = [];
    $length = strlen($css);
    $cursor = 0;
    while ($cursor < $length) {
      while ($cursor < $length && (ctype_space($css[$cursor]) || $css[$cursor] === ';')) {
        $cursor++;
      }
      if ($cursor >= $length) {
        break;
      }
      $start = $cursor;
      $quote = NULL;
      $paren_depth = 0;
      while ($cursor < $length) {
        $char = $css[$cursor];
        if ($quote !== NULL) {
          if ($char === '\\') {
            $cursor += 2;
            continue;
          }
          if ($char === $quote) {
            $quote = NULL;
          }
        }
        elseif ($char === '"' || $char === "'") {
          $quote = $char;
        }
        elseif ($char === '(') {
          $paren_depth++;
        }
        elseif ($char === ')') {
          $paren_depth = max(0, $paren_depth - 1);
        }
        elseif ($paren_depth === 0 && $char === ';') {
          $cursor++;
          continue 2;
        }
        elseif ($paren_depth === 0 && $char === '{') {
          break;
        }
        $cursor++;
      }
      if ($cursor >= $length || $css[$cursor] !== '{') {
        break;
      }
      $selector = trim(substr($css, $start, $cursor - $start));
      $body_start = ++$cursor;
      $depth = 1;
      $quote = NULL;
      while ($cursor < $length && $depth > 0) {
        $char = $css[$cursor];
        if ($quote !== NULL) {
          if ($char === '\\') {
            $cursor += 2;
            continue;
          }
          if ($char === $quote) {
            $quote = NULL;
          }
        }
        elseif ($char === '"' || $char === "'") {
          $quote = $char;
        }
        elseif ($char === '{') {
          $depth++;
        }
        elseif ($char === '}') {
          $depth--;
        }
        $cursor++;
      }
      if ($depth !== 0) {
        $errors[] = 'unclosed block for ' . ($selector !== '' ? $selector : 'selector');
        break;
      }
      $body = substr($css, $body_start, $cursor - $body_start - 1);
      [$children, $child_errors] = self::parseCssRules($body);
      $errors = array_merge($errors, $child_errors);
      $rules[] = ['selector' => $selector, 'body' => $body, 'children' => $children];
    }
    return [$rules, $errors];
  }

  private static function parseDeclarations(string $body): array {
    $declarations = [];
    foreach (self::splitCss($body, ';') as $part) {
      $part = trim($part);
      if ($part === '') {
        continue;
      }
      $colon = strpos($part, ':');
      if ($colon === FALSE) {
        $declarations[] = ['name' => '', 'value' => $part];
        continue;
      }
      $declarations[] = ['name' => trim(substr($part, 0, $colon)), 'value' => trim(substr($part, $colon + 1))];
    }
    return $declarations;
  }

  private static function splitCss(string $input, string $separator): array {
    $parts = [];
    $start = 0;
    $depth = 0;
    $quote = NULL;
    $length = strlen($input);
    for ($i = 0; $i < $length; $i++) {
      $char = $input[$i];
      if ($quote !== NULL) {
        if ($char === '\\') {
          $i++;
        }
        elseif ($char === $quote) {
          $quote = NULL;
        }
      }
      elseif ($char === '"' || $char === "'") {
        $quote = $char;
      }
      elseif ($char === '(') {
        $depth++;
      }
      elseif ($char === ')') {
        $depth = max(0, $depth - 1);
      }
      elseif ($char === $separator && $depth === 0) {
        $parts[] = substr($input, $start, $i - $start);
        $start = $i + 1;
      }
    }
    $parts[] = substr($input, $start);
    return $parts;
  }

  private static function onlyCustomPropertyDeclarations(string $body): bool {
    foreach (self::parseDeclarations($body) as $declaration) {
      if (preg_match('/^--[a-zA-Z0-9_-]+$/', $declaration['name']) !== 1 || $declaration['value'] === '') {
        return FALSE;
      }
    }
    return TRUE;
  }

  private static function containsModeSelector(string $selector): bool {
    return preg_match('/prefers-color-scheme\s*:\s*(?:dark|light)/i', $selector) === 1
      || preg_match('/\[\s*data-(?:(?:bs-)?theme|color-mode|color-scheme)\s*=\s*(["\']?)(?:dark|light)\1\s*\]/i', $selector) === 1
      || preg_match('/\.(?:dark|light|dark-theme|light-theme|theme-dark|theme-light|dark-mode|light-mode|is-dark|is-light|color-mode-dark|color-mode-light)(?![a-zA-Z0-9_-])/i', $selector) === 1;
  }

  private static function resolveToken(string $name, array $effective, array $stack = []): array {
    if (in_array($name, $stack, TRUE)) {
      return ['value' => NULL, 'error' => 'circular token reference'];
    }
    if (!array_key_exists($name, $effective)) {
      return ['value' => NULL, 'error' => 'unresolved token reference'];
    }
    $stack[] = $name;
    $error = NULL;
    $resolved = preg_replace_callback('/var\(\s*(--[a-zA-Z0-9_-]+)\s*\)/', static function (array $match) use (&$error, $effective, $stack): string {
      $result = self::resolveToken($match[1], $effective, $stack);
      if ($result['error'] !== NULL) {
        $error = $result['error'];
        return '';
      }
      return (string) $result['value'];
    }, trim($effective[$name]));
    if ($error !== NULL || str_contains((string) $resolved, 'var(')) {
      return ['value' => NULL, 'error' => $error ?? 'unsupported or unresolved var() syntax'];
    }
    return ['value' => trim((string) $resolved), 'error' => NULL];
  }

  private static function parseColor(string $value): ?array {
    $value = trim(strtolower($value));
    if (preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/', $value, $match) === 1) {
      $hex = $match[1];
      if (strlen($hex) === 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
      }
      return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2)), 1.0];
    }
    if ($value === 'black') {
      return [0, 0, 0, 1.0];
    }
    if ($value === 'white') {
      return [255, 255, 255, 1.0];
    }
    if ($value === 'transparent') {
      return [0, 0, 0, 0.0];
    }
    if (preg_match('/^rgba?\(([^)]+)\)$/', $value, $match) === 1) {
      $channels = preg_split('/\s*,\s*|\s+\/\s+/', trim($match[1])) ?: [];
      if (count($channels) < 3 || count($channels) > 4) {
        return NULL;
      }
      $rgb = [];
      foreach (array_slice($channels, 0, 3) as $channel) {
        if (preg_match('/^\d+(?:\.\d+)?$/', $channel) !== 1 || (float) $channel < 0 || (float) $channel > 255) {
          return NULL;
        }
        $rgb[] = (float) $channel;
      }
      $alpha = isset($channels[3]) && is_numeric($channels[3]) ? (float) $channels[3] : 1.0;
      if ($alpha < 0 || $alpha > 1) {
        return NULL;
      }
      return [$rgb[0], $rgb[1], $rgb[2], $alpha];
    }
    return NULL;
  }

  private static function resolvedColor(string $token, array $effective): ?array {
    $resolved = self::resolveToken($token, $effective);
    return $resolved['error'] === NULL ? self::parseColor((string) $resolved['value']) : NULL;
  }

  private static function sameColor(string $first, string $second): bool {
    $a = self::parseColor($first);
    $b = self::parseColor($second);
    return $a !== NULL && $b !== NULL && $a === $b;
  }

  private static function normalizeColorValue(string $value): string {
    $color = self::parseColor($value);
    return $color === NULL ? strtolower(preg_replace('/\s+/', '', trim($value)) ?? trim($value)) : implode(',', $color);
  }

  private static function parseRgbTriplet(string $value): ?array {
    $parts = preg_split('/\s*,\s*/', trim($value)) ?: [];
    if (count($parts) !== 3) {
      return NULL;
    }
    $channels = [];
    foreach ($parts as $part) {
      if (preg_match('/^\d+(?:\.\d+)?$/', $part) !== 1 || (float) $part < 0 || (float) $part > 255) {
        return NULL;
      }
      $channels[] = (float) $part;
    }
    return $channels;
  }

  private static function contrast(array $foreground, array $background): float {
    $luminance = static function (array $color): float {
      $channels = array_map(static function (float $channel): float {
        $value = $channel / 255;
        return $value <= 0.04045 ? $value / 12.92 : (($value + 0.055) / 1.055) ** 2.4;
      }, array_slice($color, 0, 3));
      return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
    };
    $first = $luminance($foreground);
    $second = $luminance($background);
    return (max($first, $second) + 0.05) / (min($first, $second) + 0.05);
  }

  /** Composite a translucent foreground over its opaque contrast surface. */
  private static function composite(array $foreground, array $background): array {
    $alpha = $foreground[3] ?? 1.0;
    return [
      $foreground[0] * $alpha + $background[0] * (1 - $alpha),
      $foreground[1] * $alpha + $background[1] * (1 - $alpha),
      $foreground[2] * $alpha + $background[2] * (1 - $alpha),
      1.0,
    ];
  }

  private static function hasModeDecision(string $expression): bool {
    $mode_reference = preg_match('/\$?\b(?:theme|mode|colorMode|color_mode|color-mode|getTheme|getColorMode)\b/i', $expression) === 1;
    $mode_literal = preg_match('/(?:["\'](?:dark|light)["\']|\b(?:dark|light)\b)/i', $expression) === 1;
    $boolean_mode = preg_match('/\$?\b(?:is[_-]?(?:dark|light)(?:[_-]?mode)?|(?:dark|light)[_-]?mode)\b/i', $expression) === 1;
    return ($mode_reference && $mode_literal) || $boolean_mode;
  }

  private static function readPhpParenthesized(array $tokens, int $index): array {
    $condition = '';
    $depth = 0;
    $started = FALSE;
    for ($i = $index; isset($tokens[$i]); $i++) {
      if (is_array($tokens[$i]) && in_array($tokens[$i][0], [T_COMMENT, T_DOC_COMMENT], TRUE)) {
        continue;
      }
      $part = is_array($tokens[$i]) ? $tokens[$i][1] : $tokens[$i];
      if (!$started && $part !== '(') {
        continue;
      }
      if ($part === '(') {
        $depth++;
        $started = TRUE;
        if ($depth === 1) {
          continue;
        }
      }
      elseif ($part === ')') {
        $depth--;
        if ($depth === 0) {
          return [$condition, $i + 1];
        }
      }
      $condition .= $part;
    }
    return [$condition, count($tokens)];
  }

  private static function readPhpDecisionArms(array $tokens, int $index, bool $switch = FALSE): string {
    $depth = 0;
    $started = FALSE;
    $body = '';
    for ($i = $index; isset($tokens[$i]); $i++) {
      if (is_array($tokens[$i]) && in_array($tokens[$i][0], [T_COMMENT, T_DOC_COMMENT], TRUE)) {
        continue;
      }
      $part = is_array($tokens[$i]) ? $tokens[$i][1] : $tokens[$i];
      if (!$started && $part !== '{' && !($switch && $part === ':')) {
        continue;
      }
      if ($part === '{' || ($switch && !$started && $part === ':')) {
        $depth++;
        $started = TRUE;
      }
      elseif ($part === '}') {
        $depth--;
        if ($started && $depth === 0) {
          return $body;
        }
      }
      elseif ($switch && is_array($tokens[$i]) && $tokens[$i][0] === T_ENDSWITCH) {
        return $body;
      }
      $body .= $part;
    }
    return $body;
  }

  private static function readBalancedJsBlock(string $source, int $index): string {
    $length = strlen($source);
    $depth = 1;
    $start = $index;
    $quote = NULL;
    for ($i = $index; $i < $length; $i++) {
      $char = $source[$i];
      if ($quote !== NULL) {
        if ($char === '\\') {
          $i++;
        }
        elseif ($char === $quote) {
          $quote = NULL;
        }
      }
      elseif ($char === '"' || $char === "'" || $char === '`') {
        $quote = $char;
      }
      elseif ($char === '{') {
        $depth++;
      }
      elseif ($char === '}') {
        $depth--;
        if ($depth === 0) {
          return substr($source, $start, $i - $start);
        }
      }
    }
    return substr($source, $start);
  }

  /** Return the contents and ending offset for a balanced JS parenthesis. */
  private static function readBalancedJsParentheses(string $source, int $open): array {
    $length = strlen($source);
    $depth = 0;
    $quote = NULL;
    for ($i = $open; $i < $length; $i++) {
      $char = $source[$i];
      if ($quote !== NULL) {
        if ($char === '\\') {
          $i++;
        }
        elseif ($char === $quote) {
          $quote = NULL;
        }
      }
      elseif ($char === '"' || $char === "'" || $char === '`') {
        $quote = $char;
      }
      elseif ($char === '(') {
        $depth++;
      }
      elseif ($char === ')') {
        $depth--;
        if ($depth === 0) {
          return [substr($source, $open + 1, $i - $open - 1), $i + 1];
        }
      }
    }
    return [substr($source, $open + 1), $length];
  }

  private static function stripJsComments(string $source): string {
    $source = preg_replace('~/\*.*?\*/~s', '', $source) ?? $source;
    return preg_replace('/^[ \t]*\/\/.*$/m', '', $source) ?? $source;
  }

}
