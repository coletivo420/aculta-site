# R2 — Functional Validation Matrix

Status: **pronto para execução futura no Homelab**

## Objetivo

Executar uma validação funcional repetível depois que cada conjunto de mudanças
for integrado no Runtime.

R2 não é um smoke único. É uma matriz por:

- feature;
- purpose/host;
- identidade;
- access;
- cache;
- AJAX/fallback;
- failure mode.

## Baseline técnico por rodada

```sh
composer validate
vendor/bin/drush status
vendor/bin/drush cr
vendor/bin/drush config:status
vendor/bin/drush updatedb:status
git diff --check
```

Adicionar lint PHP/JS e testes específicos dos arquivos alterados.

## Domain matrix

| Purpose | Homelab | Produção |
| --- | --- | --- |
| MAIN | aculta.toca.net.br | aculta.org |
| ACCOUNT | conta.aculta.toca.net.br | conta.aculta.org |
| SUPPORT | apoio.aculta.toca.net.br | apoio.aculta.org |
| MAGAZINE | coletivo420.aculta.toca.net.br | coletivo420.aculta.org |
| WIKI | wiki420.aculta.toca.net.br | wiki420.aculta.org |
| SHOP | loja.aculta.toca.net.br | loja.aculta.org |
| COURSES | cursos.aculta.toca.net.br | cursos.aculta.org |
| FORUM | forum.aculta.toca.net.br | forum.aculta.org |

FORUM só entra como obrigatório após 0.11.

## Regra por rota especializada

```text
host correto -> comportamento esperado
host incorreto -> 404/policy documentada
```

Rotas administrativas especializadas permanecem em MAIN quando documentado.

## Identidades mínimas

### Anonymous

Validar:

- páginas públicas;
- redirect/login;
- private ACCOUNT;
- admin;
- Forum/Wiki conforme permissão;
- noindex onde necessário.

### User A

Usuário de teste com dados reais de desenvolvimento:

- Profile;
- Address;
- curso/membership;
- apoio;
- participação quando houver.

### User B

Segundo usuário com dados distintos.

Objetivo:

provar isolamento.

### Admin parcial

Usuário com algumas permissões administrativas, não superuser.

Objetivo:

provar access do Admin Hub e rotas.

### Admin completo

Somente para validar ferramentas administrativas e configuração.

## ACCOUNT

### Dashboard

- display name;
- avatar/fallback;
- resumo de cursos;
- links;
- cache;
- AJAX/fallback.

### Dados básicos

- Profile participante;
- submit/reload;
- validação;
- tabs;
- User A/B.

### Endereço

- Profile customer;
- Address field;
- submit/reload;
- CEP;
- User A/B.

### Foto

- upload;
- crop;
- cancel;
- fallback sem JS;
- cache/file access.

### Conexões

- integração externa indisponível;
- disponível sem vínculo;
- vinculada;
- ação autorizada;
- conta sem credencial local;
- User A/B.

### Segurança

- alteração de e-mail disponível/indisponível;
- pending;
- confirmação;
- senha local;
- conta externa-only;
- transactional mail failure.

### Cursos

- zero/um/vários;
- todos os CourseStatus;
- score;
- needs evaluation;
- access;
- COURSES URL.

### Apoio

- zero/um/vários;
- payment states;
- order access;
- User A/B;
- SUPPORT links.

## WIKI

- home/list/categories;
- verbete;
- create/edit;
- revisions;
- Diff;
- workflow;
- published/unpublished;
- revision access;
- Domain Source;
- search;
- canonical/navigation;
- contributions in ACCOUNT.

## COURSES

- catalog/landing;
- course;
- membership;
- start/continue;
- results;
- needs evaluation;
- wrong host;
- shared session.

## SUPPORT / Commerce

- support landing;
- donation start;
- checkout;
- payment success/failure;
- webhook invalid/valid according to test fixture;
- order visibility;
- no private metadata public.

## MAGAZINE

- home/list;
- article;
- author/category;
- Domain Source;
- canonical;
- metadata;
- search indexing where applicable.

## FORUM

Após 0.11:

- landing;
- container;
- topic;
- reply;
- create/edit;
- permissions;
- moderation;
- canonical;
- wrong host;
- ACCOUNT participation.

## Participation Hub

Após 0.13:

- empty;
- single source;
- multiple sources;
- ordering;
- revision access;
- complete cacheability;
- URLs;
- no duplicates;
- source-filtered paging;
- User A/B.

## Admin Hub

Após 0.14:

- full admin;
- partial admin;
- no-admin;
- links;
- missing module/config;
- counts;
- MAIN only;
- no sensitive material.

## AJAX

Para cada interação migrada/afetada:

- JS enabled;
- JS disabled;
- slow response;
- failed response;
- double click;
- back;
- forward;
- refresh;
- deep link;
- focus;
- keyboard;
- behavior reattach;
- cache/access.

## Search

- index empty;
- index ready;
- reindex;
- content changed;
- content deleted;
- published/unpublished;
- revision;
- acentos/PT-BR;
- pager/sort/filter;
- wrong purpose;
- User grants.

## Engagement

- favorite/follow;
- unflag;
- notification subscribe;
- unsubscribe;
- User A/B;
- target unpublished/deleted;
- delivery failure;
- AJAX/fallback.

## Failure modes

Testar ao menos:

- mail indisponível;
- external auth indisponível;
- CEP timeout;
- search backend indisponível/vazio;
- invalid webhook;
- stale membership;
- contrib config ausente.

Drupal deve degradar de forma controlada.

## Cache verification

Para features privadas:

- não depender apenas de visual inspection;
- revisar cache contexts/tags/max-age;
- repetir requests com User A e B;
- observar conteúdo após mutation.

## Acessibilidade

Por fluxo alterado:

- keyboard;
- focus;
- heading;
- accessible name;
- status/error;
- reduced motion quando aplicável;
- mobile.

## Evidência

Cada execução deve registrar:

- commit;
- feature;
- identity;
- host;
- steps;
- expected;
- actual;
- PASS/FAIL;
- logs relevantes sem material sensível;
- screenshot somente quando realmente útil;
- finding/PR de correção.

## Critério de saída R2

R2 termina quando:

- todos os drafts integrados têm testes associados;
- features implementadas passam sua matriz;
- nenhum vazamento User A/B;
- Domain matrix coerente;
- config CLEAN;
- updatedb NONE;
- regressões resolvidas ou explicitamente bloqueiam avanço.

## Próxima fase

R3 — Hardening Execution.
