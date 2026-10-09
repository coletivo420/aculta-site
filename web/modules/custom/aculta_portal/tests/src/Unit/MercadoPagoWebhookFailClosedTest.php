<?php

declare(strict_types=1);

namespace Drupal\Tests\aculta_portal\Unit;

use Drupal\aculta_portal\Commerce\MercadoPago\WebhookGuard;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;

/**
 * Mercado Pago notifications fail closed: nothing is accepted unless every
 * precondition holds. No external call is made; the secret is a test value.
 */
#[Group('aculta_portal')]
final class MercadoPagoWebhookFailClosedTest extends UnitTestCase {

  private const FAKE_SECRET = 'test-only-secret-not-real';

  private function post(array $headers = [], string $body = '{"data":{"id":"1"},"type":"payment"}'): Request {
    $request = Request::create('/integracoes/pagamentos/mercado_pago/notificacao?data.id=1&type=payment', 'POST', [], [], [], [], $body);
    foreach ($headers as $name => $value) {
      $request->headers->set($name, $value);
    }
    return $request;
  }

  private function guard(): WebhookGuard {
    return new WebhookGuard();
  }

  public function testDisabledGatewayIsUnavailable(): void {
    $response = $this->guard()->validateAndNormalize($this->post(), FALSE, self::FAKE_SECRET);
    $this->assertSame(503, $response?->getStatusCode());
  }

  public function testMissingSecretIsUnavailable(): void {
    $this->assertSame(503, $this->guard()->validateAndNormalize($this->post(), TRUE, NULL)?->getStatusCode());
    $this->assertSame(503, $this->guard()->validateAndNormalize($this->post(), TRUE, '   ')?->getStatusCode());
  }

  public function testGetIsNotAllowed(): void {
    $request = Request::create('/integracoes/pagamentos/mercado_pago/notificacao', 'GET');
    $response = $this->guard()->validateAndNormalize($request, TRUE, self::FAKE_SECRET);
    $this->assertSame(405, $response?->getStatusCode());
    $this->assertSame('POST', $response?->headers->get('Allow'));
  }

  public function testMissingSignatureIsUnauthorized(): void {
    $response = $this->guard()->validateAndNormalize($this->post(['x-request-id' => 'req-1']), TRUE, self::FAKE_SECRET);
    $this->assertSame(401, $response?->getStatusCode());
  }

  public function testMissingRequestIdIsUnauthorized(): void {
    $response = $this->guard()->validateAndNormalize($this->post(['x-signature' => 'ts=1,v1=00']), TRUE, self::FAKE_SECRET);
    $this->assertSame(401, $response?->getStatusCode());
  }

  public function testForgedSignatureIsRejected(): void {
    $headers = ['x-signature' => 'ts=1700000000,v1=' . str_repeat('0', 64), 'x-request-id' => 'req-1'];
    $response = $this->guard()->validateAndNormalize($this->post($headers), TRUE, self::FAKE_SECRET);
    $this->assertSame(401, $response?->getStatusCode());
  }

  public function testUnsignedLegacyTopicNotificationIsRejected(): void {
    // Unsigned legacy IPN (topic/id) is never forwarded: no signature, no entry.
    $request = Request::create('/integracoes/pagamentos/mercado_pago/notificacao?topic=payment&id=1', 'POST');
    $response = $this->guard()->validateAndNormalize($request, TRUE, self::FAKE_SECRET);
    $this->assertSame(401, $response?->getStatusCode());
  }

  public function testSignedHeadersWithLegacyTopicAreBadRequest(): void {
    $request = Request::create('/integracoes/pagamentos/mercado_pago/notificacao?topic=payment&id=1', 'POST');
    $request->headers->set('x-signature', 'ts=1700000000,v1=' . str_repeat('0', 64));
    $request->headers->set('x-request-id', 'req-1');
    $response = $this->guard()->validateAndNormalize($request, TRUE, self::FAKE_SECRET);
    $this->assertSame(400, $response?->getStatusCode());
  }

}
