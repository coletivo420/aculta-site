<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\Presentation;

use Drupal\aculta_portal\AccountCoursesManager;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\lms\Entity\CourseStatusInterface;

/**
 * Prepares ACCOUNT course view-models without owning LMS/Group state.
 *
 * AccountCoursesManager remains responsible for authorized source data and
 * cross-domain course URLs. This presenter adds user-facing semantics that can
 * feed the current fallback render arrays or a future theme component.
 */
final class AccountCoursePresenter {

  public function __construct(
    private readonly AccountCoursesManager $courses,
    private readonly TranslationInterface $translation,
  ) {}

  /**
   * Builds the view-model for the current account's course section.
   *
   * @return array{
   *   items: array<int, array{
   *     id: string,
   *     title: string,
   *     description: array,
   *     status: array{code: string, label: string, tone: string},
   *     score: int|float|string|null,
   *     score_label: string|null,
   *     finished: bool,
   *     cta: array{label: string, url: \Drupal\Core\Url, kind: string}|null,
   *     cache_tags: string[]
   *   }>,
   *   empty: array{
   *     message: string,
   *     cta: array{label: string, url: \Drupal\Core\Url, kind: string}|null
   *   }
   * }
   */
  public function present(AccountInterface $account): array {
    $items = [];

    foreach ($this->courses->getCourses($account) as $course) {
      $statusCode = $course['status'] !== '' ? (string) $course['status'] : 'not_started';
      $score = $course['score'];
      $url = $course['url'];

      $items[] = [
        'id' => (string) $course['id'],
        'title' => (string) $course['label'],
        'description' => $course['description'],
        'status' => $this->statusPresentation($statusCode),
        'score' => $score,
        'score_label' => $score !== NULL
          ? (string) $this->translation->translate('Resultado: @score%', ['@score' => $score])
          : NULL,
        'finished' => (bool) $course['finished'],
        'cta' => $url !== NULL ? [
          'label' => (string) $this->translation->translate(
            $course['finished'] ? 'Ver resultado' : 'Acessar curso'
          ),
          'url' => $url,
          'kind' => 'primary',
        ] : NULL,
        'cache_tags' => $course['cache_tags'],
      ];
    }

    $catalog = $this->courses->catalogUrl();

    return [
      'items' => $items,
      'empty' => [
        'message' => (string) $this->translation->translate('Você ainda não está participando de nenhum curso.'),
        'cta' => $catalog !== NULL ? [
          'label' => (string) $this->translation->translate('Ver cursos disponíveis'),
          'url' => $catalog,
          'kind' => 'primary',
        ] : NULL,
      ],
    ];
  }

  /**
   * Maps LMS state to the shared semantic presentation contract.
   *
   * @return array{code: string, label: string, tone: string}
   */
  private function statusPresentation(string $status): array {
    return match ($status) {
      CourseStatusInterface::STATUS_PROGRESS => [
        'code' => $status,
        'label' => (string) $this->translation->translate('Em andamento'),
        'tone' => 'info',
      ],
      CourseStatusInterface::STATUS_PASSED => [
        'code' => $status,
        'label' => (string) $this->translation->translate('Concluído'),
        'tone' => 'success',
      ],
      CourseStatusInterface::STATUS_FAILED => [
        'code' => $status,
        'label' => (string) $this->translation->translate('Não aprovado'),
        'tone' => 'danger',
      ],
      CourseStatusInterface::STATUS_NEEDS_EVALUATION => [
        'code' => $status,
        'label' => (string) $this->translation->translate('Aguardando avaliação'),
        'tone' => 'warning',
      ],
      default => [
        'code' => $status,
        'label' => (string) $this->translation->translate('Não iniciado'),
        'tone' => 'neutral',
      ],
    };
  }

}
