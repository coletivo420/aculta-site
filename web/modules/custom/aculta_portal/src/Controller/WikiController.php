<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\views\Views;
use Symfony\Component\HttpFoundation\Request;

/** Public landing page and bounded title/summary/body search for the Wiki. */
final class WikiController extends ControllerBase {

  /** Builds the Wiki home from the editorial Views. */
  public function home(): array {
    $build = [
      '#type' => 'container',
      '#attributes' => ['class' => ['aculta-wiki-home']],
      '#cache' => [
        'contexts' => ['domain', 'user.permissions'],
        'tags' => ['node_list:wiki_entry', 'taxonomy_term_list'],
      ],
      'intro' => [
        '#type' => 'container',
        '#attributes' => ['class' => ['aculta-wiki-intro']],
        'title' => ['#type' => 'html_tag', '#tag' => 'h1', '#value' => $this->t('Wiki420')],
        'text' => ['#plain_text' => $this->t('Uma enciclopédia colaborativa em construção. Consulte os verbetes publicados ou pesquise por um tema.')],
      ],
      'search' => [
        '#type' => 'form',
        '#method' => 'get',
        '#action' => Url::fromRoute('aculta_portal.wiki_search')->toString(),
        '#attributes' => ['role' => 'search', 'class' => ['aculta-wiki-search']],
        'q' => [
          '#type' => 'textfield',
          '#title' => $this->t('Buscar verbetes'),
          '#size' => 40,
          '#maxlength' => 100,
          '#required' => TRUE,
          '#attributes' => ['autocomplete' => 'off'],
        ],
        'submit' => ['#type' => 'submit', '#value' => $this->t('Buscar')],
      ],
      'browse' => [
        '#type' => 'link',
        '#title' => $this->t('Ver todos os verbetes'),
        '#url' => Url::fromUserInput('/wiki/verbetes'),
      ],
      'categories_title' => ['#type' => 'html_tag', '#tag' => 'h2', '#value' => $this->t('Categorias')],
      'categories' => $this->viewBlock('wiki_categories', 'block_1'),
      'recent_title' => ['#type' => 'html_tag', '#tag' => 'h2', '#value' => $this->t('Verbetes recentes')],
      'recent' => $this->viewBlock('wiki_entries', 'block_recent'),
      'changes_title' => ['#type' => 'html_tag', '#tag' => 'h2', '#value' => $this->t('Alterações recentes')],
      'changes' => $this->viewBlock('wiki_entries', 'block_changes'),
    ];
    return $build;
  }

  /** Searches only published Wiki entries by title, summary, and body. */
  public function search(Request $request): array {
    $term = trim((string) $request->query->get('q', ''));
    $build = [
      '#type' => 'container',
      '#attributes' => ['class' => ['aculta-wiki-search-results']],
      '#cache' => ['contexts' => ['url.query_args:q', 'domain', 'user.permissions'], 'tags' => ['node_list:wiki_entry']],
      'title' => ['#type' => 'html_tag', '#tag' => 'h1', '#value' => $this->t('Buscar na Wiki420')],
      'form' => [
        '#type' => 'form',
        '#method' => 'get',
        '#action' => Url::fromRoute('aculta_portal.wiki_search')->toString(),
        '#attributes' => ['role' => 'search'],
        'q' => ['#type' => 'textfield', '#title' => $this->t('Termo de busca'), '#default_value' => $term, '#maxlength' => 100, '#required' => TRUE],
        'submit' => ['#type' => 'submit', '#value' => $this->t('Buscar')],
      ],
    ];
    if ($term === '') {
      return $build;
    }
    $term = mb_substr($term, 0, 100);
    $pattern = '%' . \Drupal::database()->escapeLike($term) . '%';
    $query = \Drupal::entityQuery('node')
      ->accessCheck(TRUE)
      ->condition('type', 'wiki_entry')
      ->condition('status', 1)
      ->range(0, 30)
      ->sort('title');
    $matches = $query->orConditionGroup()
      ->condition('title', $pattern, 'LIKE')
      ->condition('field_wiki_summary.value', $pattern, 'LIKE')
      ->condition('body.value', $pattern, 'LIKE');
    $ids = $query->condition($matches)->execute();
    $nodes = $this->entityTypeManager()->getStorage('node')->loadMultiple($ids);
    $build['count'] = ['#type' => 'item', '#plain_text' => $this->formatPlural(count($nodes), '1 verbete encontrado.', '@count verbetes encontrados.')];
    foreach ($nodes as $node) {
      if ($node->access('view')) {
        $build['results'][$node->id()] = $this->entityTypeManager()->getViewBuilder('node')->view($node, 'teaser');
      }
    }
    if (!$nodes) {
      $build['empty'] = ['#type' => 'item', '#plain_text' => $this->t('Nenhum verbete publicado corresponde a essa busca.')];
    }
    return $build;
  }

  /** Embeds one permission-checked Wiki View block. */
  private function viewBlock(string $viewId, string $displayId): array {
    $view = Views::getView($viewId);
    return $view ? $view->buildRenderable($displayId) : ['#markup' => ''];
  }

}
