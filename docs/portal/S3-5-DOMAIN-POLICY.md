# S3.5 — Domain policy

Data de preparação: 2026-10-06

RUNTIME STATUS: **DEFERRED**

## Objetivo

Separar resolução de ownership de rota/conteúdo da aplicação da política HTTP
que bloqueia host incorreto.

## ContentPurposeResolver

Novo service:

`aculta_portal.content_purpose_resolver`

Responsável por traduzir para purpose:

- Group `lms_course`;
- Wiki node add/edit/revisions/Diff;
- Domain Source de node canonical;
- wiki_category;
- editorial_author/editorial_category.

Não produz Response e não negocia Domain.

## DomainPurposeRequestSubscriber

Continua dono de:

- pre-router match;
- wrong-host 404;
- no-store/noindex da resposta 404;
- exception de password reset;
- redirect pós-logout.

O subscriber passa a delegar ownership ao resolver.

## DomainPurposeManager

- RequestStack deixa de ter fallback global;
- `routeUrl()` e `pathUrl()` clonam a Domain entity antes de adaptar scheme
  no ambiente local;
- canonical URL continua restaurando hostname/scheme da config;
- mapa de purposes ativos não ganha FORUM nesta fase.

## FORUM

A arquitetura está pronta para um novo purpose, mas `forum_aculta_org` não é
adicionado aqui.

Ativação pertence ao Portal 0.11 com Domain/config/Runtime.

## Gates Runtime

Validar:

- MAIN/ACCOUNT/SUPPORT/MAGAZINE/WIKI/SHOP/COURSES;
- wrong-host 404;
- node canonical por Domain Source;
- editorial taxonomy;
- Wiki add/edit/revisions/Diff;
- LMS course public/admin;
- password reset;
- logout;
- URL local HTTP;
- canonical production HTTPS;
- nenhuma mutação persistente de Domain.

## Merge policy

PR empilhado sobre S3.1 (#23). Manter draft até ambos passarem no Runtime.

## Próxima fase

S3.6 — Public CSS ownership.
