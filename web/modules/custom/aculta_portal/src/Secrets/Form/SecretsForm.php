<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\Secrets\Form;

use Drupal\aculta_portal\Secrets\SecretsManager;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * "Credenciais do ambiente": cadastro das chaves do contrato, com valores mascarados depois de salvos.
 *
 * Valores salvos aparecem parcialmente censurados. O botão de olho busca o valor completo sob
 * demanda (rota protegida por token), e o valor nunca é renderizado no HTML da página.
 */
final class SecretsForm extends FormBase implements ContainerInjectionInterface {

  public function __construct(
    private readonly SecretsManager $secrets,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('aculta_portal.secrets_manager'),
    );
  }

  public function getFormId(): string {
    return 'aculta_secrets_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['#attached']['library'][] = 'aculta_portal/requirements-report';
    $form['#attached']['library'][] = 'aculta_portal/secrets-reveal';
    $rows = [];
    $inputs = [];
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
      $inputs[$name] = [
        '#type' => 'password',
        '#title' => $name,
        '#description' => $row['present'] ? $this->t('Preenchida. Deixe em branco para manter o valor atual.') : $this->t('Vazia.'),
        '#autocomplete' => 'new-password',
        '#attributes' => ['spellcheck' => 'false', 'autocapitalize' => 'off'],
      ];
    }
    $form['legend'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['aculta-portal-requirements__legend']],
      'ok' => ['#plain_text' => (string) $this->t('✔ OK: chave preenchida no ambiente.')],
      'attention' => ['#plain_text' => (string) $this->t('⚠ Atenção: variável opcional sem valor.')],
      'error' => ['#plain_text' => (string) $this->t('✖ Erro: variável obrigatória sem valor.')],
    ];
    $form['environment'] = ['#markup' => '<p>' . $this->t('Ambiente: @env', ['@env' => $this->secrets->environment()]) . '</p>'];
    $form['status'] = [
      '#type' => 'table',
      '#header' => [$this->t('Variável'), $this->t('Key'), $this->t('Obrigatória'), $this->t('Valor salvo'), $this->t('Estado')],
      '#rows' => $rows,
      '#empty' => $this->t('Nenhuma variável no contrato.'),
      '#attributes' => ['class' => ['aculta-portal-requirements']],
    ];
    $form['values'] = [
      '#type' => 'details',
      '#title' => $this->t('Cadastrar ou alterar valores'),
      '#open' => TRUE,
      '#description' => $this->t('Os valores são gravados no arquivo de credenciais do ambiente, fora do banco e fora do Git. Campo vazio mantém o valor atual.'),
    ] + $inputs;
    $form['actions'] = ['#type' => 'actions'];
    if (!$this->secrets->canSaveHere()) {
      $form['actions']['submit'] = ['#type' => 'submit', '#value' => $this->t('Salvar credenciais'), '#disabled' => TRUE];
      $form['storage_notice'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['messages', 'messages--warning']],
        'text' => ['#plain_text' => (string) $this->t('⚠ Atenção: neste ambiente as credenciais são armazenadas no banco de dados, criptografadas. O provisionamento é feito pelo ACULTA Deployer após o deploy; o formulário não grava aqui.')],
      ];
    }
    else {
      $form['actions']['submit'] = ['#type' => 'submit', '#value' => $this->t('Salvar credenciais')];
    }
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $submitted = [];
    foreach ($this->secrets->status() as $name => $row) {
      $submitted[$name] = (string) $form_state->getValue($name, '');
    }
    $result = $this->secrets->save($submitted);
    if ($result['ok']) {
      $this->messenger()->addStatus($this->t('@message', ['@message' => $result['message']]));
    }
    else {
      $this->messenger()->addError($this->t('Não salvo: @reason', ['@reason' => $result['message']]));
    }
    $form_state->setRedirect('aculta_portal.secrets_import');
  }

  /**
   * Célula do valor: máscara e botão de olho. O valor completo não entra no HTML; o botão pede
   * ao servidor (rota com token) só quando acionado.
   *
   * @return array<string, mixed>
   */
  private function valueCell(string $name, bool $present): array {
    if (!$present) {
      return ['#markup' => '<span class="aculta-secret-value">—</span>'];
    }
    $value = $this->secrets->value($name);
    $masked = $value === NULL ? '—' : SecretsManager::mask($value);
    // A rota tem _csrf_token: o gerador de URL já acrescenta ?token=. Não concatenar outro.
    $url = Url::fromRoute('aculta_portal.secrets_reveal', ['name' => $name])->toString();
    return [
      '#type' => 'container',
      '#attributes' => ['class' => ['aculta-secret-value']],
      'masked' => ['#markup' => '<span class="aculta-secret-value__text" data-masked="' . htmlspecialchars($masked, ENT_QUOTES) . '">' . htmlspecialchars($masked, ENT_QUOTES) . '</span>'],
      'eye' => [
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
      ],
    ];
  }

}
