# Portal da pessoa usuária

`aculta_portal` integrates the `aculta` theme with Drupal's account, support,
domain, editorial, and Commerce subsystems. The installation has seven
canonical Domain entities and one shared Drupal user/session. Drupal Commerce
remains the source of truth for orders and payments; this module does not
implement a gateway.

The account host is `conta.aculta.org`; its local alias is
`conta.aculta.test:8080`. The account root is the overview, followed by
`/dados`, `/dados/endereco`, `/conexoes`, `/seguranca`, and private `/apoio`.
Login, registration, password recovery/reset, confirmation, and logout use the
ACCOUNT host: `/entrar`, `/criar-conta`, `/recuperar-senha`,
`/recuperar-acesso/*`, `/confirmar-email/*`, and `/sair`. The header exposes
the sign-in link to anonymous visitors and points it to ACCOUNT. The
`/criar-conta` page explains that sign-up remains closed (`admin_only`); it
does not render or enable public account creation. The former
`/minha-conta` paths are not the account interface.

MAIN (`aculta.org`) is institutional. Identity belongs to ACCOUNT, public
support to SUPPORT, editorial content to MAGAZINE (`revista.aculta.org`), and
future knowledge, commerce, and courses to WIKI, SHOP, and COURSES. These are
contexts of the same Drupal installation, database, and user store.

## Profile

The module uses the Profile package and defines the private `participante`
profile with optional nickname, first name, last name, and WhatsApp. The
Commerce `customer` Profile's standard Address field is the canonical account
address shared by the Portal and checkout.
The Profile `profile_private` setting is enabled on all fields. The obsolete
participant `field_phone` instance and storage were removed after confirming
that the field had no values. WhatsApp remains the only phone contact field in
this profile. The institution block's separate `field_phone` is unrelated and
remains in use.

Authenticated users receive only portal access and create/view/update-own
permissions for this profile type. They do not receive profile administration,
ACULTA support administration, contribution-report, Commerce gateway, or
Commerce payment administration permissions. The profile page loads the
current user's profile only; the account pages use user-specific cache context
and disable caching for private output.

## Account shell and navigation

The portal shell is shared by the ACCOUNT root, `/apoio`, `/dados`,
`/dados/endereco`, `/conexoes`, and `/seguranca` on the ACCOUNT host only.
Its desktop
layout places persistent navigation on the left and the active route content
on the right; direct links and full-page rendering remain available.

The account-navigation library progressively enhances the section links with
`fetch()`, platform behaviors, focus management, live announcements, and the
History API. Profile form submissions still use Drupal Form API normally.
Failures fall back to regular navigation. The portal shell and fragment content
are private, vary by user/permissions/route, and use max-age zero to prevent
cross-user cache reuse.

## Institutional support feature

The former standalone support module was consolidated into `aculta_portal`
under `src/Support/`. Commerce Donation Flow owns the donation order item,
amount widget, and dedicated checkout route. The public support portal is
SUPPORT (`apoio.aculta.org/`, local `apoio.aculta.test:8080/`); MAIN links to
that host. The portal composes its native link only after an enabled,
credentialed gateway is available. ACCOUNT `/apoio` lists only Commerce
orders owned by the current user that contain a Donation Flow order item, and
derives status from Commerce payments. There is no custom financial entity,
ledger, payment processor, credential store, or webhook.

The local Commerce setup has one default institutional Store in BRL. Store is
a content entity, not part of Configuration Sync; the repeatable provisioning
script is `scripts/institution/provision-commerce-store.php`. Donation Flow is
configured for one-time support only, with R$ 20, R$ 50, R$ 100, and a custom
amount. The recurring option is removed from the active gift-type field, and
empty, zero, negative, or monthly submissions are rejected server-side. The
contrib widget currently enforces a minimum custom value of R$ 5 and has no
maximum. No products, orders, or payments are created for support setup.

The Mercado Pago Commerce gateway config entity is named `mercado_pago`, is
disabled, and is constrained to the institutional Store, default order type,
and BRL. The contrib release stores credentials in gateway config and renders
them in its admin form; the portal therefore locks manual gateway editing,
redacts credential defaults, and maps only `MERCADOPAGO_PUBLIC_KEY` and
`MERCADOPAGO_ACCESS_TOKEN` into the test-mode plugin configuration at runtime.
No credentials are currently configured. `commerce_mercado_pago` 3.0.0-rc3
uses the Preferences API and does not validate the webhook `x-signature`;
real payments remain blocked until that contrib limitation is resolved and
the gateway is externally homologated.

