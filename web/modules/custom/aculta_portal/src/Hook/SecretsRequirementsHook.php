<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\Hook;

use Drupal\aculta_portal\Secrets\SecretsImporter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Relatório de status: avisa quando faltam credenciais obrigatórias e aponta para a importação.
 */
final class SecretsRequirementsHook {

  use StringTranslationTrait;

  public function __construct(
    #[Autowire(service: 'aculta_portal.secrets_importer')]
    private readonly SecretsImporter $importer,
    TranslationInterface $stringTranslation,
  ) {
    $this->setStringTranslation($stringTranslation);
  }

  /** @return array<string, array<string, mixed>> */
  #[Hook('runtime_requirements')]
  public function runtimeRequirements(): array {
    $missing = $this->importer->missingRequired();
    $title = $this->t('Credenciais do ambiente (ACULTA)');
    if ($missing === []) {
      return ['aculta_secrets' => [
        'title' => $title,
        'value' => $this->t('Todas as credenciais obrigatórias estão configuradas (@env).', ['@env' => $this->importer->environment()]),
        'severity' => REQUIREMENT_OK,
      ]];
    }
    $hint = $this->importer->stagedFiles() !== []
      ? $this->t('Há arquivo pronto para importar em /admin/config/aculta/segredos.')
      : $this->t('Copie o arquivo de credenciais para @dir e importe em /admin/config/aculta/segredos.', ['@dir' => $this->importer->importDir()]);
    return ['aculta_secrets' => [
      'title' => $title,
      'value' => $this->t('Faltam: @names. @hint', ['@names' => implode(', ', $missing), '@hint' => $hint]),
      'severity' => REQUIREMENT_WARNING,
    ]];
  }

}
