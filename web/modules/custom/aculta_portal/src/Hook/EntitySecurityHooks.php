<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\Hook;

use Drupal\aculta_portal\Domain\DomainPurposeManager;
use Drupal\aculta_portal\EventSubscriber\AccountRouteSubscriber;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;

/** Entity access and fail-closed persistence hooks. */
final class EntitySecurityHooks {

  public function __construct(
    #[Autowire(service: 'aculta_portal.domain_purpose')]
    private readonly DomainPurposeManager $domainPurpose,
    #[Autowire(service: 'current_route_match')]
    private readonly CurrentRouteMatch $routeMatch,
    #[Autowire(service: 'request_stack')]
    private readonly RequestStack $requestStack,
  ) {}

  #[Hook('entity_access')]
  public function entityAccess(
    EntityInterface $entity,
    string $operation,
    AccountInterface $account,
  ): AccessResult {
    $purpose = $this->domainPurpose->getCurrentPurpose();
    $wikiEntity = ($entity->getEntityTypeId() === 'node' && $entity->bundle() === 'wiki_entry')
      || ($entity->getEntityTypeId() === 'taxonomy_term' && $entity->bundle() === 'wiki_category');

    if ($wikiEntity && $purpose !== 'wiki') {
      return AccessResult::forbidden()
        ->cachePerPermissions()
        ->addCacheContexts(['domain'])
        ->addCacheableDependency($entity);
    }

    if ($entity->getEntityTypeId() === 'user'
      && $operation === 'update'
      && $this->routeMatch->getRouteName() === 'entity.user.edit_form'
      && !$account->hasPermission('administer users')) {
      $request = $this->requestStack->getCurrentRequest();
      if ($request && AccountRouteSubscriber::isValidCorePasswordResetRequest(
        $request,
        (int) $entity->id(),
        (int) $account->id(),
      )) {
        return AccessResult::neutral();
      }

      return AccessResult::forbidden()
        ->cachePerPermissions()
        ->addCacheContexts(['route', 'user'])
        ->setCacheMaxAge(0);
    }

    if ($entity->getEntityTypeId() !== 'commerce_payment_gateway'
      || $entity->id() !== 'mercado_pago'
      || $operation !== 'update') {
      return AccessResult::neutral();
    }

    return AccessResult::forbidden(
      'This gateway is managed through reviewed configuration and environment values; editing it here could persist credentials.'
    )
      ->cachePerPermissions()
      ->setCacheMaxAge(0);
  }

  #[Hook('entity_presave')]
  public function entityPresave(EntityInterface $entity): void {
    if ($entity->getEntityTypeId() !== 'commerce_payment_gateway'
      || $entity->id() !== 'mercado_pago'
      || !$entity->status()) {
      return;
    }

    if (!getenv('MERCADOPAGO_PUBLIC_KEY') || !getenv('MERCADOPAGO_ACCESS_TOKEN')) {
      throw new \LogicException(
        'The Mercado Pago gateway cannot be enabled without both runtime test credentials.'
      );
    }
  }

}
