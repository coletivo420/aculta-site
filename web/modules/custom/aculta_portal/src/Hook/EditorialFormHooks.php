<?php

declare(strict_types=1);

namespace Drupal\aculta_portal\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/** Editorial node form hooks. */
final class EditorialFormHooks {

  public function __construct(
    #[Autowire(service: 'string_translation')]
    private readonly TranslationInterface $translation,
  ) {}

  #[Hook('form_node_form_alter')]
  public function formNodeFormAlter(array &$form, FormStateInterface $state): void {
    $node = $state->getFormObject()->getEntity();
    if (!in_array($node->bundle(), ['article', 'activity', 'project'], TRUE)) {
      return;
    }

    $groups = [
      'editorial_content' => [
        $this->translation->translate('Conteúdo'),
        -40,
        ['title', 'body', 'field_summary', 'field_subtitle', 'field_editorial_category', 'field_tags', 'field_editorial_author', 'field_credits', 'field_references'],
      ],
      'editorial_image' => [
        $this->translation->translate('Imagem'),
        -30,
        ['field_media_image', 'field_gallery'],
      ],
      'editorial_relations' => [
        $this->translation->translate('Relacionamentos'),
        -20,
        ['field_project', 'field_related_activity'],
      ],
      'editorial_seo' => [
        $this->translation->translate('SEO e compartilhamento'),
        70,
        ['field_meta_tags'],
      ],
    ];

    foreach ($groups as $id => [$title, $weight, $fields]) {
      $form[$id] = [
        '#type' => 'details',
        '#title' => $title,
        '#open' => $id === 'editorial_content',
        '#weight' => $weight,
      ];
      foreach ($fields as $field) {
        if (isset($form[$field])) {
          $form[$field]['#group'] = $id;
        }
      }
    }

    if (isset($form['author'])) {
      $form['author']['#title'] = $this->translation->translate('Conta responsável pelo cadastro');
    }
    if (isset($form['options'])) {
      $form['options']['#title'] = $this->translation->translate('Publicação');
    }
    if ($node->bundle() === 'activity') {
      $form['#validate'][] = 'aculta_portal_validate_activity';
    }
  }

}
