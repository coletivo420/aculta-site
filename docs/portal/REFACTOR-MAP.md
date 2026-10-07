# Mapa de refatoração do aculta_portal

Este mapa transforma os achados de
[STATIC-AUDIT.md](STATIC-AUDIT.md) em uma ordem de trabalho.

Nenhum item abaixo está funcionalmente validado até a Macrofase R.

## Princípios

1. não alterar fonte de verdade;
2. access antes de metadata;
3. cache metadata permanece responsabilidade do Portal/presenter;
4. controller fino;
5. Hook class OOP + DI;
6. apresentação pública usa ACULTA Bootstrap Component Design System;
7. admin continua Drupal-native;
8. não mexer em segurança de pagamento/CEP/Twig sem Runtime;
9. uma mudança lógica por PR;
10. draft executável usa `RUNTIME STATUS: DEFERRED`.

## S3.1 — Hooks e service locator

**Estado: preparada em draft; Runtime deferred.**

Ver [S3-1-HOOKS-DI.md](S3-1-HOOKS-DI.md).

### Objetivo

Reduzir `\Drupal::...` em classes sem alterar regra funcional.

### Primeiros alvos

- `PortalHooks`;
- `AcultaBreadcrumbBuilder`;
- `DomainPurposeRequestSubscriber`;
- `PortalController`;
- `WikiController`;
- `PortalRequirementsController`.

### Estratégia

Drupal 11.1 permite Hook classes autowired.

Separar hooks por responsabilidade e injetar:

- entity type manager;
- route match;
- request stack/path current;
- translation;
- module handler;
- block plugin manager;
- title resolver;
- path matcher;
- DomainPurposeManager.

Não migrar todos os hooks num único PR.

## S3.2 — Account presentation boundary

**Estado: S3.2B semântica documental concluída; S3.2A Course presenter preparado em draft, Runtime deferred.**

Contratos normativos:

- [ACCOUNT-SDC-AJAX.md](ACCOUNT-SDC-AJAX.md)
- [ACCOUNT-PRESENTATION-MODEL.md](ACCOUNT-PRESENTATION-MODEL.md)
- [S3-2-ACCOUNT-SDC.md](S3-2-ACCOUNT-SDC.md)

### Objetivo

Separar dados/estado da apresentação.

### Extrações previstas

- Account identity presenter;
- Account security presenter;
- Connections presenter;
- Account data section builder;
- Course card presenter.

### Design System

Primeiros contratos a definir/usar:

- `status-badge`;
- `empty-state`;
- `summary-card`;
- `action-list`;
- `course-card`;
- `account-shell`;
- `account-identity`;
- `data-section`;
- `security-card`;
- `integration-card`.

AJAX é uma dimensão separada do componente: a navegação parcial da Conta
permanece enquanto o transporte é migrado gradualmente para APIs Core.

A semântica comum de status/actions/empty/summary está em
[ACCOUNT-PRESENTATION-MODEL.md](ACCOUNT-PRESENTATION-MODEL.md). Esses contratos
não devem ser convertidos automaticamente em SDC antes de aprovação no roadmap
do tema.

Usar render element SDC quando apropriado:

```php
[
  '#type' => 'component',
  '#component' => 'aculta:course-card',
  '#props' => [...],
  '#slots' => [...],
  '#cache' => [...],
]
```

Não passar entidade inteira como prop.

## S3.2B — Shared presentation semantics

**Estado: concluída documentalmente.**

Define status, action, empty state, summary e action list sem criar abstrações
visuais prematuras.

## S3.2C — Security and connections presenters

Próxima extração do `PortalController`:

- Social Auth/Google;
- status conectado/desconectado/configuração pendente;
- segurança;
- transactional mail readiness;
- pending e-mail.

Não alterar OAuth redirect/callback nem Form API.

## S3.3 — Support access first

Antes de reorganizar UI:

- verificar `order->access('view', current account)`;
- decidir tratamento de payments;
- documentar quais estados financeiros podem aparecer;
- preservar Commerce como fonte;
- avaliar View/API upstream.

Este é o primeiro finding de access a resolver no Runtime.

## S3.4 — Wiki boundary

Curto prazo:

- injetar Domain manager/database/date formatter;
- manter accessCheck;
- reduzir apresentação ad hoc;
- preparar item/list SDC.

Médio prazo:

- Search API + Views;
- retirar LIKE somente após parity.

## S3.5 — Domain policy

Preservar:

- wrong-host 404;
- canonical por Domain Source;
- rotas administrativas em MAIN;
- routes ACCOUNT/COURSES/WIKI.

Preparar:

- ContentPurposeResolver;
- DI no subscriber;
- clone de Domain antes de mudar scheme;
- FORUM purpose em versão específica.

## S3.6 — Public CSS ownership

Não mover CSS em massa.

Ordem:

1. course card;
2. empty/status/action primitives;
3. support public components;
4. account shell;
5. photo editor.

Após cada componente:

- remover apenas regras comprovadamente exclusivas;
- preservar cascade;
- preservar mobile/a11y;
- documentar ownership.

## S3.7 — Procedural module hooks

Migrar por grupos:

### Editorial/SEO

- metatag_tags_alter;
- token_info/tokens;
- metatags_alter;
- node presave.

### Forms/account

- form_alter;
- password/e-mail callbacks;
- photo/address redirects.

### Content validation/access

- entity_access;
- entity_presave;
- activity validation.

### Commerce

- donation amount validation;
- gateway form hardening.

Não fazer conversão mecânica total em um commit.

## S3.8 — Lifecycle/install

Somente documentar/preparar sem Runtime.

Avaliar depois:

- fresh install parity;
- config/install;
- config/sync;
- update hook idempotency;
- Profile participant/customer history.

## S3.9 — Assets

Criar inventário dos avatars antes de decidir.

Perguntas:

- estão expostos em UI?
- são feature futura?
- precisam de todas as resoluções?
- podem ser otimizados?
- pertencem a Media/files em vez do módulo?

Nenhuma exclusão em S3 sem evidência.

## PRs draft sugeridos

| Ordem | Draft | Risco |
| --- | --- | --- |
| 1 | DI em breadcrumb + Domain subscriber helpers | baixo/médio |
| 2 | split de PortalHooks por concern | médio |
| 3 | Account course presenter + SDC contract | médio |
| 4 | Support access enforcement | alto por comportamento |
| 5 | PortalController split | médio/alto |
| 6 | Wiki DI/presenter | médio |
| 7 | CSS -> SDC por componente | visual |
| 8 | procedural hooks -> Hook classes | médio/alto |
| 9 | Domain resolver extraction | alto |
| 10 | Search/AJAX/Forum | versões futuras |

## Gates Runtime acumulados

Quando o Homelab voltar, cada draft deve ser testado separadamente.

Nunca aplicar a fila inteira de uma vez.
