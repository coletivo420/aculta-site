<?php

declare(strict_types=1);

namespace Drupal\Tests\aculta_portal\Unit;

use Drupal\aculta_portal\Captcha\TurnstileKeyOverride;
use Drupal\aculta_portal\Environment\DeployerEnvironment;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\Group;

/** Ambiente do deployer e escolha da chave do Turnstile (sem Kernel). */
#[Group('aculta_portal')]
final class DeployerEnvironmentTest extends UnitTestCase {

  private string $projectRoot;

  protected function setUp(): void {
    parent::setUp();
    $this->projectRoot = sys_get_temp_dir() . '/aculta-env-' . bin2hex(random_bytes(4));
    mkdir($this->projectRoot . '/var/deployer', 0777, TRUE);
    mkdir($this->projectRoot . '/web', 0777, TRUE);
  }

  protected function tearDown(): void {
    @unlink($this->projectRoot . '/var/deployer/environment.json');
    @rmdir($this->projectRoot . '/var/deployer');
    @rmdir($this->projectRoot . '/var');
    @rmdir($this->projectRoot . '/web');
    @rmdir($this->projectRoot);
    parent::tearDown();
  }

  private function writeEnvironment(string $value): void {
    file_put_contents($this->projectRoot . '/var/deployer/environment.json', json_encode(['schema' => 1, 'environment' => $value, 'site' => 'x', 'changed_at' => 'x']));
  }

  public function testFallbackWhenNoFileOrInvalidValue(): void {
    $this->assertSame('production', DeployerEnvironment::current($this->projectRoot));
    $this->writeEnvironment('development');
    $this->assertSame('production', DeployerEnvironment::current($this->projectRoot, 'production'));
  }

  public function testReadsDeployerValue(): void {
    $this->writeEnvironment('test');
    $this->assertSame('test', DeployerEnvironment::current($this->projectRoot));
  }

  public function testProductionKeyByDefaultAndTestKeyOnTestEnvironment(): void {
    $override = new TurnstileKeyOverride($this->projectRoot . '/web');
    $this->assertSame(['turnstile.settings' => ['keys' => 'turnstile']], $override->loadOverrides(['turnstile.settings']));
    $this->writeEnvironment('test');
    $this->assertSame(['turnstile.settings' => ['keys' => 'turnstile_test']], $override->loadOverrides(['turnstile.settings']));
    $this->assertSame([], $override->loadOverrides(['system.site']));
  }

}
