<?php

namespace Drupal\aculta_portal\Controller;

use Drupal\aculta_portal\Presentation\AccountConnectionsPresenter;
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
      $container->get('plugin.manager.block'),
    );
  }

  public function dashboard(): array {
    $account = $this->entities->getStorage('user')->load($this->currentUser()->id());
    $profile = $this->loadParticipantProfile($account);
    $nickname = $profile && $profile->hasField('field_nickname') && !$profile->get('field_nickname')->isEmpty() ? $profile->get('field_nickname')->value : '';
    $first_name = $profile && !$profile->get('field_first_name')->isEmpty() ? $profile->get('field_first_name')->value : '';
    $display_name = $nickname ?: ($first_name ?: $account->getDisplayName());
    $file = $account->hasField('user_picture') && !$account->get('user_picture')->isEmpty() ? $account->get('user_picture')->entity : NULL;
    $photo = $file ? [
      '#theme' => 'image_style',
      '#style_name' => 'aculta_avatar',
      '#uri' => $file->getFileUri(),
      '#alt' => $this->t('Foto de perfil de @name', ['@name' => $display_name]),
      '#attributes' => ['class' => ['aculta-account__avatar']],
    ] : [
      '#type' => 'html_tag',
      '#tag' => 'span',
      '#value' => mb_strtoupper(mb_substr($display_name, 0, 1)),
      '#attributes' => ['class' => ['aculta-account__avatar', 'aculta-account__avatar--empty'], 'aria-hidden' => 'true'],
    ];

    return [
      'identity' => [
        '#type' => 'container',
        '#attributes' => ['class' => ['aculta-account__identity']],
        'photo_editor' => [
          '#theme' => 'aculta_portal_photo_editor',
          '#photo' => $photo,
          '#photo_form' => $this->buildPortalAccountForm($account),
          '#display_name' => $display_name,
        ],
        'details' => [
          '#type' => 'container',
          'name' => ['#type' => 'html_tag', '#tag' => 'h3', '#value' => $display_name],
          'email' => ['#plain_text' => $account->getEmail()],
        ],
      ],
      'intro' => [
        '#plain_text' => $this->t('Use o menu para acompanhar seu apoio, seus cursos, atualizar seus dados e gerenciar sua conta.'),
      ],
      'courses_summary' => $this->buildCoursesSummary($account),
      '#cache' => ['contexts' => ['user'], 'tags' => $account->getCacheTags(), 'max-age' => 0],
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
    $profile = $section === 'address'
      ? $this->loadCommerceAddressProfile($account)
      : $this->loadParticipantProfile($account);
    $route = $section === 'address' ? 'aculta_portal.my_data_address' : 'aculta_portal.my_data';
    $title = $section === 'address' ? $this->t('Endereço') : $this->t('Informações básicas');
    $links = [];
    foreach ([
      'basics' => ['title' => $this->t('Informações básicas'), 'route' => 'aculta_portal.my_data'],
      'address' => ['title' => $this->t('Endereço'), 'route' => 'aculta_portal.my_data_address'],
    ] as $key => $tab) {
      $link = [
        '#type' => 'link',
        '#title' => $tab['title'],
        '#url' => Url::fromRoute($tab['route']),
        '#attributes' => ['data-aculta-account-data-link' => 'true', 'class' => ['aculta-account-data__link']],
      ];
      if ($section === $key) {
        $link['#attributes']['aria-current'] = 'page';
        $link['#attributes']['class'][] = 'is-active';
      }
      $links[] = $link;
    }
    return [
      'description' => ['#plain_text' => $this->t('Mantenha suas informações pessoais e de contato atualizadas.')],
      'tabs' => [
        '#type' => 'container',
        '#attributes' => ['class' => ['aculta-account-data__nav'], 'aria-label' => $this->t('Seções de meus dados'), 'data-aculta-account-data-nav' => 'true'],
        'links' => $links,
      ],
      'content' => [
        '#type' => 'container',
        '#attributes' => ['class' => ['aculta-account-data__content'], 'data-aculta-account-data-content' => 'true', 'aria-busy' => 'false'],
        'heading' => ['#type' => 'html_tag', '#tag' => 'h3', '#value' => $title, '#attributes' => ['tabindex' => '-1']],
        'account_email' => $section === 'basics' ? [
          '#type' => 'container',
          '#attributes' => ['class' => ['aculta-account-data__email']],
          'label' => ['#type' => 'html_tag', '#tag' => 'h4', '#value' => $this->t('E-mail')],
          'value' => ['#plain_text' => $account->getEmail()],
        ] : [],
        'form' => $this->forms->getForm($profile, 'edit'),
      ],
      '#cache' => ['contexts' => ['user', 'user.permissions', 'route'], 'tags' => $account->getCacheTags(), 'max-age' => 0],
    ];
  }

  private function loadParticipantProfile($account) {
    $profiles = $this->entities->getStorage('profile')->loadByUser($account, 'participante');
    return $profiles ?: $this->entities->getStorage('profile')->create([
      'type' => 'participante',
      'uid' => $account->id(),
      'status' => TRUE,
      'is_default' => TRUE,
    ]);
  }

  /**
   * Loads or prepares the account's canonical Commerce customer address.
   *
   * An unsaved Profile entity lets the standard Commerce Address field form
   * create the customer profile through normal Form API submission.
   */
  private function loadCommerceAddressProfile($account) {
    $storage = $this->entities->getStorage('profile');
    $existing = $storage->loadByUser($account, 'customer');
    if (!$existing) {
      $profiles = $storage->loadByProperties(['uid' => $account->id(), 'type' => 'customer']);
      $existing = $profiles ? reset($profiles) : NULL;
    }
    return $existing ?: $storage->create([
      'type' => 'customer',
      'uid' => $account->id(),
      'status' => TRUE,
      'is_default' => TRUE,
    ]);
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
