<?php

declare(strict_types=1);

namespace Drupal\aculta_portal;

use Drupal\aculta_portal\Domain\DomainPurposeManager;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\group\GroupMembershipLoaderInterface;
use Drupal\lms\Entity\Bundle\Course;
use Drupal\lms\Entity\CourseStatusInterface;
use Drupal\lms\TrainingManager;

/**
 * Read-only integration between the account experience and Drupal LMS/Group.
 *
 * Group owns enrolment and Drupal LMS owns progress. This service does not
 * persist parallel course, membership or progress data.
 */
final class AccountCoursesManager {

  public function __construct(
    private readonly TrainingManager $trainingManager,
    private readonly DomainPurposeManager $domainPurposeManager,
    private readonly GroupMembershipLoaderInterface $membershipLoader,
  ) {}

  /**
   * Returns authorized LMS/Group data for the account's visible memberships.
   *
   * Membership alone never grants visibility. Group access remains authoritative
   * and is checked before course metadata or LMS progress is loaded.
   *
   * @return array<int, array<string, mixed>>
   *   Authorized LMS/Group source records keyed sequentially for presentation.
   *   User-facing labels, tones, action semantics and empty states belong to the
   *   presenter, not to this integration boundary.
   */
  public function getCourses(AccountInterface $account): array {
    $items = [];

    foreach ($this->membershipLoader->loadByUser($account) as $membership) {
      $group = $membership->getGroup();
      if (!$group instanceof Course || $group->bundle() !== 'lms_course') {
        continue;
      }

      // A stale membership must not disclose unpublished or otherwise
      // restricted course metadata through ACCOUNT.
      if (!$group->access('view', $account)) {
        continue;
      }

      $status = $this->trainingManager->loadCourseStatus($group, $account, ['current' => TRUE]);
      $description = [];
      if ($group->hasField('field_description') && !$group->get('field_description')->isEmpty()) {
        // Preserve the stored text format and its cacheability metadata. Do not
        // flatten formatted text into a raw string or bypass Drupal's filters.
        $description = $group->get('field_description')->view([
          'label' => 'hidden',
          'type' => 'text_default',
        ]);
      }

      $items[] = [
        'id' => (string) $group->id(),
        'label' => (string) $group->label(),
        'description' => $description,
        'status' => $status?->getStatus() ?? '',
        'score' => $status?->getScore(),
        'finished' => $status?->isFinished() ?? FALSE,
        'url' => $this->courseUrl($group, $status),
        'cache_tags' => array_values(array_unique(array_merge(
          $group->getCacheTags(),
          $status?->getCacheTags() ?? [],
        ))),
      ];
    }

    usort($items, static fn(array $a, array $b): int => strnatcasecmp($a['label'], $b['label']));
    return $items;
  }

  public function countCourses(AccountInterface $account): int {
    return count($this->getCourses($account));
  }

  public function catalogUrl(): ?Url {
    return $this->domainPurposeManager->routeUrl('courses', 'aculta_portal.courses_home');
  }

  private function courseUrl(Course $course, ?CourseStatusInterface $status): ?Url {
    // Drupal LMS blocks course navigation while manually graded work is
    // awaiting evaluation. Do not generate a misleading start/continue action.
    if ($status?->getStatus() === CourseStatusInterface::STATUS_NEEDS_EVALUATION) {
      return NULL;
    }

    $route = $status?->isFinished() ? 'lms.group.self_results' : 'lms.course.start';
    return $this->domainPurposeManager->routeUrl('courses', $route, ['group' => $course->id()]);
  }

}
