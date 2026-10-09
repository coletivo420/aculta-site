<?php

declare(strict_types=1);

namespace Drupal\Tests\aculta_portal\Unit;

use Drupal\aculta_portal\Secrets\SecretsFormat;
use Drupal\aculta_portal\Secrets\SecretsImporter;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Site\Settings;
use Drupal\key\KeyRepositoryInterface;
use Drupal\Tests\UnitTestCase;

/** Formato do arquivo de credenciais e importação com diretório temporário. */
#[\PHPUnit\Framework\Attributes\Group('aculta_portal')]
final class SecretsImporterTest extends UnitTestCase {

  private string $base;

  protected function setUp(): void {
    parent::setUp();
    $this->base = sys_get_temp_dir() . '/aculta-secrets-unit-' . bin2hex(random_bytes(6));
    mkdir($this->base . '/web', 0700, TRUE);
    mkdir($this->base . '/config', 0700, TRUE);
    mkdir($this->base . '/secrets/import', 0700, TRUE);
    file_put_contents($this->base . '/config/secrets-contract.json', json_encode([
      'variables' => [
        ['name' => 'GOOGLE_OAUTH_CLIENT_ID', 'key_id' => 'g_id', 'required_in' => ['production', 'test']],
        ['name' => 'SMTP2GO_PASSWORD', 'key_id' => 'smtp_pw', 'required_in' => ['test']],
        ['name' => 'TURNSTILE_KEYS_JSON', 'key_id' => 'turnstile', 'required_in' => []],
      ],
    ]));
  }

  protected function tearDown(): void {
    $this->rrmdir($this->base);
    parent::tearDown();
  }

  public function testParseIgnoresCommentsAndKeepsLiteralEquals(): void {
    $this->assertSame(
      ['A_B' => 'x=y', 'C' => '1'],
      SecretsFormat::parse("# nota\n\nA_B=x=y\nC=1\n"),
    );
    $this->assertNull(SecretsFormat::parse("minuscula=1\n"));
    $this->assertNull(SecretsFormat::parse("SEM_IGUAL\n"));
  }

  public function testModeProblemFlagsOtherAndGroupWriteAccess(): void {
    $this->assertNull(SecretsFormat::modeProblem(0100600));
    $this->assertNotNull(SecretsFormat::modeProblem(0100604));
  }

  public function testImportRefusesExistingStoreWithoutOverwrite(): void {
    $this->writeStore('OLD=1');
    $this->stageFile('GOOGLE_OAUTH_CLIENT_ID=abc' . "\n" . 'SMTP2GO_PASSWORD=pw' . "\n");
    $result = $this->importer()->import('novo.env', FALSE);
    $this->assertFalse($result['ok']);
    $this->assertSame('OLD=1', trim((string) file_get_contents($this->store())));
    $this->assertFileExists($this->base . '/secrets/import/novo.env');
  }

  public function testImportWritesStoreAndShredsSource(): void {
    $this->writeStore('OLD=1');
    $this->stageFile("GOOGLE_OAUTH_CLIENT_ID=abc\nSMTP2GO_PASSWORD=pw\n");
    $result = $this->importer()->import('novo.env', TRUE);
    $this->assertTrue($result['ok']);
    $this->assertTrue($result['source_deleted']);
    $this->assertSame(2, $result['count']);
    $this->assertFileDoesNotExist($this->base . '/secrets/import/novo.env');
    $this->assertSame("GOOGLE_OAUTH_CLIENT_ID=abc\nSMTP2GO_PASSWORD=pw\n", (string) file_get_contents($this->store()));
  }

  public function testImportRefusesUnknownVariables(): void {
    $this->stageFile("GOOGLE_OAUTH_CLIENT_ID=abc\nDESCONHECIDA=1\n");
    $result = $this->importer()->import('novo.env', FALSE);
    $this->assertFalse($result['ok']);
    $this->assertStringContainsString('DESCONHECIDA', $result['message']);
    $this->assertFileExists($this->base . '/secrets/import/novo.env');
  }

  public function testImportRefusesMissingRequiredVariables(): void {
    $this->stageFile("GOOGLE_OAUTH_CLIENT_ID=abc\n");
    $result = $this->importer()->import('novo.env', FALSE);
    $this->assertFalse($result['ok']);
    $this->assertStringContainsString('SMTP2GO_PASSWORD', $result['message']);
    $this->assertFileExists($this->base . '/secrets/import/novo.env');
  }

  public function testImportRejectsNamesOutsideStagingFolder(): void {
    $result = $this->importer()->import('../config/secrets-contract.json', TRUE);
    $this->assertFalse($result['ok']);
  }

  public function testShredRemovesFile(): void {
    $path = $this->base . '/apagar.env';
    file_put_contents($path, str_repeat('x', 20000));
    $this->assertTrue(SecretsImporter::shred($path));
    $this->assertFileDoesNotExist($path);
  }

  private function importer(): SecretsImporter {
    $keys = $this->createMock(KeyRepositoryInterface::class);
    $logger = $this->createMock(LoggerChannelInterface::class);
    $factory = $this->createMock(LoggerChannelFactoryInterface::class);
    $factory->method('get')->willReturn($logger);
    $settings = new Settings([
      'aculta_secrets_file' => $this->store(),
      'aculta_secrets_environment' => 'test',
    ]);
    return new SecretsImporter($keys, $settings, $factory, $this->base . '/web');
  }

  private function store(): string {
    return $this->base . '/secrets/aculta.secrets.env';
  }

  private function writeStore(string $content): void {
    file_put_contents($this->store(), $content);
    chmod($this->store(), 0600);
  }

  private function stageFile(string $content): void {
    $path = $this->base . '/secrets/import/novo.env';
    file_put_contents($path, $content);
    chmod($path, 0600);
  }

  private function rrmdir(string $dir): void {
    if (!is_dir($dir)) {
      return;
    }
    foreach (scandir($dir) ?: [] as $entry) {
      if ($entry === '.' || $entry === '..') {
        continue;
      }
      $path = $dir . '/' . $entry;
      is_dir($path) ? $this->rrmdir($path) : @unlink($path);
    }
    @rmdir($dir);
  }

}
