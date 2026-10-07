<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\Presentation;

use Drupal\aculta_portal\AccountProfileManager;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Url;
use Drupal\user\UserInterface;

/** Prepares the ACCOUNT basic/address section state around Profile Form API. */
final class AccountDataPresenter {

  public function __construct(
    private readonly AccountProfileManager $profiles,
    private readonly TranslationInterface $translation,
  ) {}

  /**
   * @return array{
   *   description: string,
   *   title: string,
   *   section: string,
   *   profile: object,
   *   account_email: string|null,
   *   tabs: array<int, array{label: string, url: \Drupal\Core\Url, current: bool}>,
   *   cache_tags: string[]
   * }
   */
  public function present(UserInterface $account, string $section): array {
    $section = $section === 'address' ? 'address' : 'basics';
    $profile = $section === 'address'
      ? $this->profiles->customerProfile($account)
      : $this->profiles->participantProfile($account);

    $tabs = [];
    foreach ([
      'basics' => [
        'label' => (string) $this->translation->translate('Informações básicas'),
        'route' => 'aculta_portal.my_data',
      ],
      'address' => [
        'label' => (string) $this->translation->translate('Endereço'),
        'route' => 'aculta_portal.my_data_address',
      ],
    ] as $key => $definition) {
      $tabs[] = [
        'label' => $definition['label'],
        'url' => Url::fromRoute($definition['route']),
        'current' => $key === $section,
      ];
    }

    $cacheTags = array_merge(
      $account->getCacheTags(),
      method_exists($profile, 'getCacheTags') ? $profile->getCacheTags() : [],
    );

    return [
      'description' => (string) $this->translation->translate(
        'Mantenha suas informações pessoais e de contato atualizadas.'
      ),
      'title' => $section === 'address'
        ? (string) $this->translation->translate('Endereço')
        : (string) $this->translation->translate('Informações básicas'),
      'section' => $section,
      'profile' => $profile,
      'account_email' => $section === 'basics' ? (string) $account->getEmail() : NULL,
      'tabs' => $tabs,
      'cache_tags' => array_values(array_unique($cacheTags)),
    ];
  }

}
