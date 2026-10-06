# S2 — Static Portal Audit

Data: 2026-10-06  
Baseline auditado: `29ff8ca60c0257fb50e61c1fc41bd4860404a0f8`

## Objetivo

Auditar estaticamente o `aculta_portal` sem Homelab, sem Composer/Drush e sem
alterar comportamento executável.

Este documento não declara nenhuma feature como PASS. Achados que dependem de
runtime permanecem pendentes para a Macrofase R.

## Escopo observado

No baseline auditado, o módulo contém:

- 31 classes PHP em `src/`;
- cerca de 128 KB de PHP orientado a objetos em `src/`;
- cerca de 43,5 KB em `.module` + `.install`;
- 2 arquivos JavaScript;
- 3 arquivos CSS, totalizando ~11,9 KB;
- 2 templates Twig;
- 8 arquivos YAML próprios do módulo;
- 107 PNGs de avatar, totalizando ~112,9 MB.

Os assets de avatar não foram removidos. O volume é registrado apenas como
débito de inventário.

## Convenções da auditoria

- **KEEP** — responsabilidade específica e coerente com a arquitetura.
- **REFACTOR** — responsabilidade válida, mas implementação deve ser dividida,
  injetada ou alinhada ao Design System.
- **UPSTREAM/CONFIG** — investigar substituição por Core/contrib/Views/config.
- **RUNTIME-SENSITIVE** — não alterar sem testes no Homelab.
- **DEAD-ASSET CANDIDATE** — parece não ter consumidor executável visível;
  confirmar antes de excluir.

## Achados prioritários

### P0 — não introduzir regressão

#### Segurança Mercado Pago

`WebhookGuard`, `WebhookEventSubscriber` e
`MercadoPagoEnvironmentOverride` formam uma barreira de segurança em torno de
um gateway contrib ainda não homologado.

Classificação:

**KEEP + RUNTIME-SENSITIVE**

Não simplificar, remover ou migrar sem testes específicos de assinatura,
payload, gateway disabled e secrets por Key/env.

#### CEP

`CepLookupRateLimitSubscriber` e `cep-address.js` cobrem necessidades reais
da central de dados.

Classificação:

**KEEP + RUNTIME-SENSITIVE**

O JavaScript local pode continuar existindo enquanto acrescentar acessibilidade,
stale-response protection e adaptação ao Profile customer. Não duplicar endpoint
ou cache server-side.

#### Restricted Twig storage

`RestrictedTwigStorage` é infraestrutura específica do Homelab para permissões
de PHP/Twig compilado.

Classificação:

**KEEP + RUNTIME-SENSITIVE**

Pode futuramente ser movido para um módulo de infraestrutura, mas não pertence a
uma limpeza sem Runtime.

### P1 — primeira refatoração funcional futura

#### PortalController

Arquivo grande e multifuncional.

Hoje mistura:

- dashboard;
- foto;
- resumo LMS;
- conexões Social Auth;
- segurança;
- SMTP readiness;
- confirmação de e-mail;
- dados pessoais;
- endereço Commerce customer;
- construção de User form.

Além de DI parcial, ainda chama serviços estaticamente:

- `plugin.manager.block`;
- `user.data`;
- `email_confirmer`;
- `routeMatch()`.

Classificação:

**REFACTOR**

Direção:

- manter controllers finos;
- extrair presenters/builders por capacidade;
- injetar dependências;
- preparar render arrays para SDC;
- não mover regras para o tema.

#### PortalHooks

A classe possui 10 hooks OOP e cobre várias áreas:

- page preprocess;
- account shell;
- menus;
- cross-domain links;
- form alters;
- login;
- CEP/address presentation;
- page attachments;
- theme registry;
- metatags.

Ainda usa muitos acessos globais `\Drupal::...`.

Drupal 11.1 suporta classes `#[Hook]` autowired, portanto essa classe pode
receber dependências via construtor em vez de atuar como service locator.

Classificação:

**REFACTOR — alta prioridade**

Separação sugerida:

