<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\Captcha;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\key\KeyRepositoryInterface;

/**
 * Liga e desliga o Turnstile do site (política de CAPTCHA do Portal).
 *
 * A fonte da verdade é a própria configuração do CAPTCHA (captcha.settings, enable_globally, e os pontos com o
 * desafio Turnstile): o serviço lê e grava só ali, sem configuração própria. Ao mudar, aplica a mesma decisão
 * ao CAPTCHA global e a todos os pontos Turnstile (cadastro, login, recuperação de senha e contato).
 * O segredo nunca passa por aqui: só se verifica se a chave do Key está configurada no ambiente.
 */
final class TurnstileToggle {

  public const CHALLENGE = 'turnstile/Turnstile';

  public const KEY_ID = 'turnstile';

  public function __construct(
    private readonly ConfigFactoryInterface $configFactory,
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly KeyRepositoryInterface $keys,
    private readonly LoggerChannelFactoryInterface $loggerFactory,
  ) {}

  public function isEnabled(): bool {
    return (bool) $this->configFactory->get('captcha.settings')->get('enable_globally');
  }

  /** A chave existe no ambiente (sem revelar o valor). */
  public function keyConfigured(): bool {
    $value = $this->keys->getKey(self::KEY_ID)?->getKeyValue();
    return is_string($value) && $value !== '';
  }

  /** Aplica a decisão ao CAPTCHA global e a todos os pontos Turnstile, e registra quem mudou. */
  public function setEnabled(bool $enabled, string $account): void {
    $this->configFactory->getEditable('captcha.settings')->set('enable_globally', $enabled)->save();
    foreach ($this->entityTypeManager->getStorage('captcha_point')->loadByProperties(['captchaType' => self::CHALLENGE]) as $point) {
      if ((bool) $point->status() !== $enabled) {
        $point->setStatus($enabled)->save();
      }
    }
    $this->loggerFactory->get('aculta_portal')->notice('Turnstile @state por @account.', [
      '@state' => $enabled ? 'ativado' : 'desativado',
      '@account' => $account,
    ]);
  }

}
