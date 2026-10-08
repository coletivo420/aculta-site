<?php

declare(strict_types=1);

/**
 * Progressive static gate for the aculta_portal Drupal 11+ standard.
 *
 * P1 intentionally freezes known modernization debt instead of pretending it
 * is already removed. Later phases must reduce the allowlists/count ceilings
 * when they eliminate debt. New files/usages must comply immediately.
 */

$root = dirname(__DIR__);
$moduleRoot = $root . '/web/modules/custom/aculta_portal';
$srcRoot = $moduleRoot . '/src';
$failures = [];
$checks = 0;

$check = static function (bool $condition, string $message) use (&$failures, &$checks): void {
  $checks++;
  if (!$condition) {
    $failures[] = $message;
  }
};

$read = static function (string $path) use (&$failures): string {
  $content = @file_get_contents($path);
  if ($content === FALSE) {
    $failures[] = 'Cannot read required file: ' . $path;
    return '';
  }
  return $content;
};

$check(is_dir($moduleRoot), 'aculta_portal module directory must exist.');
$check(is_dir($srcRoot), 'aculta_portal src directory must exist.');

$composerLock = $read($root . '/composer.lock');
$coreVersion = NULL;
$composerData = json_decode($composerLock, TRUE);
if (is_array($composerData)) {
  foreach (array_merge($composerData['packages'] ?? [], $composerData['packages-dev'] ?? []) as $package) {
    if (($package['name'] ?? '') === 'drupal/core-recommended') {
      $coreVersion = ltrim((string) ($package['version'] ?? ''), 'v');
      break;
    }
    if ($coreVersion === NULL && ($package['name'] ?? '') === 'drupal/core') {
      $coreVersion = ltrim((string) ($package['version'] ?? ''), 'v');
    }
  }
}
$check(
  is_string($coreVersion)
    && $coreVersion !== ''
    && version_compare($coreVersion, '11.3.0', '>=')
    && version_compare($coreVersion, '12.0.0', '<'),
  'composer.lock must pin Drupal Core within the supported 11.3+ major line.',
);

$installSource = $read($moduleRoot . '/aculta_portal.install');
$check(
  preg_match('/^function\\s+aculta_portal_requirements\\s*\\(/m', $installSource) !== 1,
  'Do not reintroduce deprecated hook_requirements(); use Drupal 11.2+ install/runtime/update requirements APIs.',
);
$info = $read($moduleRoot . '/aculta_portal.info.yml');
$check(
  preg_match('/^core_version_requirement:\s*\^11\.3\s*$/m', $info) === 1,
  'aculta_portal.info.yml must declare core_version_requirement: ^11.3.',
);

$requiredDocs = [
  $root . '/AGENTS.md' => 'docs/portal/DRUPAL-11-STANDARDS.md',
  $root . '/docs/ANTI-REGRESSION.md' => 'DRUPAL-11-STANDARDS.md',
  $root . '/docs/README.md' => 'DRUPAL-11-STANDARDS.md',
  $root . '/docs/portal/README.md' => 'DRUPAL-11-STANDARDS.md',
  $moduleRoot . '/README.md' => 'DRUPAL-11-STANDARDS.md',
  $moduleRoot . '/CHANGELOG.md' => 'Drupal 11+',
];
foreach ($requiredDocs as $path => $needle) {
  $content = $read($path);
  $check(
    str_contains($content, $needle),
    basename($path) . ' must reference the Drupal 11+ standard/change.',
  );
}
$check(
  is_file($root . '/docs/portal/DRUPAL-11-STANDARDS.md'),
  'Normative docs/portal/DRUPAL-11-STANDARDS.md must exist.',
);

$services = $read($moduleRoot . '/aculta_portal.services.yml');
$legacySubscriberTags = preg_match_all('/name:\s*kernel\.event_subscriber\b/', $services);
$check(
  is_int($legacySubscriberTags) && $legacySubscriberTags <= 6,
  'Legacy kernel.event_subscriber debt may not grow above the P1 baseline of 6.',
);
$check(
  preg_match('/name:\s*event_subscriber\b/', $services) === 1,
  'At least one canonical event_subscriber tag must remain present.',
);

