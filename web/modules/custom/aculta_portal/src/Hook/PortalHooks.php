<?php

namespace Drupal\aculta_portal\Hook;

use Drupal\aculta_portal\AccountShellBuilder;
use Drupal\aculta_portal\Domain\DomainPurposeManager;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Drupal hooks for the ACULTA account portal.
 */
final class PortalHooks {

  public function __construct(
    #[Autowire(service: 'current_route_match')]
    private readonly CurrentRouteMatch $routeMatch,
    #[Autowire(service: 'current_user')]
    private readonly AccountProxyInterface $currentUser,
    #[Autowire(service: 'path.current')]
    private readonly CurrentPathStack $currentPath,
    #[Autowire(service: 'request_stack')]
    private readonly RequestStack $requestStack,
    #[Autowire(service: 'string_translation')]
    private readonly TranslationInterface $translation,
    #[Autowire(service: 'module_handler')]
    private readonly ModuleHandlerInterface $moduleHandler,
    #[Autowire(service: 'plugin.manager.block')]
    private readonly BlockManagerInterface $blockManager,
    #[Autowire(service: 'entity_type.manager')]
    private readonly EntityTypeManagerInterface $entityTypeManager,
    #[Autowire(service: 'config.factory')]
    private readonly ConfigFactoryInterface $configFactory,
    #[Autowire(service: 'extension.list.module')]
    private readonly ModuleExtensionList $moduleList,
    #[Autowire(service: 'aculta_portal.domain_purpose')]
    private readonly DomainPurposeManager $domainPurposeManager,
    #[Autowire(service: 'aculta_portal.account_shell_builder')]
    private readonly AccountShellBuilder $accountShellBuilder,
  ) {}


  /**
   * Implements hook_preprocess_page().
   */
  #[Hook('preprocess_page')]
  public function preprocessPage(array &$variables): void {
    $account = $this->currentUser;
    $path = $this->currentPath->getPath();
    $request = $this->requestStack->getCurrentRequest();
    if ($request !== NULL
      && $account->isAuthenticated()
      && !$account->hasPermission('administer users')
      && preg_match('#^/user/([1-9][0-9]*)/edit$#D', $path, $matches)) {
      $target_uid = (int) $matches[1];
      if ($target_uid === (int) $account->id()
        && !\Drupal\aculta_portal\EventSubscriber\AccountRouteSubscriber::isValidCorePasswordResetRequest($request, $target_uid, (int) $account->id())) {
        $variables['aculta_account_edit_blocked'] = TRUE;
        $variables['aculta_account_security_url'] = Url::fromRoute('aculta_portal.security')->toString();
      }
    }

    $this->accountShellBuilder->build($variables);
  }

  /**
   * Implements hook_menu_links_discovered_alter().
   */
  #[Hook('menu_links_discovered_alter')]
  public function menuLinksDiscoveredAlter(array &$links): void {
    // The portal owns the account landing page, not the generic user profile.
    if (($links['user.page']['menu_name'] ?? NULL) === 'account') {
      unset($links['user.page']);
    }
  }

  /** Sends identity, support and editorial links to their canonical domains. */
  #[Hook('preprocess_menu')]
  public function preprocessMenu(array &$variables): void {
    if (empty($variables['items']) || !is_array($variables['items'])) {
      return;
    }
    $resolver = $this->domainPurposeManager;
    $this->rewriteDomainMenuItems($variables['items'], $resolver);
    if (isset($variables['#cache']) && is_array($variables['#cache'])) {
      $variables['#cache']['contexts'] = array_values(array_unique(array_merge(
        $variables['#cache']['contexts'] ?? [],
        ['domain'],
      )));
    }
  }

  /** Sends editorial author and category links to MAGAZINE. */
  #[Hook('preprocess_links')]
  public function preprocessLinks(array &$variables): void {
    if (!isset($variables['links']) || !is_array($variables['links'])) {
      return;
    }
    $resolver = $this->domainPurposeManager;
    foreach ($variables['links'] as &$link) {
      $url = $link['url'] ?? NULL;
      if (!$url instanceof Url || !$url->isRouted() || $url->getRouteName() !== 'entity.taxonomy_term.canonical') {
        continue;
      }
      $term_id = $url->getRouteParameters()['taxonomy_term'] ?? NULL;
      $term = is_numeric($term_id) ? $this->entityTypeManager->getStorage('taxonomy_term')->load((int) $term_id) : NULL;
      if ($term && in_array($term->bundle(), ['editorial_author', 'editorial_category'], TRUE)) {
        $link['url'] = $resolver->routeUrl('magazine', 'entity.taxonomy_term.canonical', ['taxonomy_term' => $term->id()]) ?? $url;
      }
    }
    unset($link);
  }

