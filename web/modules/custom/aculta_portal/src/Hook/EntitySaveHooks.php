<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\Hook;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Entity lifecycle guards owned by the Portal.
 */
final class EntitySaveHooks {

  /**
   * Keeps the Mercado Pago gateway fail-closed on save.
   */
  #[Hook('entity_presave')]
  public function entityPresave(EntityInterface $entity): void {
    if ($entity->getEntityTypeId() !== 'commerce_payment_gateway'
      || $entity->id() !== 'mercado_pago'
      || !$entity->status()) {
      return;
    }

    if (!getenv('MERCADOPAGO_PUBLIC_KEY') || !getenv('MERCADOPAGO_ACCESS_TOKEN')) {
      throw new \LogicException(
        'The Mercado Pago gateway cannot be enabled without both runtime test credentials.',
      );
    }
  }

}
