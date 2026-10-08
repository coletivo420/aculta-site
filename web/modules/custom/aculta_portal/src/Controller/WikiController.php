<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\Controller;

use Drupal\Core\Cache\CacheableMetadata;
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
        'text' => ['#plain_text' => $this->t('Wiki420 é uma wiki antiproibicionista, participativa e baseada em fontes. Aqui reunimos conhecimento sobre maconha, direitos, cultura, ciência, saúde, história, redução de danos e luta pela legalização.')],
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
      'collaboration' => [
        '#type' => 'link',
        '#title' => $this->t('Quer colaborar? Proponha um verbete.'),
        '#url' => Url::fromRoute('node.add', ['node_type' => 'wiki_entry']),
        '#access' => $this->currentUser()->hasPermission('create wiki_entry content'),
      ],
      'categories_title' => ['#type' => 'html_tag', '#tag' => 'h2', '#value' => $this->t('Categorias da Wiki420')],
      'categories' => $this->viewBlock('wiki_categories', 'block_1'),
      'recent_title' => ['#type' => 'html_tag', '#tag' => 'h2', '#value' => $this->t('Verbetes recentes')],
      'recent' => $this->viewBlock('wiki_entries', 'block_recent'),
      'changes_title' => ['#type' => 'html_tag', '#tag' => 'h2', '#value' => $this->t('Alterações recentes da Wiki420')],
      'changes' => $this->recentChanges(),
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
    $wikiDomain = \Drupal::service('aculta_portal.domain_purpose')->getDomain('wiki');
    if (!$wikiDomain) {
      $build['empty'] = ['#type' => 'item', '#plain_text' => $this->t('A busca da Wiki420 está indisponível no momento.')];
      return $build;
    }
    $pattern = '%' . \Drupal::database()->escapeLike($term) . '%';
    $query = \Drupal::entityQuery('node')
      ->accessCheck(TRUE)
      ->condition('type', 'wiki_entry')
      ->condition('field_domain_source.target_id', $wikiDomain->id())
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

  /** Lists published Wiki420 revisions by latest update with public attribution. */
  private function recentChanges(): array {
    $build = [
      '#theme' => 'item_list',
      '#items' => [],
      '#cache' => [
        'contexts' => ['domain', 'user.permissions', 'user.node_grants:view'],
        'tags' => ['node_list:wiki_entry'],
      ],
    ];
    $wikiDomain = \Drupal::service('aculta_portal.domain_purpose')->getDomain('wiki');
    if (!$wikiDomain) {
      return $build;
    }
    $ids = \Drupal::entityQuery('node')
      ->accessCheck(TRUE)
      ->condition('type', 'wiki_entry')
      ->condition('field_domain_source.target_id', $wikiDomain->id())
      ->condition('status', 1)
      ->sort('changed', 'DESC')
      ->range(0, 5)
      ->execute();
    $nodes = $this->entityTypeManager()->getStorage('node')->loadMultiple($ids);
    $cacheability = CacheableMetadata::createFromRenderArray($build);
    $dateFormatter = \Drupal::service('date.formatter');
    foreach ($nodes as $node) {
      if (!$node->access('view')) {
        continue;
      }
      $changed = $node->getChangedTime();
      $row = [
        '#type' => 'container',
        '#attributes' => ['class' => ['aculta-wiki-recent-change']],
        'title' => [
          '#type' => 'link',
          '#title' => $node->label(),
          '#url' => $node->toUrl('canonical'),
        ],
        'updated' => [
          '#type' => 'html_tag',
          '#tag' => 'time',
          '#value' => $dateFormatter->format($changed, 'medium'),
          '#attributes' => ['datetime' => gmdate(DATE_ATOM, $changed)],
        ],
      ];
      $owner = $node->getOwner();
      if ($owner && $owner->access('view')) {
        $row['author'] = ['#plain_text' => $this->t('por @name', ['@name' => $owner->getDisplayName()])];
        $cacheability->addCacheableDependency($owner);
      }
      $build['#items'][] = $row;
      $cacheability->addCacheableDependency($node);
    }
    $cacheability->applyTo($build);
    return $build;
  }

  /** Embeds one permission-checked Wiki View block. */
  private function viewBlock(string $viewId, string $displayId): array {
    $view = Views::getView($viewId);
    return $view ? $view->buildRenderable($displayId) : ['#markup' => ''];
  }

}
