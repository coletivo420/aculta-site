<?php

declare(strict_types=1);

namespace Drupal\aculta420\Hook;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Hook\Attribute\Hook;
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
    $variables['aculta_domain_identity'] = $this->normalizeDomainIdentity(
      $variables['domain_presentation']['identity'] ?? NULL,
    );

    $destinations = [
      'page_title_block' => 'aculta_page_title',
      'system_breadcrumb_block' => 'aculta_breadcrumb',
      'system_menu_block:account' => 'aculta_account_menu',
      'help_block' => 'aculta_help',
    ];
    foreach ($destinations as $destination) {
      $variables[$destination] = [];
    }

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
      $destination = $destinations[$pluginId] ?? NULL;
      if ($destination !== NULL) {
        $variables[$destination][$key] = $block;
        unset($variables['page']['header'][$key]);
      }
    }
  }

  /**
   * Adapts the neutral Portal identity contract for shell presentation.
   *
   * @param mixed $identity
   *   The neutral identity array prepared by aculta_portal, when available.
   *
   * @return array{purpose: string, title: string, short_title: ?string, home_url: string, logo_alt: ?string}|null
   *   Presentation-safe scalar identity, or NULL when the contract is absent
   *   or incomplete. No Domain lookup or functional fallback happens here.
   */
  private function normalizeDomainIdentity(mixed $identity): ?array {
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
      : NULL;
    $logoAlt = isset($identity['logo_alt']) && is_string($identity['logo_alt'])
      ? trim($identity['logo_alt'])
      : NULL;

    return [
      'purpose' => $purpose,
      'title' => $title,
      'short_title' => $shortTitle !== '' ? $shortTitle : NULL,
      'home_url' => $homeUrl,
      'logo_alt' => $logoAlt !== '' ? $logoAlt : NULL,
    ];
  }

}
