<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\Support\Controller;

use Drupal\aculta_portal\Support\Presentation\SupportHistoryPresenter;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/** Presents support records owned by Commerce to the current account. */
final class SupportController extends ControllerBase {

  public function __construct(
    private readonly SupportHistoryPresenter $presenter,
    private readonly AccountProxyInterface $currentAccount,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('aculta_portal.presentation.support_history'),
      $container->get('current_user'),
    );
  }

  public function mySupport(): array {
    $view = $this->presenter->present($this->currentAccount);

    return [
      'intro' => ['#plain_text' => $view['intro']],
      'table' => [
        '#type' => 'table',
        '#header' => [
          $this->t('Data'),
          $this->t('Valor'),
          $this->t('Situação'),
        ],
        '#rows' => array_map(
          static fn (array $row): array => [
            $row['date'],
            $row['amount'],
            $row['status'],
          ],
          $view['rows'],
        ),
        '#empty' => $view['empty'],
      ],
      'support_link' => [
        '#type' => 'link',
        '#title' => $view['action']['label'],
        '#url' => $view['action']['url'],
      ],
      '#cache' => [
        'contexts' => ['user', 'user.permissions'],
        'max-age' => 0,
      ],
    ];
  }

}