  /** Rewrites only known cross-domain links, keeping their menu structure. */
  private function rewriteDomainMenuItems(array &$items, \Drupal\aculta_portal\Domain\DomainPurposeManager $resolver): void {
    foreach ($items as &$item) {
      $url = $item['url'] ?? NULL;
      if ($url instanceof Url) {
        $route = $url->isRouted() ? $url->getRouteName() : NULL;
        $uri = $url->isRouted() ? NULL : $url->getUri();
        $target = NULL;
        if (in_array($route, ['user.login', 'user.register', 'user.pass', 'user.logout'], TRUE)) {
          $target = $resolver->routeUrl('account', $route, $url->getRouteParameters());
        }
        elseif ($route === 'aculta_portal.dashboard' || $route === 'user.page') {
          $target = $resolver->pathUrl('account', '/');
        }
        elseif ($route === 'entity.node.canonical') {
          $parameters = $url->getRouteParameters();
          $node_parameter = $parameters['node'] ?? NULL;
          $node = $node_parameter instanceof \Drupal\node\NodeInterface
            ? $node_parameter
            : (is_numeric($node_parameter) ? $this->entityTypeManager->getStorage('node')->load((int) $node_parameter) : NULL);
          if ($node && $node->hasField('field_domain_source') && !$node->get('field_domain_source')->isEmpty()) {
            $purpose = $resolver->getPurposeForDomainId((string) $node->get('field_domain_source')->target_id);
            if ($purpose !== NULL) {
              $target = $resolver->routeUrl($purpose, 'entity.node.canonical', ['node' => $node->id()]);
            }
          }
        }
        elseif ($route === 'aculta_portal.support_form' || $uri === 'internal:/apoie') {
          $target = $resolver->pathUrl('support', '/');
        }
        elseif ($uri === 'internal:/noticias') {
          $target = $resolver->pathUrl('magazine', '/noticias');
        }
        if ($target instanceof Url) {
          $item['url'] = $target;
        }
      }
      if (!empty($item['below']) && is_array($item['below'])) {
        $this->rewriteDomainMenuItems($item['below'], $resolver);
      }
    }
    unset($item);
  }

  /**
   * Implements hook_page_attachments().
   */
  #[Hook('page_attachments')]
  public function pageAttachments(array &$attachments): void {
    $route = $this->routeMatch->getRouteName();
    if (in_array($route, [
      'aculta_portal.dashboard',
      'aculta_portal.my_data',
      'aculta_portal.my_data_address',
      'aculta_portal.connections',
      'aculta_portal.security',
      'aculta_portal.support_my',
      'aculta_portal.account_courses',
    ], TRUE)) {
      $attachments['#attached']['library'][] = 'aculta_portal/account-navigation';
    }
  }

