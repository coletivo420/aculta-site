<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\views\ViewExecutableFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/** Presents the course catalog on the COURSES Domain. */
final class CoursesController extends ControllerBase {

  public function __construct(
    private readonly EntityTypeManagerInterface $entities,
    private readonly ViewExecutableFactory $viewExecutableFactory,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('views.executable'),
    );
  }

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

  /** Builds the configured catalog View without static service wrappers. */
  private function catalogView(): array {
    $viewEntity = $this->entities->getStorage('view')->load('courses_catalog');
    if ($viewEntity === NULL) {
      return ['#plain_text' => $this->t('Nenhum curso está disponível no momento.')];
    }

    return $this->viewExecutableFactory
      ->get($viewEntity)
      ->buildRenderable('block_1');
  }

}
