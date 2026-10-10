<?php

declare(strict_types=1);

namespace Drupal\Tests\aculta_portal\Kernel;

use Drupal\aculta_portal\Account\RegistrationMailPolicy;
use Drupal\KernelTests\KernelTestBase;
use Drupal\user\Entity\User;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Cadastro com e-mail já usado (DT-P06): a política encontra a conta e avisa o dono, sem criar nada.
 */
#[Group('aculta_portal')]
#[RunTestsInSeparateProcesses]
final class RegistrationMailPolicyKernelTest extends KernelTestBase {

  protected static $modules = ['system', 'user'];

  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installSchema('user', ['users_data']);
  }

  public function testExistingAccountIsFoundOnlyForRegisteredMail(): void {
    $owner = User::create(['name' => 'titular', 'mail' => 'titular@example.invalid', 'status' => 1]);
    $owner->save();
    $policy = $this->policy();
    $this->assertSame((int) $owner->id(), (int) $policy->existingAccount('titular@example.invalid')?->id());
    $this->assertNull($policy->existingAccount('ninguem@example.invalid'));
    $this->assertNull($policy->existingAccount('   '));
  }

  public function testOwnerIsNotifiedAndNoAccountIsCreated(): void {
    $owner = User::create(['name' => 'titular2', 'mail' => 'titular2@example.invalid', 'status' => 1]);
    $owner->save();
    $before = count(User::loadMultiple());

    $sent = $this->policy()->notifyExistingAccount($owner);

    $this->assertTrue($sent);
    $mails = $this->container->get('state')->get('system.test_mail_collector', []);
    $this->assertNotEmpty($mails);
    $last = end($mails);
    $this->assertSame('titular2@example.invalid', $last['to']);
    $this->assertSame(RegistrationMailPolicy::MAIL_KEY, $last['key']);
    $this->assertSame($before, count(User::loadMultiple()), 'Nenhuma conta nova foi criada.');
  }

  private function policy(): RegistrationMailPolicy {
    return new RegistrationMailPolicy(
      $this->container->get('entity_type.manager'),
      $this->container->get('plugin.manager.mail'),
    );
  }

}