  /**
   * Keep the two Profile subsections focused while using the Profile entity form.
   */
  #[Hook('form_alter')]
  public function formAlter(array &$form, FormStateInterface $form_state, string $form_id): void {
    if ($form_id === 'user_login_form') {
      $form['name']['#title'] = $this->translation->translate('Nome de usuário ou e-mail');
      $form['name']['#description'] = $this->translation->translate('Informe seu e-mail ou nome de usuário.');
      $form['password_reset_link'] = [
        '#type' => 'link',
        '#title' => $this->translation->translate('Esqueci minha senha'),
        '#url' => Url::fromRoute('user.pass'),
        '#attributes' => ['class' => ['aculta-login__reset-link']],
        '#weight' => 10,
      ];
      $form['registration_notice'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['aculta-login__registration']],
        'text' => ['#plain_text' => $this->translation->translate('Ainda não tem uma conta?')],
        'link' => [
          '#type' => 'link',
          '#title' => $this->translation->translate('Ver informações sobre o cadastro'),
          '#url' => Url::fromRoute('user.register'),
          '#attributes' => ['class' => ['aculta-button', 'aculta-button--secondary']],
        ],
        '#weight' => 30,
      ];
    }
    if ($form_id === 'user_login_form'
      && $this->routeMatch->getRouteName() === 'user.login'
      && $this->currentPath->getPath() === '/entrar'
      && $this->moduleHandler->moduleExists('social_auth')
      && $this->googleLoginConfigured()) {
      $form['social_auth_divider'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['aculta-login__divider']],
        'line_before' => ['#type' => 'html_tag', '#tag' => 'span', '#attributes' => ['aria-hidden' => 'true']],
        'label' => ['#plain_text' => $this->translation->translate('ou')],
        'line_after' => ['#type' => 'html_tag', '#tag' => 'span', '#attributes' => ['aria-hidden' => 'true']],
        '#weight' => 20,
      ];
      $form['social_auth'] = $this->blockManager
        ->createInstance('social_auth_login', [])
        ->build();
      $form['social_auth']['#weight'] = 21;
    }

    $form_object = $form_state->getFormObject();
    if (!method_exists($form_object, 'getEntity')) {
      return;
    }
    $entity = $form_object->getEntity();
    if (!$entity) {
      return;
    }

    $path = $this->currentPath->getPath();
    if ($entity->getEntityTypeId() === 'profile' && $entity->bundle() === 'participante') {
      if ($path === '/dados') {
        // Keep this tab limited to personal basics, even if older active
        // Profile display configuration still contains address components.
        $allowed = [
          'field_nickname',
          'field_first_name',
          'field_last_name',
          'field_whatsapp',
          'actions',
          'form_build_id',
          'form_token',
          'form_id',
        ];
        foreach (array_keys($form) as $key) {
          if (!str_starts_with((string) $key, '#') && !in_array($key, $allowed, TRUE)) {
            unset($form[$key]);
          }
        }
      }
      else {
        foreach (['field_nickname', 'field_first_name', 'field_last_name', 'field_whatsapp'] as $field) {
          unset($form[$field]);
        }
      }
      if ($path === '/dados' && isset($form['actions']['submit'])) {
        $form['actions']['submit']['#submit'][] = 'aculta_portal_sync_customer_address_names';
      }
      return;
    }

    if ($entity->getEntityTypeId() === 'profile' && $entity->bundle() === 'customer'
      && $path === '/dados/endereco'
      && (int) $entity->getOwnerId() === (int) $this->currentUser->id()) {
      // The Portal and Commerce edit the same customer Profile Address.
      $allowed = ['address', 'actions', 'form_build_id', 'form_token', 'form_id'];
      foreach (array_keys($form) as $key) {
        if (!str_starts_with((string) $key, '#') && !in_array($key, $allowed, TRUE)) {
          unset($form[$key]);
        }
      }
      if (isset($form['address']['widget'][0]['address'])) {
        $address_element = &$form['address']['widget'][0]['address'];
        $address_element['#default_value']['country_code'] ??= 'BR';
        $participant = $this->entityTypeManager->getStorage('profile')->loadByUser($this->currentUser, 'participante');
        if ($participant) {
          $address_element['#default_value']['given_name'] ??= $participant->get('field_first_name')->value ?? '';
          $address_element['#default_value']['family_name'] ??= $participant->get('field_last_name')->value ?? '';
        }
        foreach ([
          'country_code' => 'País',
          'address_line1' => 'Endereço / Logradouro',
          'address_line2' => 'Complemento',
          'locality' => 'Cidade',
          'dependent_locality' => 'Bairro',
          'administrative_area' => 'Estado',
          'postal_code' => 'CEP',
          'sorting_code' => 'Código postal',
        ] as $key => $label) {
          if (isset($address_element[$key])) {
            $address_element[$key]['#title'] = $this->translation->translate($label);
          }
        }
        foreach (['organization', 'given_name', 'additional_name', 'family_name'] as $private_or_duplicate) {
          if (isset($address_element[$private_or_duplicate])) {
            $address_element[$private_or_duplicate]['#access'] = FALSE;
          }
        }
        unset($address_element);
      }
      if (isset($form['actions']['submit'])) {
        $form['actions']['submit']['#value'] = $this->translation->translate('Salvar endereço');
        $form['actions']['submit']['#submit'][] = 'aculta_portal_address_redirect';
      }
      return;
    }

    // The account overview edits only the current account's photo here.
    if ($entity->getEntityTypeId() === 'user' && $path === '/conta-interna') {
      $allowed = ['user_picture', 'actions', 'form_build_id', 'form_token', 'form_id'];
      foreach (array_keys($form) as $key) {
        if (!in_array($key, $allowed, TRUE) && !str_starts_with((string) $key, '#')) {
          unset($form[$key]);
        }
      }
      if (isset($form['actions']['submit'])) {
        $form['actions']['submit']['#value'] = $this->translation->translate('Salvar foto do perfil');
        $form['actions']['submit']['#submit'][] = 'aculta_portal_account_photo_redirect';
      }
    }
  }

  /**
   * Applies the approved public login wording after generic form alters.
   */
  #[Hook('form_user_login_form_alter')]
  public function userLoginFormAlter(array &$form, FormStateInterface $form_state, string $form_id): void {
    $form['name']['#title'] = $this->translation->translate('Nome de usuário ou e-mail');
    $form['name']['#description'] = $this->translation->translate('Informe seu e-mail ou nome de usuário.');
  }

  /**
   * Implements hook_page_attachments_alter().
   */
  #[Hook('page_attachments_alter')]
  public function pageAttachmentsAlter(array &$attachments): void {
    foreach ($attachments['#attached']['html_head'] ?? [] as $delta => $item) {
      $element = $item[0] ?? [];
      if (($element['#tag'] ?? NULL) === 'meta' && strtolower($element['#attributes']['name'] ?? '') === 'generator') {
        unset($attachments['#attached']['html_head'][$delta]);
      }
    }
  }

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme(): array {
    return [
      'aculta_portal_shell' => [
        'variables' => [
          'section_title' => NULL,
          'menu' => NULL,
          'content' => NULL,
        ],
        'template' => 'aculta-portal-shell',
        'path' => $this->moduleList->getPath('aculta_portal') . '/templates',
      ],
      'aculta_portal_photo_editor' => [
        'variables' => [
          'photo' => NULL,
          'photo_form' => NULL,
          'display_name' => NULL,
        ],
        'template' => 'aculta-portal-photo-editor',
        'path' => $this->moduleList->getPath('aculta_portal') . '/templates',
      ],
    ];
  }

  private function googleLoginConfigured(): bool {
    $config = $this->configFactory->get('social_auth_google.settings');
    return !empty($config->get('client_id')) && !empty($config->get('client_secret'));
  }

  /**
   * Supplies metadata for the public support page and private result routes.
   *
   * #[Hook('metatags_alter')]
   */
  #[Hook('metatags_alter')]
  public function metatagsAlter(array &$tags, array $context): void {
    $route = $this->routeMatch->getRouteName();
    if ($route === 'aculta_portal.support_form') {
      $description = 'Apoie os projetos culturais, ações de formação, comunicação, cuidado e participação social da Associação Cultural Antiproibicionista.';
      $supportUrl = $this->domainPurposeManager->routeUrl('support', '<front>');
      $supportUrl = $supportUrl ? $supportUrl->toString() : '';
      $tags = array_replace($tags, [
        'title' => 'Apoie a Associação | Associação Cultural Antiproibicionista',
        'description' => $description,
        'canonical_url' => $supportUrl,
        'og_title' => 'Apoie a Associação',
        'og_description' => $description,
        'og_url' => $supportUrl,
        'og_type' => 'website',
        'schema_web_page_type' => 'WebPage',
        'schema_web_page_name' => 'Apoie a Associação',
        'schema_web_page_description' => $description,
        'schema_web_page_url' => $supportUrl,
      ]);
    }
    elseif (str_starts_with((string) $route, 'aculta_portal.') && $route !== 'aculta_portal.support_form') {
      $tags['robots'] = 'noindex, nofollow';
    }
    elseif (str_starts_with((string) $route, 'commerce_donation_flow.')
      || str_starts_with((string) $route, 'commerce_checkout.')
      || str_starts_with((string) $route, 'commerce_payment.')
      || str_starts_with((string) $route, 'entity.commerce_order.')) {
      $tags['robots'] = 'noindex, nofollow';
      unset($tags['canonical_url'], $tags['og_url'], $tags['schema_web_page_url']);
      if (str_starts_with((string) $route, 'commerce_donation_flow.')) {
        $tags['title'] = 'Apoio | Associação Cultural Antiproibicionista';
      }
    }
  }

}