Support settings in `aculta_portal.support` contain only the public
introduction text and are protected by the restricted
`administer aculta support settings` permission. There is no parallel
financial ledger or administrative payment report.

## Extending the dashboard

Only functional destinations should be shown. Future Commerce orders, courses,
activities, and certificates should be added when their owning modules are
available, without placing gateway logic in this module.

See `scripts/institution/PORTAL-APOIO-COMMERCE-LOCAL-REVIEW.md` and
`scripts/institution/ACCOUNT-INTEGRATIONS-LOCAL-REVIEW.md` for route access
checks and implementation status.

## Administração e requisitos

As opções e os relatórios administrativos do módulo ficam centralizados em
**Configuração → ACULTA Portal** (`/admin/config/aculta/portal`). A página
principal lista somente as opções que a conta administrativa pode acessar.
O item **Status dos módulos e temas** abre
`/admin/config/aculta/portal/requisitos` e é protegido pela permissão Core
`administer site configuration`; contas comuns não têm acesso à área.

O painel compara extensões habilitadas e versões instaladas com os requisitos
do projeto e inclui os temas público `aculta` e base `bootstrap5`. Os principais
requisitos são Drupal Core `^11.4`; Profile `^1.14`; Address `^2.0`; CEP
Autocomplete `^1.0`; Login Email or Username `^3.0`; Change Mail Page `^1.0.2`;
Email Confirmer `^1.0`; Social Auth `^4.1` e Social Auth Google `^4.0`; Crop
`^2.6` e Image Widget Crop `^3.0`; Drupal Commerce `^3.3` e Commerce Donation
Flow `^1.2`; Key `^1.22`; CAPTCHA `^2.0` e Turnstile `^1.2`; SMTP `^1.4`;
Agreement `^3.0`; Metatag `^2.2` e Schema Metatag `^3.0`; Webform `^6.3`.
Bootstrap 5 `4.0.8` é o tema base. Os valores exibidos são obtidos do Composer
instalado e das extensões ativas, sem imprimir valores de configuração secreta.

O detalhamento do relatório separa os componentes por função: Core/User/Node;
Profile e Address; CEP Autocomplete; login por username/e-mail; Change Mail
Page e Email Confirmer; Social API/Auth/Google; Image/Crop/Image Widget Crop;
Commerce (checkout, order, payment e store) e Donation Flow; Key;
CAPTCHA/Turnstile; SMTP; Agreement; Metatag/Schema Metatag; Webform; e os temas
`aculta`/`bootstrap5`. As constraints efetivamente usadas são as de
`composer.json`, incluindo Commerce Mercado Pago `^3.0@RC`.

O painel usa check, atenção e erro com rótulos textuais acessíveis. CEP
Autocomplete não tem cobertura da Drupal Security Advisory Policy; o aviso
permanece mesmo quando a extensão está atualizada. Commerce Mercado Pago está
em `3.0.0-rc3`, fora da cobertura da política de advisories, e seu gateway
precisa permanecer desabilitado. SMTP, credenciais Google e chaves reais
Turnstile continuam deliberadamente pendentes de homologação pós-deploy.

## Account integrations in this local phase

- The overview presents the current user's private profile photo, nickname
  (falling back to first name and then Drupal display name), and account email.
- `participante` stores nickname, name, surname, WhatsApp, city, UF, and an
  optional private Address field. The existing Drupal User picture field is
  private and uses the contributed crop widget.
- `Meus Dados` has real routes for basic information and address. Profile and
  User forms continue to use Drupal Form API and ownership/access checks.
- `Conexões` uses Social Auth's Google provider and standard provider block; it
  does not keep a second OAuth connection table. The connection is presented
  only when the provider is actually configured. Disconnect uses the provider
  entity confirmation flow and is unavailable when it would lock the user out.
- CAPTCHA is disabled globally and Turnstile is attached only to the canonical
  full-page login, registration, password reset, Contact, and Faça Parte forms.
  The Drupal.org Turnstile module performs Siteverify server-side. Local test
  credentials are process-only; the registration route remains closed until
  SMTP delivery has been tested.
- Key uses environment-backed providers for Turnstile, Google, and SMTP2GO.
  Secret values are not stored in active ordinary configuration or `config/sync`.
- SMTP2GO is prepared for `mail.smtp2go.com:2525` with TLS configuration, but
  outbound mail is disabled and credentials/sender validation are pending.
  Google OAuth is also prepared but has no credentials and was not tested
  against Google.
