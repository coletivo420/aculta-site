<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/** Library integration hooks. */
final class LibraryHooks {

  #[Hook('library_info_alter')]
  public function libraryInfoAlter(array &$libraries, string $extension): void {
    if ($extension === 'cep_autocomplete' && isset($libraries['viacep'])) {
      // Keep the contrib endpoint/client/cache while using the local behavior
      // for accessible status, stale-response protection, and post-fill focus.
      $libraries['viacep']['dependencies'][] = 'aculta_portal/cep-address';
    }
  }

}
