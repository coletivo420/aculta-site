<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\Controller;

use Drupal\aculta_portal\AccountCoursesManager;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/** Presents the current user's LMS memberships inside ACCOUNT. */
final class AccountCoursesController extends ControllerBase {

  public function __construct(
    private readonly AccountCoursesManager $courses,
    private readonly AccountProxyInterface $currentAccount,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('aculta_portal.account_courses'),
      $container->get('current_user'),
    );
  }

  public function page(): array {
    $items = $this->courses->getCourses($this->currentAccount);
    $build = [
      '#type' => 'container',
      '#attributes' => ['class' => ['aculta-account-courses']],
      '#cache' => [
        'contexts' => ['user', 'user.permissions'],
        'max-age' => 0,
      ],
    ];

    if ($items === []) {
      $build['empty'] = [
        '#type' => 'container',
        'message' => ['#plain_text' => $this->t('Você ainda não está participando de nenhum curso.')],
      ];
      if ($catalog = $this->courses->catalogUrl()) {
        $build['empty']['link'] = [
          '#type' => 'link',
          '#title' => $this->t('Ver cursos disponíveis'),
          '#url' => $catalog,
          '#attributes' => ['class' => ['btn', 'btn-primary']],
        ];
      }
      return $build;
    }

    foreach ($items as $delta => $course) {
      $card = [
        '#type' => 'container',
        '#attributes' => ['class' => ['aculta-account-course']],
        '#cache' => ['tags' => $course['cache_tags']],
        'title' => ['#type' => 'html_tag', '#tag' => 'h3', '#value' => $course['label']],
      ];
      if ($course['description'] !== '') {
        $card['description'] = ['#plain_text' => $course['description']];
      }
      $card['meta'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['aculta-account-course__meta']],
        'status' => ['#plain_text' => $this->t('Status: @status', ['@status' => $course['status_label']])],
      ];
      if ($course['score'] !== NULL) {
        $card['meta']['score'] = ['#plain_text' => $this->t('Resultado: @score%', ['@score' => $course['score']])];
      }
      if ($course['url']) {
        $card['action'] = [
          '#type' => 'link',
          '#title' => $course['finished'] ? $this->t('Ver resultado') : $this->t('Acessar curso'),
          '#url' => $course['url'],
          '#attributes' => ['class' => ['btn', 'btn-primary']],
        ];
      }
      $build['course_' . $delta] = $card;
    }

    return $build;
  }

}
