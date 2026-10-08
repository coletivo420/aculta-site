<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\Domain;

/**
 * Centralizes route-family ownership rules shared across Portal boundaries.
 */
final class DomainRoutePolicy {

  /**
   * Returns TRUE for cart/checkout/payment routes that always belong to MAIN.
   */
  public static function isCentralTransactionRouteName(string $routeName): bool {
    return str_starts_with($routeName, 'commerce_cart.')
      || str_starts_with($routeName, 'commerce_checkout.')
      || str_starts_with($routeName, 'commerce_payment.checkout.')
      || $routeName === 'commerce_payment.notify'
      || str_starts_with($routeName, 'commerce_donation_flow.');
  }

}
