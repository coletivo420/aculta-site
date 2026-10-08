<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\Support\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/** Editorial settings for the institutional support information page. */
final class SettingsForm extends ConfigFormBase {

  public function getFormId(): string {
    return 'aculta_support_settings';
  }

  protected function getEditableConfigNames(): array {
    return ['aculta_portal.support'];
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['intro'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Introdução pública'),
      '#default_value' => $this->config('aculta_portal.support')->get('intro') ?? '',
    ];
    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('aculta_portal.support')
      ->set('intro', (string) $form_state->getValue('intro'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
