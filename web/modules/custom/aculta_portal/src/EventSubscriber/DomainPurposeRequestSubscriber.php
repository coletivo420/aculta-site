<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\EventSubscriber;

use Drupal\aculta_portal\Domain\DomainPurposeManager;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Route;

/** Enforces route purpose after Drupal has resolved the active Domain alias. */
final class DomainPurposeRequestSubscriber implements EventSubscriberInterface {

  public function __construct(
    private readonly DomainPurposeManager $domainPurposeManager,
    private readonly RequestMatcherInterface $accessFreeMatcher,
    private readonly AccountProxyInterface $currentUser,
  ) {}

  public static function getSubscribedEvents(): array {
    // Match the same path before RouterListener/access checks (priority 32).
    return [
      KernelEvents::REQUEST => [['onRequestBeforeRouter', 33], ['onRequest', 31]],
      KernelEvents::RESPONSE => ['onResponse', 0],
    ];
  }

  /** Sends completed logouts to the public account login page. */
  public function onResponse(ResponseEvent $event): void {
    if (!$event->isMainRequest()
      || $event->getRequest()->attributes->get('_route') !== 'user.logout'
      || !$event->getResponse()->isRedirection()
      || $this->currentUser->isAuthenticated()) {
      return;
    }

    $login = $this->domainPurposeManager->routeUrl('account', 'user.login');
    if ($login) {
      // Keep Core's status, cookies, and headers after a successful logout.
      $event->getResponse()->headers->set('Location', $login->toString());
    }
  }

  /** Hides a wrong-host route before the access-aware router can return 403. */
  public function onRequestBeforeRouter(RequestEvent $event): void {
    if (!$event->isMainRequest()) {
      return;
    }
    try {
      $matched = $this->accessFreeMatcher->matchRequest($event->getRequest());
    }
    catch (ResourceNotFoundException | MethodNotAllowedException) {
      return;
    }
    $route = \Drupal::service('router.route_provider')->getRouteByName($matched['_route']);
    $requiredPurpose = $this->getRequiredPurpose($route, $matched['_route'], $event->getRequest(), $matched);
    if (is_string($requiredPurpose) && $this->domainPurposeManager->getCurrentPurpose() !== $requiredPurpose) {
      $event->setResponse($this->notFoundResponse());
    }
  }

  public function onRequest(RequestEvent $event): void {
    if (!$event->isMainRequest()) {
      return;
    }
    $request = $event->getRequest();
    $route = $request->attributes->get('_route_object');
    if (!$route || !method_exists($route, 'getOption')) {
      return;
    }
    $requiredPurpose = $this->getRequiredPurpose($route, (string) $request->attributes->get('_route'), $request);
    $currentPurpose = $this->domainPurposeManager->getCurrentPurpose();

    $resetEditException = FALSE;
    if ($request->attributes->get('_route') === 'entity.user.edit_form'
      && $currentPurpose === 'account') {
      $raw = $request->attributes->get('_raw_variables');
      $uid = $request->attributes->get('user') ?? ($raw instanceof \Symfony\Component\HttpFoundation\ParameterBag ? $raw->get('user') : NULL);
      $resetEditException = is_numeric($uid)
        && \Drupal\aculta_portal\EventSubscriber\AccountRouteSubscriber::isValidCorePasswordResetRequest(
          $request,
          (int) $uid,
          (int) \Drupal::currentUser()->id(),
        );
    }
    if (is_string($requiredPurpose) && $currentPurpose !== $requiredPurpose && !$resetEditException) {
      $this->notFound($event);
      return;
    }

    // Domain Source controls canonical outbound URLs. Enforce the same source
    // on direct node requests so a specialized page is not served on MAIN or a
    // different application host as a duplicate.
    if ($request->attributes->get('_route') === 'entity.node.canonical') {
      $raw = $request->attributes->get('_raw_variables');
      $nodeParameter = $request->attributes->get('node');
      if ($nodeParameter === NULL && $raw instanceof \Symfony\Component\HttpFoundation\ParameterBag) {
        $nodeParameter = $raw->get('node');
      }
      $node = is_numeric($nodeParameter)
        ? \Drupal::entityTypeManager()->getStorage('node')->load((int) $nodeParameter)
        : (is_object($nodeParameter) ? $nodeParameter : NULL);
      if ($node && $node->hasField('field_domain_source') && !$node->get('field_domain_source')->isEmpty()) {
        $sourceDomainId = (string) $node->get('field_domain_source')->target_id;
        $sourcePurpose = $this->domainPurposeManager->getPurposeForDomainId($sourceDomainId);
        if ($sourcePurpose !== NULL && $this->domainPurposeManager->getCurrentPurpose() !== $sourcePurpose) {
          $this->notFound($event);
          return;
        }
      }
    }

    // Editorial taxonomy pages belong to MAGAZINE; account and other hosts do
    // not become fallback public profiles for editorial authors/categories.
    if ($request->attributes->get('_route') === 'entity.taxonomy_term.canonical') {
      $raw = $request->attributes->get('_raw_variables');
      $termParameter = $request->attributes->get('taxonomy_term');
      if ($termParameter === NULL && $raw instanceof \Symfony\Component\HttpFoundation\ParameterBag) {
        $termParameter = $raw->get('taxonomy_term');
      }
      $term = is_numeric($termParameter)
        ? \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load((int) $termParameter)
        : (is_object($termParameter) ? $termParameter : NULL);
      $termPurpose = $term && $term->bundle() === 'wiki_category' ? 'wiki' : 'magazine';
      if ($term && (in_array($term->bundle(), ['editorial_author', 'editorial_category', 'wiki_category'], TRUE))
        && $this->domainPurposeManager->getCurrentPurpose() !== $termPurpose) {
        $this->notFound($event);
      }
    }
  }

