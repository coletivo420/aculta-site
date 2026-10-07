<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\Presentation;

use Drupal\aculta_portal\AccountProfileManager;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\user\UserInterface;

/** Prepares identity data for the ACCOUNT dashboard. */
final class AccountIdentityPresenter {

  public function __construct(
    private readonly AccountProfileManager $profiles,
    private readonly TranslationInterface $translation,
  ) {}

  /**
   * Builds identity state without rendering the photo editor or User form.
   *
   * @return array{
   *   display_name: string,
   *   email: string,
   *   avatar: array{
   *     has_picture: bool,
   *     uri: string|null,
   *     alt: string,
   *     initial: string
   *   },
   *   cache_tags: string[]
   * }
   */
  public function present(UserInterface $account): array {
    $profile = $this->profiles->participantProfile($account);
    $nickname = $profile->hasField('field_nickname') && !$profile->get('field_nickname')->isEmpty()
      ? (string) $profile->get('field_nickname')->value
      : '';
    $firstName = $profile->hasField('field_first_name') && !$profile->get('field_first_name')->isEmpty()
      ? (string) $profile->get('field_first_name')->value
      : '';
    $displayName = $nickname ?: ($firstName ?: $account->getDisplayName());

    $file = $account->hasField('user_picture') && !$account->get('user_picture')->isEmpty()
      ? $account->get('user_picture')->entity
      : NULL;

    $cacheTags = array_merge(
      $account->getCacheTags(),
      method_exists($profile, 'getCacheTags') ? $profile->getCacheTags() : [],
      $file && method_exists($file, 'getCacheTags') ? $file->getCacheTags() : [],
    );

    return [
      'display_name' => (string) $displayName,
      'email' => (string) $account->getEmail(),
      'avatar' => [
        'has_picture' => $file !== NULL,
        'uri' => $file ? (string) $file->getFileUri() : NULL,
        'alt' => (string) $this->translation->translate(
          'Foto de perfil de @name',
          ['@name' => $displayName],
        ),
        'initial' => mb_strtoupper(mb_substr((string) $displayName, 0, 1)),
      ],
      'cache_tags' => array_values(array_unique($cacheTags)),
    ];
  }

}
