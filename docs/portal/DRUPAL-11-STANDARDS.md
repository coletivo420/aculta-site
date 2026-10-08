# Padrão Drupal 11+ — aculta_portal

Status: **normativo**.

Baseline arquitetural do projeto: **Drupal Core 11.3+**. No início desta revisão, o `composer.lock` registrava Drupal Core 11.4.8; esse número é evidência de estado, não substitui a verificação do Composer em cada fase.

Este documento vale para desenvolvedores humanos, Codex, ChatGPT/agentes e demais ferramentas de IA. Compatibilidade formal com Drupal 12 ou 13 só pode ser declarada depois de validar Core **e** módulos contrib realmente instalados.

## Arquitetura e fonte de verdade

Fluxo obrigatório:

```text
Core/contrib
    ↓
aculta_portal
    ↓
contrato neutro
    ↓
ACULTA420
```

- Core/contrib continuam fonte de verdade para entidades, autenticação, Commerce, LMS/Group e demais capacidades que já fornecem.
- `aculta_portal` integra, orquestra e adapta; não cria storage paralelo sem necessidade comprovada.
- ACULTA420 apresenta dados preparados; não recebe `DomainInterface`, entity storage, services do Portal, decisões de hostname, access ou business logic.
- `DomainPurposeManager` continua autoridade para purpose e URLs multidomínio.
- Administração Drupal, carrinho, checkout e pagamento permanecem em MAIN conforme as políticas canônicas do Portal.
- Requests mutáveis cross-domain não são redirecionados nem replayados.

## Hooks

Drupal Core introduziu hooks OOP com `#[Hook]` em 11.1. Para código runtime novo/refatorado do módulo:

- preferir classes em `src/Hook/` com `#[Hook('hook_name')]`;
- confirmar no Core instalado que o hook específico aceita implementação OOP e conferir sua assinatura;
- preservar ordering/module weight; não alterar ordem apenas por modernização;
- dependências de hook entram por DI/autowiring compatível com o Core;
- não adicionar novos hooks runtime a `aculta_portal.module`.

Continuam procedurais quando o Core assim exige: `hook_install()`, `hook_schema()`, `hook_update_N()`, `hook_post_update_NAME()`, uninstall e lifecycle equivalentes. Não criar wrapper OOP artificial para lifecycle.

Referência Core: https://www.drupal.org/node/3442349 e https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Hook%21Attribute%21Hook.php/class/Hook/11.x

## Dependency Injection

Em `web/modules/custom/aculta_portal/src/`:

- não introduzir `\Drupal::service()`, `\Drupal::entityTypeManager()`, `\Drupal::database()`, `\Drupal::request()`, `\Drupal::routeMatch()`, `\Drupal::currentUser()`, `\Drupal::config()`, `\Drupal::messenger()` ou `\Drupal::entityQuery()`;
- services, controllers, forms, plugins, subscribers e hooks recebem dependências explicitamente;
- `ControllerBase` e `FormBase` podem permanecer quando úteis, mas não justificam esconder novas dependências via helpers lazy;
- não habilitar autowiring global apenas para reduzir YAML; manter definitions previsíveis quando o service contract importa;
- service replacement/decorator exige justificativa arquitetural e teste.

Referência: https://www.drupal.org/docs/drupal-apis/services-and-dependency-injection/services-and-dependency-injection-in-drupal

## Form API

Desde Drupal 11.3, callbacks de Form API podem ser resolvidos pelo `CallableResolver` e viver em serviços com DI.

Preferir:

```php
$form['#validate'][] = 'aculta_portal.form_callbacks:validateActivity';
```

Regras:

- não criar novos callbacks globais `aculta_portal_*`;
- evitar `[$this, 'callback']` quando o formulário puder ser serializado;
- agrupar callbacks em serviço coeso;
- antes de migrar callback legado, validar assinatura e fluxo no Core/contrib instalado.

Referência: https://www.drupal.org/node/3548821

## Event subscribers

- implementar `EventSubscriberInterface`;
- novos registros usam a tag Drupal `event_subscriber`;
- usar `isMainRequest()` quando o comportamento só vale para a request principal;
- prioridade fica em `getSubscribedEvents()` quando ordering importa;
- não mudar prioridade sem análise de RouterListener, RedirectResponseSubscriber e demais listeners relevantes;
- DI é explícita.

Referência: https://api.drupal.org/api/drupal/core%21core.api.php/group/events/11.x

## Entity API e access

Preferir storage injetado:

```php
$storage = $entityTypeManager->getStorage('node');
$query = $storage->getQuery();
```

Toda EntityQuery de conteúdo declara conscientemente:

```php
->accessCheck(TRUE)
```

ou:

```php
->accessCheck(FALSE)
```

A escolha precisa refletir a finalidade da query; `FALSE` não é atalho de performance. Entity access, route access, permissions, ownership e Domain purpose devem ser resolvidos antes da apresentação, com cacheability correta do resultado.

Evitar static entity loads em runtime custom quando storage injetado estiver disponível e não consultar tabelas internas de contrib quando houver API pública.

Referência: https://www.drupal.org/node/3201242

## Views

`views_embed_view()` e `Views::getView()` existentes são dívida a revisar, não motivo para refactor cego.

Quando apropriado, preferir storage da entidade `view` e `ViewExecutableFactory`/`views.executable`. Antes de substituir um wrapper funcional, provar equivalência de argumentos, display, cache, access, attachments e exposed inputs.

## Render API e cacheability

- render arrays permanecem render arrays;
- não pré-renderizar HTML para atravessar a fronteira Portal → tema;
- cache contexts, tags e max-age acompanham a decisão que exige variação;
- preferir `CacheableMetadata`, `BubbleableMetadata` e contratos cacheáveis;
- `max-age: 0` é válido quando o conteúdo é realmente não-cacheável, mas não deve ser usado automaticamente no lugar de contexts/tags corretos;
- access e cacheability são parte do mesmo contrato funcional.

Referência: https://www.drupal.org/docs/drupal-apis/render-api/cacheability-of-render-arrays

## Commerce, pagamentos e integrações

- Commerce permanece fonte de verdade para cart/order/checkout/payment;
- MAIN centraliza carrinho, checkout, payment, callbacks browser-facing e webhooks conforme as políticas existentes;
- não criar estado paralelo nem reimplementar Commerce;
- segredos ficam fora de Configuration Sync/Git e entram por Key/environment;
- Mercado Pago preserva os guards e comportamento fail-closed já definidos;
- LMS/Group, Social Auth e demais contrib continuam fonte de verdade de seus domínios.

## PHP

Classes runtime novas em `src/` usam:

```php
declare(strict_types=1);
```

Aplicar type hints quando compatíveis com as assinaturas extensíveis do Core/contrib. Não alterar assinatura upstream válida apenas por estilo.

## Drupal 12/13 e deprecations

Antes de qualquer refactor estrutural, registrar quando aplicável:

```text
CURRENTLY RECOMMENDED IN D11:
DEPRECATED IN D11:
REMOVED/CHANGED IN D12:
ANNOUNCED FOR D13:
```

Consultar API do Core instalado e change records. Não fazer mudança “para Drupal 12/13” por suposição. Upgrade Status/Rector entram como parte própria quando forem úteis; atualização major do Core não pertence a esta modernização.

## Gate progressivo

Executar:

```sh
php scripts/validate-aculta-portal-drupal11.php
```

O gate da P1 não finge que a modernização terminou. Ele registra e congela dívidas observadas na `main` de origem:

- funções procedurais runtime existentes em `aculta_portal.module`;
- service locators ainda existentes em quatro arquivos `src/`;
- wrappers de Views conhecidos;
- um static load conhecido em SupportForm;
- arquivos runtime ainda sem `strict_types`;
- seis registros `kernel.event_subscriber`.

Esses limites são **tetos**, não metas. Fases posteriores devem reduzir os allowlists/contagens no mesmo commit que eliminarem a dívida. Qualquer arquivo novo deve nascer conforme o padrão atual.

## Validação por fase

Nunca declarar PASS sem executar.

Mudança estática/documental:

```text
STATIC: PASS/FAIL
RUNTIME HOMELAB: DEFERRED quando não houver alteração funcional
```

Quando runtime Drupal mudar, executar no mínimo lint dos PHP modificados, `git diff --check`, cache rebuild, `composer validate`, `composer audit`, `updatedb:status` e `config:status`, além dos gates específicos. Não executar `drush updb`, `cim` ou `cex` automaticamente.

## Fontes oficiais consultadas

- OOP hooks: https://www.drupal.org/node/3442349
- Hook attribute/API: https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Hook%21Attribute%21Hook.php/class/Hook/11.x
- Form API CallableResolver: https://www.drupal.org/node/3548821
- EntityQuery access: https://www.drupal.org/node/3201242
- Events/subscribers: https://api.drupal.org/api/drupal/core%21core.api.php/group/events/11.x
- Services/DI: https://www.drupal.org/docs/drupal-apis/services-and-dependency-injection/services-and-dependency-injection-in-drupal
- Render cacheability: https://www.drupal.org/docs/drupal-apis/render-api/cacheability-of-render-arrays
