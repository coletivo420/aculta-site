<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\Support\Presentation;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Url;

/**
 * Prepares the current account's support history from Commerce entities.
 */
final class SupportHistoryPresenter {

  public function __construct(
    private readonly EntityTypeManagerInterface $entities,
    private readonly TranslationInterface $translation,
  ) {}

  /**
   * @return array{
   *   intro: string,
   *   rows: array<int, array{date: string, amount: string, status: string, tone: string}>,
   *   empty: string,
   *   action: array{label: string, url: \Drupal\Core\Url}
   * }
   */
  public function present(AccountInterface $account): array {
    $orders = $this->entities->getStorage('commerce_order')->loadByProperties([
      'uid' => $account->id(),
    ]);
    $paymentStorage = $this->entities->getStorage('commerce_payment');
    $rows = [];

    foreach ($orders as $order) {
      // Access must be resolved before order items, total or payment state are
      // inspected. Ownership by UID alone is not an authorization decision.
      if (!$order->access('view', $account)) {
        continue;
      }

      $hasDonationItem = FALSE;
      foreach ($order->getItems() as $item) {
        if ($item->bundle() === 'donation') {
          $hasDonationItem = TRUE;
          break;
        }
      }
      if (!$hasDonationItem) {
        continue;
      }

      $payments = $paymentStorage->loadMultipleByOrder($order);
      $paymentStates = array_map(
        static fn ($payment): string => $payment->getState()->getId(),
        $payments,
      );
      [$status, $tone] = $this->paymentStatus(
        $paymentStates,
        (string) $order->getState()->getId(),
      );
      $total = $order->getTotalPrice();

      $rows[] = [
        'date' => gmdate('d/m/Y', $order->getPlacedTime() ?: $order->getCreatedTime()),
        'amount' => $total
          ? $total->getCurrencyCode() . ' ' . $total->getNumber()
          : (string) $this->translation->translate('A confirmar'),
        'status' => $status,
        'tone' => $tone,
      ];
    }

    return [
      'intro' => (string) $this->translation->translate(
        'Quando houver apoios vinculados à sua conta, eles aparecerão aqui.'
      ),
      'rows' => $rows,
      'empty' => (string) $this->translation->translate(
        'Ainda não há apoios vinculados a esta conta.'
      ),
      'action' => [
        'label' => (string) $this->translation->translate('Conheça as formas de apoio'),
        'url' => Url::fromRoute('aculta_portal.support_form'),
      ],
    ];
  }

  /**
   * @param string[] $paymentStates
   *
   * @return array{0: string, 1: string}
   */
  private function paymentStatus(array $paymentStates, string $orderState): array {
    return match (TRUE) {
      in_array('completed', $paymentStates, TRUE) => [
        (string) $this->translation->translate('Aprovado'),
        'success',
      ],
      in_array('authorization', $paymentStates, TRUE) => [
        (string) $this->translation->translate('Em processamento'),
        'info',
      ],
      in_array('partially_refunded', $paymentStates, TRUE) => [
        (string) $this->translation->translate('Reembolso parcial'),
        'warning',
      ],
      in_array('refunded', $paymentStates, TRUE) => [
        (string) $this->translation->translate('Reembolsado'),
        'neutral',
      ],
      in_array('voided', $paymentStates, TRUE) => [
        (string) $this->translation->translate('Cancelado'),
        'danger',
      ],
      $orderState === 'canceled' => [
        (string) $this->translation->translate('Não concluído'),
        'danger',
      ],
      default => [
        (string) $this->translation->translate('Aguardando pagamento'),
        'warning',
      ],
    };
  }

}
