<?php

declare(strict_types=1);

/**
 * Provision the single ACULTA LMS pilot course using native Drupal entities.
 *
 * Run with:
 *   vendor/bin/drush scr scripts/institution/provision-lms-pilot.php
 */

use Drupal\group\Entity\Group;
use Drupal\lms\Entity\Bundle\CourseInterface;

$entityTypeManager = \Drupal::entityTypeManager();
$ownerId = 1;
$courseTitle = 'Introdução ao Antiproibicionismo';
$lessonTitle = 'Proibicionismo e antiproibicionismo';
$courseStorage = $entityTypeManager->getStorage('group');
$lessonStorage = $entityTypeManager->getStorage('lms_lesson');
$activityStorage = $entityTypeManager->getStorage('lms_activity');

$findOne = static function ($storage, array $conditions, string $label): ?object {
  $query = $storage->getQuery()->accessCheck(FALSE);
  foreach ($conditions as $field => $value) {
    $query->condition($field, $value);
  }
  $ids = $query->range(0, 2)->execute();
  if (count($ids) > 1) {
    throw new \RuntimeException(sprintf('More than one existing %s matched the pilot identity.', $label));
  }
  return $ids ? $storage->load(reset($ids)) : NULL;
};

$courseType = $entityTypeManager->getStorage('group_type')->load('lms_course');
if (!$courseType || !$entityTypeManager->getStorage('lms_activity_type')->load('no_answer') || !$entityTypeManager->getStorage('lms_activity_type')->load('true_false')) {
  throw new \RuntimeException('LMS configuration is incomplete; run configuration setup before provisioning course content.');
}

$course = $findOne($courseStorage, ['type' => 'lms_course', 'label' => $courseTitle], 'pilot course');
if (!$course) {
  $course = Group::create([
    'type' => 'lms_course',
    'label' => $courseTitle,
    'uid' => $ownerId,
    'status' => TRUE,
    'new_revision' => TRUE,
  ]);
}
$course->set('label', $courseTitle);
$course->set('field_description', [
  'value' => 'Curso introdutório sobre proibicionismo, antiproibicionismo, direitos, redução de danos e os debates sociais relacionados às políticas de drogas.',
  'format' => 'plain_text',
]);
$course->set('status', TRUE);

$lesson = $findOne($lessonStorage, ['name' => $lessonTitle], 'pilot lesson');
if (!$lesson) {
  $lesson = $lessonStorage->create([
    'name' => $lessonTitle,
    'uid' => $ownerId,
    'status' => TRUE,
    'lms_lesson_type' => 'lesson',
  ]);
}
$lesson->set('name', $lessonTitle);
$lesson->set('status', TRUE);
$lesson->set('description', [
  'value' => 'Apresentação introdutória sobre proibicionismo e antiproibicionismo.',
  'format' => 'plain_text',
]);
$lesson->save();

$activityDefinitions = [
  [
    'type' => 'no_answer',
    'name' => 'Conceitos iniciais',
    'max_score' => 0,
    'field_content' => [
      'value' => 'O proibicionismo organiza determinadas políticas a partir da proibição e da criminalização. O antiproibicionismo questiona esse modelo e reúne debates sobre direitos, redução de danos e outras formas de política pública.',
      'format' => 'plain_text',
    ],
  ],
  [
    'type' => 'true_false',
    'name' => 'Questão de revisão',
    'max_score' => 1,
    'question' => [
      'value' => 'O antiproibicionismo questiona políticas centradas na proibição e discute alternativas baseadas em direitos e redução de danos.',
      'format' => 'plain_text',
    ],
    'description' => 'Selecione verdadeiro ou falso.',
    'bool_expected' => TRUE,
  ],
];

$activities = [];
foreach ($activityDefinitions as $definition) {
  $activity = $findOne($activityStorage, [
    'type' => $definition['type'],
    'name' => $definition['name'],
  ], 'pilot activity');
  if (!$activity) {
    $activity = $activityStorage->create([
      'type' => $definition['type'],
      'uid' => $ownerId,
      'status' => TRUE,
    ]);
  }
  foreach ($definition as $field => $value) {
    if (in_array($field, ['type', 'max_score'], TRUE)) {
      continue;
    }
    $activity->set($field, $value);
  }
  $activity->set('status', TRUE);
  $activity->save();
  $activities[] = $activity;
}

$lesson->set('activities', [
  ['target_id' => $activities[0]->id(), 'data' => ['max_score' => 0]],
  ['target_id' => $activities[1]->id(), 'data' => ['max_score' => 1]],
]);
$lesson->save();

$course->set(CourseInterface::LESSONS, [
  ['target_id' => $lesson->id(), 'data' => ['mandatory' => TRUE, 'required_score' => 1]],
]);
$course->setRevisionLogMessage('Provisionamento idempotente do curso piloto.');
$course->save();

printf(
  "COURSE gid=%s; LESSON id=%s; ACTIVITIES=%s,%s\n",
  $course->id(),
  $lesson->id(),
  $activities[0]->id(),
  $activities[1]->id(),
);

