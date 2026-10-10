<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\Commerce\MercadoPago;

use Drupal\aculta_portal\Environment\DeployerEnvironment;

/**
 * Credenciais do Mercado Pago por ambiente, lidas do ambiente do processo.
 *
 * O ambiente vem do ACULTA Deployer (var/deployer/environment.json); aqui não há troca de ambiente.
 * - Ambiente de testes: modo `test` do gateway, com as credenciais de teste.
 * - Ambiente de produção: modo `live` do gateway, com as credenciais de produção.
 * Client ID e Client Secret identificam a aplicação e valem para os dois ambientes.
 *
 * Os valores ficam fora da configuração exportada. Campo ausente vira campo vazio no gateway,
 * que falha fechado (ver EntitySaveHooks).
 */
final class MercadoPagoCredentials {

  public const GATEWAY_ID = 'mercado_pago';

  private const ENVIRONMENTS = [
    'test' => [
      'mode' => 'test',
      'public_key_variable' => 'MERCADOPAGO_TEST_PUBLIC_KEY',
      'access_token_variable' => 'MERCADOPAGO_TEST_ACCESS_TOKEN',
      'public_key_field' => 'public_key_test',
      'access_token_field' => 'access_token_test',
    ],
    'production' => [
      'mode' => 'live',
      'public_key_variable' => 'MERCADOPAGO_PRODUCTION_PUBLIC_KEY',
      'access_token_variable' => 'MERCADOPAGO_PRODUCTION_ACCESS_TOKEN',
      'public_key_field' => 'public_key_prod',
      'access_token_field' => 'access_token_prod',
    ],
  ];

  /** Ambiente que o ACULTA Deployer declarou (produção quando não há declaração). */
  public static function currentEnvironment(string $projectRoot): string {
    return DeployerEnvironment::current($projectRoot, 'production');
  }

  /**
   * Campos do gateway para o ambiente, sem valores vazios.
   *
   * @return array<string, string>
   */
  public static function configuration(string $environment): array {
    $spec = self::spec($environment);
    $fields = ['mode' => $spec['mode']];
    $sources = [
      $spec['public_key_field'] => $spec['public_key_variable'],
      $spec['access_token_field'] => $spec['access_token_variable'],
      'client_id' => 'MERCADOPAGO_CLIENT_ID',
      'client_secret' => 'MERCADOPAGO_CLIENT_SECRET',
    ];
    foreach ($sources as $field => $variable) {
      $value = self::read($variable);
      if ($value !== NULL) {
        $fields[$field] = $value;
      }
    }
    return $fields;
  }

  /** Verdadeiro quando o ambiente tem Public Key e Access Token para o modo dele. */
  public static function hasRuntimeCredentials(string $environment): bool {
    $spec = self::spec($environment);
    return self::read($spec['public_key_variable']) !== NULL
      && self::read($spec['access_token_variable']) !== NULL;
  }

  /** @return array{mode: string, public_key_variable: string, access_token_variable: string, public_key_field: string, access_token_field: string} */
  private static function spec(string $environment): array {
    return self::ENVIRONMENTS[$environment] ?? self::ENVIRONMENTS['production'];
  }

  private static function read(string $name): ?string {
    $value = getenv($name);
    return $value === FALSE || $value === '' ? NULL : $value;
  }

}
