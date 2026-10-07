# S3.4 — Wiki boundary

Data de preparação: 2026-10-06

RUNTIME STATUS: **DEFERRED**

## Objetivo

Reduzir service locator e static helpers do `WikiController` sem alterar a
fonte de verdade, a política de access ou a busca transitória atual.

## Mudanças preparadas

O controller passa a receber por DI:

- EntityTypeManager;
- DomainPurposeManager;
- Database Connection;
- DateFormatter;
- ViewExecutableFactory;
- current user.

Foram removidos do controller:

- `\Drupal::service('aculta_portal.domain_purpose')`;
- `\Drupal::database()`;
- `\Drupal::entityQuery()`;
- `\Drupal::service('date.formatter')`;
- `Views::getView()`.

## Views

Views continuam fonte das listagens editoriais.

O controller carrega a View config via Entity API e usa o service
`views.executable`/ViewExecutableFactory.

## Busca

A busca continua deliberadamente transitória:

- EntityQuery;
- published `wiki_entry`;
- Domain Source WIKI;
- accessCheck(TRUE);
- LIKE em title/summary/body;
- máximo 30 resultados.

Não migrar para Search API nesta fase.

Search API permanece Portal 0.16.

## Access

Preservado e tornado explícito com a conta atual:

- query `accessCheck(TRUE)`;
- `node->access('view', current account)`;
- `owner->access('view', current account)`.

## Cache

Mantidos:

- domain;
- user.permissions;
- user.node_grants:view;
- query arg;
- node list tags;
- dependencies de nodes/authors nas alterações recentes.

## Design System

Nenhum SDC novo.

Resultados, alterações recentes e listagens podem futuramente alimentar
componentes de conteúdo/participação do tema, mas a fase atual preserva o render
existente.

## Gates Runtime

```sh
php -l web/modules/custom/aculta_portal/src/Controller/WikiController.php
composer validate
vendor/bin/drush status
vendor/bin/drush cr
vendor/bin/drush config:status
vendor/bin/drush updatedb:status
```

Validar:

- home Wiki;
- Views categorias/recentes;
- busca vazia;
- busca com resultado/sem resultado;
- wildcard/acentos;
- unpublished sem vazamento;
- wrong Domain;
- recent changes;
- author sem access;
- create link com/sem permission;
- cache metadata;
- User A/User B.

## Merge policy

Manter draft até Runtime PASS.

## Próxima fase

S3.5 — Domain policy.
