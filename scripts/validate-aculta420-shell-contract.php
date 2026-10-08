<?php

declare(strict_types=1);

/**
 * Read-only Runtime gate for ACULTA420 0.2-B.3/B.4 shell contract consumption.
 */

$root = dirname(__DIR__);
$themeRoot = $root . '/web/themes/custom/aculta420';
require_once $root . '/scripts/lib/Aculta420ShellContractAnalyzer.php';
$checks = [];

$assert = static function (bool $condition, string $message) use (&$checks): void {
  if (!$condition) {
    throw new RuntimeException($message);
  }
  $checks[] = $message;
};

$findings = Aculta420ShellContractAnalyzer::analyze($themeRoot);
$assert(
  $findings === [],
  'Static shell boundary analyzer passes: ' . ($findings === [] ? 'clean' : implode(' | ', $findings)),
);

$hookPath = $themeRoot . '/src/Hook/ThemeHooks.php';
$pagePath = $themeRoot . '/templates/page.html.twig';
$hookSource = file_get_contents($hookPath);
$pageSource = file_get_contents($pagePath);

$assert(
  str_contains($hookSource, "\$variables['domain_presentation']['identity']"),
  'ThemeHooks consumes the neutral domain_presentation identity contract.',
);
$assert(
  str_contains($pageSource, 'data-aculta-domain-purpose'),
  'Purpose is exposed only as neutral shell metadata.',
);

$hooks = new \Drupal\aculta420\Hook\ThemeHooks(
  \Drupal::service('path.matcher'),
  \Drupal::service('config.factory'),
);

$complete = [
  'domain_presentation' => [
    'identity' => [
      'purpose' => 'wiki',
      'title' => 'Wiki420',
      'short_title' => 'Wiki420',
      'home_url' => 'https://wiki.example.test/',
      'logo_alt' => 'Wiki420',
    ],
    'regions' => [
      'brand_media' => NULL,
      'navigation' => NULL,
      'actions' => NULL,
    ],
  ],
  'page' => ['header' => []],
];
$hooks->preprocessPage($complete);
$assert(
  ($complete['aculta_domain_identity'] ?? NULL) === [
    'purpose' => 'wiki',
    'title' => 'Wiki420',
    'short_title' => 'Wiki420',
    'home_url' => 'https://wiki.example.test/',
    'logo_alt' => 'Wiki420',
  ],
  'Complete neutral identity is adapted without Domain objects or extra fields.',
);

$assert(
  ($complete['aculta_header_has_content'] ?? NULL) === FALSE,
  'Empty header render array is recognized as having no renderable branding content.',
);

$withHeader = [
  'domain_presentation' => $complete['domain_presentation'],
  'page' => [
    'header' => [
      'branding' => ['#markup' => 'Existing branding'],
    ],
  ],
];
$hooks->preprocessPage($withHeader);
$assert(
  ($withHeader['aculta_header_has_content'] ?? FALSE) === TRUE,
  'Existing renderable header content remains the primary branding path.',
);

$partial = [
  'domain_presentation' => [
    'identity' => [
      'purpose' => 'wiki',
      'title' => 'Wiki420',
      'home_url' => '',
    ],
  ],
  'page' => ['header' => []],
];
$hooks->preprocessPage($partial);
$assert(
  ($partial['aculta_domain_identity'] ?? 'missing') === NULL,
  'Incomplete identity fails safely to NULL instead of inventing functional fallback.',
);

$absent = ['page' => ['header' => []]];
$hooks->preprocessPage($absent);
$assert(
  array_key_exists('aculta_domain_identity', $absent)
    && $absent['aculta_domain_identity'] === NULL,
  'Absent domain_presentation preserves a NULL presentation fallback.',
);

$extra = [
  'domain_presentation' => [
    'identity' => [
      'purpose' => 'main',
      'title' => 'ACULTA',
      'short_title' => '',
      'home_url' => 'https://example.test/',
      'logo_alt' => '',
      'unexpected' => new stdClass(),
    ],
  ],
  'page' => ['header' => []],
];
$hooks->preprocessPage($extra);
$assert(
  ($extra['aculta_domain_identity']['short_title'] ?? 'missing') === NULL
    && ($extra['aculta_domain_identity']['logo_alt'] ?? 'missing') === NULL
    && !array_key_exists('unexpected', $extra['aculta_domain_identity'] ?? []),
  'Theme adapter normalizes optional empty strings and does not forward unknown/object fields.',
);

$portalContract = DRUPAL_ROOT . '/modules/custom/aculta_portal/src/Presentation/DomainPresentationBuilder.php';
$portalSource = file_get_contents($portalContract);
$assert(
  !str_contains($portalSource, 'aculta420:'),
  'Portal presentation builder still has no ACULTA420 SDC dependency.',
);

echo 'ACULTA420 B.4 SHELL CONTRACT: PASS (' . count($checks) . " checks)\n";
