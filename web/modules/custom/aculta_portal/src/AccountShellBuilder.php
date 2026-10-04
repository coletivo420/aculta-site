<?php

namespace Drupal\aculta_portal;

use Drupal\Core\Link;
use Drupal\Core\Menu\MenuLinkManagerInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Url;

/**
 * Builds the shared, personalized account shell.
 */
final class AccountShellBuilder {

  public function __construct(
    private readonly CurrentRouteMatch $routeMatch,
    private readonly MenuLinkManagerInterface $menuLinkManager,
    private readonly TranslationInterface $translation,
  ) {}

  /**
   * Wraps an existing route's render array in the shared account shell.
   */
  public function build(array &$variables): void {
    $route = $this->routeMatch->getRouteName();
    $sections = [
      'aculta_portal.dashboard' => ['title' => $this->translation->translate('Visão geral'), 'route' => 'aculta_portal.dashboard'],
      'aculta_portal.support_my' => ['title' => $this->translation->translate('Meu Apoio'), 'route' => 'aculta_portal.support_my'],
      'aculta_portal.my_data' => ['title' => $this->translation->translate('Meus Dados'), 'route' => 'aculta_portal.my_data'],
      'aculta_portal.connections' => ['title' => $this->translation->translate('Conexões'), 'route' => 'aculta_portal.connections'],
      'aculta_portal.security' => ['title' => $this->translation->translate('Segurança'), 'route' => 'aculta_portal.security'],
    ];
    if (!isset($sections[$route])) {
      return;
    }

    $items = [];
    foreach ($sections as $section_route => $section) {
      $link = Link::fromTextAndUrl($section['title'], Url::fromRoute($section['route']))->toRenderable();
      $link['#attributes'] = [
        'class' => ['aculta-account__link'],
        'data-aculta-portal-link' => 'true',
      ];
      if ($route === $section_route) {
        $link['#attributes']['class'][] = 'is-active';
        $link['#attributes']['aria-current'] = 'page';
      }
      $items[] = $link;
    }

    // Reuse Drupal's dynamic logout link to retain its session-bound CSRF URL.
    $logout = $this->menuLinkManager->createInstance('user.logout');
    $logout_link = Link::fromTextAndUrl(
      $this->translation->translate('Sair'),
      $logout->getUrlObject(),
    )->toRenderable();
    $logout_link['#attributes'] = ['class' => ['aculta-account__link', 'aculta-account__logout']];
    $items[] = $logout_link;

    $variables['page']['content'] = [
      '#theme' => 'aculta_portal_shell',
      '#section_title' => $sections[$route]['title'],
      '#menu' => [
        '#theme' => 'item_list',
        '#items' => $items,
        '#attributes' => ['class' => ['aculta-account__menu']],
      ],
      '#content' => $variables['page']['content'] ?? [],
      '#cache' => [
        'contexts' => ['user', 'user.permissions', 'route'],
        'max-age' => 0,
      ],
    ];
    // The shell owns the only H1. Keep account actions in its sidebar.
    $variables['aculta_page_title'] = [];
    $variables['aculta_account_menu'] = [];
  }

}
