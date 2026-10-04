<?php
/** Local transaction test; outgoing mail is captured by Core, never delivered. */
use Drupal\webform\Entity\WebformSubmission;
use Drupal\webform\WebformSubmissionForm;
if (!in_array(\Drupal::request()->getHost(), ['localhost', '127.0.0.1'], TRUE)) throw new RuntimeException('Local only.');
$transaction = \Drupal::database()->startTransaction();
$initial = count(\Drupal::state()->get('system.test_mail_collector', []));
try {
  \Drupal::configFactory()->getEditable('system.mail')->set('interface', ['default' => 'test_mail_collector', 'webform' => 'test_mail_collector'])->save();
  if (\Drupal::service('plugin.manager.mail')->getInstance(['module' => 'webform', 'key' => 'email'])::class !== \Drupal\Core\Mail\Plugin\Mail\TestMailCollector::class) throw new RuntimeException('Safe mail collector was not selected; do not submit.');
  $submission = WebformSubmission::create(['webform_id' => 'aculta_contact', 'uid' => 0, 'langcode' => 'pt-br', 'data' => ['name' => 'Verificação local', 'email' => '4e20coletivo@gmail.com', 'subject' => 'Teste local sem envio externo', 'message' => 'Validação técnica temporária do formulário.']]);
  $result = WebformSubmissionForm::submitWebformSubmission($submission);
  if (!$result instanceof WebformSubmission || !$result->id()) throw new RuntimeException('Contact validation/save failed.');
  $messages = \Drupal::state()->get('system.test_mail_collector', []);
  if (count($messages) !== $initial + 1 || end($messages)['to'] !== '4e20coletivo@gmail.com') throw new RuntimeException('Expected one institutional notification.');
  echo "Four-field submission validated and saved; notification captured locally, no external mail: OK\n";
} finally {
  $transaction->rollBack();
  \Drupal::configFactory()->reset('system.mail');
  \Drupal::entityTypeManager()->getStorage('webform_submission')->resetCache();
  \Drupal\Core\Cache\Cache::invalidateTags(['config:system.mail', 'webform_submission_list']);
}
echo "Temporary submission, mail collector and mail configuration rolled back.\n";
