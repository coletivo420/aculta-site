<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\Hook;

use Drupal\aculta_portal\Domain\DomainPurposeManager;
use Drupal\aculta_portal\EventSubscriber\AccountRouteSubscriber;
use Drupal\commerce_payment\Form\PaymentGatewayForm;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\user\AccountForm;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Account-security and payment hardening hooks.
 */
final class SecurityCommerceHooks {

  public function __construct(
    #[Autowire(service: 'aculta_portal.domain_purpose')]
    private readonly DomainPurposeManager $domainPurposeManager,
    #[Autowire(service: 'current_route_match')]
    private readonly CurrentRouteMatch $currentRouteMatch,
    #[Autowire(service: 'request_stack')]
    private readonly RequestStack $requestStack,
    #[Autowire(service: 'current_user')]
    private readonly AccountProxyInterface $currentUser,
    #[Autowire(service: 'string_translation')]
    private readonly TranslationInterface $translation,
  ) {}

  /** Applies Portal/Domain and payment-gateway access defense in depth. */
  #[Hook('entity_access')]
  public function entityAccess(
    EntityInterface $entity,
    string $operation,
    AccountInterface $account,
  ): AccessResult {
    $purpose = $this->domainPurposeManager->getCurrentPurpose();
    $wikiEntity = ($entity->getEntityTypeId() === 'node' && $entity->bundle() === 'wiki_entry')
      || ($entity->getEntityTypeId() === 'taxonomy_term' && $entity->bundle() === 'wiki_category');

    if ($wikiEntity && $purpose !== 'wiki') {
      return AccessResult::forbidden()
        ->addCacheContexts(['domain'])
        ->addCacheableDependency($entity);
    }

    if ($entity->getEntityTypeId() === 'user'
      && $operation === 'update'
      && $this->currentRouteMatch->getRouteName() === 'entity.user.edit_form'
      && !$account->hasPermission('administer users')) {
      $request = $this->requestStack->getCurrentRequest();
      if ($request !== NULL && AccountRouteSubscriber::isValidCorePasswordResetRequest(
        $request,
        (int) $entity->id(),
        (int) $account->id(),
      )) {
        return AccessResult::neutral();
      }

      return AccessResult::forbidden()
        ->cachePerPermissions()
        ->addCacheContexts(['route', 'user'])
        ->setCacheMaxAge(0);
    }

    if ($entity->getEntityTypeId() !== 'commerce_payment_gateway'
      || $entity->id() !== 'mercado_pago'
      || $operation !== 'update') {
      return AccessResult::neutral();
    }

    return AccessResult::forbidden(
      'This gateway is managed through reviewed configuration and environment values; editing it here could persist credentials.',
    )
      ->cachePerPermissions()
      ->setCacheMaxAge(0);
  }

  /** Keeps the Mercado Pago gateway fail-closed when secrets are absent. */
  #[Hook('entity_presave')]
  public function entityPresave(EntityInterface $entity): void {
    if ($entity->getEntityTypeId() !== 'commerce_payment_gateway'
      || $entity->id() !== 'mercado_pago'
      || !$entity->status()) {
      return;
    }

    if (!getenv('MERCADOPAGO_PUBLIC_KEY') || !getenv('MERCADOPAGO_ACCESS_TOKEN')) {
      throw new \LogicException(
        'The Mercado Pago gateway cannot be enabled without both runtime test credentials.',
      );
    }
  }

  /** Redacts payment secrets and adapts Portal account/donation forms. */
  #[Hook('form_alter')]
  public function formAlter(
    array &$form,
    FormStateInterface $formState,
    string $formId,
  ): void {
    $formObject = $formState->getFormObject();
    $routeName = $this->currentRouteMatch->getRouteName();

    if ($formId === 'change_mail_form' && $routeName === 'aculta_portal.security') {
      $form['account']['mail']['#title'] = $this->translation->translate('Novo e-mail');
      $form['account']['mail']['#description'] = $this->translation->translate(
        'Você precisará confirmar este endereço antes que ele passe a ser usado na sua conta.',
      );
      $form['account']['mail']['#attributes']['autocomplete'] = 'email';
      $form['account']['current_pass']['#title'] = $this->translation->translate('Senha atual');
      $form['account']['current_pass']['#attributes']['autocomplete'] = 'current-password';
      $form['actions']['submit']['#value'] = $this->translation->translate(
        'Solicitar alteração de e-mail',
      );
      $form['#submit'][] = 'aculta_portal.form_callbacks:changeMailConfirmationMessage';
    }

    if ($formId === 'user_form'
      && $routeName === 'aculta_portal.security'
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
      $form['#validate'][] = 'aculta_portal.form_callbacks:validateDonationAmount';
      $form['commerce_donation_pane']['#title'] = $this->translation->translate('Seu apoio');
      $amountWidget = &$form['commerce_donation_pane']['field_donation_amount']['widget'][0]['donation_level'];
      if (isset($amountWidget)) {
        $amountWidget['#title'] = $this->translation->translate('Escolha um valor');
        if (isset($amountWidget['value']['#options']['custom_amount'])) {
          $amountWidget['value']['#options']['custom_amount'] = $this->translation->translate(
            'Outro valor',
          );
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
  }

}