- Agreement presents the short acceptance statement with links to the local
  Terms of Use and Privacy Policy. Both documents are marked for final human
  review before production.

The custom module remains an integration layer: Commerce owns stores, orders,
payments, and financial state; Donation Flow owns donation order items and
checkout; the portal owns only the Association-specific presentation and
current-user account view.

### Account security and public controls

The public login accepts a Drupal username or email through the existing
`login_emailusername` 3.0.1 module; the internal username remains unchanged.
Minha Conta > Segurança composes Drupal Core's own user password form with
Change Mail Page 1.0.2 and Email Confirmer 1.0.0 (`email_confirmer_user`).
Email Confirmer is the source of truth for pending and confirmed addresses;
the Drupal User entity remains the source of truth for the final email and
password. The current address stays in place until the native confirmation
link is used. The change-email form stays unavailable until SMTP is actually
selected as Drupal's mail backend and its runtime settings are ready. No
confirmation token or pending-email storage is implemented by this module.

Ordinary authenticated users manage their own account through the Portal and
do not receive Drupal administration permissions. Exact account route guards
redirect the current user's generic account/profile pages to Minha Conta while
leaving password reset, social callbacks, confirmation, Agreement, and Commerce
flows alone. Password hashing, validation, and current-password checking stay
with Drupal Core.

The theme owns public button and header-navigation states. A single CTA uses
the yellow/red primary style; paired calls such as the Home hero use a
yellow/red primary and a green outlined secondary. The primary header menu
uses Drupal's `.is-active` / `aria-current="page"` state, with yellow/red for
the current section and dark-green/white for temporary hover/focus. Provider
branding for Google and Cloudflare is excluded from global button styling.
The requested current-navigation red-on-yellow combination measures 2.83:1
for contrast; this phase keeps the approved palette unchanged and records the
contrast issue for visual approval.

Editorial text-link color is opt-in in the `aculta` theme. Only the Node body
fields for `page`, `article`, `activity`, `project`, `editorial_highlight`, and
`document` receive `.aculta-prose` from theme field preprocessing; Commerce and
other Node bundles do not opt in. Only inline links inside prose paragraphs/lists/
quotes are red and underlined. Dedicated editorial summary/complement/CTA,
“more” links inside the editorial list, and clickable breadcrumbs have
separate scoped selectors. No global anchor, content-region, Node, or View
link rule is used; card headings, header, footer, Portal, forms, Commerce,
provider widgets, and admin links keep their own styling.

## Complete Configuration Sync

`config/sync` is the full desired Drupal configuration for import/export.
The critical account manifest is only an additional validation subset; it
does not limit what deploys. Environment variables provide runtime secrets and
simple environment-specific values. Normal deployment uses the complete
configuration import, not a partial import. Config Split and Config Ignore
are not part of the current policy.

## Domain purposes and editorial separation

The `aculta_portal.domain_purpose` service maps stable purposes (`main`,
`account`, `support`, `magazine`, `wiki`, `shop`, `courses`) to Domain entities
derived from production hostnames. Custom routes declare
`_aculta_domain_purpose`; a central request subscriber returns a private 404
on the wrong host. Direct node routes use `field_domain_source`. Editorial
articles/highlights default to MAGAZINE; pages, projects, documents, and
activities default to MAIN. Existing Nodes were assigned through Drupal's
entity API; no content was copied or deleted.

Institutional home, project, activity, contact, and transparency blocks are
restricted to MAIN. Editorial-highlight nodes remain in Drupal but their
duplicate teaser placement on the MAIN home is disabled; the content itself
is preserved. The `aculta_news` block is restricted to MAGAZINE and
`/noticias`. Domain-specific output uses Drupal's `domain` cache context as a
required render cache context. Canonical editorial URLs use `field_domain_source` through
Drupal's entity URL generator; local aliases use the current local request
scheme. Image URLs use the central Domain resolver instead of fixed host
strings. `/apoie` was removed from the
static MAIN sitemap custom links. Simple XML Sitemap currently has one shared
sitemap configuration; per-domain sitemap indexes and indexing behavior still
need a separate verification before production.

The installed Domain 3.0.1 API generates IDs from hostnames, so the actual IDs
are `aculta_org`, `conta_aculta_org`, `apoio_aculta_org`,
`revista_aculta_org`, `wiki_aculta_org`, `loja_aculta_org`, and
`cursos_aculta_org`. Domain Alias maps each local `.test:8080` hostname to its
production entity in environment `local`. Production requires all seven hosts
to point to the same Drupal document root over HTTPS. The shared
`.aculta.org` session cookie makes all seven subdomains one trust boundary;
none may be handed to an independent or SaaS application while that cookie is
shared.