- `AccountHooks`;
- `NavigationHooks`;
- `ProfileFormHooks`;
- `AuthFormHooks`;
- `ThemeHooks`;
- `MetatagHooks`.

Não dividir apenas por tamanho: cada classe deve representar responsabilidade.

#### aculta_portal.module

Ainda contém 18 hooks/funções procedurais, incluindo:

- Metatag/tokens;
- Node presave;
- entity access/presave;
- form alters;
- Activity validation;
- donation validation;
- password/e-mail redirects;
- account photo/address submit callbacks;
- library alter.

Há sobreposição de hook names com `PortalHooks` em `form_alter` e
`metatags_alter`. Isso não é automaticamente bug — Drupal permite múltiplas
implementações — mas dificulta descobrir ordem, ownership e dependências.

Classificação:

**REFACTOR — alta prioridade**

Objetivo S3:

migrar gradualmente para Hook classes OOP por responsabilidade, preservando
ordem e comportamento.

#### SupportController

Consulta pedidos do UID atual e depois pagamentos por pedido.

Pontos:

- fonte de verdade Commerce está correta;
- não existe ledger paralelo;
- há potencial N+1 de payments;
- os pedidos são apresentados sem chamada explícita a
  `$order->access('view', $account)`.

Classificação:

**REFACTOR / ACCESS REVIEW**

Antes de qualquer otimização, garantir entity access antes de expor metadata.

Depois avaliar View/Commerce APIs para substituir parte da tabela manual.

#### AccountCoursesController

A fonte de dados está corretamente isolada em `AccountCoursesManager`.

O controller ainda monta manualmente:

- container de lista;
- card;
- meta;
- status;
- CTA;
- classes Bootstrap.

Classificação:

**REFACTOR -> COMPONENT DESIGN SYSTEM**

É um dos melhores candidatos para o primeiro consumo de SDC pelo Portal.

#### WikiController

Pontos positivos:

- `accessCheck(TRUE)`;
- `node->access('view')`;
- Views para listagens;
- cache metadata em recent changes.

Dívidas:

- busca transitória com `EntityQuery + LIKE`;
- service locator para Domain, database e formatter;
- apresentação montada diretamente no controller;
- `Views::getView()` estático;
- fallback `#markup => ''`.

Classificação:

**REFACTOR + UPSTREAM/CONFIG**

Search API permanece o destino planejado. A busca atual não deve ser removida
antes de paridade funcional.

### P2 — dívida estrutural

#### AccountShellBuilder

O conceito é válido e específico do Portal.

Dívidas:

- menu de seções duplicado em array PHP em vez de fonte configurável;
- builder é instanciado manualmente com `new` em `PortalHooks`;
- shell inteiro recebe `max-age: 0`;
- markup público ainda depende de template/CSS próprios do módulo.

Classificação:

**KEEP CONCEPT / REFACTOR IMPLEMENTATION**

Direção:

service injetável + contrato `account-shell` do Design System.

#### DomainPurposeManager

É peça arquitetural central e deve permanecer.

Dívidas:

- fallback para `\Drupal::request()`;
- mapa de Domain IDs hardcoded;
- `routeUrl()` e `pathUrl()` podem alterar a entidade Domain carregada ao
  mudar scheme para ambiente local;
- ainda não contém FORUM.

Classificação:

**KEEP / REFACTOR CAUTELOSO**

No futuro:

- remover fallback estático quando container/runtime confirmarem segurança;
- clonar Domain antes de mutações temporárias;
- avaliar configuração declarativa do mapa sem perder IDs estáveis;
- adicionar FORUM somente na versão correspondente.

#### DomainPurposeRequestSubscriber

A política de 404 em host incorreto é deliberada e importante.

Dívidas:

- usa `router.no_access_checks` de propósito para esconder wrong-host antes do
  403;
- resolve nodes/terms/groups dentro do subscriber;
- usa vários `\Drupal::...`;
- `getRequiredPurpose()` acumula regras Wiki/LMS/Domain Source.

