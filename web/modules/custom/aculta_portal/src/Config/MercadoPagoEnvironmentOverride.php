<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\Config;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryOverrideInterface;

/**
 * Supplies Mercado Pago credentials from the process environment at runtime.
 *
 * The contrib gateway stores credentials in its configuration entity by
 * default. This override keeps those values out of active/exported config.
 */
final class MercadoPagoEnvironmentOverride implements ConfigFactoryOverrideInterface {

  private const CONFIG_NAME = 'commerce_payment.commerce_payment_gateway.mercado_pago';

  /**
   * {@inheritdoc}
   */
  public function loadOverrides($names): array {
    if (!in_array(self::CONFIG_NAME, $names, TRUE)) {
      return [];
    }

    $configuration = [];
    foreach ([
      'MERCADOPAGO_PUBLIC_KEY' => 'public_key_test',
      'MERCADOPAGO_ACCESS_TOKEN' => 'access_token_test',
    ] as $environment_name => $configuration_name) {
      $value = getenv($environment_name);
      if ($value !== FALSE && $value !== '') {
        $configuration[$configuration_name] = $value;
      }
    }

    return $configuration ? [
      self::CONFIG_NAME => [
        'configuration' => $configuration,
      ],
    ] : [];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheSuffix(): string {
    return 'aculta_portal_mercado_pago_environment';
  }

  /**
   * {@inheritdoc}
   */
  public function createConfigObject($name, $collection = 'default') {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata($name): CacheableMetadata {
    return (new CacheableMetadata())->setCacheMaxAge(Cache::PERMANENT);
  }

}
