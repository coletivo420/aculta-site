# S3.1 — Hooks + Dependency Injection

Data de preparação: 2026-10-06

RUNTIME STATUS: **DEFERRED**

## Objetivo

Reduzir o uso de service locator (`\Drupal::...`) na camada de hooks e
políticas de domínio sem alterar comportamento funcional.

Drupal 11.1 introduziu hooks orientados a objeto com classes `#[Hook]`
registradas como serviços autowired. A S3.1 usa essa capacidade já disponível no
baseline Drupal 11.4 do projeto.

## Escopo preparado

### PortalHooks

Antes:

- criava `AccountShellBuilder` manualmente;
- resolvia current user/route/path/request por globals;
- buscava DomainPurposeManager pelo container;
- buscava EntityTypeManager, BlockManager, ConfigFactory, ModuleHandler,
  Translation e ModuleExtensionList via `\Drupal::...`.

Depois:

- todas essas dependências entram por construtor;
- serviços ambíguos usam `#[Autowire(service: ...)]`;
- `AccountShellBuilder` é serviço explícito;
- nenhum `\Drupal::...` permanece em `PortalHooks`.

A assinatura e a lógica dos hooks foram preservadas.

### AccountShellBuilder

Passa a ser serviço:

`aculta_portal.account_shell_builder`

Dependências:

- current route match;
- menu link manager;
- string translation.

O builder continua responsável pela composição do shell existente. A conversão
para SDC pertence à S3.2, não a esta fase.

### AcultaBreadcrumbBuilder

Foram injetados:

- `TitleResolverInterface`;
- `PathMatcherInterface`.

Saem os dois acessos globais anteriores.

A política de breadcrumb não foi alterada.

### DomainPurposeRequestSubscriber

Foram injetados:

- `RouteProviderInterface`;
- `EntityTypeManagerInterface`.

Saem os acessos globais a:

- route provider;
- entity type manager;
- current user estático.

A política fail-closed de 404 por host incorreto foi preservada.

## Fora do escopo

Não foi alterado nesta fase:

- `aculta_portal.module`;
- os 18 hooks/funções procedurais;
- `PortalController`;
- `WikiController`;
- `PortalRequirementsController`;
- CSS;
- templates;
- SDC;
- AJAX;
- Composer/config sync;
- Domain records;
- CEP;
- Mercado Pago;
- Twig storage.

A migração dos hooks procedurais continua prevista em S3.7.

## Revisão estática realizada

A preparação confirmou:

- zero chamadas `\Drupal::...` em `PortalHooks`;
- zero chamadas `\Drupal::...` em `AcultaBreadcrumbBuilder`;
- zero chamadas `\Drupal::...` em `DomainPurposeRequestSubscriber`;
- service definitions atualizadas para os novos construtores;
- nenhum arquivo do tema alterado;
- nenhuma dependência Composer adicionada.

A tentativa de clonar a branch em ambiente auxiliar para executar `php -l`
falhou por indisponibilidade de resolução DNS externa. Isso **não** é tratado
como PASS.

## Gates Runtime obrigatórios

Antes do merge do código funcional:

```sh
php -l web/modules/custom/aculta_portal/src/Hook/PortalHooks.php
php -l web/modules/custom/aculta_portal/src/Domain/AcultaBreadcrumbBuilder.php
php -l web/modules/custom/aculta_portal/src/EventSubscriber/DomainPurposeRequestSubscriber.php
php -l web/modules/custom/aculta_portal/src/AccountShellBuilder.php

composer validate

vendor/bin/drush status
vendor/bin/drush cr
vendor/bin/drush config:status
vendor/bin/drush updatedb:status
```

Também validar comportamento:

- login ACCOUNT;
- Google login quando configurado;
- menu cross-domain;
- shell da Conta;
- Básicos/Endereço;
- CEP;
- Account courses;
- Meu Apoio;
- breadcrumb MAIN/ACCOUNT/SUPPORT/MAGAZINE;
- wrong-host 404;
- Wiki routes;
- LMS/COURSES routes;
- logout cross-domain;
- password reset exception;
- admin routes no MAIN.

## Critério de merge

Enquanto esses gates não forem executados no Homelab, o PR da S3.1 deve
permanecer **draft**.

## Próxima fase

S3.2 — Minha Conta -> SDC.

A S3.2 deve começar por componentes visuais pequenos e reutilizáveis, conforme
[ACCOUNT-SDC-AJAX.md](ACCOUNT-SDC-AJAX.md), sem misturar a validação Runtime
pendente da S3.1.
