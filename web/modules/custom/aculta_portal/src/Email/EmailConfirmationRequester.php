<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\Email;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;
use Drupal\email_confirmer\EmailConfirmerManagerInterface;
use Drupal\user\UserInterface;

/**
 * Solicita um novo e-mail de confirmação de cadastro. Cada solicitação gera um hash novo e cancela os pedidos
 * pendentes anteriores do mesmo usuário, para que apenas o último link continue válido.
 */
final class EmailConfirmationRequester {

  public const REALM = 'aculta_registration';

  public function __construct(
    private readonly EmailConfirmerManagerInterface $manager,
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /** Retorna TRUE se o pedido foi criado e enviado. */
  public function request(UserInterface $account): bool {
    $email = (string) $account->getEmail();
    if ($email === '' || (int) $account->id() <= 0) {
      return FALSE;
    }
    foreach ($this->pendingFor((int) $account->id()) as $old) {
      $old->cancel();
      $old->save();
    }
    $confirmation = $this->manager->createConfirmation($email);
    $confirmation->setRealm(self::REALM)
      ->setProperty('user', (int) $account->id())
      ->setPrivate()
      ->setResponseUrl(Url::fromRoute('aculta_portal.dashboard'), 'confirm')
      ->sendRequest();
    $confirmation->save();
    return TRUE;
  }

  /** @return \Drupal\email_confirmer\EmailConfirmationInterface[] */
  private function pendingFor(int $uid): array {
    $storage = $this->entityTypeManager->getStorage('email_confirmer_confirmation');
    return array_values(array_filter(
      $storage->loadMultiple($storage->getQuery()->accessCheck(FALSE)->condition('realm', self::REALM)->execute()),
      static fn($c) => $c->isPending() && (int) $c->getProperty('user') === $uid,
    ));
  }

}
