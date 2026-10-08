<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\Support\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Utility\Html;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Url;

/** Institutional support page, integrated with Commerce Donation Flow. */
final class SupportForm extends FormBase {

  public function __construct(
    private readonly ConfigFactoryInterface $configs,
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly BlockManagerInterface $blockManager,
    private readonly TranslationInterface $translation,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.block'),
      $container->get('string_translation'),
    );
  }

  public function getFormId(): string {
    return 'aculta_support_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['#cache']['max-age'] = 0;
    $form['#attributes']['class'][] = 'aculta-support';
    $form['#attached']['library'][] = 'aculta_portal/support';
    $form['intro'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => Html::escape((string) ($this->configs->get('aculta_portal.support')->get('intro') ?: $this->translation->translate('Sua contribuição ajuda a manter iniciativas culturais, comunicação, formação e ações de interesse coletivo.'))),
      '#attributes' => ['class' => ['aculta-support-intro']],
    ];
    $gateway = $this->entityTypeManager->getStorage('commerce_payment_gateway')->load('mercado_pago');
    $gateway_configuration = $gateway ? $gateway->getPluginConfiguration() : [];
    $gateway_ready = $gateway && $gateway->status()
      && !empty($gateway_configuration['public_key_test'])
      && !empty($gateway_configuration['access_token_test']);
    if ($gateway_ready) {
      // Donation Flow owns the route and order creation; the portal only
      // presents its native link when the reviewed gateway is enabled.
      $donation_link = $this->blockManager->createInstance(
        'commerce_donation_flow_link',
        ['return_path' => FALSE]
      )->build();
      if (isset($donation_link['#links']['default']['title'])) {
        $donation_link['#links']['default']['title'] = $this->translation->translate('Continuar para pagamento');
      }
      $form['donation_flow'] = $donation_link;
    }
    else {
      $form['status'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['messages', 'messages--warning']],
        'message' => [
          '#plain_text' => $this->translation->translate('O apoio financeiro está temporariamente indisponível. Nenhuma cobrança será iniciada por esta página.'),
        ],
      ];
    }
    $form['other_support'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['aculta-support-other']],
      'title' => ['#type' => 'html_tag', '#tag' => 'h2', '#value' => $this->translation->translate('Outras formas de apoio')],
      'text' => ['#type' => 'html_tag', '#tag' => 'p', '#value' => $this->translation->translate('Parcerias institucionais, apoio cultural e patrocínio de projetos também fortalecem nossas iniciativas.')],
      'contact' => ['#type' => 'link', '#title' => $this->translation->translate('Fale com a Associação'), '#url' => Url::fromUri('internal:/contato'), '#attributes' => ['class' => ['aculta-button', 'aculta-button--outline']]],
    ];
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    // There is intentionally no payment submission until the Commerce flow is
    // implemented and a restricted Commerce gateway is configured.
  }

}
