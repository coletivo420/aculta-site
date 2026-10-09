<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\Secrets\Form;

use Drupal\aculta_portal\Secrets\SecretsImporter;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Painel de importação de credenciais. Mostra só nomes e estado (presente ou ausente).
 */
final class SecretsImportForm extends FormBase implements ContainerInjectionInterface {

  public function __construct(private readonly SecretsImporter $importer) {}

  public static function create(ContainerInterface $container): static {
    return new static($container->get('aculta_portal.secrets_importer'));
  }

  public function getFormId(): string {
    return 'aculta_secrets_import';
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {
    $status = $this->importer->status();
    $rows = [];
    foreach ($status as $name => $row) {
      // Mesmo sistema de status do diagnóstico do Portal (PortalRequirementsController):
      // ✔ OK, ⚠ Atenção (opcional ausente) e ✖ Erro (obrigatória ausente).
      $state = $row['present'] ? 'ok' : ($row['required'] ? 'error' : 'attention');
      $label = match ($state) {
        'ok' => $this->t('✔ OK — presente'),
        'attention' => $this->t('⚠ Atenção — opcional ausente'),
        default => $this->t('✖ Erro — obrigatória ausente'),
      };
      $rows[] = [
        ['data' => ['#plain_text' => $name]],
        ['data' => ['#plain_text' => $row['key_id']]],
        ['data' => ['#plain_text' => $row['required'] ? $this->t('Sim') : $this->t('Não')]],
        [
          'data' => ['#plain_text' => $label],
          'class' => ['aculta-portal-requirements__status', 'aculta-portal-requirements__status--' . $state],
        ],
      ];
    }
    $form['#attached']['library'][] = 'aculta_portal/requirements-report';
    $form['legend'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['aculta-portal-requirements__legend'], 'aria-label' => (string) $this->t('Legenda de status')],
      'ok' => ['#plain_text' => (string) $this->t('✔ OK: chave presente no ambiente.')],
      'attention' => ['#plain_text' => (string) $this->t('⚠ Atenção: variável opcional sem valor.')],
      'error' => ['#plain_text' => (string) $this->t('✖ Erro: variável obrigatória sem valor.')],
    ];
    $form['status'] = [
      '#type' => 'table',
      '#caption' => $this->t('Ambiente: @env', ['@env' => $this->importer->environment()]),
      '#header' => [$this->t('Variável'), $this->t('Key'), $this->t('Obrigatória'), $this->t('Estado')],
      '#rows' => $rows,
      '#empty' => $this->t('Nenhuma variável no contrato.'),
      '#attributes' => ['class' => ['aculta-portal-requirements']],
    ];
    $files = $this->importer->stagedFiles();
    if ($files === []) {
      $form['empty'] = [
        '#markup' => '<p>' . $this->t('Nenhum arquivo .env na pasta de importação. Copie o arquivo (modo 0600) para a pasta e recarregue esta página.') . '</p>',
      ];
      $form['folder'] = ['#plain_text' => $this->importer->importDir()];
      return $form;
    }
    $form['file'] = [
      '#type' => 'radios',
      '#title' => $this->t('Arquivo de origem'),
      '#options' => array_combine($files, $files),
      '#required' => TRUE,
    ];
    $form['overwrite'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Substituir o arquivo de credenciais atual'),
      '#description' => $this->t('Necessário quando já existe um arquivo de credenciais.'),
    ];
    $form['confirm'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Entendo que o arquivo de origem será apagado após a importação.'),
      '#required' => TRUE,
    ];
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Importar e apagar a origem'),
    ];
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $result = $this->importer->import(
      (string) $form_state->getValue('file'),
      (bool) $form_state->getValue('overwrite'),
    );
    if ($result['ok']) {
      $this->messenger()->addStatus($this->t('@message Variáveis gravadas: @count.', [
        '@message' => $result['message'],
        '@count' => $result['count'],
      ]));
    }
    else {
      $this->messenger()->addError($this->t('Importação recusada: @reason', ['@reason' => $result['message']]));
    }
    $form_state->setRedirect('aculta_portal.secrets_import');
  }

}