## Architectural principle: theme, portal, and functional modules

> `aculta_portal` integrates the experience between the `aculta` theme and
> Drupal's functional modules. It organizes, presents, and connects these
> capabilities without replacing their data sources or business rules.

The custom theme at `web/themes/custom/aculta/` owns the site's global visual
identity: palette, typography, spacing, shared components, header, footer, and
general presentation. The portal reuses that identity. It may provide account-
specific templates, libraries, wrappers, classes, navigation, states, layouts,
and progressive enhancement, while keeping those elements visually consistent
with the theme.

The portal is the integration layer between the theme and account features:

```text
                         TEMA ACULTA
                              │
                 identidade visual global
                              │
                              ▼
                       ACULTA_PORTAL
                              │
           experiência integrada de /minha-conta
                              │
        ┌─────────────────────┼─────────────────────┐
        │                     │                     │
   Drupal User             Profile              Social Auth
   autenticação           dados pessoais       Google/OAuth
        │                     │                     │
        ├──────────────┬──────┴────────────┬────────┤
        │              │                   │        │
   Image/Crop       Address             Commerce   LMS futuro
   foto/avatar      endereço            apoio      cursos
                                      pedidos      progresso
                                      pagamentos  certificados
```

The functional source of truth stays with the specialized subsystem:

- Drupal User owns authentication, password, email, and account status.
- Social Auth and Social Auth Google own OAuth and provider connections.
- Profile and Address own personal and address data.
- Image, Crop, and Image Widget Crop own profile images, framing, and image
  processing.
- Drupal Commerce owns support orders, purchases, payments, and financial
  history.
- A future LMS will own courses, enrolments, progress, and certificates.

`aculta_portal` composes those capabilities into the authenticated account
experience, including the shared shell, navigation, AJAX enhancement, forms,
and user-specific presentation. It does not copy subsystem data into its own
tables without a concrete need, replace Drupal User/Profile/Address/Social
Auth/Commerce/LMS/Image, or recreate authentication, OAuth, payments, or course
management.

Before adding account functionality, confirm: (1) whether Drupal Core or an
adopted module already provides it; (2) which subsystem is its source of truth;
(3) how the portal should integrate it; and (4) how the `aculta` theme should
present it. Custom business logic belongs here only when the adopted ecosystem
does not provide an appropriate solution.

The current account areas are overview, support, personal data, connections,
and security. Future areas may include orders, courses, progress, certificates,
enrolments, and activities. Each follows the same composition: `aculta` theme
for shared identity, `aculta_portal` for account experience, and a specialized
module for data and business rules.

## Editorial integration

The former `aculta_editorial` custom module was audited and removed. Its
required Schema.org Metatag tags, institution/node tokens, publication-date
presave behavior, editorial node-form grouping, Activity validation, and
conditional JSON-LD integration now live in `aculta_portal` using the
`Drupal\aculta_portal` namespace. The portal provides only glue between the
theme, Views, Nodes, and institutional components. Drupal Nodes, content types,
fields, Views, and contributed modules remain the sources of truth for
editorial content and its data; no editorial storage was duplicated.

## Phase 4 status — full configuration and Commerce support

The current support integration uses Commerce Donation Flow 1.2.0 and Commerce 3.3.10. ACCOUNT `/apoio` reads only the signed-in user's Commerce support orders. The SUPPORT root shows payment unavailability and no checkout link while the Mercado Pago gateway remains disabled. The local site has one default BRL Store, zero Commerce Orders, and zero Payments.

`config/sync` is the complete desired configuration: 651 objects match active storage and `drush config:status` has no differences. The 59-object account manifest is an audit subset only.

### Mercado Pago webhook boundary

`aculta_portal` contains a small security/compatibility boundary for the
current Mercado Pago gateway because its contrib release does not validate
the modern webhook signature. It validates and normalizes the supported
request before the contrib handler; it does not create or process payments.
Drupal Commerce and `commerce_mercado_pago` remain the financial sources of
truth. The signing secret is supplied only through the environment-backed
Drupal Key `mercadopago_webhook_secret`. The gateway remains disabled pending
external homologation. The current contrib uses Preferences API (legacy but
still supported); Orders API migration is future technical debt.

See `scripts/institution/ACCOUNT-INTEGRATIONS-LOCAL-REVIEW.md` and
`scripts/institution/DEPLOY-RUNBOOK.md` for the local audit and post-deploy
validation steps.
