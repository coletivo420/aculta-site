<?php

declare(strict_types=1);

namespace Drupal\aculta420\Hook;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\Render\Element;
use Drupal\node\NodeInterface;

/**
 * Presentation-only hooks for ACULTA420.
 */
final class ThemeHooks {

  public function __construct(
    private readonly PathMatcherInterface $pathMatcher,
    private readonly ConfigFactoryInterface $configFactory,
  ) {}

  /**
   * Keeps a solitary editorial highlight static using native VVJB options.
   */
  #[Hook('preprocess_views_view_vvjb')]
  public function preprocessViewsViewVvjb(array &$variables): void {
    if (($variables['view']?->id() ?? '') !== 'home_editorial_highlights') {
      return;
    }
    if (count($variables['rows'] ?? []) === 1) {
      $variables['options']['slide_time'] = 0;
      $variables['options']['navigation'] = 'none';
      $variables['options']['show_play_pause'] = FALSE;
      $variables['options']['show_progress_bar'] = FALSE;
      $variables['options']['show_page_counter'] = FALSE;
    }
  }

  /**
   * Feeds the home hero pattern from the page fields on the full view.
   *
   * Only pages that filled the hero title get it. The hero is a render array
   * with weight -100, so its access and cache metadata bubble with the node.
   */
  #[Hook('preprocess_node')]
  public function preprocessNode(array &$variables): void {
    $node = $variables['elements']['#node'] ?? NULL;
    if (!$node instanceof NodeInterface
      || $node->bundle() !== 'page'
      || ($variables['view_mode'] ?? '') !== 'full'
      || !$node->hasField('field_hero_title')
      || $node->get('field_hero_title')->isEmpty()) {
      return;
    }

    $actions = [];
    $classes = [
      'field_hero_primary' => ['btn', 'btn-primary'],
      'field_hero_secondary' => ['btn', 'btn-outline-primary'],
    ];
    foreach ($classes as $field => $class) {
      $item = $node->get($field)->first();
      if ($item === NULL || $item->uri === NULL || $item->uri === '') {
        continue;
      }
      $actions[] = Link::fromTextAndUrl(
        $item->title !== NULL && $item->title !== '' ? $item->title : $item->uri,
        Url::fromUri($item->uri, ['attributes' => ['class' => $class]]),
      )->toRenderable();
    }

    $text = static fn(string $field): ?string => $node->hasField($field) && !$node->get($field)->isEmpty()
      ? (string) $node->get($field)->value
      : NULL;

    $variables['content']['aculta_hero'] = [
      '#type' => 'component',
      '#component' => 'aculta420:hero',
      '#props' => [
        'eyebrow' => $text('field_hero_eyebrow'),
        'title' => (string) $text('field_hero_title'),
        'slogan' => $text('field_hero_slogan'),
        'lead' => $text('field_hero_lead'),
      ],
      '#slots' => ['actions' => $actions],
      '#weight' => -100,
    ];
  }

  /**
   * Marks editorial body fields as prose without styling unrelated links.
   */
  #[Hook('preprocess_field')]
  public function preprocessField(array &$variables): void {
    if (($variables['entity_type'] ?? '') !== 'node'
      || ($variables['field_name'] ?? '') !== 'body') {
      return;
    }

    $entity = $variables['element']['#object'] ?? NULL;
    if (!$entity instanceof NodeInterface
      || !in_array($entity->bundle(), [
        'page',
        'article',
        'activity',
        'project',
        'editorial_highlight',
        'document',
      ], TRUE)) {
      return;
    }

    if (isset($variables['attributes']) && is_object($variables['attributes'])
      && method_exists($variables['attributes'], 'addClass')) {
      $variables['attributes']->addClass('aculta-prose');
    }
    elseif (isset($variables['attributes']) && is_array($variables['attributes'])) {
      $variables['attributes']['class'][] = 'aculta-prose';
    }
  }

  /**
   * Composes stored header blocks into the current public shell.
   */
  #[Hook('preprocess_page')]
  public function preprocessPage(array &$variables): void {
    $variables['institutional_home'] = $this->pathMatcher->isFrontPage();
    $brandMedia = $variables['domain_presentation']['regions']['brand_media'] ?? NULL;
    $variables['aculta_domain_brand_media'] = is_array($brandMedia) ? $brandMedia : [];
    // The text fallback is only built when there is no brand media; otherwise the
    // header would show two home links (image and text) for the same destination.
    $variables['aculta_domain_brand_fallback'] = $variables['aculta_domain_brand_media'] === []
      ? $this->buildDomainBrandFallback($variables['domain_presentation']['identity'] ?? NULL)
      : NULL;

    $destinations = [
      'page_title_block' => 'aculta_page_title',
      'system_breadcrumb_block' => 'aculta_breadcrumb',
      'system_menu_block:account' => 'aculta_account_menu',
      'help_block' => 'aculta_help',
    ];
    foreach ($destinations as $destination) {
      $variables[$destination] = [];
    }
    $variables['aculta_has_system_branding'] = FALSE;

    if (!isset($variables['page']['header'])) {
      return;
    }

    foreach (Element::children($variables['page']['header']) as $key) {
      $block = $variables['page']['header'][$key];
      $pluginId = $block['#plugin_id'] ?? '';
      $lazyBuilder = $block['#lazy_builder'] ?? [];
      if ($pluginId === ''
        && ($lazyBuilder[0] ?? '') === 'Drupal\\block\\BlockViewBuilder::lazyBuilder') {
        $blockId = $lazyBuilder[1][0] ?? '';
        $pluginId = is_string($blockId) && $blockId !== ''
          ? (string) ($this->configFactory->get('block.block.' . $blockId)->get('plugin') ?? '')
          : '';
      }
      if ($pluginId === 'system_branding_block') {
        if ($variables['aculta_domain_brand_media'] !== []) {
          unset($variables['page']['header'][$key]);
          continue;
        }
        $variables['aculta_has_system_branding'] = TRUE;
      }

      $destination = $destinations[$pluginId] ?? NULL;
      if ($destination !== NULL) {
        $variables[$destination][$key] = $block;
        unset($variables['page']['header'][$key]);
      }
    }
  }

  /**
   * Builds the minimal textual branding fallback from the neutral identity.
   *
   * @param mixed $identity
   *   The neutral identity array prepared by the Portal presentation layer.
   *
   * @return array{label: string, home_url: string}|null
   *   Presentation-only fallback data, or NULL when the contract is absent
   *   or incomplete. No Domain lookup or functional fallback happens here.
   */
  private function buildDomainBrandFallback(mixed $identity): ?array {
    if (!is_array($identity)) {
      return NULL;
    }

    $purpose = isset($identity['purpose']) && is_string($identity['purpose'])
      ? trim($identity['purpose'])
      : '';
    $title = isset($identity['title']) && is_string($identity['title'])
      ? trim($identity['title'])
      : '';
    $homeUrl = isset($identity['home_url']) && is_string($identity['home_url'])
      ? trim($identity['home_url'])
      : '';

    if ($purpose === '' || $title === '' || $homeUrl === '') {
      return NULL;
    }

    $shortTitle = isset($identity['short_title']) && is_string($identity['short_title'])
      ? trim($identity['short_title'])
      : '';

    return [
      'label' => $shortTitle !== '' ? $shortTitle : $title,
      'home_url' => $homeUrl,
    ];
  }

}