Classificação:

**KEEP POLICY / REFACTOR**

Extrair um resolver de purpose por conteúdo/rota e injetar:

- route provider;
- entity type manager;
- current user.

Não enfraquecer o fail-closed 404.

#### DomainRouteSubscriber

Centraliza:

- paths públicos;
- purpose metadata;
- rotas Account;
- rota de webhook;
- cadastro fechado;
- tradução parcial de paths administrativos.

Classificação:

**KEEP / SPLIT CANDIDATE**

Possível divisão futura:

- route purpose policy;
- public path localization;
- registration policy.

Somente com testes de rotas.

#### AcultaBreadcrumbBuilder

O breadcrumb foi recentemente centralizado no Portal.

Hoje suporta explicitamente:

`main`, `account`, `support`, `magazine`.

WIKI/COURSES/SHOP/FORUM ainda não estão em `PUBLIC_PURPOSES`.

Classificação:

**KEEP**

Expandir apenas quando cada experiência pública tiver hierarquia definida.

Ainda há chamadas estáticas a `title_resolver` e `path.matcher`; podem virar
DI em S3.

#### PortalRequirementsController

Útil como diagnóstico integrado.

Dívidas:

- lista de requisitos duplicada do Composer;
- chamadas estáticas a theme handler e `Drupal::root()`;
- relatório mistura requisito técnico e estado de homologação;
- Admin Hub futuro deve absorver essa experiência.

Classificação:

**KEEP / REFACTOR BAIXO RISCO**

A UI administrativa deve continuar Drupal-native, não SDC público.

#### SupportForm

Integra Donation Flow sem criar fluxo financeiro próprio.

Dívidas:

- `PaymentGateway::load()` estático;
- `plugin.manager.block` estático;
- CSS público próprio;
- classes ACULTA hardcoded;
- integração direta ao plugin `commerce_donation_flow_link`.

Classificação:

**KEEP FUNCTION / REFACTOR**

Validar se Donation Flow é dependência obrigatória ou integração opcional
guardada.

#### aculta_portal.install

Contém instalação histórica e updates que criam/migram:

- profiles/fields;
- crop/image style;
- Key/env;
- CAPTCHA;
- SMTP;
- permissions;
- address migration.

Classificação:

**RUNTIME-SENSITIVE / LIFECYCLE DEBT**

Não reescrever sem testar:

- fresh install;
- update path;
- config sync;
- Estado restaurado.

Novas features devem preferir configuração exportável e update hooks somente
quando há migração real.

### P3 — deduplicação futura

#### Schema/Metatag plugins

Classes:

- OrganizationAlternateName;
- OrganizationEmail;
- OrganizationLegalName;
- OrganizationTaxId;
- PostalAddressTag;
- SchemaWebPageName;
- SchemaWebPageUrl.

Classificação:

**UPSTREAM CANDIDATE**

Comparar com Schema Metatag instalado antes de remover.

#### DomainPurposeCondition

É conceito específico da ACULTA e usa cache context `domain`.

Classificação:

**KEEP**

Adicionar FORUM quando a feature for implementada.

#### RegistrationController

Controller pequeno para cadastro fechado.

Classificação:

**KEEP enquanto necessário**

Pode desaparecer quando registro público for aberto e a rota Core voltar a ser
fonte.

## JavaScript

### account-navigation.js

Implementa:

- fetch de documento HTML;
- DOMParser;
- merge manual de `drupalSettings`;
- detach/attach behaviors;
- History API;
- focus e fallback full-page.

Classificação:

**REFACTOR FUTURO — Portal 0.15**

Destino:

- Views AJAX;
- Form API AJAX;
- Drupal Ajax;
- Core HTMX quando apropriado.

Não remover antes de parity test.

### cep-address.js

Classificação:

**KEEP + RUNTIME-SENSITIVE**

Observação:

o arquivo substitui `Drupal.behaviors.cepAutocomplete` pelo behavior ACULTA.
Isso é deliberado, mas cria acoplamento à implementação contrib. Antes de
atualizar `cep_autocomplete`, testar o contrato novamente.

