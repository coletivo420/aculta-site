<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/** Provides the public registration status while account creation is closed. */
final class RegistrationController implements ContainerInjectionInterface {

  public function __construct(
    private readonly TranslationInterface $translation,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static($container->get('string_translation'));
  }

  /** Returns an informational page without exposing account creation. */
  public function closed(): array {
    return [
      '#type' => 'container',
      '#attributes' => ['class' => ['aculta-registration-status']],
      'message' => [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $this->translation->translate('O cadastro de novas contas está temporariamente fechado. Ele será disponibilizado quando a confirmação de e-mail estiver pronta.'),
      ],
      'login' => [
        '#type' => 'link',
        '#title' => $this->translation->translate('Já tenho uma conta — entrar'),
        '#url' => Url::fromRoute('user.login'),
        '#attributes' => ['class' => ['aculta-button', 'aculta-button--primary']],
      ],
      '#cache' => [
        'contexts' => ['url.site'],
        'tags' => ['config:user.settings'],
      ],
    ];
  }

}
