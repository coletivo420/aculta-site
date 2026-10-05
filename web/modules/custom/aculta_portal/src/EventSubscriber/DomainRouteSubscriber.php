<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\EventSubscriber;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\Routing\RouteCollection;

/** Applies canonical paths and purpose metadata to known route families. */
final class DomainRouteSubscriber extends RouteSubscriberBase {

  public function __construct(private readonly ConfigFactoryInterface $configFactory) {}

  /** {@inheritdoc} */
  public static function getSubscribedEvents(): array {
    // Rename Admin Paths runs at -2048; translate its resulting paths after it.
    return [RoutingEvents::ALTER => [['onAlterRoutes', -2049]]];
  }

  /** {@inheritdoc} */
  protected function alterRoutes(RouteCollection $collection): void {
    $pathChanges = [
      'user.login' => '/entrar',
      'user.login.http' => '/entrar',
      'user.register' => '/criar-conta',
      'user.pass' => '/recuperar-senha',
      'user.pass.http' => '/recuperar-senha',
      'user.reset' => '/recuperar-acesso/{uid}/{timestamp}/{hash}',
      'user.reset.form' => '/recuperar-acesso/{uid}',
      'user.reset.login' => '/recuperar-acesso/{uid}/{timestamp}/{hash}/entrar',
      'user.logout' => '/sair',
      'user.logout.confirm' => '/sair/confirmar',
      'user.logout.http' => '/sair',
      'user.page' => '/identidade',
      'user.edit' => '/identidade/editar',
      'entity.user.canonical' => '/identidade/{user}',
      'entity.user.edit_form' => '/painel-administrativo/pessoas/{user}/editar',
      'entity.email_confirmer_confirmation.response_form' => '/confirmar-email/{email_confirmer_confirmation}/{hash}',
      'entity.email_confirmer_confirmation.resend' => '/confirmar-email/reenviar/{confirmation}',
      'entity.user.cancel_email_change' => '/seguranca/email/cancelar/{user}',
      'change_mail_page.change_mail' => '/seguranca/email',
      'change_mail_page.change_mail_form' => '/seguranca/email/{user}',
      'social_auth.network.redirect' => '/acesso/{network}',
      'social_auth.network.callback' => '/acesso/{network}/retorno',
      'commerce_payment.notify' => '/integracoes/pagamentos/{commerce_payment_gateway}/notificacao',
    ];
    $accountRoutes = array_fill_keys(array_keys($pathChanges), TRUE);
    unset($accountRoutes['commerce_payment.notify']);

    foreach ($pathChanges as $name => $path) {
      if ($route = $collection->get($name)) {
        $route->setPath($path);
        if (isset($accountRoutes[$name])) {
          $route->setOption('_aculta_domain_purpose', 'account');
        }
        elseif ($name === 'commerce_payment.notify') {
          $route->setOption('_aculta_domain_purpose', 'main');
        }
        elseif ($name === 'entity.user.edit_form') {
          // Core's one-time password reset uses this same route. The request
          // policy permits it on ACCOUNT only with Core's session token.
          $route->setOption('_aculta_domain_purpose', 'main');
        }
      }
    }

    // Keep the canonical registration URL available while public sign-up is
    // closed. When Core registration is enabled later, its normal entity form
    // remains at the same route and path.
    if ($route = $collection->get('user.register')) {
      if ($this->configFactory->get('user.settings')->get('register') !== 'visitors') {
        $route->setDefault('_title', 'Cadastro temporariamente indisponível');
        $route->setDefault('_controller', '\\Drupal\\aculta_portal\\Controller\\RegistrationController::closed');
        $defaults = $route->getDefaults();
        unset($defaults['_entity_form']);
        $route->setDefaults($defaults);
        $requirements = $route->getRequirements();
        unset($requirements['_access_user_register']);
        $requirements['_access'] = 'TRUE';
        $route->setRequirements($requirements);
      }
    }

    foreach (['aculta_portal.dashboard', 'aculta_portal.my_data', 'aculta_portal.my_data_address', 'aculta_portal.connections', 'aculta_portal.security', 'aculta_portal.support_my'] as $name) {
      if ($route = $collection->get($name)) {
        $route->setOption('_aculta_domain_purpose', 'account');
      }
    }
    if ($route = $collection->get('aculta_portal.support_form')) {
      $route->setOption('_aculta_domain_purpose', 'support');
    }
    foreach (['aculta_portal.wiki_home', 'aculta_portal.wiki_search', 'view.wiki_entries.page_1'] as $name) {
      if ($route = $collection->get($name)) {
        $route->setOption('_aculta_domain_purpose', 'wiki');
      }
    }

    foreach ($collection as $route) {
      if ($route->getOption('_admin_route')) {
        $route->setOption('_aculta_domain_purpose', 'main');
      }
    }

    // Rename Admin Paths runs at -2048. Translate only first-level groups after
    // its prefix rewrite, leaving stable internal route suffixes untouched.
    $adminSegments = [
      'config' => 'configuracoes',
      'people' => 'pessoas',
      'content' => 'conteudo',
      'structure' => 'estrutura',
      'commerce' => 'comercio',
      'reports' => 'relatorios',
      'appearance' => 'aparencia',
      'modules' => 'modulos',
    ];
    foreach ($collection as $route) {
      $path = $route->getPath();
      if ($path === '/painel-administrativo' || str_starts_with($path, '/painel-administrativo/')) {
        $route->setOption('_aculta_domain_purpose', 'main');
      }
      foreach ($adminSegments as $source => $target) {
        if (preg_match('~^/painel-administrativo/' . preg_quote($source, '~') . '(?:/|$)~', $path)) {
          $route->setPath(preg_replace('~^/painel-administrativo/' . preg_quote($source, '~') . '(?=/|$)~', '/painel-administrativo/' . $target, $path, 1));
          break;
        }
      }
    }
  }

}
