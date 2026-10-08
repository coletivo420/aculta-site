<?php

declare(strict_types=1);

/**
 * Read-only gate for the aculta_portal Drupal 11+ development standard.
 */

$root = dirname(__DIR__);
$moduleRoot = $root . '/web/modules/custom/aculta_portal';
$srcRoot = $moduleRoot . '/src';
$checks = [];

$assert = static function (bool $condition, string $message) use (&$checks): void {
  if (!$condition) {
    throw new RuntimeException($message);
  }
  $checks[] = $message;
};

$assert(is_dir($moduleRoot), 'aculta_portal module directory exists.');
$assert(
  version_compare((string) \Drupal::VERSION, '11.3.0', '>='),
  'Runtime Drupal Core is 11.3.0 or newer.',
);

$info = (string) file_get_contents($moduleRoot . '/aculta_portal.info.yml');
$assert(
  preg_match('/^core_version_requirement:\s*\^11\.3\s*$/m', $info) === 1,
  'Module metadata declares Drupal 11.3+ baseline.',
);

$assert(
  !is_file($moduleRoot . '/aculta_portal.module'),
  'No procedural aculta_portal.module runtime file exists.',
);

$services = (string) file_get_contents($moduleRoot . '/aculta_portal.services.yml');
$assert(
  str_contains($services, 'aculta_portal.form_callbacks:'),
  'Dependency-injected Form API callback service is registered.',
);
$assert(
  str_contains($services, 'name: event_subscriber'),
  'Event subscribers use the Drupal event_subscriber tag.',
);
$assert(
  !str_contains($services, 'kernel.event_subscriber'),
  'Legacy/Symfony kernel.event_subscriber spelling is absent.',
);

$requiredHookClasses = [
  'Drupal\\aculta_portal\\Hook\\EditorialHooks' => $srcRoot . '/Hook/EditorialHooks.php',
  'Drupal\\aculta_portal\\Hook\\SecurityCommerceHooks' => $srcRoot . '/Hook/SecurityCommerceHooks.php',
  'Drupal\\aculta_portal\\Hook\\TokenHooks' => $srcRoot . '/Hook/TokenHooks.php',
  'Drupal\\aculta_portal\\Hook\\PortalHooks' => $srcRoot . '/Hook/PortalHooks.php',
];
foreach ($requiredHookClasses as $class => $path) {
  $assert(is_file($path), 'OOP hook class exists: ' . $class);
  $source = (string) file_get_contents($path);
  $assert(str_contains($source, '#[Hook('), 'OOP hook attributes are present: ' . $class);
  $assert(class_exists($class), 'Drupal autoloads OOP hook class: ' . $class);
}

$forbiddenRuntimePatterns = [
  'Drupal service locator' => '/\\\\Drupal::(?:service|entityTypeManager|database|request|routeMatch|currentUser|config|messenger)\s*\(/',
  'views_embed_view wrapper' => '/\bviews_embed_view\s*\(/',
  'Views::getView wrapper' => '/\bViews::getView\s*\(/',
  'PaymentGateway::load wrapper' => '/\bPaymentGateway::load\s*\(/',
  'legacy procedural callback reference' => '/[\'"]aculta_portal_(?:validate_activity|change_mail_confirmation_message|security_password_redirect|security_password_after_build|account_photo_redirect|address_redirect|sync_customer_address_names|validate_donation_amount)[\'"]/',
];

$phpFiles = [];
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($srcRoot, FilesystemIterator::SKIP_DOTS)) as $file) {
  if ($file->isFile() && strtolower($file->getExtension()) === 'php') {
    $phpFiles[] = $file->getPathname();
  }
}
sort($phpFiles);
$assert($phpFiles !== [], 'Portal runtime PHP source files were discovered.');

foreach ($phpFiles as $path) {
  $source = (string) file_get_contents($path);
  $relative = str_replace($moduleRoot . DIRECTORY_SEPARATOR, '', $path);

  $assert(
    preg_match('/declare\s*\(\s*strict_types\s*=\s*1\s*\)\s*;/', $source) === 1,
    'Runtime PHP uses strict_types: ' . $relative,
  );

  foreach ($forbiddenRuntimePatterns as $label => $pattern) {
    $assert(
      preg_match($pattern, $source) !== 1,
      $label . ' absent from runtime source: ' . $relative,
    );
  }
}

$callbacks = \Drupal::service('aculta_portal.form_callbacks');
$assert(
  $callbacks instanceof \Drupal\aculta_portal\Form\PortalFormCallbacks,
  'Form callback service resolves to PortalFormCallbacks.',
);

foreach ([
  'validateActivity',
  'changeMailConfirmationMessage',
  'securityPasswordRedirect',
  'securityPasswordAfterBuild',
  'accountPhotoRedirect',
  'addressRedirect',
  'syncCustomerAddressNames',
  'validateDonationAmount',
] as $method) {
  $assert(
    method_exists($callbacks, $method),
    'Form callback service exposes method: ' . $method,
  );
}

$callbackConsumers = '';
foreach ([
  $srcRoot . '/Hook/EditorialHooks.php',
  $srcRoot . '/Hook/SecurityCommerceHooks.php',
  $srcRoot . '/Hook/PortalHooks.php',
] as $path) {
  $callbackConsumers .= "\n" . (string) file_get_contents($path);
}
foreach ([
  'aculta_portal.form_callbacks:validateActivity',
  'aculta_portal.form_callbacks:changeMailConfirmationMessage',
  'aculta_portal.form_callbacks:securityPasswordRedirect',
  'aculta_portal.form_callbacks:securityPasswordAfterBuild',
  'aculta_portal.form_callbacks:accountPhotoRedirect',
  'aculta_portal.form_callbacks:addressRedirect',
  'aculta_portal.form_callbacks:syncCustomerAddressNames',
  'aculta_portal.form_callbacks:validateDonationAmount',
] as $callable) {
  $assert(
    str_contains($callbackConsumers, $callable),
    'Form API uses DI service callable: ' . $callable,
  );
}

$domainPresentation = new \ReflectionClass(\Drupal\aculta_portal\Presentation\DomainPresentation::class);
$assert(
  $domainPresentation->implementsInterface(\Drupal\Core\Cache\CacheableDependencyInterface::class),
  'DomainPresentation implements CacheableDependencyInterface.',
);

echo 'ACULTA PORTAL DRUPAL 11+: PASS (' . count($checks) . " checks)\n";
