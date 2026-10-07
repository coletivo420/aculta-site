<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\Presentation;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Url;
use Drupal\user\UserInterface;

/**
 * Prepares the ACCOUNT connections view-model.
 *
 * Social Auth remains the source of truth for linked provider accounts. This
 * presenter only translates provider state into user-facing status/actions.
 */
final class AccountConnectionsPresenter {

  public function __construct(
    private readonly EntityTypeManagerInterface $entities,
    private readonly ConfigFactoryInterface $configFactory,
    private readonly ModuleHandlerInterface $moduleHandler,
    private readonly TranslationInterface $translation,
  ) {}

  /**
   * Builds the Google/Social Auth view-model for the current account.
   *
   * @return array{
   *   description: string,
   *   provider: string,
   *   status: array{code: string, label: string, tone: string},
   *   action: array{label: string, url: \Drupal\Core\Url, kind: string}|null,
   *   notice: string|null,
   *   login_available: bool
   * }
   */
  public function present(UserInterface $account): array {
    $googleConfig = $this->configFactory->get('social_auth_google.settings');
    $googleReady = !empty($googleConfig->get('client_id'))
      && !empty($googleConfig->get('client_secret'));
    $socialAuthAvailable = $this->moduleHandler->moduleExists('social_auth')
      && $this->entities->hasDefinition('social_auth');

    $links = [];
    if ($socialAuthAvailable) {
      $links = $this->entities->getStorage('social_auth')->loadByProperties([
        'user_id' => $account->id(),
        'plugin_id' => 'google',
      ]);
    }

    $view = [
      'description' => (string) $this->translation->translate('Gerencie as contas externas conectadas à sua conta.'),
      'provider' => 'Google',
      'status' => [
        'code' => 'unavailable',
        'label' => (string) $this->translation->translate('A conexão com o Google estará disponível quando a configuração institucional estiver concluída.'),
        'tone' => 'neutral',
      ],
      'action' => NULL,
      'notice' => NULL,
      'login_available' => FALSE,
    ];

    if ($links !== []) {
      $socialAuth = reset($links);
      $view['status'] = [
        'code' => 'connected',
        'label' => (string) $this->translation->translate('Conta conectada.'),
        'tone' => 'success',
      ];

      if ($account->getPassword()) {
        if ($socialAuth && $socialAuth->access('delete', $account)) {
          $view['action'] = [
            'label' => (string) $this->translation->translate('Desconectar Google'),
            'url' => Url::fromRoute('entity.social_auth.delete_form', ['social_auth' => $socialAuth->id()]),
            'kind' => 'secondary',
          ];
        }
      }
      else {
        $view['notice'] = (string) $this->translation->translate('Defina uma senha para sua conta antes de desconectar o Google.');
        $view['action'] = [
          'label' => (string) $this->translation->translate('Gerenciar segurança da conta'),
          'url' => Url::fromRoute('aculta_portal.security'),
          'kind' => 'link',
        ];
      }

      return $view;
    }

    if ($googleReady && $socialAuthAvailable) {
      $view['status'] = [
        'code' => 'disconnected',
        'label' => (string) $this->translation->translate('Nenhuma conta Google está conectada.'),
        'tone' => 'neutral',
      ];
      $view['login_available'] = TRUE;
    }

    return $view;
  }

}
