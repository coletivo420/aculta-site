# Portal 1.0 — Release Gates

Status: **especificação pronta; release bloqueado até Runtime PASS**

## Objetivo

Definir o que significa chamar o `aculta_portal` de 1.0 estável.

Portal 1.0 não é "todas as ideias implementadas". É o primeiro baseline em que
as capacidades assumidas como parte da experiência integrada possuem fonte de
verdade clara, acesso/cache corretos, testes Runtime e operação documentada.

## Gates funcionais

### Conta

- dashboard;
- identidade;
- dados básicos;
- endereço;
- foto;
- segurança;
- conexões;
- cursos;
- apoio;
- participação.

### CEP

- Profile/Address;
- autocomplete;
- failure/stale response;
- acessibilidade;
- rate limit;
- persistência correta.

### Cursos

- LMS/Group como fontes;
- membership/access;
- progresso;
- score/needs evaluation;
- ACCOUNT + COURSES;
- URLs por purpose.

### Apoio/Commerce

- Donation Flow;
- orders/payments;
- access-first;
- gateway policy;
- webhook hardening;
- ACCOUNT + SUPPORT.

### Wiki

- Node/Taxonomy/Revisions;
- Diff/workflow;
- Domain Source;
- busca Search API;
- contribuições na Conta;
- access por revisão.

### Fórum

- Forum/Node/Comment/Taxonomy;
- Domain FORUM;
- tópicos/respostas;
- shared session;
- participação na Conta;
- follow/notify quando 0.17 for adotado.

### Participation Hub

- agregação sem storage;
- Forum/Wiki/Cursos;
- access antes de metadata;
- cacheability completa;
- URLs environment-aware.

### Admin Hub

- MAIN;
- admin theme;
- access-first;
- links/status;
- sem CRUD paralelo;
- sem material sensível.

## Gates de arquitetura

- fontes de verdade de `SOURCE-OF-TRUTH.md` respeitadas;
- Portal = integração/orquestração;
- tema = apresentação;
- Bootstrap Component Design System sem segunda suíte concorrente;
- SDC sem business logic;
- Domain purpose sem hostname hardcoded;
- config/segredos conforme policy;
- SQLite/MariaDB portability.

## Gates de URLs e slugs

- inventário completo das rotas humanas públicas;
- slugs amigáveis em português para todos os purposes ativos;
- mesma estrutura de path em Homelab e produção;
- nenhuma navegação principal usando slug técnico/inglês sem justificativa;
- redirects para paths públicos substituídos;
- canonical, sitemap, menus e breadcrumbs coerentes;
- Search/Views apontando para aliases públicos;
- callbacks técnicos de Core/contrib preservados;
- access e wrong-host policy inalterados.

Ver [FRIENDLY-PORTUGUESE-SLUGS.md](FRIENDLY-PORTUGUESE-SLUGS.md).

## Gates AJAX

- UX assíncrona preservada onde definida;
- progressive enhancement;
- Core/contrib preferido;
- CEP permanece confiável;
- History/focus/behaviors;
- failure/fallback;
- sem SPA paralela.

## Gates Search

- Search API;
- backend aprovado;
- Wiki/Fórum;
- access;
- lifecycle;
- acentos;
- pager/filter;
- busca LIKE antiga removida somente depois da cutover.

## Gates Engagement

Se 0.17 entrar no escopo do 1.0:

- Flag;
- Comment Notify;
- opt-in/out;
- unsubscribe;
- transactional mail;
- privacy;
- User A/B.

Se for deliberadamente adiado, isso precisa estar documentado como
"post-1.0", e não parcialmente habilitado.

## Gates de hardening

Todos os gates de [S4-0.19-HARDENING.md](S4-0.19-HARDENING.md) precisam estar
PASS ou ter exceção formal aceita.

Nenhum finding alto/crítico aberto.

## Gates de configuração

No baseline final:

```text
drush config:status = CLEAN
drush updatedb:status = NONE
```

Config exportada deve representar o estado funcional aprovado.

## Gates de banco

Homelab:

- SQLite Runtime PASS.

Produção:

- compatibilidade MariaDB comprovada.

Não migrar o Runtime para MariaDB somente para "fechar" 1.0.

## Gates Domain

Testar todos os purposes ativos:

- MAIN;
- ACCOUNT;
- SUPPORT;
- MAGAZINE;
- WIKI;
- SHOP;
- COURSES;
- FORUM quando implementado.

Para rotas especializadas:

```text
host correto -> esperado
host incorreto -> 404/policy documentada
```

## Gates User A / User B

Obrigatórios para:

- Profile;
- Address;
- apoio;
- cursos;
- participation;
- favorites;
- notification preferences.

Nenhum dado privado cruzado.

## Gates de deploy

- Apache baseline;
- PHP-FPM;
- rewrite/headers;
- settings por ambiente;
- cron;
- queue;
- cache rebuild;
- config import;
- DB update;
- rollback;
- backup/runbook.

## Gates de documentação

Atualizar antes da tag:

- README;
- docs/portal;
- module README;
- CHANGELOG;
- source of truth;
- upstream modules;
- testing;
- deploy/runbook;
- known limitations.

Nenhum comportamento importante deve existir apenas no código.

## Versionamento

Release tag:

`portal-v1.0.0`

A tag só é criada depois:

1. merge de todos os PRs aprovados;
2. Runtime PASS;
3. config CLEAN;
4. hardening PASS;
5. changelog final;
6. main estável;
7. nenhuma alteração local pendente.

## Evidence pack

Guardar no relatório final:

- main SHA;
- versões Drupal/PHP;
- módulos relevantes;
- composer validate/audit;
- Drush status;
- config/updatedb;
- Domain HTTP matrix;
- User A/B;
- Search;
- AJAX;
- integrations;
- hardening;
- rollback test;
- known limitations.

## Não-gates

Não bloquear 1.0 por:

- Google for Nonprofits ainda não aprovado;
- Classroom sem caso de uso aprovado;
- catálogo SHOP ainda não especificado, desde que SHOP esteja explicitamente
  marcado como preparado e não como storefront completo;
- futura migração BDTGN;
- features pós-1.0 documentadas.

## Resultado

1.0 significa:

**baseline integrado, testado, operável e documentado — não finalização do
produto ACULTA.**
