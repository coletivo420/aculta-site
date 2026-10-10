<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\Search\Form;

use Drupal\aculta_portal\Domain\DomainPurposeManager;
use Drupal\aculta_portal\Search\SearchPageController;
use Drupal\aculta_portal\Search\SearchResults;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Popup da busca (ícone da barra multidomínio). A prévia é atualizada por AJAX enquanto a pessoa digita,
 * no próprio host (o índice é do site inteiro). O envio vai para a busca central no MAIN.
 */
final class SearchPopupForm extends FormBase implements ContainerInjectionInterface {

  private const PREVIEW_LIMIT = 8;

  public function __construct(
    private readonly DomainPurposeManager $domainPurposeManager,
    private readonly SearchResults $searchResults,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('aculta_portal.domain_purpose'),
      $container->get('aculta_portal.search_results'),
    );
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
      '#ajax' => [
        'callback' => '::previewCallback',
        'event' => 'keyup',
        'wrapper' => 'aculta-search-preview',
        'progress' => ['type' => 'throbber', 'message' => ''],
        'disable-refocus' => TRUE,
      ],
    ];
    $form['preview'] = $this->previewContainer([
      'hint' => ['#markup' => '<p class="small text-body-secondary">' . $this->t('Digite pelo menos @n caracteres para ver resultados.', ['@n' => SearchPageController::MIN_CHARS]) . '</p>'],
    ]);
    $form['actions'] = ['#type' => 'actions', 'submit' => ['#type' => 'submit', '#value' => $this->t('Buscar')]];
    return $form;
  }

  /** Prévia por AJAX: atualiza a lista de resultados conforme o termo digitado. */
  public function previewCallback(array $form, FormStateInterface $form_state): array {
    $term = mb_substr(trim((string) $form_state->getValue('q')), 0, SearchPageController::MAX_CHARS);
    if (mb_strlen($term) < SearchPageController::MIN_CHARS) {
      return $this->previewContainer([
        'hint' => ['#markup' => '<p class="small text-body-secondary">' . $this->t('Digite pelo menos @n caracteres para ver resultados.', ['@n' => SearchPageController::MIN_CHARS]) . '</p>'],
      ]);
    }
    $preview = $this->searchResults->search($term, self::PREVIEW_LIMIT);
    if ($preview['total'] === 0) {
      return $this->previewContainer([
        'empty' => ['#markup' => '<p>' . $this->t('Nenhum resultado para «@term».', ['@term' => $term]) . '</p>'],
      ]);
    }
    $items = [];
    foreach ($preview['items'] as $result) {
      $items[] = [
        '#type' => 'link',
        '#title' => $this->t('@title (@type)', ['@title' => $result['title'], '@type' => $result['type']]),
        '#url' => $result['url'],
      ];
    }
    // Como na Wikipedia: a última linha leva à pesquisa completa pelo mesmo termo, com o total.
    $full = $this->domainPurposeManager->routeUrl('main', 'aculta_portal.search') ?? Url::fromRoute('aculta_portal.search');
    $full->setOption('query', ['q' => $term]);
    return $this->previewContainer([
      'list' => ['#theme' => 'item_list', '#items' => $items, '#attributes' => ['class' => ['aculta-search-preview__list']]],
      'full' => [
        '#type' => 'link',
        '#title' => $this->t('Pesquisa completa por «@term» (@total resultados)', ['@term' => $term, '@total' => $preview['total']]),
        '#url' => $full,
        '#attributes' => ['class' => ['aculta-search-preview__full']],
      ],
    ]);
  }

  /** @param array<string, mixed> $content */
  private function previewContainer(array $content): array {
    return [
      '#type' => 'container',
      '#attributes' => ['id' => 'aculta-search-preview', 'class' => ['aculta-search-preview'], 'aria-live' => 'polite'],
      '#cache' => ['max-age' => 0],
    ] + $content;
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    // GET: o navegador envia direto para a busca central; nada a processar no servidor.
  }

}
