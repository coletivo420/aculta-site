<?php

declare(strict_types=1);

/**
 * Read-only Runtime gate for ACULTA420 0.2-B.3 shell contract consumption.
 */

$root = dirname(__DIR__);
$themeRoot = $root . '/web/themes/custom/aculta420';
$checks = [];

$assert = static function (bool $condition, string $message) use (&$checks): void {
  if (!$condition) {
    throw new RuntimeException($message);
  }
  $checks[] = $message;
};

$hookPath = $themeRoot . '/src/Hook/ThemeHooks.php';
$pagePath = $themeRoot . '/templates/page.html.twig';
$hookSource = file_get_contents($hookPath);
$pageSource = file_get_contents($pagePath);

$assert(
  str_contains($hookSource, "$variables['domain_presentation']['identity']"),
  'ThemeHooks consumes the neutral domain_presentation identity contract.',
);
$assert(
  str_contains($hookSource, 'normalizeDomainIdentity'),
  'ThemeHooks adapts identity through a presentation-only normalizer.',
);
$assert(
  str_contains($pageSource, 'aculta_domain_identity.home_url')
    && str_contains($pageSource, 'aculta_domain_identity.short_title')
    && str_contains($pageSource, 'aculta_domain_identity.title'),
  'page.html.twig consumes prepared home/title identity only as shell presentation.',
);
$assert(
  str_contains($pageSource, '{% if page.header %}')
    && str_contains($pageSource, '{% elseif aculta_domain_identity %}'),
  'Existing Drupal header remains primary and domain identity is fallback-only.',
);
$assert(
  !str_contains($pageSource, "identity.purpose ==")
    && !str_contains($pageSource, "identity.purpose is")
    && !preg_match('/\b(?:wiki|courses|shop|support|account|magazine|main)\b\s*(?:==|!=)/i', $pageSource),
  'Twig contains no purpose-specific branching.',
);

$forbiddenThemePatterns = [
  'DomainInterface' => '/\bDomainInterface\b/',
  'DomainPurposeManager' => '/\bDomainPurposeManager\b/',
  'domain negotiator' => '/domain\.negotiator|DomainNegotiator/i',
  'Portal service' => '/aculta_portal\./',
  'hostname decision' => '/getHost\s*\(|HTTP_HOST|SERVER_NAME|\.aculta\.org|\.toca\.net\.br/i',
];
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($themeRoot, FilesystemIterator::SKIP_DOTS)) as $file) {
  if (!$file->isFile() || !in_array(strtolower($file->getExtension()), ['php', 'twig', 'js'], TRUE)) {
    continue;
  }
  $source = file_get_contents($file->getPathname());
  $relative = str_replace($themeRoot . DIRECTORY_SEPARATOR, '', $file->getPathname());
  foreach ($forbiddenThemePatterns as $label => $pattern) {
    $assert(
      !preg_match($pattern, $source),
      $label . ' absent from theme runtime source: ' . $relative,
    );
  }
}

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

$cssChanges = [];
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($themeRoot . '/css', FilesystemIterator::SKIP_DOTS)) as $file) {
  if ($file->isFile()) {
    $cssChanges[] = $file->getPathname();
  }
}
$assert($cssChanges !== [], 'Theme CSS exists but B.3 requires no CSS mutation; verify via Git diff in CI/review.');

echo 'ACULTA420 B.3 SHELL CONTRACT: PASS (' . count($checks) . " checks)\n";
