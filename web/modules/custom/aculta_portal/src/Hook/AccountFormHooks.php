<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\user\AccountForm;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/** Account/security form alters. */
final class AccountFormHooks {

  public function __construct(
    #[Autowire(service: 'current_route_match')]
    private readonly CurrentRouteMatch $routeMatch,
    #[Autowire(service: 'current_user')]
    private readonly AccountProxyInterface $currentUser,
    #[Autowire(service: 'string_translation')]
    private readonly TranslationInterface $translation,
  ) {}

  #[Hook('form_alter')]
  public function formAlter(
    array &$form,
    FormStateInterface $formState,
    string $formId,
  ): void {
    $formObject = $formState->getFormObject();

    if ($formId === 'change_mail_form'
      && $this->routeMatch->getRouteName() === 'aculta_portal.security') {
      $form['account']['mail']['#title'] = $this->translation->translate('Novo e-mail');
      $form['account']['mail']['#description'] = $this->translation->translate(
        'Você precisará confirmar este endereço antes que ele passe a ser usado na sua conta.'
      );
      $form['account']['mail']['#attributes']['autocomplete'] = 'email';
      $form['account']['current_pass']['#title'] = $this->translation->translate('Senha atual');
      $form['account']['current_pass']['#attributes']['autocomplete'] = 'current-password';
      $form['actions']['submit']['#value'] = $this->translation->translate(
        'Solicitar alteração de e-mail'
      );
      $form['#submit'][] = 'aculta_portal_change_mail_confirmation_message';
    }

    if ($formId === 'user_form'
      && $this->routeMatch->getRouteName() === 'aculta_portal.security'
      && $formObject instanceof AccountForm) {
      $account = $formObject->getEntity();
      if ((int) $account->id() === (int) $this->currentUser->id()) {
        $formState->set('user_pass_reset', FALSE);
        $form['account']['current_pass']['#access'] = TRUE;
        $form['account']['current_pass']['#required'] = TRUE;
        $form['account']['current_pass']['#title'] = $this->translation->translate('Senha atual');
        $form['account']['current_pass']['#attributes']['autocomplete'] = 'current-password';
        $form['account']['pass']['#title'] = $this->translation->translate('Nova senha');
        $form['account']['pass']['#attributes']['autocomplete'] = 'new-password';
        $form['#after_build'][] = 'aculta_portal_security_password_after_build';
        $form['#attributes']['class'][] = 'aculta-security-password-form';
        $form['actions']['submit']['#value'] = $this->translation->translate('Salvar nova senha');

        foreach (array_keys($form) as $key) {
          if (!str_starts_with((string) $key, '#')
            && !in_array($key, ['account', 'actions', 'form_build_id', 'form_token', 'form_id'], TRUE)) {
            unset($form[$key]);
          }
        }
        foreach (array_keys($form['account']) as $key) {
          if (!str_starts_with((string) $key, '#')
            && !in_array($key, ['current_pass', 'pass'], TRUE)) {
            unset($form['account'][$key]);
          }
        }
        $form['actions']['submit']['#submit'][] = 'aculta_portal_security_password_redirect';
      }
    }
  }

}