$legacyProceduralFunctions = [
  'aculta_portal_entity_presave',
];

$tokenHooks = $srcRoot . '/Hook/TokenHooks.php';
$tokenHooksSource = $read($tokenHooks);
$check(is_file($tokenHooks), 'P2 TokenHooks class must exist.');
$check(
  substr_count($tokenHooksSource, "#[Hook('token_info')]") === 1,
  'P2 TokenHooks must implement token_info as an OOP hook.',
);
$check(
  substr_count($tokenHooksSource, "#[Hook('tokens')]") === 1,
  'P2 TokenHooks must implement tokens as an OOP hook.',
);

$editorialHooks = $srcRoot . '/Hook/EditorialHooks.php';
$editorialHooksSource = $read($editorialHooks);
$check(is_file($editorialHooks), 'P2.2 EditorialHooks class must exist.');
foreach (['metatag_tags_alter', 'node_presave', 'metatags_alter', 'library_info_alter'] as $hookName) {
  $check(
    substr_count($editorialHooksSource, "#[Hook('" . $hookName . "')]") === 1,
    'P2 EditorialHooks must implement ' . $hookName . ' as an OOP hook.',
  );
}

$hookRuntimeSources = '';
foreach (glob($srcRoot . '/Hook/*.php') ?: [] as $hookFile) {
  $hookRuntimeSources .= $read($hookFile);
}
$p2MigratedHooks = [
  'token_info',
  'tokens',
  'metatag_tags_alter',
  'node_presave',
  'metatags_alter',
  'library_info_alter',
];
foreach ($p2MigratedHooks as $hookName) {
  $check(
    substr_count($hookRuntimeSources, "#[Hook('" . $hookName . "')]") === 1,
    'P2-R migrated hook must be implemented exactly once: ' . $hookName,
  );
}
$check(
  preg_match('/declare\\s*\\(\\s*strict_types\\s*=\\s*1\\s*\\)\\s*;/', $tokenHooksSource) === 1
    && preg_match('/declare\\s*\\(\\s*strict_types\\s*=\\s*1\\s*\\)\\s*;/', $editorialHooksSource) === 1,
  'P2-R TokenHooks and EditorialHooks must keep strict_types=1.',
);
$check(
  !str_contains($tokenHooksSource, '\\Drupal::')
    && !str_contains($editorialHooksSource, '\\Drupal::'),
  'P2-R migrated hook classes must not use Drupal static service locators.',
);
$check(
  preg_match('/function\\s+tokenInfo\\s*\\(\\s*\\)\\s*:\\s*array/', $tokenHooksSource) === 1,
  'P2-R token_info signature must return array.',
);
$check(
  preg_match('/function\\s+tokens\\s*\\([\\s\\S]*?BubbleableMetadata\\s+\\$metadata,?\\s*\\)\\s*:\\s*array/', $tokenHooksSource) === 1,
  'P2-R tokens signature must preserve BubbleableMetadata and array return.',
);
$check(
  preg_match('/function\\s+metatagTagsAlter\\s*\\(array\\s*&\\$definitions\\)\\s*:\\s*void/', $editorialHooksSource) === 1,
  'P2-R metatag_tags_alter signature must preserve definitions by reference.',
);
$check(
  preg_match('/function\\s+nodePresave\\s*\\(NodeInterface\\s+\\$node\\)\\s*:\\s*void/', $editorialHooksSource) === 1,
  'P2-R node_presave signature must preserve NodeInterface.',
);
$check(
  preg_match('/function\\s+metatagsAlter\\s*\\(array\\s*&\\$tags,\\s*array\\s*&\\$context\\)\\s*:\\s*void/', $editorialHooksSource) === 1,
  'P2-R metatags_alter must preserve both tags and context references.',
);
$check(
  preg_match('/function\\s+libraryInfoAlter\\s*\\(array\\s*&\\$libraries,\\s*string\\s+\\$extension\\)\\s*:\\s*void/', $editorialHooksSource) === 1,
  'P2-R library_info_alter signature must match Drupal 11.',
);
foreach ([
  'config.factory',
  'entity_type.manager',
  'aculta_portal.domain_purpose',
  'domain.negotiator',
  'request_stack',
  'file_url_generator',
  'string_translation',
] as $serviceId) {
  $check(
    str_contains($tokenHooksSource, "service: '" . $serviceId . "'"),
    'P2-R TokenHooks must keep explicit DI for service ' . $serviceId . '.',
  );
}
$check(
  str_contains($tokenHooksSource, '$metadata->addCacheableDependency($settings)')
    && str_contains($tokenHooksSource, '$metadata->addCacheableDependency($node)'),
  'P2-R TokenHooks must keep config and node cacheability.',
);
$check(
  str_contains($editorialHooksSource, "'aculta_portal/cep-address'")
    && str_contains($editorialHooksSource, 'SchemaMetatagManager::serialize')
    && str_contains($editorialHooksSource, "'courses'")
    && str_contains($editorialHooksSource, "'wiki'"),
  'P2-R editorial/CEP/Domain behavior invariants must remain present.',
);
$entityHooks = $srcRoot . '/Hook/EntityHooks.php';
$entityHooksSource = $read($entityHooks);
$check(is_file($entityHooks), 'P4.2 EntityHooks class must exist.');
$check(
  substr_count($hookRuntimeSources, "#[Hook('entity_access')]") === 1,
  'P4.2 entity_access must be implemented exactly once as an OOP hook.',
);
$check(
  preg_match(
    '/function\\s+entityAccess\\s*\\(\\s*EntityInterface\\s+\\$entity,\\s*\\$operation,\\s*AccountInterface\\s+\\$account,?\\s*\\)\\s*:\\s*AccessResultInterface/',
    $entityHooksSource,
  ) === 1,
  'P4.2 EntityHooks::entityAccess() must preserve the Drupal 11 hook_entity_access signature.',
);
$check(
  preg_match('/declare\\s*\\(\\s*strict_types\\s*=\\s*1\\s*\\)\\s*;/', $entityHooksSource) === 1,
  'P4.2 EntityHooks must declare strict_types=1.',
);
$check(
  !str_contains($entityHooksSource, '\\Drupal::'),
  'P4.2 EntityHooks must use explicit DI instead of Drupal static service locators.',
);
foreach (['aculta_portal.domain_purpose', 'current_route_match', 'request_stack'] as $serviceId) {
  $check(
    str_contains($entityHooksSource, "service: '" . $serviceId . "'"),
    'P4.2 EntityHooks must inject service ' . $serviceId . '.',
  );
}
$check(
  !str_contains($services, 'Drupal\\aculta_portal\\Hook\\EntityHooks'),
  'P4.2 Hook classes are auto-discovered/autowired; do not add redundant EntityHooks YAML service definitions.',
);
foreach ([
  "'wiki_entry'",
  "'wiki_category'",
  "'domain'",
  "'entity.user.edit_form'",
  "'administer users'",
  'AccountRouteSubscriber::isValidCorePasswordResetRequest',
  "['route', 'user']",
  'setCacheMaxAge(0)',
  "'commerce_payment_gateway'",
  "'mercado_pago'",
] as $invariant) {
  $check(
    str_contains($entityHooksSource, $invariant),
    'P4.2 entity access invariant must remain present: ' . $invariant,
  );
}
$check(
  preg_match(
    '/AccessResult::neutral\\(\\)[\\s\\S]{0,220}?cachePerPermissions\\(\\)[\\s\\S]{0,220}?addCacheContexts\\(\\[\\'route\\', \\'user\\'\\]\\)[\\s\\S]{0,220}?setCacheMaxAge\\(0\\)/',
    $entityHooksSource,
  ) === 1,
  'P4.2 valid password-reset neutral result must remain request-sensitive and uncacheable.',
);

