<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\Hook;

use Drupal\aculta_portal\Domain\DomainPurposeManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\domain\DomainInterface;
use Drupal\domain\DomainNegotiatorInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\node\NodeInterface;
use Drupal\schema_metatag\SchemaMetatagManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;

/** Editorial, token and metadata hooks for ACULTA. */
final class EditorialHooks {

  public function __construct(
    #[Autowire(service: 'config.factory')]
    private readonly ConfigFactoryInterface $configFactory,
    #[Autowire(service: 'entity_type.manager')]
    private readonly EntityTypeManagerInterface $entities,
    #[Autowire(service: 'aculta_portal.domain_purpose')]
    private readonly DomainPurposeManager $domainPurpose,
    #[Autowire(service: 'domain.negotiator')]
    private readonly DomainNegotiatorInterface $domainNegotiator,
    #[Autowire(service: 'request_stack')]
    private readonly RequestStack $requestStack,
    #[Autowire(service: 'file_url_generator')]
    private readonly FileUrlGeneratorInterface $fileUrlGenerator,
    #[Autowire(service: 'string_translation')]
    private readonly TranslationInterface $translation,
  ) {}

  #[Hook('metatag_tags_alter')]
  public function metatagTagsAlter(array &$definitions): void {
    foreach (['schema_organization_address', 'schema_event_location'] as $id) {
      if (isset($definitions[$id])) {
        $definitions[$id]['class'] = \Drupal\aculta_portal\Plugin\metatag\Tag\PostalAddressTag::class;
      }
    }
  }

  #[Hook('token_info')]
  public function tokenInfo(): array {
    $tokens = [];
    foreach (['editorial-summary', 'canonical', 'image', 'published', 'modified', 'author-type', 'author-name', 'start', 'end', 'event-status', 'attendance', 'organizer', 'free'] as $key) {
      $tokens[$key] = [
        'name' => $this->translation->translate('Editorial: @field', ['@field' => $key]),
        'description' => $this->translation->translate('Derived from structured editorial fields; never from the Drupal account name.'),
      ];
    }
    $institution = [];
    foreach (['name', 'alternate', 'tax-id', 'email', 'phone', 'url', 'description', 'street', 'city', 'region', 'postal', 'country'] as $key) {
      $institution[$key] = [
        'name' => $this->translation->translate('Institutional: @field', ['@field' => $key]),
        'description' => $this->translation->translate('Verified public institutional data.'),
      ];
    }
    return [
      'types' => [
        'institution' => [
          'name' => $this->translation->translate('Institutional data'),
          'description' => $this->translation->translate('Public official data from the institutional block.'),
        ],
      ],
      'tokens' => ['node' => $tokens, 'institution' => $institution],
    ];
  }

  #[Hook('tokens')]
  public function tokens(
    string $type,
    array $tokens,
    array $data,
    array $options,
    BubbleableMetadata $metadata,
  ): array {
    $result = [];

    if ($type === 'institution') {
      $uuid = $this->configFactory->get('aculta.settings')->get('institution_data_uuid');
      $blocks = $this->entities->getStorage('block_content')->loadByProperties(['uuid' => $uuid]);
      $block = $blocks ? reset($blocks) : NULL;
      if (!$block) {
        return [];
      }
      $metadata->addCacheableDependency($block)->addCacheTags(['config:aculta.settings']);
      $fields = [
        'name' => 'field_org_name',
        'alternate' => 'field_trade_name',
        'tax-id' => 'field_cnpj',
        'email' => 'field_email',
        'phone' => 'field_schema_phone',
        'description' => 'field_org_description',
        'street' => 'field_street_address',
        'city' => 'field_locality',
        'region' => 'field_address_region',
        'postal' => 'field_postal_code',
        'country' => 'field_country',
      ];
      foreach ($tokens as $name => $original) {
        if ($name === 'url') {
          $result[$original] = (string) ($block->get('field_site')->uri ?? '');
        }
        elseif (isset($fields[$name]) && $block->hasField($fields[$name])) {
          $result[$original] = (string) ($block->get($fields[$name])->value ?? '');
        }
      }
    }

    if ($type !== 'node' || !($data['node'] ?? NULL) instanceof NodeInterface) {
      return $result;
    }

    $node = $data['node'];
    $metadata->addCacheableDependency($node);
    $purpose = 'main';
    if ($node->hasField('field_domain_source') && !$node->get('field_domain_source')->isEmpty()) {
      $purpose = $this->domainPurpose->getPurposeForDomainId(
        (string) $node->get('field_domain_source')->target_id
      ) ?? 'main';
    }

    $get = static fn (string $field): string => $node->hasField($field)
      ? (string) ($node->get($field)->value ?? '')
      : '';
    $date = static fn (string $field): string => $get($field) !== ''
      ? (new \DateTimeImmutable($get($field), new \DateTimeZone('UTC')))->format(DATE_ATOM)
      : '';

    foreach ($tokens as $name => $original) {
      $value = NULL;
      switch ($name) {
        case 'editorial-summary':
          $value = $get('field_summary') ?: (string) ($node->get('body')->summary ?? '');
          break;

        case 'canonical':
          $canonicalUrl = $node->toUrl('canonical');
          $sourceDomain = $node->hasField('field_domain_source')
            ? $node->get('field_domain_source')->entity
            : NULL;
          if ($sourceDomain instanceof DomainInterface) {
            $canonicalUrl->setOption('domain', $sourceDomain);
            $metadata->addCacheableDependency($sourceDomain);
          }
          $activeDomain = $this->domainNegotiator->getActiveDomain();
          $request = $this->requestStack->getCurrentRequest();
          if ($activeDomain && isset($activeDomain->alias)
            && $activeDomain->alias->getEnvironment() === 'local'
            && $request) {
            $canonicalUrl->setOption('https', $request->isSecure());
          }
          $generated = $canonicalUrl->setAbsolute()->toString(TRUE);
          $metadata->addCacheableDependency($generated);
          $value = $generated->getGeneratedUrl();
          break;

        case 'published':
          $value = $date('field_published_at');
          break;

        case 'modified':
          $value = gmdate(DATE_ATOM, $node->getChangedTime());
          break;

        case 'start':
          $value = $date('field_event_start');
          break;

        case 'end':
          $value = $date('field_event_end');
          break;

        case 'event-status':
          $value = [
            'agendado' => 'https://schema.org/EventScheduled',
            'reagendado' => 'https://schema.org/EventRescheduled',
            'adiado' => 'https://schema.org/EventPostponed',
            'cancelado' => 'https://schema.org/EventCancelled',
            'concluido' => 'https://schema.org/EventScheduled',
          ][$get('field_event_status')] ?? '';
          break;

        case 'attendance':
          $value = [
            'presencial' => 'https://schema.org/OfflineEventAttendanceMode',
            'online' => 'https://schema.org/OnlineEventAttendanceMode',
            'hibrido' => 'https://schema.org/MixedEventAttendanceMode',
          ][$get('field_modality')] ?? '';
          break;

        case 'organizer':
          $value = $get('field_organizer');
          break;

        case 'free':
          $value = $node->hasField('field_free')
            ? ($get('field_free') === '1' ? 'True' : 'False')
            : '';
          break;

        case 'author-type':
        case 'author-name':
          $author = $node->hasField('field_editorial_author')
            ? $node->get('field_editorial_author')->entity
            : NULL;
          if ($author) {
            $metadata->addCacheableDependency($author);
            $value = $name === 'author-name'
              ? $author->label()
              : ($author->get('field_author_kind')->value === 'person' ? 'Person' : 'Organization');
          }
          else {
            $value = '';
          }
          break;

        case 'image':
          $media = $node->hasField('field_media_image')
            ? $node->get('field_media_image')->entity
            : NULL;
          $file = $media && $media->hasField('field_media_image')
            ? $media->get('field_media_image')->entity
            : NULL;
          $value = '';
          if ($file && $media->isPublished()) {
            $metadata->addCacheableDependency($media)->addCacheableDependency($file);
            $relative = $this->fileUrlGenerator->generateString($file->getFileUri());
            $imageUrl = str_starts_with($relative, '/')
              ? $this->domainPurpose->pathUrl($purpose, $relative)
              : NULL;
            $value = $imageUrl ? $imageUrl->toString() : $relative;
          }
          break;
      }

      if ($value !== NULL) {
        $result[$original] = $value;
      }
    }

    return $result;
  }