## CSS e Bootstrap Component Design System

### account-portal.css

~10 KB de apresentação pública vivem no módulo.

Contém:

- account shell;
- navigation;
- photo editor;
- security cards;
- data tabs;
- course cards;
- loading/status.

Também contém cores e tipografia ACULTA diretamente.

Classificação:

**MIGRATE GRADUALMENTE PARA O DESIGN SYSTEM**

Não mover tudo de uma vez.

Primeiros candidatos:

- course card;
- status badge;
- empty state;
- action list;
- account shell.

### support.css

~1,2 KB de apresentação pública e tokens visuais hardcoded.

Classificação:

**MIGRATE PARA DESIGN SYSTEM**

### requirements-report.css

CSS de uma tela administrativa.

Classificação:

**KEEP NO MÓDULO**

Não forçar design system público sobre admin UI.

## Twig

### aculta-portal-shell.html.twig

É apresentação pública do Account shell.

Classificação:

**SDC CANDIDATE**

O Portal deve continuar dono das seções/rotas/access; o tema deve ser dono da
apresentação do shell.

### aculta-portal-photo-editor.html.twig

Componente público, progressive enhancement e diálogo acessível.

Classificação:

**SDC CANDIDATE / RUNTIME-SENSITIVE**

Migrar somente preservando fallback sem JS e Image Widget Crop.

## Assets de avatar

Foram encontrados 107 PNGs (~112,9 MB) dentro do módulo.

A auditoria estática não encontrou consumidor PHP/JS óbvio por busca textual,
mas arquivos JSON em `assets/avatars/meta/` descrevem as coleções.

Classificação:

**DEAD-ASSET CANDIDATE / INVENTORY REQUIRED**

Não excluir agora.

Antes de qualquer limpeza:

1. verificar referências em config/content/runtime;
2. confirmar se existe feature de avatar planejada;
3. decidir se assets pertencem ao Git, Media, object storage ou release asset;
4. só então remover/migrar.

## Classificação por classe

| Classe | Destino |
| --- | --- |
| AccountCoursesManager | KEEP; melhorar presenter/i18n/cache |
| AccountShellBuilder | REFACTOR para service + SDC |
| WebhookEventSubscriber | KEEP / runtime-sensitive |
| WebhookGuard | KEEP / runtime-sensitive |
| MercadoPagoEnvironmentOverride | KEEP / runtime-sensitive |
| AccountCoursesController | REFACTOR para SDC |
| CoursesController | REFACTOR; landing/config/Views candidate |
| PortalController | SPLIT / DI / SDC |
| PortalRequirementsController | KEEP / DI / Admin Hub |
| RegistrationController | KEEP temporariamente |
| WikiController | REFACTOR / Search API / SDC |
| AcultaBreadcrumbBuilder | KEEP / DI |
| DomainPurposeManager | KEEP / DI cleanup |
| AccountRouteSubscriber | KEEP / security-sensitive |
| CepLookupRateLimitSubscriber | KEEP |
| DomainPurposeRequestSubscriber | KEEP POLICY / split resolver |
| DomainRouteSubscriber | KEEP / split candidate |
| SocialAuthUserCreatedSubscriber | KEEP |
| PortalHooks | SPLIT OOP hooks / DI |
| RestrictedTwigStorage | KEEP / infra-sensitive |
| DomainPurposeCondition | KEEP |
| Schema/Metatag plugins | UPSTREAM AUDIT |
| SupportController | ACCESS REVIEW / Views candidate |
| SettingsForm | KEEP |
| SupportForm | REFACTOR / DI / Design System |

## Conclusão

O módulo não precisa ser refeito.

A direção correta é:

```text
preservar integrações específicas
        +
reduzir service locator
        +
separar hooks por responsabilidade
        +
extrair presenters
        +
consumir SDC/Bootstrap
        +
substituir duplicações por upstream/config
```

A próxima etapa é S3 — Behavior-preserving preparation.
