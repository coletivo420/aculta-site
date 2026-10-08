<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\Support\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/** Presents support records owned by Commerce to the current account. */
final class SupportController implements ContainerInjectionInterface {

  public function __construct(
    private readonly EntityTypeManagerInterface $entities,
    private readonly AccountProxyInterface $currentUser,
    private readonly TranslationInterface $translation,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_user'),
      $container->get('string_translation'),
    );
  }

  public function mySupport(): array {
    $uid = (int) $this->currentUser->id();
    $orders = $this->entities->getStorage('commerce_order')->loadByProperties(['uid' => $uid]);
    $rows = [];
    $payment_storage = $this->entities->getStorage('commerce_payment');
    foreach ($orders as $order) {
      $has_donation_item = FALSE;
      foreach ($order->getItems() as $item) {
        if ($item->bundle() === 'donation') {
          $has_donation_item = TRUE;
          break;
        }
      }
      if (!$has_donation_item) {
        continue;
      }

      $total = $order->getTotalPrice();
      $payments = $payment_storage->loadByProperties(['order_id' => $order->id()]);
      $payment_states = array_map(static fn ($payment): string => $payment->getState()->getId(), $payments);
      $status = match (TRUE) {
        in_array('completed', $payment_states, TRUE) => $this->translation->translate('Aprovado'),
        in_array('authorization', $payment_states, TRUE) => $this->translation->translate('Em processamento'),
        in_array('partially_refunded', $payment_states, TRUE) => $this->translation->translate('Reembolso parcial'),
        in_array('refunded', $payment_states, TRUE) => $this->translation->translate('Reembolsado'),
        in_array('voided', $payment_states, TRUE) => $this->translation->translate('Cancelado'),
        $order->getState()->getId() === 'canceled' => $this->translation->translate('Não concluído'),
        default => $this->translation->translate('Aguardando pagamento'),
      };
      $rows[] = [
        gmdate('d/m/Y', $order->getPlacedTime() ?: $order->getCreatedTime()),
        $total ? $total->getCurrencyCode() . ' ' . $total->getNumber() : $this->translation->translate('A confirmar'),
        $status,
      ];
    }

    return [
      'intro' => ['#plain_text' => $this->translation->translate('Quando houver apoios vinculados à sua conta, eles aparecerão aqui.')],
      'table' => [
        '#type' => 'table',
        '#header' => [$this->translation->translate('Data'), $this->translation->translate('Valor'), $this->translation->translate('Situação')],
        '#rows' => $rows,
        '#empty' => $this->translation->translate('Ainda não há apoios vinculados a esta conta.'),
      ],
      'support_link' => [
        '#type' => 'link',
        '#title' => $this->translation->translate('Conheça as formas de apoio'),
        '#url' => Url::fromRoute('aculta_portal.support_form'),
      ],
      '#cache' => ['contexts' => ['user', 'user.permissions'], 'max-age' => 0],
    ];
  }

}
