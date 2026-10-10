<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\Secrets;

use Drupal\aculta_portal\Secrets\Form\SecretEditForm;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * "Credenciais do ambiente": tabela de status, botão de olho (valor completo sob demanda) e lápis
 * (popup para alterar o valor). Os valores nunca entram no HTML da página.
 */
final class SecretsController {

  use StringTranslationTrait;

  public function __construct(
    private readonly SecretsManager $secrets,
    private readonly FormBuilderInterface $formBuilder,
    TranslationInterface $translation,
  ) {
    $this->setStringTranslation($translation);
  }

  /** @return array<string, mixed> */
  public function page(): array {
    $rows = [];
    foreach ($this->secrets->status() as $name => $row) {
      $state = $row['present'] ? 'ok' : ($row['required'] ? 'error' : 'attention');
      $label = match ($state) {
        'ok' => $this->t('✔ OK — preenchida'),
        'attention' => $this->t('⚠ Atenção — opcional sem valor'),
        default => $this->t('✖ Erro — obrigatória sem valor'),
      };
      $rows[] = [
        ['data' => ['#plain_text' => $name]],
        ['data' => ['#plain_text' => $row['key_id']]],
        ['data' => ['#plain_text' => $row['required'] ? $this->t('Sim') : $this->t('Não')]],
        ['data' => $this->valueCell($name, $row['present'])],
        [
          'data' => ['#plain_text' => $label],
          'class' => ['aculta-portal-requirements__status', 'aculta-portal-requirements__status--' . $state],
        ],
      ];
    }
    $page = [
      '#attached' => [
        // dialog.ajax: o lápis abre o popup por link use-ajax com data-dialog-type="modal".
        'library' => ['aculta_portal/requirements-report', 'aculta_portal/secrets-reveal', 'core/drupal.dialog.ajax'],
      ],
      'legend' => [
        '#type' => 'container',
        '#attributes' => ['class' => ['aculta-portal-requirements__legend']],
        'ok' => ['#plain_text' => (string) $this->t('✔ OK: chave preenchida no ambiente.')],
        'attention' => ['#plain_text' => (string) $this->t('⚠ Atenção: variável opcional sem valor.')],
        'error' => ['#plain_text' => (string) $this->t('✖ Erro: variável obrigatória sem valor.')],
      ],
      'environment' => ['#markup' => '<p>' . $this->t('Ambiente: @env', ['@env' => $this->secrets->environment()]) . '</p>'],
      'status' => [
        '#type' => 'table',
        '#header' => [$this->t('Variável'), $this->t('Key'), $this->t('Obrigatória'), $this->t('Valor salvo'), $this->t('Estado')],
        '#rows' => $rows,
        '#empty' => $this->t('Nenhuma variável no contrato.'),
        '#attributes' => ['class' => ['aculta-portal-requirements']],
      ],
    ];
    if (!$this->secrets->canSaveHere()) {
      $page['storage_notice'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['messages', 'messages--warning']],
        'text' => ['#plain_text' => (string) $this->t('⚠ Atenção: neste ambiente as credenciais são armazenadas no banco de dados, criptografadas. O provisionamento é feito pelo ACULTA Deployer após o deploy; o formulário não grava aqui.')],
      ];
    }
    return $page;
  }

  /** Popup de alteração de uma credencial (rota com link use-ajax modal). */
  public function edit(string $name): array {
    return $this->formBuilder->getForm(SecretEditForm::class, $name);
  }

  /**
   * Célula do valor: máscara, botão de olho e lápis. O valor completo não entra no HTML; o olho pede
   * ao servidor (rota com token) só quando acionado. O lápis aparece só onde a gravação é possível.
   *
   * @return array<string, mixed>
   */
  private function valueCell(string $name, bool $present): array {
    $cell = [
      '#type' => 'container',
      '#attributes' => ['class' => ['aculta-secret-value']],
    ];
    if ($present) {
      $value = $this->secrets->value($name);
      $masked = $value === NULL ? '—' : SecretsManager::mask($value);
      // A rota tem _csrf_token: o gerador de URL já acrescenta ?token=. Não concatenar outro.
      $url = Url::fromRoute('aculta_portal.secrets_reveal', ['name' => $name])->toString();
      $cell['masked'] = ['#markup' => '<span class="aculta-secret-value__text" data-masked="' . htmlspecialchars($masked, ENT_QUOTES) . '">' . htmlspecialchars($masked, ENT_QUOTES) . '</span>'];
      $cell['eye'] = [
        '#type' => 'html_tag',
        '#tag' => 'button',
        '#value' => '👁',
        '#attributes' => [
          'type' => 'button',
          'class' => ['aculta-secret-value__eye'],
          'data-reveal-url' => $url,
          'aria-pressed' => 'false',
          'aria-label' => (string) $this->t('Mostrar valor de @name', ['@name' => $name]),
        ],
      ];
    }
    else {
      $cell['masked'] = ['#markup' => '<span class="aculta-secret-value__text">—</span>'];
    }
    if ($this->secrets->canSaveHere()) {
      $cell['edit'] = [
        '#type' => 'link',
        '#title' => '✎',
        '#url' => Url::fromRoute('aculta_portal.secrets_edit', ['name' => $name]),
        '#attributes' => [
          'class' => ['aculta-secret-value__edit', 'use-ajax'],
          'data-dialog-type' => 'modal',
          'data-dialog-options' => Json::encode(['width' => 480]),
          'aria-label' => (string) $this->t('Alterar valor de @name', ['@name' => $name]),
          'title' => (string) $this->t('Alterar valor'),
        ],
      ];
    }
    return $cell;
  }

}
