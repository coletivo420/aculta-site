<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\Hook;

use Drupal\aculta_portal\Access\EmailConfirmationResponseAccessHandler;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Session\AccountInterface;
use Drupal\email_confirmer\EmailConfirmationInterface;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Integração do Portal com as confirmações de e-mail (email_confirmer).
 */
final class EmailConfirmerHooks {

  public function __construct(
    #[Autowire(service: 'current_user')]
    private readonly AccountInterface $currentUser,
    #[Autowire(service: 'entity_type.manager')]
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * Troca o handler de acesso das confirmações de e-mail por um que libera a resposta ao visitante.
   */
  #[Hook('entity_type_alter')]
  public function alterConfirmationAccess(array &$entity_types): void {
    if (isset($entity_types['email_confirmer_confirmation'])) {
      $entity_types['email_confirmer_confirmation']->setHandlerClass('access', EmailConfirmationResponseAccessHandler::class);
    }
  }

  /**
   * Após confirmar a troca de e-mail, entra na conta do pedido se o visitante estiver anônimo.
   *
   * Só vale para confirmações de troca de e-mail de conta, para conta ativa, e só quando o visitante ainda não
   * está logado (não troca a sessão de outro usuário). O hash do link já foi verificado por confirm().
   */
  #[Hook('email_confirmer')]
  public function loginAfterConfirmation(string $op, EmailConfirmationInterface $confirmation): void {
    if ($op !== 'confirm' || $confirmation->getRealm() !== 'email_confirmer_user' || !$this->currentUser->isAnonymous()) {
      return;
    }
    $uid = (int) $confirmation->getProperty('user');
    $account = $uid > 0 ? $this->entityTypeManager->getStorage('user')->load($uid) : NULL;
    if (!$account instanceof UserInterface || !$account->isActive()) {
      return;
    }
    user_login_finalize($account);
  }

}
