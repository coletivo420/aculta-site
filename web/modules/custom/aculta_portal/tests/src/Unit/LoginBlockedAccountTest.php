<?php

declare(strict_types=1);

namespace Drupal\Tests\aculta_portal\Unit;

use Drupal\aculta_portal\Form\PortalFormCallbacks;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\user\UserAuthenticationInterface;
use Drupal\user\UserInterface;
use PHPUnit\Framework\Attributes\Group;

/**
 * Blocked accounts must not be distinguishable from unknown ones on login.
 */
#[Group('aculta_portal')]
final class LoginBlockedAccountTest extends UnitTestCase {

  private function callbacks(UserAuthenticationInterface $auth): PortalFormCallbacks {
    return new PortalFormCallbacks(
      $this->createMock(\Drupal\Core\Messenger\MessengerInterface::class),
      $this->createMock(\Drupal\Core\Session\AccountProxyInterface::class),
      $this->createMock(\Drupal\user\UserDataInterface::class),
      $this->createMock(\Drupal\Core\Entity\EntityTypeManagerInterface::class),
      $this->createMock(\Drupal\Core\StringTranslation\TranslationInterface::class),
      $auth,
    );
  }

  private function formState(string $name, FormInterface $form_object): FormStateInterface {
    $state = $this->createMock(FormStateInterface::class);
    $state->method('getValue')->with('name')->willReturn($name);
    $state->method('getFormObject')->willReturn($form_object);
    return $state;
  }

  public function testBlockedAccountSkipsCoreAuthenticationStep(): void {
    $account = $this->createMock(UserInterface::class);
    $account->method('isBlocked')->willReturn(TRUE);
    $auth = $this->createMock(UserAuthenticationInterface::class);
    $auth->method('lookupAccount')->willReturn($account);
    $form_object = $this->createMock(\Drupal\user\Form\UserLoginForm::class);
    $form_object->expects($this->never())->method('validateAuthentication');

    $form = [];
    $this->callbacks($auth)->validateLoginAuthentication($form, $this->formState('blocked@example.invalid', $form_object));
    $this->addToAssertionCount(1);
  }

  public function testActiveAccountGoesThroughCoreAuthentication(): void {
    $account = $this->createMock(UserInterface::class);
    $account->method('isBlocked')->willReturn(FALSE);
    $auth = $this->createMock(UserAuthenticationInterface::class);
    $auth->method('lookupAccount')->willReturn($account);
    $form_object = $this->createMock(\Drupal\user\Form\UserLoginForm::class);
    $form_object->expects($this->once())->method('validateAuthentication');

    $form = [];
    $this->callbacks($auth)->validateLoginAuthentication($form, $this->formState('active@example.invalid', $form_object));
  }

  public function testUnknownAccountGoesThroughCoreAuthentication(): void {
    $auth = $this->createMock(UserAuthenticationInterface::class);
    $auth->method('lookupAccount')->willReturn(FALSE);
    $form_object = $this->createMock(\Drupal\user\Form\UserLoginForm::class);
    $form_object->expects($this->once())->method('validateAuthentication');

    $form = [];
    $this->callbacks($auth)->validateLoginAuthentication($form, $this->formState('nobody@example.invalid', $form_object));
  }

}
