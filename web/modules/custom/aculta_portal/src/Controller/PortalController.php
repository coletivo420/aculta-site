<?php

namespace Drupal\aculta_portal\Controller;

use Drupal\aculta_portal\Presentation\AccountConnectionsPresenter;
use Drupal\aculta_portal\Presentation\AccountDataPresenter;
use Drupal\aculta_portal\Presentation\AccountIdentityPresenter;
use Drupal\aculta_portal\Presentation\AccountSecurityPresenter;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/** Private user account area. */
final class PortalController extends ControllerBase {

  public function __construct(
    private readonly EntityTypeManagerInterface $entities,
    private readonly EntityFormBuilderInterface $forms,
    private readonly FormBuilderInterface $accountFormBuilder,
    private readonly \Drupal\aculta_portal\AccountCoursesManager $accountCourses,
    private readonly AccountConnectionsPresenter $connectionsPresenter,
    private readonly AccountSecurityPresenter $securityPresenter,
    private readonly AccountIdentityPresenter $identityPresenter,
    private readonly AccountDataPresenter $dataPresenter,
    private readonly BlockManagerInterface $blockManager,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity.form_builder'),
      $container->get('form_builder'),
      $container->get('aculta_portal.account_courses'),
      $container->get('aculta_portal.presentation.account_connections'),
      $container->get('aculta_portal.presentation.account_security'),
      $container->get('aculta_portal.presentation.account_identity'),
      $container->get('aculta_portal.presentation.account_data'),
      $container->get('plugin.manager.block'),
    );
  }

  public function dashboard(): array {
    $account = $this->entities->getStorage('user')->load($this->currentUser()->id());
    $identity = $this->identityPresenter->present($account);

    $photo = $identity['avatar']['has_picture'] ? [
      '#theme' => 'image_style',
      '#style_name' => 'aculta_avatar',
      '#uri' => $identity['avatar']['uri'],
      '#alt' => $identity['avatar']['alt'],
      '#attributes' => ['class' => ['aculta-account__avatar']],
    ] : [
      '#type' => 'html_tag',
      '#tag' => 'span',
      '#value' => $identity['avatar']['initial'],
      '#attributes' => [
        'class' => ['aculta-account__avatar', 'aculta-account__avatar--empty'],
        'aria-hidden' => 'true',
      ],
    ];

    return [
      'identity' => [
        '#type' => 'container',
        '#attributes' => ['class' => ['aculta-account__identity']],
        'photo_editor' => [
          '#theme' => 'aculta_portal_photo_editor',
          '#photo' => $photo,
          '#photo_form' => $this->buildPortalAccountForm($account),
          '#display_name' => $identity['display_name'],
        ],
        'details' => [
          '#type' => 'container',
          'name' => ['#type' => 'html_tag', '#tag' => 'h3', '#value' => $identity['display_name']],
          'email' => ['#plain_text' => $identity['email']],
        ],
      ],
      'intro' => [
        '#plain_text' => $this->t('Use o menu para acompanhar seu apoio, seus cursos, atualizar seus dados e gerenciar sua conta.'),
      ],
      'courses_summary' => $this->buildCoursesSummary($account),
      '#cache' => [
        'contexts' => ['user'],
        'tags' => $identity['cache_tags'],
        'max-age' => 0,
      ],
    ];
  }

  /** Builds a compact learning summary for the account overview. */
  private function buildCoursesSummary(\Drupal\Core\Session\AccountInterface $account): array {
    $count = $this->accountCourses->countCourses($account);
    $build = [
      '#type' => 'container',
      '#attributes' => ['class' => ['aculta-account-courses-summary']],
      'title' => ['#type' => 'html_tag', '#tag' => 'h3', '#value' => $this->t('Cursos')],
      'summary' => [
        '#plain_text' => $count === 1
          ? $this->t('Você participa de 1 curso.')
          : $this->t('Você participa de @count cursos.', ['@count' => $count]),
      ],
    ];
    if ($count > 0) {
      $build['link'] = [
        '#type' => 'link',
        '#title' => $this->t('Ver meus cursos'),
        '#url' => Url::fromRoute('aculta_portal.account_courses'),
      ];
    }
    else {
      $catalog = $this->accountCourses->catalogUrl();
      if ($catalog) {
        $build['link'] = [
          '#type' => 'link',
          '#title' => $this->t('Ver cursos disponíveis'),
          '#url' => $catalog,
        ];
      }
    }
    return $build;
  }

  public function myData(): array {
    return $this->buildDataSection('basics');
  }

  public function address(): array {
    return $this->buildDataSection('address');
  }

  public function connections(): array {
    $account = $this->entities->getStorage('user')->load($this->currentUser()->id());
    $view = $this->connectionsPresenter->present($account);

    $items = [
      'description' => ['#plain_text' => $view['description']],
      'google_title' => ['#type' => 'html_tag', '#tag' => 'h3', '#value' => $view['provider']],
      'google_status' => ['#plain_text' => $view['status']['label']],
    ];

    if ($view['notice'] !== NULL) {
      $items['password_notice'] = ['#plain_text' => $view['notice']];
    }
    if ($view['action'] !== NULL) {
      $items['action'] = [
        '#type' => 'link',
        '#title' => $view['action']['label'],
        '#url' => $view['action']['url'],
        '#attributes' => [
          'class' => $view['action']['kind'] === 'secondary'
            ? ['button', 'button--secondary']
            : [],
        ],
      ];
    }
    if ($view['login_available']) {
      $items['google_login'] = $this->blockManager
        ->createInstance('social_auth_login', [])
        ->build();
    }

    $items['#cache'] = [
      'contexts' => ['user'],
      'tags' => $account->getCacheTags(),
      'max-age' => 0,
    ];
    return $items;
  }

  public function security(): array {
    $account = $this->entities->getStorage('user')->load($this->currentUser()->id());
    $view = $this->securityPresenter->present($account);

    $content = [
      'intro' => ['#plain_text' => $view['intro']],
      'email_section' => [
        '#type' => 'container',
        '#attributes' => ['class' => ['aculta-security-card']],
        'heading' => ['#type' => 'html_tag', '#tag' => 'h3', '#value' => $view['email']['heading']],
        'current_label' => ['#type' => 'html_tag', '#tag' => 'h4', '#value' => $view['email']['current_label']],
        'current_email' => ['#plain_text' => $view['email']['current']],
      ],
      'password_section' => [
        '#type' => 'container',
        '#attributes' => ['class' => ['aculta-security-card']],
        'heading' => ['#type' => 'html_tag', '#tag' => 'h3', '#value' => $view['password']['heading']],
        'description' => ['#plain_text' => $view['password']['description']],
      ],
      '#cache' => [
        'contexts' => ['user', 'user.permissions'],
        'tags' => $account->getCacheTags(),
        'max-age' => 0,
      ],
    ];

    if ($view['email']['change_available']) {
      $content['email_section']['change_form'] = $this->accountFormBuilder
        ->getForm(\Drupal\change_mail_page\Form\ChangeMailForm::class, $account);

      if ($view['email']['pending'] !== NULL) {
        $content['email_section']['pending'] = [
          '#type' => 'container',
          '#attributes' => ['class' => ['aculta-security-pending'], 'aria-live' => 'polite'],
          'label' => ['#type' => 'html_tag', '#tag' => 'h4', '#value' => $view['email']['pending']['label']],
          'email' => ['#plain_text' => $view['email']['pending']['email']],
          'message' => ['#plain_text' => $view['email']['pending']['message']],
        ];
      }
    }
    elseif ($view['email']['unavailable_message'] !== NULL) {
      $content['email_section']['mail_notice'] = [
        '#plain_text' => $view['email']['unavailable_message'],
      ];
    }

    if ($view['password']['change_available']) {
      $content['password_section']['change_form'] = $this->buildPortalAccountForm($account);
    }
    elseif ($view['password']['unavailable_message'] !== NULL) {
      $content['password_section']['social_notice'] = [
        '#plain_text' => $view['password']['unavailable_message'],
      ];
    }

    return ['content' => $content];
  }

  private function buildDataSection(string $section): array {
    $account = $this->entities->getStorage('user')->load($this->currentUser()->id());
    $view = $this->dataPresenter->present($account, $section);
    $links = [];

    foreach ($view['tabs'] as $tab) {
      $link = [
        '#type' => 'link',
        '#title' => $tab['label'],
        '#url' => $tab['url'],
        '#attributes' => [
          'data-aculta-account-data-link' => 'true',
          'class' => ['aculta-account-data__link'],
        ],
      ];
      if ($tab['current']) {
        $link['#attributes']['aria-current'] = 'page';
        $link['#attributes']['class'][] = 'is-active';
      }
      $links[] = $link;
    }

    return [
      'description' => ['#plain_text' => $view['description']],
      'tabs' => [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['aculta-account-data__nav'],
          'aria-label' => $this->t('Seções de meus dados'),
          'data-aculta-account-data-nav' => 'true',
        ],
        'links' => $links,
      ],
      'content' => [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['aculta-account-data__content'],
          'data-aculta-account-data-content' => 'true',
          'aria-busy' => 'false',
        ],
        'heading' => [
          '#type' => 'html_tag',
          '#tag' => 'h3',
          '#value' => $view['title'],
          '#attributes' => ['tabindex' => '-1'],
        ],
        'account_email' => $view['account_email'] !== NULL ? [
          '#type' => 'container',
          '#attributes' => ['class' => ['aculta-account-data__email']],
          'label' => [
            '#type' => 'html_tag',
            '#tag' => 'h4',
            '#value' => $this->t('E-mail'),
          ],
          'value' => ['#plain_text' => $view['account_email']],
        ] : [],
        'form' => $this->forms->getForm($view['profile'], 'edit'),
      ],
      '#cache' => [
        'contexts' => ['user', 'user.permissions', 'route'],
        'tags' => $view['cache_tags'],
        'max-age' => 0,
      ],
    ];
  }

  /**
   * Builds Core's user form safely inside the Portal route.
   *
   * Social Auth's generic user-form alter expects a `user` route parameter,
   * which account pages do not have. Temporarily supply the current account
   * during form construction, then remove Social Auth's separate account
   * management section because the Portal has a dedicated Connections page.
   */
  private function buildPortalAccountForm($account): array {
    $parameters = \Drupal::routeMatch()->getParameters();
    $had_user_parameter = $parameters->has('user');
    $previous_user_parameter = $parameters->get('user');
    $parameters->set('user', $account);
    try {
      $form = $this->forms->getForm($account, 'default');
      unset($form['social_auth']);
      return $form;
    }
    finally {
      if ($had_user_parameter) {
        $parameters->set('user', $previous_user_parameter);
      }
      else {
        $parameters->remove('user');
      }
    }
  }

}
