<?php

declare(strict_types=1);

namespace Drupal\aculta_portal;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\user\UserInterface;

/**
 * Resolves the account Profiles already owned by Profile/Commerce.
 *
 * This service does not create a parallel account store. It only centralizes
 * the existing Profile lookup/preparation rules used by ACCOUNT.
 */
final class AccountProfileManager {

  public function __construct(
    private readonly EntityTypeManagerInterface $entities,
  ) {}

  public function participantProfile(UserInterface $account): object {
    $storage = $this->entities->getStorage('profile');
    $profile = $storage->loadByUser($account, 'participante');

    return $profile ?: $storage->create([
      'type' => 'participante',
      'uid' => $account->id(),
      'status' => TRUE,
      'is_default' => TRUE,
    ]);
  }

  public function customerProfile(UserInterface $account): object {
    $storage = $this->entities->getStorage('profile');
    $profile = $storage->loadByUser($account, 'customer');

    if (!$profile) {
      $profiles = $storage->loadByProperties([
        'uid' => $account->id(),
        'type' => 'customer',
      ]);
      $profile = $profiles ? reset($profiles) : NULL;
    }

    return $profile ?: $storage->create([
      'type' => 'customer',
      'uid' => $account->id(),
      'status' => TRUE,
      'is_default' => TRUE,
    ]);
  }

}
