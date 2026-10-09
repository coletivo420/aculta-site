<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\Secrets;

use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Revela o valor completo de uma credencial sob demanda (botão de olho). Rota com token CSRF,
 * permissão restrita e resposta sem cache. Registra no log só o nome e o usuário.
 */
final class SecretsRevealController {

  public function __construct(
    #[Autowire(service: 'aculta_portal.secrets_manager')]
    private readonly SecretsManager $secrets,
    #[Autowire(service: 'current_user')]
    private readonly AccountInterface $account,
  ) {}

  public function reveal(string $name): JsonResponse {
    $value = $this->secrets->value($name);
    if ($value === NULL) {
      $response = new JsonResponse(['error' => 'not_found'], 404);
    }
    else {
      $this->secrets->logReveal($name, (string) $this->account->getAccountName());
      $response = new JsonResponse(['name' => $name, 'value' => $value]);
    }
    $response->headers->set('Cache-Control', 'no-store, private');
    return $response;
  }

}
