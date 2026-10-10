<?php

declare(strict_types=1);

namespace Drupal\Tests\aculta_portal\Kernel;

use Drupal\aculta_portal\Captcha\TurnstileToggle;
use Drupal\captcha\Entity\CaptchaPoint;
use Drupal\KernelTests\KernelTestBase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/** Política de CAPTCHA (S4/DT-P08): o Turnstile liga e desliga em todos os pontos, sem tocar em outros desafios. */
#[Group('aculta_portal')]
#[RunTestsInSeparateProcesses]
final class TurnstileToggleKernelTest extends KernelTestBase {

  protected static $modules = ['system', 'user', 'key', 'captcha'];

  private TurnstileToggle $toggle;

  protected function setUp(): void {
    parent::setUp();
    $this->installConfig(['captcha']);
    // O módulo captcha já traz pontos padrão: ajusta-os para o cenário do teste, sem criar duplicados.
    $this->point('user_login_form', TurnstileToggle::CHALLENGE);
    $this->point('user_register_form', TurnstileToggle::CHALLENGE);
    $this->point('user_pass', 'captcha/Math');
    $this->toggle = new TurnstileToggle(
      $this->container->get('config.factory'),
      $this->container->get('entity_type.manager'),
      $this->container->get('key.repository'),
      $this->container->get('logger.factory'),
    );
  }

  private function point(string $formId, string $type): void {
    $point = CaptchaPoint::load($formId) ?? CaptchaPoint::create(['formId' => $formId]);
    $point->set('captchaType', $type);
    $point->setStatus(TRUE);
    $point->save();
  }

  public function testDisableTurnsOffOnlyTurnstilePoints(): void {
    $this->toggle->setEnabled(FALSE, 'teste');
    $this->assertFalse($this->toggle->isEnabled());
    $this->assertFalse((bool) $this->config('captcha.settings')->get('enable_globally'));
    $this->assertFalse((bool) CaptchaPoint::load('user_login_form')->status());
    $this->assertFalse((bool) CaptchaPoint::load('user_register_form')->status());
    $this->assertTrue((bool) CaptchaPoint::load('user_pass')->status(), 'Desafio de outro tipo não muda.');
  }

  public function testEnableRestoresTurnstilePoints(): void {
    $this->toggle->setEnabled(FALSE, 'teste');
    $this->toggle->setEnabled(TRUE, 'teste');
    $this->assertTrue($this->toggle->isEnabled());
    $this->assertTrue((bool) $this->config('captcha.settings')->get('enable_globally'));
    $this->assertTrue((bool) CaptchaPoint::load('user_login_form')->status());
  }

  public function testKeyIsNotConfiguredWithoutSecretValue(): void {
    $this->assertFalse($this->toggle->keyConfigured());
  }

}
