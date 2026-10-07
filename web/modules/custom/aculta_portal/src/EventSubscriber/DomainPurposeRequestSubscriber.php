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
        && str_starts_with($destination['path'], '/')
        && !str_starts_with($destination['path'], '//')) {
        $url = $this->domainPurposeManager->pathUrl($destination['purpose'], $destination['path']);
        if ($url !== NULL) {
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
      $destinationPath = $request->query->get('destination');
      $destinationPurpose = $request->query->get('aculta_destination_purpose');
      if (is_string($destinationPath)
        && str_starts_with($destinationPath, '/')
        && !str_starts_with($destinationPath, '//')
        && !preg_match('#^/(?:entrar|oauth|sair|recuperar(?:-senha|-acesso)?)(?:/|$)#', $destinationPath)) {
        $destinationPurpose = is_string($destinationPurpose) && $this->domainPurposeManager->getDomain($destinationPurpose)
          ? $destinationPurpose
          : $this->domainPurposeManager->getCurrentPurpose();
        if ($destinationPurpose !== NULL) {
          $request->getSession()->set(self::LOGIN_DESTINATION_SESSION_KEY, [
            'purpose' => $destinationPurpose,
            'path' => $destinationPath,
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
