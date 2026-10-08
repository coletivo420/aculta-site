<?php

declare(strict_types=1);

/**
 * Read-only Runtime gate for centralized checkout/payment Domain ownership.
 */

use Drupal\aculta_portal\Domain\DomainRoutePolicy;

$checks = [];
$assert = static function (bool $condition, string $message) use (&$checks): void {
  if (!$condition) {
    throw new RuntimeException($message);
  }
  $checks[] = $message;
};

$routeProvider = \Drupal::service('router.route_provider');
$routes = $routeProvider->getAllRoutes();

$centralRoutes = [];
foreach ($routes as $name => $route) {
  if (!DomainRoutePolicy::isCentralPaymentRouteName((string) $name)) {
    continue;
  }
  $centralRoutes[(string) $name] = $route;
  $assert(
    $route->getOption('_aculta_domain_purpose') === 'main',
    'Central payment route belongs to MAIN: ' . $name,
  );
}

$assert($centralRoutes !== [], 'At least one central Commerce payment route was inspected.');

foreach (['commerce_cart.page', 'commerce_checkout.checkout', 'commerce_checkout.form'] as $required) {
  $assert(isset($centralRoutes[$required]), 'Required checkout route is central: ' . $required);
}

foreach (['commerce_payment.checkout.return', 'commerce_payment.checkout.cancel'] as $routeName) {
  if (isset($routes[$routeName])) {
    $assert(
      isset($centralRoutes[$routeName]),
      'Browser-facing payment callback route is central: ' . $routeName,
    );
  }
}

$notify = $routes['commerce_payment.notify'] ?? NULL;
$assert($notify !== NULL, 'Commerce payment notify route exists.');
$assert(
  $notify->getOption('_aculta_domain_purpose') === 'main',
  'Commerce payment notify belongs to MAIN.',
);
$methods = $notify->getMethods();
$assert(
  $methods === ['POST'],
  'Commerce payment notify is POST-only.',
);

$modules = \Drupal::moduleHandler();
if ($modules->moduleExists('commerce_donation_flow')) {
  $donationRoutes = array_filter(
    array_keys($centralRoutes),
    static fn(string $name): bool => str_starts_with($name, 'commerce_donation_flow.'),
  );
  $assert($donationRoutes !== [], 'Commerce Donation Flow exposes central routes.');
}

$domainPurpose = \Drupal::service('aculta_portal.domain_purpose');
$checkoutUrl = $domainPurpose->routeUrl('main', 'commerce_checkout.checkout');
$assert($checkoutUrl !== NULL, 'Portal can generate absolute MAIN checkout URL.');
$assert(
  preg_match('#^https?://[^/]+/checkout(?:/)?$#', $checkoutUrl->toString()) === 1,
  'MAIN checkout URL is absolute and Domain-managed.',
);

$portalRoot = DRUPAL_ROOT . '/modules/custom/aculta_portal';
$routePolicySource = file_get_contents($portalRoot . '/src/Domain/DomainRoutePolicy.php');
$routeSubscriberSource = file_get_contents($portalRoot . '/src/EventSubscriber/DomainRouteSubscriber.php');
$requestSubscriberSource = file_get_contents($portalRoot . '/src/EventSubscriber/DomainPurposeRequestSubscriber.php');
$hooksSource = file_get_contents($portalRoot . '/src/Hook/PortalHooks.php');

foreach ([
  'commerce_cart.',
  'commerce_checkout.',
  'commerce_payment.checkout.',
  'commerce_payment.notify',
  'commerce_donation_flow.',
] as $family) {
  $assert(
    str_contains($routePolicySource, $family),
    'Shared DomainRoutePolicy owns payment route family: ' . $family,
  );
}

$assert(
  str_contains($routeSubscriberSource, 'DomainRoutePolicy::isCentralPaymentRouteName'),
  'Route classification reuses the shared payment route policy.',
);
$assert(
  str_contains($requestSubscriberSource, 'DomainRoutePolicy::isCentralPaymentRouteName')
    && str_contains($requestSubscriberSource, "['GET', 'HEAD']"),
  'Wrong-host checkout navigation canonicalizes only safe methods.',
);
$assert(
  str_contains($hooksSource, 'DomainRoutePolicy::isCentralPaymentRouteName')
    && str_contains($hooksSource, "routeUrl(
          'main'"),
  'Portal rewrites routed checkout/payment links directly to MAIN.',
);

foreach ([$routePolicySource, $routeSubscriberSource, $requestSubscriberSource, $hooksSource] as $source) {
  $assert(
    !str_contains($source, 'https://aculta.org')
      && !str_contains($source, '.toca.net.br'),
    'Payment Domain policy contains no hardcoded environment hostname.',
  );
}

echo 'PAYMENT DOMAIN POLICY: PASS (' . count($checks) . " checks)\n";