  private function notFound(RequestEvent $event): void {
    $event->setResponse($this->notFoundResponse());
  }

  /**
   * Resolves editorial routes by bundle while keeping generic node routes on
   * their normal host. Wiki entry creation, editing, history and Diff belong
   * to Wiki420 even though Core marks some of them as administrative routes.
   */
  private function getRequiredPurpose(Route $route, string $routeName, Request $request, array $matched = []): ?string {
    // Public LMS course routes follow the COURSES Domain. Routes marked as
    // administrative by the central route subscriber remain on MAIN.
    $groupParameter = $matched['group'] ?? $request->attributes->get('group');
    $group = is_object($groupParameter) ? $groupParameter : NULL;
    if (!$group && is_numeric($groupParameter)) {
      $group = \Drupal::entityTypeManager()->getStorage('group')->load((int) $groupParameter);
    }
    if ($group && method_exists($group, 'bundle') && $group->bundle() === 'lms_course') {
      if ($route->getOption('_admin_route') || $route->getOption('_aculta_domain_purpose') === 'main') {
        return 'main';
      }
      return 'courses';
    }

    $nodeRoutes = [
      'entity.node.edit_form',
      'entity.node.version_history',
      'entity.node.revision',
      'node.revision_revert_confirm',
      'node.revision_revert_translation_confirm',
      'node.revision_delete_confirm',
      'diff.revisions_diff',
    ];
    if ($routeName === 'node.add') {
      $nodeType = $matched['node_type'] ?? $request->attributes->get('node_type');
      if (is_object($nodeType) && method_exists($nodeType, 'id')) {
        $nodeType = $nodeType->id();
      }
      if (is_string($nodeType) && $nodeType === 'wiki_entry') {
        return 'wiki';
      }
    }
    if (in_array($routeName, $nodeRoutes, TRUE)) {
      $node = $request->attributes->get('node') ?? ($matched['node'] ?? NULL);
      if (is_object($node) && method_exists($node, 'bundle')) {
        return $node->bundle() === 'wiki_entry' ? 'wiki' : $route->getOption('_aculta_domain_purpose');
      }
      if (is_numeric($node)) {
        $node = \Drupal::entityTypeManager()->getStorage('node')->load((int) $node);
        if ($node) {
          return $node->bundle() === 'wiki_entry' ? 'wiki' : $route->getOption('_aculta_domain_purpose');
        }
      }
    }
    return $route->getOption('_aculta_domain_purpose');
  }

  private function notFoundResponse(): Response {
    return new Response('', Response::HTTP_NOT_FOUND, [
      'Cache-Control' => 'private, no-store',
      'X-Robots-Tag' => 'noindex, nofollow',
    ]);
  }

}
