<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\EventSubscriber;

use Drupal\aculta_portal\Domain\DomainPurposeManager;
use Drupal\Core\Routing\RouteProviderInterface;
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

/** Enforces route purpose after Drupal has resolved the active Domain alias. */
final class DomainPurposeRequestSubscriber implements EventSubscriberInterface {

  private const LOGIN_DESTINATION_SESSION_KEY = 'aculta_portal.login_destination';

  public function __construct(
    private readonly DomainPurposeManager $domainPurposeManager,
    private readonly RequestMatcherInterface $accessFreeMatcher,
    private readonly AccountProxyInterface $currentUser,
    private readonly RouteProviderInterface $routeProvider,
    private readonly \Drupal\aculta_portal\Domain\ContentPurposeResolver $contentPurposeResolver,
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
    if (!$event->isMainRequest() || !$event->getResponse()->isRedirection()) {
      return;
    }

    $request = $event->getRequest();
    $route = $request->attributes->get('_route');
    if (in_array($route, ['user.login', 'social_auth.network.callback'], TRUE)
      && $this->currentUser->isAuthenticated()
      && $request->hasSession()) {
      $destination = $request->getSession()->get(self::LOGIN_DESTINATION_SESSION_KEY);
      $request->getSession()->remove(self::LOGIN_DESTINATION_SESSION_KEY);
      if (is_array($destination)
        && isset($destination['purpose'], $destination['path'])
        && is_string($destination['purpose'])
        && is_string($destination['path'])
        && $this->isSafeLoginDestinationPath($destination['path'])) {
        $url = $this->domainPurposeManager->pathUrl($destination['purpose'], $destination['path']);
        if ($url !== NULL) {
          $query = $destination['query'] ?? [];
          if (is_array($query) && $query !== []) {
            $url->setOption('query', $query);
          }
          $event->getResponse()->headers->set('Location', $url->toString());
          return;
        }
      }

      // ACCOUNT's Domain root is the public landing URL. Its domain-specific
      // front page resolves /conta-interna internally.
      $accountRoot = $this->domainPurposeManager->pathUrl('account', '/');
      if ($accountRoot !== NULL) {
        $event->getResponse()->headers->set('Location', $accountRoot->toString());
        return;
      }
    }

    if ($route !== 'user.logout' || $this->currentUser->isAuthenticated()) {
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
    $route = $this->routeProvider->getRouteByName($matched['_route']);
    $requiredPurpose = $this->contentPurposeResolver->requiredPurpose(
      $route,
      $matched['_route'],
      $event->getRequest(),
      $matched,
    );
    if (is_string($requiredPurpose) && $this->domainPurposeManager->getCurrentPurpose() !== $requiredPurpose) {
      $event->setResponse($this->notFoundResponse());
    }
  }

  public function onRequest(RequestEvent $event): void {
    if (!$event->isMainRequest()) {
      return;
    }
    $request = $event->getRequest();
    $routeName = (string) $request->attributes->get('_route');
    if (in_array($routeName, ['user.login', 'social_auth.network.redirect'], TRUE)
      && !$this->currentUser->isAuthenticated()
      && $request->hasSession()) {
      $session = $request->getSession();
      $destination = $this->parseLoginDestination($request->query->get('destination'));
      $requestedPurpose = $request->query->get('aculta_destination_purpose');

      // A direct/fresh login must not inherit a destination abandoned earlier
      // in the same anonymous session.
      if ($routeName === 'user.login' && $destination === NULL) {
        $session->remove(self::LOGIN_DESTINATION_SESSION_KEY);
      }

      if ($destination !== NULL) {
        $storedDestination = $session->get(self::LOGIN_DESTINATION_SESSION_KEY);
        $destinationPurpose = is_string($requestedPurpose)
          && $this->domainPurposeManager->getDomain($requestedPurpose)
            ? $requestedPurpose
            : NULL;

        // Social Auth forwards Drupal's standard destination but not the
        // ACULTA-specific purpose. Preserve the purpose captured on /entrar
        // when the OAuth request refers to the same path and query.
        if ($destinationPurpose === NULL
          && $routeName === 'social_auth.network.redirect'
          && is_array($storedDestination)
          && ($storedDestination['path'] ?? NULL) === $destination['path']
          && ($storedDestination['query'] ?? []) === $destination['query']
          && is_string($storedDestination['purpose'] ?? NULL)
          && $this->domainPurposeManager->getDomain($storedDestination['purpose']) !== NULL) {
          $destinationPurpose = $storedDestination['purpose'];
        }

        $destinationPurpose ??= $this->domainPurposeManager->getCurrentPurpose();
        if ($destinationPurpose !== NULL) {
          $session->set(self::LOGIN_DESTINATION_SESSION_KEY, [
            'purpose' => $destinationPurpose,
            'path' => $destination['path'],
            'query' => $destination['query'],
          ]);
        }
      }
    }

    $route = $request->attributes->get('_route_object');
    if (!$route || !method_exists($route, 'getOption')) {
      return;
    }
    $requiredPurpose = $this->contentPurposeResolver->requiredPurpose(
      $route,
      (string) $request->attributes->get('_route'),
      $request,
    );
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
          (int) $this->currentUser->id(),
        );
    }
    if (is_string($requiredPurpose) && $currentPurpose !== $requiredPurpose && !$resetEditException) {
      $this->notFound($event);
      return;
    }

    $contentPurpose = $this->contentPurposeResolver->canonicalContentPurpose($request);
    if ($contentPurpose !== NULL && $currentPurpose !== $contentPurpose) {
      $this->notFound($event);
    }
  }

  /**
   * Parses a Drupal-internal post-login destination into safe URL components.
   *
   * @return array{path: string, query: array}|null
   *   The normalized destination, or NULL for external/unsafe destinations.
   */
  private function parseLoginDestination(mixed $destination): ?array {
    if (!is_string($destination)
      || $destination === ''
      || !str_starts_with($destination, '/')
      || str_starts_with($destination, '//')) {
      return NULL;
    }

    $parts = parse_url($destination);
    if ($parts === FALSE
      || isset($parts['scheme'])
      || isset($parts['host'])
      || isset($parts['user'])
      || isset($parts['pass'])
      || isset($parts['port'])
      || isset($parts['fragment'])) {
      return NULL;
    }

    $path = $parts['path'] ?? '';
    if (!$this->isSafeLoginDestinationPath($path)) {
      return NULL;
    }

    $query = [];
    if (isset($parts['query']) && $parts['query'] !== '') {
      parse_str($parts['query'], $query);
    }

    return ['path' => $path, 'query' => $query];
  }

  /** Checks an already-parsed internal path for post-login reuse. */
  private function isSafeLoginDestinationPath(string $path): bool {
    return str_starts_with($path, '/')
      && !str_starts_with($path, '//')
      && !preg_match('#^/(?:entrar|oauth|sair|recuperar(?:-senha|-acesso)?)(?:/|$)#', $path);
  }

  private function notFound(RequestEvent $event): void {
    $event->setResponse($this->notFoundResponse());
  }

  private function notFoundResponse(): Response {
    return new Response('', Response::HTTP_NOT_FOUND, [
      'Cache-Control' => 'private, no-store',
      'X-Robots-Tag' => 'noindex, nofollow',
    ]);
  }

}
