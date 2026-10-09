<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\Account;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Registra o aceite dos termos (acordo aculta_account_terms) no momento do cadastro, quando o visitante
 * marca o checkbox do formulário. Assim o aceite não depende de uma etapa posterior.
 */
final class RegistrationTerms {

  private const AGREEMENT = 'aculta_account_terms';

  public function __construct(
    private readonly \Drupal\agreement\AgreementHandlerInterface $agreementHandler,
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /** Grava o aceite do acordo para a conta recém-criada. Retorna TRUE se foi gravado. */
  public function record(AccountInterface $account): bool {
    $agreement = $this->entityTypeManager->getStorage('agreement')->load(self::AGREEMENT);
    if ($agreement === NULL || (int) $account->id() <= 0) {
      return FALSE;
    }
    $this->agreementHandler->agree($agreement, $account, 1);
    return TRUE;
  }

}
