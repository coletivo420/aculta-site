<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\Email;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Reenvia a confirmação do e-mail do próprio usuário logado (gera um hash novo).
 */
final class EmailResendController extends ControllerBase {

  public function __construct(
    #[Autowire(service: 'aculta_portal.email_confirmation_requester')]
    private readonly EmailConfirmationRequester $requester,
    #[Autowire(service: 'current_user')]
    private readonly AccountInterface $account,
  ) {}

  public function resend(): RedirectResponse {
    $user = $this->entityTypeManager()->getStorage('user')->load($this->account->id());
    $sent = $user !== NULL && $this->requester->request($user);
    $this->messenger()->addStatus($sent
      ? $this->t('Enviamos um novo link de confirmação para o seu e-mail. O link anterior deixou de valer.')
      : $this->t('Não foi possível enviar a confirmação agora. Tente novamente mais tarde.'));
    return new RedirectResponse(Url::fromRoute('aculta_portal.dashboard')->toString());
  }

}
