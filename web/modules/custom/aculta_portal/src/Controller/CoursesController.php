<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\Controller;

use Drupal\Core\Controller\ControllerBase;

/** Presents the course catalog on the COURSES Domain. */
final class CoursesController extends ControllerBase {

  /** Builds the public course landing page from the configured catalog View. */
  public function home(): array {
    return [
      '#type' => 'container',
      '#attributes' => ['class' => ['courses-home']],
      'intro' => [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $this->t('Formação para ampliar conhecimento, fortalecer direitos e construir caminhos antiproibicionistas.'),
        '#attributes' => ['class' => ['courses-home__intro']],
      ],
      'catalog_title' => [
        '#type' => 'html_tag',
        '#tag' => 'h2',
        '#value' => $this->t('Cursos disponíveis'),
      ],
      'catalog' => views_embed_view('courses_catalog', 'block_1') ?: [
        '#markup' => $this->t('Nenhum curso está dispon\u00edvel no momento.'),
      ],
      'collaboration' => [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $this->t('Acesse sua conta para participar dos cursos.'),
        '#attributes' => ['class' => ['courses-home__collaboration']],
      ],
    ];
  }

}