$formHooks = $srcRoot . '/Hook/FormHooks.php';
$formHooksSource = $read($formHooks);
$check(is_file($formHooks), 'P4.1 FormHooks class must exist.');
$check(
  substr_count($hookRuntimeSources, "#[Hook('form_alter')]") === 1,
  'P4.1 form_alter must be implemented exactly once as an OOP hook.',
);
$check(
  !str_contains($hookRuntimeSources, '#[FormAlter'),
  'P4.1 removed Drupal 11.2 #[FormAlter] attribute must not be used; use #[Hook(\'form_alter\')].',
);
$check(
  preg_match(
    '/function\\s+formAlter\\s*\\(array\\s*&\\$form,\\s*FormStateInterface\\s+\\$formState,\\s*string\\s+\\$formId\\s*\\)\\s*:\\s*void/',
    $formHooksSource,
  ) === 1,
  'P4.1 FormHooks::formAlter() must match the Drupal 11 hook_form_alter signature.',
);
$check(
  preg_match('/declare\\s*\\(\\s*strict_types\\s*=\\s*1\\s*\\)\\s*;/', $formHooksSource) === 1,
  'P4.1 FormHooks must declare strict_types=1.',
);
$check(
  !str_contains($formHooksSource, '\\Drupal::'),
  'P4.1 FormHooks must use explicit DI instead of Drupal static service locators.',
);
foreach (['current_route_match', 'current_user', 'string_translation'] as $serviceId) {
  $check(
    str_contains($formHooksSource, "service: '" . $serviceId . "'"),
    'P4.1 FormHooks must inject service ' . $serviceId . '.',
  );
}
$check(
  !str_contains($services, 'Drupal\\aculta_portal\\Hook\\FormHooks'),
  'P4.1 Hook classes are auto-discovered/autowired; do not add redundant FormHooks YAML service definitions.',
);
foreach ([
  "'change_mail_form'",
  "'user_form'",
  'PaymentGatewayForm',
  "'commerce_donation_pane'",
  "'credentials_test'",
  "'access_token_test'",
  "'client_secret'",
  "'aculta_portal.form_callbacks:changeMailConfirmationMessage'",
  "'aculta_portal.form_callbacks:securityPasswordAfterBuild'",
  "'aculta_portal.form_callbacks:securityPasswordRedirect'",
  "'aculta_portal.form_callbacks:validateDonationAmount'",
] as $invariant) {
  $check(
    str_contains($formHooksSource, $invariant),
    'P4.1 form behavior invariant must remain present: ' . $invariant,
  );
}

