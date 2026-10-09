<?php

/**
 * Static gate for the public URL directive (AGENTS.md, "URLs e slugs públicos em português").
 *
 * Reads the routing files of the customised modules and reprova public paths that:
 * - contain a purely numeric segment (an internal ID or a position), except inside parameters;
 * - use an English term from the forbidden list in a public segment.
 *
 * Administrative and technical paths are exempt: /admin, /ajax, /api, /_ and *.json.
 * The gate does not bootstrap Drupal, so it runs in any checkout.
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;

$root = dirname(__DIR__);

// Ceiling for contributed public routes (DT-P20). It may only shrink: a route leaves this list when it is
// fixed, and a new violation outside this list fails the gate.
$contribBaseline = [
  'lms.course.start', 'lms.course.reset_test', 'lms.group.answer_form', 'lms.group.results', 'lms.group.self_results',
  'lms.answer.details', 'entity.lms_answer.edit_form', 'entity.group.join', 'entity.group.leave',
  'entity.group.revision_delete_form', 'social_auth.network.redirect', 'social_auth.network.callback',
  'social_auth.user.profiles', 'change_mail_page.change_mail', 'change_mail_page.change_mail_form',
  'entity.webform_submission.user', 'profile.user_page.single', 'profile.user_page.multiple',
  'profile.user_page.add_form', 'diff.revisions_diff', 'entity.user.cancel_email_change',
];

$forbidden = ['course', 'courses_', 'group', 'node', 'user', 'lesson', 'activity', 'answer', 'taxonomy', 'comment', 'forum_', 'block', 'views'];
$exempt = ['/admin', '/ajax', '/api', '/_', '.json'];

$customRouting = glob($root . '/web/modules/custom/*/*.routing.yml') ?: [];
$contribRouting = glob($root . '/web/modules/contrib/*/*.routing.yml') ?: [];
$contribRouting = array_merge($contribRouting, glob($root . '/web/modules/contrib/*/*/*.routing.yml') ?: []);
$themeRouting = glob($root . '/web/themes/custom/*/*.routing.yml') ?: [];

$violations = function (string $path) use ($exempt, $forbidden): array {
  if (array_filter($exempt, static fn(string $e) => str_contains($path, $e))) {
    return [];
  }
  $public = preg_replace('/\{[^}]+\}/', '{}', $path);
  $segments = array_values(array_filter(explode('/', (string) $public), static fn(string $s) => $s !== '' && $s !== '{}'));
  foreach ($segments as $segment) {
    if (ctype_digit($segment)) {
      return ['numeric segment'];
    }
    foreach ($forbidden as $term) {
      if ($segment === $term || (str_starts_with($segment, $term) && !str_starts_with($segment, $term . '-'))) {
        return ["English term '$term'"];
      }
    }
  }
  return [];
};

$failures = [];
$checks = 0;
$seenContrib = [];

foreach (array_merge($customRouting, $themeRouting) as $file) {
  foreach (Yaml::parseFile($file) ?: [] as $name => $route) {
    $path = is_array($route) ? ($route['path'] ?? NULL) : NULL;
    if (!is_string($path)) {
      continue;
    }
    $checks++;
    foreach ($violations($path) as $reason) {
      $failures[] = basename(dirname($file)) . ": route '$name' ($path) has $reason. Use a Portuguese slug.";
    }
  }
}

foreach ($contribRouting as $file) {
  foreach (Yaml::parseFile($file) ?: [] as $name => $route) {
    $path = is_array($route) ? ($route['path'] ?? NULL) : NULL;
    if (!is_string($path)) {
      continue;
    }
    $checks++;
    if ($violations($path) === []) {
      continue;
    }
    $seenContrib[] = $name;
    if (!in_array($name, $contribBaseline, TRUE)) {
      $failures[] = "contrib: route '$name' ($path) is a new public-slug violation outside the DT-P20 baseline.";
    }
  }
}

foreach (array_diff($contribBaseline, $seenContrib) as $fixed) {
  $failures[] = "contrib: route '$fixed' is no longer a violation. Remove it from the DT-P20 baseline.";
}

if ($failures !== []) {
  foreach ($failures as $failure) {
    fwrite(STDERR, "PUBLIC SLUGS FAIL: $failure\n");
  }
  fwrite(STDERR, sprintf("PUBLIC SLUGS: FAIL (%d failures in %d routes)\n", count($failures), $checks));
  exit(1);
}

echo sprintf("PUBLIC SLUGS: PASS (%d routes; %d contributed violations in the DT-P20 baseline)\n", $checks, count($contribBaseline));
