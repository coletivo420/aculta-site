<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\Hook;

use Drupal\commerce_payment\Form\PaymentGatewayForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\user\AccountForm;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Account and Commerce form alterations owned by the Portal.
 */
final class FormHooks {

  public function __construct(
    #[Autowire(service: 'current_route_match')]
    private readonly CurrentRouteMatch $routeMatch,
    #[Autowire(service: 'current_user')]
    private readonly AccountProxyInterface $currentUser,
    #[Autowire(service: 'string_translation')]
    private readonly TranslationInterface $translation,
  ) {}

  /**
   * Alters Portal-owned account and Commerce form surfaces.
   */
  #[Hook('form_alter')]
  public function formAlter(array &$form, FormStateInterface $formState, string $formId): void {
    $formObject = $formState->getFormObject();

    if ($formId === 'user_login_form') {
      // Core reports blocked or unactivated accounts before the password check,
      // which discloses that an address is registered. The Portal callback keeps
      // them indistinguishable from unknown accounts.
      $form['#validate'] = array_map(
        static fn($validator) => $validator === '::validateAuthentication'
          ? 'aculta_portal.form_callbacks:validateLoginAuthentication'
          : $validator,
        $form['#validate'] ?? [],
      );
    }

    if ($formId === 'change_mail_form'
      && $this->routeMatch->getRouteName() === 'aculta_portal.security') {
      $form['account']['mail']['#title'] = $this->translation->translate('Novo e-mail');
      $form['account']['mail']['#description'] = $this->translation->translate(
        'Você precisará confirmar este endereço antes que ele passe a ser usado na sua conta.',
      );
      $form['account']['mail']['#attributes']['autocomplete'] = 'email';
      $form['account']['current_pass']['#title'] = $this->translation->translate('Senha atual');
      $form['account']['current_pass']['#attributes']['autocomplete'] = 'current-password';
      $form['actions']['submit']['#value'] = $this->translation->translate('Solicitar alteração de e-mail');
      $form['#submit'][] = 'aculta_portal.form_callbacks:changeMailConfirmationMessage';
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
        $form['#after_build'][] = 'aculta_portal.form_callbacks:securityPasswordAfterBuild';
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
        $form['actions']['submit']['#submit'][] = 'aculta_portal.form_callbacks:securityPasswordRedirect';
      }
    }

    if (isset($form['commerce_donation_pane']['field_donation_amount'])) {
      // The contrib widget converts an empty custom value to zero while
      // massaging values. Require a real positive custom value server-side.
      $form['#validate'][] = 'aculta_portal.form_callbacks:validateDonationAmount';
      $form['commerce_donation_pane']['#title'] = $this->translation->translate('Seu apoio');
      $amountWidget = &$form['commerce_donation_pane']['field_donation_amount']['widget'][0]['donation_level'];
      if (isset($amountWidget)) {
        $amountWidget['#title'] = $this->translation->translate('Escolha um valor');
        if (isset($amountWidget['value']['#options']['custom_amount'])) {
          $amountWidget['value']['#options']['custom_amount'] = $this->translation->translate('Outro valor');
        }
        if (isset($amountWidget['amount'])) {
          $amountWidget['amount']['#title'] = $this->translation->translate('Outro valor');
          $amountWidget['amount']['#placeholder'] = $this->translation->translate('Outro valor');
        }
      }
    }

    if (!$formObject instanceof PaymentGatewayForm
      || $formObject->getEntity()->id() !== 'mercado_pago'
      || !isset($form['configuration']['form'])) {
      return;
    }

    $configurationForm = &$form['configuration']['form'];
    foreach ([
      ['credentials_test', 'access_token_test'],
      ['credentials_stage', 'access_token_stage'],
      ['credentials_prod', 'access_token_prod'],
    ] as [$fieldset, $key]) {
      if (isset($configurationForm[$fieldset][$key])) {
        $configurationForm[$fieldset][$key]['#type'] = 'password';
        $configurationForm[$fieldset][$key]['#default_value'] = '';
        unset($configurationForm[$fieldset][$key]['#value']);
        $configurationForm[$fieldset][$key]['#attributes']['autocomplete'] = 'new-password';
      }
    }

    if (isset($configurationForm['client_secret'])) {
      $configurationForm['client_secret']['#type'] = 'password';
      $configurationForm['client_secret']['#default_value'] = '';
      unset($configurationForm['client_secret']['#value']);
      $configurationForm['client_secret']['#attributes']['autocomplete'] = 'new-password';
    }
  }

}
