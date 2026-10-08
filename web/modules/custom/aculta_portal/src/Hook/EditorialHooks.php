<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\Hook;

use Drupal\aculta_portal\Domain\DomainPurposeManager;
use Drupal\aculta_portal\Plugin\metatag\Tag\PostalAddressTag;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\node\NodeInterface;
use Drupal\schema_metatag\SchemaMetatagManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Editorial, metadata, and library hook implementations.
 */
final class EditorialHooks {

  public function __construct(
    #[Autowire(service: 'aculta_portal.domain_purpose')]
    private readonly DomainPurposeManager $domainPurposeManager,
    #[Autowire(service: 'string_translation')]
    private readonly TranslationInterface $translation,
  ) {}

  /** Preserves punctuation in structured postal addresses. */
  #[Hook('metatag_tags_alter')]
  public function metatagTagsAlter(array &$definitions): void {
    foreach (['schema_organization_address', 'schema_event_location'] as $id) {
      if (isset($definitions[$id])) {
        $definitions[$id]['class'] = PostalAddressTag::class;
      }
    }
  }

  /** Records the first actual publication time for articles. */
  #[Hook('node_presave')]
  public function nodePresave(NodeInterface $node): void {
    if ($node->bundle() === 'article'
      && $node->isPublished()
      && $node->hasField('field_published_at')
      && $node->get('field_published_at')->isEmpty()) {
      $node->set('field_published_at', gmdate('Y-m-d\TH:i:s'));
    }
  }

  /** Adds canonical and structured metadata owned by the Portal. */
  #[Hook('metatags_alter')]
  public function metatagsAlter(array &$tags, array $context): void {
    $entity = $context['entity'] ?? NULL;
    $node = $entity instanceof NodeInterface ? $entity : NULL;

    if ($node !== NULL
      && in_array($node->bundle(), ['article', 'activity', 'project'], TRUE)
      && $node->get('field_media_image')->isEmpty()) {
      foreach (['schema_article_image', 'schema_event_image', 'schema_web_page_image'] as $id) {
        if (str_contains((string) ($tags[$id] ?? ''), '[node:image]')) {
          unset($tags[$id]);
        }
      }
    }

    if ($entity instanceof GroupInterface && $entity->bundle() === 'lms_course') {
      $canonical = $this->domainPurposeManager->canonicalRouteUrl(
        'courses',
        'entity.group.canonical',
        ['group' => $entity->id()],
      );
      if ($canonical !== NULL) {
        $canonicalUrl = $canonical->toString();
        $tags['canonical_url'] = $canonicalUrl;
        $tags['og_url'] = $canonicalUrl;
      }
    }

    if ($node !== NULL && $node->bundle() === 'wiki_entry') {
      $canonical = $this->domainPurposeManager->canonicalRouteUrl(
        'wiki',
        'entity.node.canonical',
        ['node' => $node->id()],
      );
      if ($canonical !== NULL) {
        $canonicalUrl = $canonical->toString();
        $tags['canonical_url'] = $canonicalUrl;
        $tags['og_url'] = $canonicalUrl;
      }
    }

    if ($node === NULL || $node->bundle() !== 'activity') {
      return;
    }

    $locations = [];
    $mode = $node->get('field_modality')->value;
    if (in_array($mode, ['presencial', 'hibrido'], TRUE)
      && !$node->get('field_place_name')->isEmpty()) {
      $place = [
        '@type' => 'Place',
        'name' => $node->get('field_place_name')->value,
      ];
      $address = ['@type' => 'PostalAddress'];
      foreach ([
        'streetAddress' => 'field_event_street',
        'addressLocality' => 'field_event_city',
        'addressRegion' => 'field_event_region',
        'postalCode' => 'field_event_postal',
        'addressCountry' => 'field_event_country',
      ] as $key => $field) {
        if (!$node->get($field)->isEmpty()) {
          $address[$key] = $node->get($field)->value;
        }
      }
      if (count($address) > 1) {
        $place['address'] = $address;
      }
      $locations[] = $place;
    }

    if (in_array($mode, ['online', 'hibrido'], TRUE)
      && !$node->get('field_online_url')->isEmpty()) {
      $locations[] = [
        '@type' => 'VirtualLocation',
        'url' => $node->get('field_online_url')->uri,
      ];
    }

    // Respect explicit editorial overrides of location.
    if (empty($tags['schema_event_location']) && $locations !== []) {
      $tags['schema_event_location'] = SchemaMetatagManager::serialize(
        count($locations) === 1 ? $locations[0] : $locations,
      );
    }
  }

  /** Groups editorial fields without replacing entity-form validation. */
  #[Hook('form_node_form_alter')]
  public function formNodeFormAlter(array &$form, FormStateInterface $formState): void {
    $node = $formState->getFormObject()->getEntity();
    if (!in_array($node->bundle(), ['article', 'activity', 'project'], TRUE)) {
      return;
    }

    $groups = [
      'editorial_content' => [
        $this->translation->translate('Conteúdo'),
        -40,
        ['title', 'body', 'field_summary', 'field_subtitle', 'field_editorial_category', 'field_tags', 'field_editorial_author', 'field_credits', 'field_references'],
      ],
      'editorial_image' => [
        $this->translation->translate('Imagem'),
        -30,
        ['field_media_image', 'field_gallery'],
      ],
      'editorial_relations' => [
        $this->translation->translate('Relacionamentos'),
        -20,
        ['field_project', 'field_related_activity'],
      ],
      'editorial_seo' => [
        $this->translation->translate('SEO e compartilhamento'),
        70,
        ['field_meta_tags'],
      ],
    ];

    foreach ($groups as $id => [$title, $weight, $fields]) {
      $form[$id] = [
        '#type' => 'details',
        '#title' => $title,
        '#open' => $id === 'editorial_content',
        '#weight' => $weight,
      ];
      foreach ($fields as $field) {
        if (isset($form[$field])) {
          $form[$field]['#group'] = $id;
        }
      }
    }

    if (isset($form['author'])) {
      $form['author']['#title'] = $this->translation->translate('Conta responsável pelo cadastro');
    }
    if (isset($form['options'])) {
      $form['options']['#title'] = $this->translation->translate('Publicação');
    }
    if ($node->bundle() === 'activity') {
      $form['#validate'][] = 'aculta_portal.form_callbacks:validateActivity';
    }
  }

  /** Replaces the contrib CEP behavior with the accessible Portal integration. */
  #[Hook('library_info_alter')]
  public function libraryInfoAlter(array &$libraries, string $extension): void {
    if ($extension === 'cep_autocomplete' && isset($libraries['viacep'])) {
      $libraries['viacep']['dependencies'][] = 'aculta_portal/cep-address';
      $libraries['viacep']['dependencies'] = array_values(array_unique(
        $libraries['viacep']['dependencies'],
      ));
    }
  }

}
