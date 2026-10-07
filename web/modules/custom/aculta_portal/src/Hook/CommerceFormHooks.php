<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\Hook;

use Drupal\commerce_payment\Form\PaymentGatewayForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/** Commerce donation/gateway form alters. */
final class CommerceFormHooks {

  public function __construct(
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

    if (isset($form['commerce_donation_pane']['field_donation_amount'])) {
      $form['#validate'][] = 'aculta_portal_validate_donation_amount';
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
      unset($amountWidget);
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
    unset($configurationForm);
  }

}
