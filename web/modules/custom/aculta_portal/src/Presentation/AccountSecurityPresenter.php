<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\Presentation;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\email_confirmer\EmailConfirmerManagerInterface;
use Drupal\user\UserDataInterface;
use Drupal\user\UserInterface;

/**
 * Prepares ACCOUNT security state without rebuilding auth or mail workflows.
 *
 * Change Mail, Email Confirmer, SMTP and Drupal User remain authoritative. This
 * presenter only exposes readiness and already-authorized account state.
 */
final class AccountSecurityPresenter {

  public function __construct(
    private readonly ConfigFactoryInterface $configFactory,
    private readonly ModuleHandlerInterface $moduleHandler,
    private readonly UserDataInterface $userData,
    private readonly EmailConfirmerManagerInterface $emailConfirmer,
    private readonly TranslationInterface $translation,
  ) {}

  /**
   * Builds the view-model for the account security page.
   *
   * @return array{
   *   intro: string,
   *   email: array{
   *     heading: string,
   *     current_label: string,
   *     current: string,
   *     change_available: bool,
   *     unavailable_message: string|null,
   *     pending: array{label: string, email: string, message: string, tone: string}|null
   *   },
   *   password: array{
   *     heading: string,
   *     description: string,
   *     change_available: bool,
   *     unavailable_message: string|null
   *   }
   * }
   */
  public function present(UserInterface $account): array {
    $changeAvailable = $this->transactionalMailReady()
      && $this->moduleHandler->moduleExists('email_confirmer_user')
      && $this->moduleHandler->moduleExists('change_mail_page');

    $pendingEmail = $changeAvailable ? $this->pendingEmail($account) : NULL;
    $socialPasswordUnset = (bool) $this->userData->get(
      'aculta_portal',
      $account->id(),
      'social_auth_password_unset',
    );
    $passwordAvailable = (bool) $account->getPassword() && !$socialPasswordUnset;

    return [
      'intro' => (string) $this->translation->translate('Gerencie como você acessa sua conta.'),
      'email' => [
        'heading' => (string) $this->translation->translate('E-mail de acesso'),
        'current_label' => (string) $this->translation->translate('E-mail atual'),
        'current' => (string) $account->getEmail(),
        'change_available' => $changeAvailable,
        'unavailable_message' => $changeAvailable ? NULL : (string) $this->translation->translate(
          'A alteração de e-mail estará disponível após a ativação do serviço de mensagens da conta.'
        ),
        'pending' => $pendingEmail !== NULL ? [
          'label' => (string) $this->translation->translate('Alteração pendente'),
          'email' => $pendingEmail,
          'message' => (string) $this->translation->translate('Aguardando confirmação no novo endereço.'),
          'tone' => 'warning',
        ] : NULL,
      ],
      'password' => [
        'heading' => (string) $this->translation->translate('Senha'),
        'description' => (string) $this->translation->translate('Mantenha uma senha segura para acessar sua conta.'),
        'change_available' => $passwordAvailable,
        'unavailable_message' => $passwordAvailable ? NULL : (string) $this->translation->translate(
          'Sua conta utiliza acesso externo. Quando o fluxo de definição de senha local estiver homologado, ele poderá ser oferecido aqui.'
        ),
      ],
    ];
  }

  private function transactionalMailReady(): bool {
    $smtp = $this->configFactory->get('smtp.settings');
    $mailSystem = $this->configFactory->get('system.mail')->get('interface.default');
    $siteMail = trim((string) $this->configFactory->get('system.site')->get('mail'));

    return $this->moduleHandler->moduleExists('smtp')
      && $mailSystem === 'SMTPMailSystem'
      && (bool) $smtp->get('smtp_on')
      && trim((string) $smtp->get('smtp_host')) !== ''
      && trim((string) $smtp->get('smtp_username')) !== ''
      && trim((string) $smtp->get('smtp_password')) !== ''
      && $siteMail !== '';
  }

  private function pendingEmail(UserInterface $account): ?string {
    $pending = $this->userData->get(
      'email_confirmer_user',
      $account->id(),
      'email_change_new_address',
    );
    if (!is_string($pending) || $pending === '') {
      return NULL;
    }

    $confirmations = $this->emailConfirmer->getConfirmations(
      $pending,
      'pending',
      0,
      'email_confirmer_user',
    );
    foreach ($confirmations as $confirmation) {
      if ((int) $confirmation->get('uid')->target_id === (int) $account->id()) {
        return $pending;
      }
    }

    return NULL;
  }

}