$formCallbacks = $srcRoot . '/Form/PortalFormCallbacks.php';
$formCallbacksSource = $read($formCallbacks);
$check(is_file($formCallbacks), 'P3.1 PortalFormCallbacks service class must exist.');
$check(
  str_contains($services, 'aculta_portal.form_callbacks:'),
  'P3.1 aculta_portal.form_callbacks service must be registered.',
);
$check(
  str_contains($services, 'Drupal\\aculta_portal\\Form\\PortalFormCallbacks'),
  'P3.1 form callback service must use PortalFormCallbacks.',
);
$check(
  str_contains($editorialHooksSource, "#[Hook('form_node_form_alter')]"),
  'P3.1 EditorialHooks must implement form_node_form_alter as OOP.',
);
$check(
  str_contains($editorialHooksSource, "'aculta_portal.form_callbacks:validateActivity'"),
  'P3.1 activity validation must use the Drupal 11.3+ service callback syntax.',
);
$check(
  str_contains($formCallbacksSource, 'function validateActivity('),
  'P3.1 PortalFormCallbacks must provide validateActivity().',
);

$moduleCallbacksPath = $moduleRoot . '/aculta_portal.module';
$moduleCallbacksSource = is_file($moduleCallbacksPath) ? $read($moduleCallbacksPath) : '';
$portalHooksSource = $read($srcRoot . '/Hook/PortalHooks.php');