  #[Hook('node_presave')]
  public function nodePresave(NodeInterface $node): void {
    if ($node->bundle() === 'article'
      && $node->isPublished()
      && $node->hasField('field_published_at')
      && $node->get('field_published_at')->isEmpty()) {
      $node->set('field_published_at', gmdate('Y-m-d\TH:i:s'));
    }
  }

  #[Hook('metatags_alter')]
  public function metatagsAlter(array &$tags, array $context): void {
    $node = $context['entity'] ?? NULL;
    if ($node instanceof NodeInterface
      && in_array($node->bundle(), ['article', 'activity', 'project'], TRUE)
      && $node->get('field_media_image')->isEmpty()) {
      foreach (['schema_article_image', 'schema_event_image', 'schema_web_page_image'] as $id) {
        if (str_contains((string) ($tags[$id] ?? ''), '[node:image]')) {
          unset($tags[$id]);
        }
      }
    }

    $group = $context['entity'] ?? NULL;
    if ($group instanceof GroupInterface && $group->bundle() === 'lms_course') {
      $canonical = $this->domainPurpose->canonicalRouteUrl(
        'courses',
        'entity.group.canonical',
        ['group' => $group->id()],
      );
      if ($canonical) {
        $canonicalUrl = $canonical->toString();
        $tags['canonical_url'] = $canonicalUrl;
        $tags['og_url'] = $canonicalUrl;
      }
    }

    if ($node instanceof NodeInterface && $node->bundle() === 'wiki_entry') {
      $canonical = $this->domainPurpose->canonicalRouteUrl(
        'wiki',
        'entity.node.canonical',
        ['node' => $node->id()],
      );
      if ($canonical) {
        $canonicalUrl = $canonical->toString();
        $tags['canonical_url'] = $canonicalUrl;
        $tags['og_url'] = $canonicalUrl;
      }
    }

    if (!$node instanceof NodeInterface || $node->bundle() !== 'activity') {
      return;
    }

    $locations = [];
    $mode = $node->get('field_modality')->value;
    if (in_array($mode, ['presencial', 'hibrido'], TRUE) && !$node->get('field_place_name')->isEmpty()) {
      $place = ['@type' => 'Place', 'name' => $node->get('field_place_name')->value];
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

    if (in_array($mode, ['online', 'hibrido'], TRUE) && !$node->get('field_online_url')->isEmpty()) {
      $locations[] = [
        '@type' => 'VirtualLocation',
        'url' => $node->get('field_online_url')->uri,
      ];
    }

    if (empty($tags['schema_event_location']) && $locations) {
      $tags['schema_event_location'] = SchemaMetatagManager::serialize(
        count($locations) === 1 ? $locations[0] : $locations
      );
    }
  }

}
