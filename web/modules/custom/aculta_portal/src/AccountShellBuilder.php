<?php

declare(strict_types=1);

namespace Drupal\aculta_portal;

use Drupal\Component\Utility\Html;
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
      'aculta_portal.dashboard' => ['title' => $this->translation->translate('Visão geral'), 'route' => 'aculta_portal.dashboard', 'icon' => 'bi-grid', 'description' => 'Resumo da sua conta e atalhos para as outras áreas.'],
      'aculta_portal.support_my' => ['title' => $this->translation->translate('Meu Apoio'), 'route' => 'aculta_portal.support_my', 'icon' => 'bi-heart', 'description' => 'Seus apoios e contribuições.'],
      'aculta_portal.account_courses' => ['title' => $this->translation->translate('Cursos'), 'route' => 'aculta_portal.account_courses', 'icon' => 'bi-mortarboard', 'description' => 'Os cursos em que você está matriculada.'],
      'aculta_portal.my_data' => ['title' => $this->translation->translate('Meus Dados'), 'route' => 'aculta_portal.my_data', 'icon' => 'bi-person-vcard', 'description' => 'Nome, telefone e endereços.'],
      'aculta_portal.connections' => ['title' => $this->translation->translate('Conexões'), 'route' => 'aculta_portal.connections', 'icon' => 'bi-link-45deg', 'description' => 'Contas conectadas, como o Google.'],
      'aculta_portal.security' => ['title' => $this->translation->translate('Segurança'), 'route' => 'aculta_portal.security', 'icon' => 'bi-shield-lock', 'description' => 'E-mail de acesso e senha.'],
      'aculta_portal.account_settings' => ['title' => $this->translation->translate('Configurações'), 'route' => 'aculta_portal.account_settings', 'icon' => 'bi-sliders', 'description' => 'Modo de cor da página.'],
    ];
    if (!isset($sections[$route])) {
      return;
    }

    $items = [];
    foreach ($sections as $section_route => $section) {
      $items[] = $this->menuItem(
        Url::fromRoute($section['route'])->toString(),
        (string) $section['title'],
        $section['icon'],
        $route === $section_route,
      );
    }

    // Reuse Drupal's dynamic logout link to retain its session-bound CSRF URL.
    $logout = $this->menuLinkManager->createInstance('user.logout');
    $items[] = $this->menuItem(
      $logout->getUrlObject()->toString(),
      (string) $this->translation->translate('Sair'),
      'bi-box-arrow-right',
      FALSE,
      ['portal-account__logout'],
    );

    $accordion = '';
    foreach ($sections as $section_route => $section) {
      $accordion .= $this->accordionItem(
        Url::fromRoute($section['route'])->toString(),
        (string) $section['title'],
        (string) $section['description'],
        $section['icon'],
        $route === $section_route,
      );
    }
    $logoutUrl = $this->menuLinkManager->createInstance('user.logout')->getUrlObject()->toString();
    $accordion .= $this->logoutRow($logoutUrl);

    $variables['page']['content'] = [
      '#theme' => 'aculta_portal_shell',
      '#menu_accordion' => ['#markup' => '<div class="portal-account__accordion">' . $accordion . '</div>'],
      '#section_title' => $sections[$route]['title'],
      '#menu' => [
        '#theme' => 'item_list',
        '#items' => $items,
        '#attributes' => ['class' => ['portal-account__menu']],
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

  /**
   * Item do menu da conta. Mesmo link em todas as larguras: no celular ganha ícone, rótulo e seta
   * (ver CSS de mobile); no desktop os ícones e a seta ficam ocultos.
   *
   * @param string[] $extraClasses
   */
  private function menuItem(string $href, string $label, string $icon, bool $active, array $extraClasses = []): array {
    $classes = array_merge(['portal-account__link'], $extraClasses);
    $attributes = ' class="' . Html::escape(implode(' ', $classes) . ($active ? ' is-active' : '')) . '"'
      . ' href="' . Html::escape($href) . '" data-aculta-portal-link="true"'
      . ($active ? ' aria-current="page"' : '');
    $markup = '<a' . $attributes . '>'
      . '<i class="bi ' . Html::escape($icon) . ' portal-account__icon" aria-hidden="true"></i>'
      . '<span class="portal-account__label">' . Html::escape($label) . '</span>'
      . '<i class="bi bi-chevron-right portal-account__chevron" aria-hidden="true"></i>'
      . '</a>';
    return ['#markup' => $markup];
  }

  /** Item em sanfona (celular): cabeçalho com ícone, rótulo e seta; painel com descrição e link para a página. */
  private function accordionItem(string $href, string $label, string $description, string $icon, bool $active): string {
    $open = $active ? ' open' : '';
    return '<details class="portal-account__item"' . $open . '>'
      . '<summary class="portal-account__summary">'
      . '<i class="bi ' . Html::escape($icon) . ' portal-account__icon" aria-hidden="true"></i>'
      . '<span class="portal-account__label">' . Html::escape($label) . '</span>'
      . '<i class="bi bi-chevron-right portal-account__chevron" aria-hidden="true"></i>'
      . '</summary>'
      . '<div class="portal-account__panel">'
      . '<p>' . Html::escape($description) . '</p>'
      . '<a class="portal-account__go" href="' . Html::escape($href) . '"' . ($active ? ' aria-current="page"' : '') . '>'
      . 'Ir para ' . Html::escape($label) . '</a>'
      . '</div>'
      . '</details>';
  }

  /** Sair (celular): linha simples, sem painel. */
  private function logoutRow(string $href): string {
    return '<a class="portal-account__logout-row portal-account__summary" href="' . Html::escape($href) . '">'
      . '<i class="bi bi-box-arrow-right portal-account__icon" aria-hidden="true"></i>'
      . '<span class="portal-account__label">' . Html::escape((string) $this->translation->translate('Sair')) . '</span>'
      . '<i class="bi bi-chevron-right portal-account__chevron" aria-hidden="true"></i>'
      . '</a>';
  }

}
