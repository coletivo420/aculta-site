<?php

declare(strict_types=1);

namespace Drupal\Tests\aculta_portal\Unit;

use Drupal\aculta_portal\Hook\FormHooks;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * The login form always runs the Portal callback exactly once, whatever Core's
 * validator list looks like. A silent miss would restore the disclosure.
 */
#[Group('aculta_portal')]
final class LoginValidatorSwapTest extends UnitTestCase {

  private const WRAPPER = 'aculta_portal.form_callbacks:validateLoginAuthentication';

  private function validators(array $start): array {
    $hooks = new FormHooks(
      $this->createMock(CurrentRouteMatch::class),
      $this->createMock(AccountProxyInterface::class),
      $this->createMock(TranslationInterface::class),
      new \Drupal\aculta_portal\Account\EmailConfirmationPolicy($this->createMock(\Drupal\user\UserDataInterface::class), $this->createMock(\Drupal\Core\Entity\EntityTypeManagerInterface::class)),
    );
    $form = ['#validate' => $start];
    $state = $this->createMock(FormStateInterface::class);
    $state->method('getFormObject')->willReturn(NULL);
    $hooks->formAlter($form, $state, 'user_login_form');
    return $form['#validate'];
  }

  public function testCoreValidatorIsReplacedInPlace(): void {
    $this->assertSame(
      [self::WRAPPER, '::validateFinal'],
      $this->validators(['::validateAuthentication', '::validateFinal']),
    );
  }

  public function testWrapperIsAddedFirstWhenCoreValidatorIsMissing(): void {
    $this->assertSame(
      [self::WRAPPER, '::validateFinal'],
      $this->validators(['::validateFinal']),
    );
  }

  public function testWrapperAppearsExactlyOnce(): void {
    $result = $this->validators([self::WRAPPER, '::validateAuthentication', '::validateFinal']);
    $this->assertSame(1, count(array_filter($result, static fn($v) => $v === self::WRAPPER)));
    $this->assertNotContains('::validateAuthentication', $result);
  }

}
