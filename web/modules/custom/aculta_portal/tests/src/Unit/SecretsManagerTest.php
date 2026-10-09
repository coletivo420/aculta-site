<?php

declare(strict_types=1);

namespace Drupal\Tests\aculta_portal\Unit;

use Drupal\aculta_portal\Secrets\SecretsFormat;
use Drupal\aculta_portal\Secrets\SecretsManager;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Site\Settings;
use Drupal\key\KeyRepositoryInterface;
use Drupal\Tests\UnitTestCase;

/**
 * Credenciais do ambiente: máscara, salvamento com merge e regras de armazenamento.
 */
#[\PHPUnit\Framework\Attributes\Group('aculta_portal')]
final class SecretsManagerTest extends UnitTestCase {

  private string $base;

  protected function setUp(): void {
    parent::setUp();
    $this->base = sys_get_temp_dir() . '/aculta-manager-' . bin2hex(random_bytes(6));
    mkdir($this->base . '/web', 0700, TRUE);
    mkdir($this->base . '/config', 0700, TRUE);
    mkdir($this->base . '/secrets', 0700, TRUE);
    file_put_contents($this->base . '/config/secrets-contract.json', json_encode([
      'variables' => [
        ['name' => 'GOOGLE_OAUTH_CLIENT_ID', 'key_id' => 'g_id', 'required_in' => ['production', 'test']],
        ['name' => 'SMTP2GO_PASSWORD', 'key_id' => 'smtp_pw', 'required_in' => ['test']],
        ['name' => 'TURNSTILE_KEYS_JSON', 'key_id' => 'turnstile', 'required_in' => []],
      ],
    ]));
  }

  protected function tearDown(): void {
    foreach (glob($this->base . '/secrets/*') ?: [] as $f) {
      @unlink($f);
    }
    @rmdir($this->base . '/secrets');
    @unlink($this->base . '/config/secrets-contract.json');
    @rmdir($this->base . '/config');
    @rmdir($this->base . '/web');
    @rmdir($this->base);
    parent::tearDown();
  }

  public function testMaskKeepsOnlyEdgesAndHidesShortValues(): void {
    $this->assertSame('ab••••••••kl', SecretsManager::mask('abcdefghijkl'));
    $this->assertSame('••••••••', SecretsManager::mask('abc'));
  }

  public function testSaveMergesAndKeepsValuesLeftEmpty(): void {
    file_put_contents($this->store(), "GOOGLE_OAUTH_CLIENT_ID=antigo\nSMTP2GO_PASSWORD=senha-antiga\n");
    $result = $this->manager()->save(['GOOGLE_OAUTH_CLIENT_ID' => 'novo', 'SMTP2GO_PASSWORD' => '', 'TURNSTILE_KEYS_JSON' => '']);
    $this->assertTrue($result['ok']);
    $this->assertSame(['GOOGLE_OAUTH_CLIENT_ID'], $result['updated']);
    $this->assertSame(
      ['GOOGLE_OAUTH_CLIENT_ID' => 'novo', 'SMTP2GO_PASSWORD' => 'senha-antiga'],
      SecretsFormat::parse((string) file_get_contents($this->store())),
    );
    $this->assertSame(0640, fileperms($this->store()) & 0777);
  }

  public function testSaveRejectsUnknownNamesAndMultilineValues(): void {
    $this->assertFalse($this->manager()->save(['NAO_EXISTE' => 'x'])['ok']);
    $this->assertFalse($this->manager()->save(['GOOGLE_OAUTH_CLIENT_ID' => "a\nb"])['ok']);
    $this->assertFileDoesNotExist($this->store());
  }

  public function testDatabaseStorageRefusesToWriteFile(): void {
    $manager = $this->manager('database');
    $this->assertFalse($manager->canSaveHere());
    $this->assertFalse($manager->save(['GOOGLE_OAUTH_CLIENT_ID' => 'x'])['ok']);
    $this->assertFileDoesNotExist($this->store());
  }

  private function manager(string $storage = 'file'): SecretsManager {
    $keys = $this->createMock(KeyRepositoryInterface::class);
    $logger = $this->createMock(LoggerChannelInterface::class);
    $factory = $this->createMock(LoggerChannelFactoryInterface::class);
    $factory->method('get')->willReturn($logger);
    $settings = new Settings([
      'aculta_secrets_file' => $this->store(),
      'aculta_secrets_environment' => 'test',
      'aculta_secrets_storage' => $storage,
    ]);
    return new SecretsManager($keys, $settings, $factory, $this->base . '/web');
  }

  private function store(): string {
    return $this->base . '/secrets/aculta.secrets.env';
  }

}