$legacyFormCallbackNames = [
  'aculta_portal_validate_activity',
  'aculta_portal_change_mail_confirmation_message',
  'aculta_portal_security_password_redirect',
  'aculta_portal_security_password_after_build',
  'aculta_portal_account_photo_redirect',
  'aculta_portal_address_redirect',
  'aculta_portal_sync_customer_address_names',
  'aculta_portal_validate_donation_amount',
];
$p3RuntimeSources = [
  $moduleCallbacksSource,
  $portalHooksSource,
  $editorialHooksSource,
  $formHooksSource,
  $formCallbacksSource,
  $services,
];
foreach ($legacyFormCallbackNames as $legacyCallback) {
  $remaining = 0;
  foreach ($p3RuntimeSources as $source) {
    $remaining += substr_count($source, "'" . $legacyCallback . "'");
  }
  $check(
    $remaining === 0,
    'P3.3 legacy form callback must not return: ' . $legacyCallback,
  );
}

$check(
  substr_count($services, '  aculta_portal.form_callbacks:') === 1,
  'P3.3 aculta_portal.form_callbacks must be defined exactly once.',
);
$formCallbackServiceBlock = '';
if (preg_match(
  '/^  aculta_portal\.form_callbacks:\R(?:(?:    ).*(?:\R|$))+/m',
  $services,
  $serviceMatches,
) === 1) {
  $formCallbackServiceBlock = $serviceMatches[0];
}
$check(
  str_contains($formCallbackServiceBlock, 'class: Drupal\\aculta_portal\\Form\\PortalFormCallbacks'),
  'P3.3 form callback service must resolve to PortalFormCallbacks.',
);
foreach ([
  '@messenger',
  '@current_user',
  '@user.data',
  '@entity_type.manager',
  '@string_translation',
] as $dependency) {
  $check(
    str_contains($formCallbackServiceBlock, "'" . $dependency . "'"),
    'P3.3 form callback service must inject ' . $dependency . '.',
  );
}

$formCallbackRegistrations = [
  'validateActivity' => [$editorialHooksSource],
  'changeMailConfirmationMessage' => [$formHooksSource],
  'securityPasswordRedirect' => [$formHooksSource],
  'securityPasswordAfterBuild' => [$formHooksSource],
  'accountPhotoRedirect' => [$portalHooksSource],
  'addressRedirect' => [$portalHooksSource],
  'syncCustomerAddressNames' => [$portalHooksSource],
  'validateDonationAmount' => [$formHooksSource],
];
foreach ($formCallbackRegistrations as $methodName => $sources) {
  $check(
    substr_count($formCallbacksSource, 'function ' . $methodName . '(') === 1,
    'P3.3 PortalFormCallbacks must define ' . $methodName . '() exactly once.',
  );

  $registration = "'aculta_portal.form_callbacks:" . $methodName . "'";
  $registrationCount = 0;
  foreach ($sources as $source) {
    $registrationCount += substr_count($source, $registration);
  }
  $check(
    $registrationCount === 1,
    'P3.3 service callback must be registered exactly once: ' . $methodName,
  );
}

$check(
  preg_match(
    '/function\s+formNodeFormAlter\s*\(array\s*&\$form,\s*FormStateInterface\s+\$formState,\s*string\s+\$formId\s*\)/',
    $editorialHooksSource,
  ) === 1,
  'P3.3 form_node_form_alter must preserve the Drupal 11 base-form hook signature.',
);

$moduleFile = $moduleRoot . '/aculta_portal.module';
if (is_file($moduleFile)) {
  $moduleSource = $read($moduleFile);
  preg_match_all('/^function\s+(aculta_portal_[A-Za-z0-9_]+)\s*\(/m', $moduleSource, $matches);
  $unknown = array_values(array_diff($matches[1] ?? [], $legacyProceduralFunctions));
  $check(
    $unknown === [],
    'No new procedural runtime functions may be added to aculta_portal.module: ' . implode(', ', $unknown),
  );
}
else {
  $check(TRUE, 'aculta_portal.module removed after procedural debt migration.');
}

