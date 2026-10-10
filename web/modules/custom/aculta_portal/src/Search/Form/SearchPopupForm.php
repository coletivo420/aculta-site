<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\Search\Form;

use Drupal\aculta_portal\Domain\DomainPurposeManager;
use Drupal\aculta_portal\Search\SearchPageController;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Popup da busca (ícone da barra multidomínio). GET para a busca central no MAIN. As sugestões (AJAX de
 * mesma origem) só existem no MAIN; nos outros hosts o campo envia direto para a busca central.
 */
final class SearchPopupForm extends FormBase implements ContainerInjectionInterface {

  public function __construct(
    private readonly DomainPurposeManager $domainPurposeManager,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static($container->get('aculta_portal.domain_purpose'));
  }

  public function getFormId(): string {
    return 'aculta_search_popup_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {
    $onMain = $this->domainPurposeManager->getCurrentPurpose() === 'main';
    $form['#method'] = 'get';
    $form['#token'] = FALSE;
    $form['#action'] = $onMain
      ? Url::fromRoute('aculta_portal.search')->toString()
      : ($this->domainPurposeManager->routeUrl('main', 'aculta_portal.search')?->toString() ?? Url::fromRoute('aculta_portal.search')->toString());
    $form['#attributes'] = ['role' => 'search', 'class' => ['aculta-search-popup']];
    $form['#cache'] = ['contexts' => ['domain', 'url.path']];
    $form['q'] = [
      '#type' => 'search',
      '#title' => $this->t('Buscar no site'),
      '#title_display' => 'invisible',
      '#placeholder' => $this->t('Buscar no site'),
      '#maxlength' => SearchPageController::MAX_CHARS,
      '#required' => TRUE,
      '#attributes' => ['autocomplete' => 'off'],
    ];
    if ($onMain) {
      $form['q']['#autocomplete_route_name'] = 'aculta_portal.search_suggestions';
    }
    $form['actions'] = ['#type' => 'actions', 'submit' => ['#type' => 'submit', '#value' => $this->t('Buscar')]];
    $form['hint'] = ['#markup' => '<p class="small text-body-secondary">' . $this->t('A busca cobre o site inteiro.') . '</p>'];
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    // GET: o navegador envia direto para a busca central; nada a processar no servidor.
  }

}
