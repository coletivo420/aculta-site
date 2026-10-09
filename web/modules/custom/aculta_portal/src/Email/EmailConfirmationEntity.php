<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\Email;

use Drupal\email_confirmer\Entity\EmailConfirmation;

/**
 * Pedido de confirmação de e-mail com validade infinita (decisão do responsável).
 *
 * O link não expira; a segurança passa a depender do hash de 43 caracteres de uso único e de o pedido ser
 * substituído (com novo hash) quando um novo e-mail de confirmação é solicitado.
 */
class EmailConfirmationEntity extends EmailConfirmation {

  public function isExpired() {
    return FALSE;
  }

}