$serviceLocatorCeilings = [
  'src/Controller/PortalController.php' => 3,
  'src/Controller/PortalRequirementsController.php' => 2,
  'src/Controller/WikiController.php' => 6,
  'src/Support/Form/SupportForm.php' => 1,
];
$viewsWrapperCeilings = [
  'src/Controller/CoursesController.php' => 1,
  'src/Controller/WikiController.php' => 1,
];
$staticLoadCeilings = [
  'src/Support/Form/SupportForm.php' => 1,
];
$strictTypesDebt = [
  'src/AccountShellBuilder.php',
  'src/Auth/AuthIntegrationManager.php',
  'src/Controller/PortalController.php',
  'src/Controller/PortalRequirementsController.php',
  'src/Hook/PortalHooks.php',
  'src/Support/Controller/SupportController.php',
  'src/Support/Form/SettingsForm.php',
  'src/Support/Form/SupportForm.php',
  'src/Plugin/metatag/Tag/OrganizationAlternateName.php',
  'src/Plugin/metatag/Tag/OrganizationEmail.php',
  'src/Plugin/metatag/Tag/OrganizationLegalName.php',
  'src/Plugin/metatag/Tag/OrganizationTaxId.php',
  'src/Plugin/metatag/Tag/PostalAddressTag.php',
  'src/Plugin/metatag/Tag/SchemaWebPageName.php',
  'src/Plugin/metatag/Tag/SchemaWebPageUrl.php',
];

$phpFiles = [];
$iterator = new RecursiveIteratorIterator(
  new RecursiveDirectoryIterator($srcRoot, FilesystemIterator::SKIP_DOTS),
);
foreach ($iterator as $file) {
  if ($file->isFile() && strtolower($file->getExtension()) === 'php') {
    $phpFiles[] = $file->getPathname();
  }
}
sort($phpFiles);
$check(count($phpFiles) >= 38, 'Portal runtime PHP inventory must not unexpectedly shrink below the P1 baseline without gate review.');

$locatorPattern = '/\\\\Drupal::(?:service|entityTypeManager|database|request|routeMatch|currentUser|config|messenger|entityQuery)\s*\(/';
$viewsPattern = '/\b(?:views_embed_view|Views::getView)\s*\(/';
$staticLoadPattern = '/\b(?:Node|User|Role|PaymentGateway|ProfileType|FieldConfig|FieldStorageConfig)::(?:load|loadByName)\s*\(/';

foreach ($phpFiles as $path) {
  $source = $read($path);
  $relative = 'src/' . str_replace('\\', '/', substr($path, strlen($srcRoot) + 1));

  $locatorCount = preg_match_all($locatorPattern, $source);
  $locatorCeiling = $serviceLocatorCeilings[$relative] ?? 0;
  $check(
    is_int($locatorCount) && $locatorCount <= $locatorCeiling,
    $relative . ' exceeds service-locator debt ceiling ' . $locatorCeiling . '.',
  );

  $viewsCount = preg_match_all($viewsPattern, $source);
  $viewsCeiling = $viewsWrapperCeilings[$relative] ?? 0;
  $check(
    is_int($viewsCount) && $viewsCount <= $viewsCeiling,
    $relative . ' exceeds Views-wrapper debt ceiling ' . $viewsCeiling . '.',
  );

  $staticLoadCount = preg_match_all($staticLoadPattern, $source);
  $staticLoadCeiling = $staticLoadCeilings[$relative] ?? 0;
  $check(
    is_int($staticLoadCount) && $staticLoadCount <= $staticLoadCeiling,
    $relative . ' exceeds static entity-load debt ceiling ' . $staticLoadCeiling . '.',
  );

  if (!in_array($relative, $strictTypesDebt, TRUE)) {
    $check(
      preg_match('/declare\s*\(\s*strict_types\s*=\s*1\s*\)\s*;/', $source) === 1,
      $relative . ' must declare strict_types=1.',
    );
  }
}

if ($failures !== []) {
  fwrite(STDERR, "ACULTA PORTAL DRUPAL 11+ GATE: FAIL\n");
  foreach ($failures as $failure) {
    fwrite(STDERR, '- ' . $failure . "\n");
  }
  exit(1);
}

echo 'ACULTA PORTAL DRUPAL 11+ GATE: PASS (' . $checks . " checks; progressive Drupal 11+ baseline)\n";
