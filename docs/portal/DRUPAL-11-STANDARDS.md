# Padrão Drupal 11+ — aculta_portal

Status: **normativo**.

Baseline do projeto: Drupal Core **11.3+**. O código corrente está em Drupal 11.4.x. Compatibilidade formal com uma nova major só é declarada depois de validar as dependências contrib, mas código novo deve evitar APIs já deprecated e seguir a direção publicada para Drupal 12/13.

## Princípios

O `aculta_portal` é a camada funcional/integrativa do ACULTA. Código novo e refatorações devem priorizar APIs Core/contrib estáveis, serviços e dependency injection, hooks OOP com `#[Hook]`, Render API/cacheability explícita, Entity/Plugin/Menu/Views APIs em vez de wrappers estáticos, fronteiras pequenas entre Domain/Commerce/LMS/apresentação e gates anti-regressão.

## Hooks

Desde Drupal 11.1, hooks de módulos preferem implementação OOP em `src/Hook/` usando `#[Hook('hook_name')]`. O projeto assume Drupal 11.3+.

Regras:
- hooks runtime ficam em classes OOP;
- não recriar `aculta_portal.module` para hooks;
- classes de hook recebem dependências por DI/autowiring;
- quando o alias não é inferível, usar `#[Autowire(service: 'service.id')]`;
- agrupar hooks por responsabilidade coesa.

Exceções Core que continuam procedurais ficam em `.install`, incluindo `hook_install()`, `hook_schema()`, `hook_update_N()`, `hook_post_update_NAME()` e equivalentes de lifecycle. Não criar wrappers OOP falsos para hooks que o Core exige procedurais.

## Dependency injection

Em `web/modules/custom/aculta_portal/src/`:
- não usar `\Drupal::service()`, `\Drupal::entityTypeManager()`, `\Drupal::database()`, `\Drupal::request()`, `\Drupal::routeMatch()` ou outros service locators;
- dependências de services/controllers/forms/plugins/subscribers/hooks são injetadas;
- service IDs próprios permanecem estáveis;
- preferir interfaces/Core aliases quando disponíveis;
- não ativar autowiring global apenas para reduzir YAML;
- `ControllerBase`/`FormBase` podem permanecer quando o comportamento herdado for útil, mas dependências novas não são buscadas via `\Drupal::`.

## Form API callbacks

Drupal 11.3+ resolve callbacks por `CallableResolver`. O Portal usa callbacks DI serializáveis em notação de serviço:

```php
$form['#validate'][] = 'aculta_portal.form_callbacks:validateActivity';
```

Não usar callbacks globais procedurais nem `[$this, 'callback']` em Form API persistível.

## Services e eventos

- event subscribers implementam `EventSubscriberInterface`;
- usar o tag Drupal `event_subscriber`;
- prioridade funcional fica em `getSubscribedEvents()` quando a ordem importa;
- preferir hooks/events/plugins/decorators a substituir services Core/contrib;
- service replacement exige justificativa arquitetural explícita.

## Entities, queries e Views

- entities via `EntityTypeManagerInterface`/storage;
- queries via `$storage->getQuery()` com `accessCheck(TRUE|FALSE)` explícito;
- evitar `EntityClass::load()` em runtime custom quando há storage injetado;
- não usar `\Drupal::entityQuery()`;
- Views custom usam storage `view` + `ViewExecutableFactory` (`views.executable`);
- não adicionar `views_embed_view()` ou `Views::getView()` no Portal.

## Render API, cache e access

- render arrays permanecem render arrays;
- contexts/tags/max-age acompanham a decisão que os exige;
- usar `CacheableMetadata`, `BubbleableMetadata` e `CacheableDependencyInterface`;
- objetos entregues a `Renderer::addCacheableDependency()` devem implementar `CacheableDependencyInterface`;
- access é resolvido antes de expor dados privados;
- entity queries declaram `accessCheck()`;
- não usar `max-age: 0` como substituto automático para cacheability correta.

## Domain / multidomínio

- `DomainPurposeManager` é a fonte de purpose e URLs cross-domain;
- não hardcodar hostnames;
- browser navigation cross-domain só para métodos seguros quando autorizada;
- requests mutáveis não são replayados entre hosts;
- apresentação recebe contratos neutros, nunca `DomainInterface` bruto;
- ACULTA420 não consulta services do Portal/Domain.

## Commerce, LMS e integrações

- Core/contrib são fonte de verdade;
- não criar storage paralelo;
- Portal compõe/adapta, não reimplementa engines contrib;
- callbacks/webhooks mantêm validação e fail-closed antes de delegar;
- segredos ficam em environment/Key, não em config exportado.

## PHP

- classes runtime em `src/` usam `declare(strict_types=1);`;
- propriedades e parâmetros são tipados quando a API permite;
- imports explícitos são preferidos a FQCN espalhado dentro de métodos;
- código novo segue Drupal coding standards e o PHP suportado pelo Core corrente.

## Deprecated APIs / upgrades

Antes de manter/adotar API: consultar API/change records do Core, evitar APIs deprecated, preferir o substituto recomendado, usar Upgrade Status/Rector ao subir a linha Core e documentar exceções duráveis.

Mudança de major Core é projeto próprio: atualizar primeiro para uma minor 11.x suportada, atualizar contribs/eliminar deprecations e só então validar a nova major.

## Gate

```sh
php vendor/drush/drush/drush.php php:script validate-aculta-portal-drupal11 --script-path=../scripts
```

O gate deve impedir no mínimo:
- recriação de `aculta_portal.module`;
- service locator em `src/`;
- PHP runtime sem `strict_types`;
- `views_embed_view()`/`Views::getView()`;
- static entity load conhecido em código modernizado;
- callbacks procedurais legados;
- `kernel.event_subscriber` em services;
- regressão do baseline `core_version_requirement: ^11.3`.

Após qualquer mudança estrutural do Portal, este gate é obrigatório junto aos gates específicos da feature.
