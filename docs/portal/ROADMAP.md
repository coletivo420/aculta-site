# Roadmap do ACULTA Portal

Este roadmap organiza a evolução do `aculta_portal` no modo atual
**GitHub-first / Runtime-last**.

O tema `aculta` possui linha própria e já está em fase avançada do
**ACULTA Bootstrap Component Design System**. O Portal consome esse sistema; não
reinicia a refatoração do tema.

## Estado de base

- Portal 0.10.0 Foundation documental: concluída;
- arquitetura Wiki/Revista/Loja/Fórum/Google: documentada;
- Bootstrap Component Design System: adotado por ADR-007;
- trabalho local antigo não publicado/9.3B: descartado como base;
- `origin/main`: única fonte autoritativa;
- validação Runtime: concentrada para uma janela posterior.

Ver [DELIVERY-MODE.md](DELIVERY-MODE.md).

# Macrofase S — trabalho sem Homelab

**Status global: encerrada no limite seguro do trabalho sem Runtime.**

Documento de fechamento:
[S-MACROPHASE-CLOSURE.md](S-MACROPHASE-CLOSURE.md).

Código executável preparado permanece em PRs draft; "encerrada" aqui significa
que o trabalho seguro sem Homelab foi esgotado, não que esses drafts passaram em
Runtime.

## S1 — Component contracts

**Estado: concluída documentalmente.**

Objetivo:

alinhar `aculta_portal` ao Component Design System.

Entregas:

- contrato presenter/view-model -> SDC;
- famílias comuns de card/status/empty-state/action-list;
- mapeamento Bootstrap antes de markup custom;
- inventário de telas Portal;
- matriz de access/cache por componente;
- documentação de props/slots;
- definição de componentes candidatos para Conta, Cursos, Wiki, Fórum e Loja.

Documentação pode ser integrada ao main.

Código executável fica draft se não houver Runtime.

## S2 — Static Portal Audit

**Estado: concluída documentalmente.**

Resultado: [STATIC-AUDIT.md](STATIC-AUDIT.md) e [REFACTOR-MAP.md](REFACTOR-MAP.md).

Objetivo:

auditar o código existente sem mudar comportamento em produção.

Mapear:

- controllers com markup/apresentação excessiva;
- usos de `\Drupal::`;
- DI;
- render arrays;
- cache metadata;
- entity access;
- URLs por Domain purpose;
- queries custom;
- AJAX próprio;
- duplicações com Core/contrib;
- pontos que devem consumir SDC.

Saída:

inventário + plano de refatoração por arquivo.

## S2.1 — Account SDC/AJAX contract

**Estado: concluída documentalmente.**

Resultado: [ACCOUNT-SDC-AJAX.md](ACCOUNT-SDC-AJAX.md).

Objetivo:

mapear cada elemento da Minha Conta entre fonte de verdade, integração Portal,
SDC e comportamento AJAX.

Decisões:

- SDC é apresentação; não substitui AJAX/Form API;
- navegação da Conta permanece progressivamente assíncrona;
- CEP permanece AJAX;
- OAuth, checkout e confirmações externas não viram fetch genérico;
- listagens de participação preferem Views AJAX;
- componentes simples/reutilizáveis entram antes de shell/forms complexos.

## S3 — Behavior-preserving preparation

**Estado: preparação disponível concluída; drafts aguardam Runtime.**

Objetivo:

preparar refactors pequenos e reversíveis.

Exemplos:

- extrair presenters/view-models;
- reduzir markup de controller;
- normalizar estruturas de dados;
- preparar interfaces/adapters;
- organizar services;
- preparar Minha Conta para SDC sem mover regra de negócio ao tema;
- manter AJAX somente onde definido na matriz da Conta.

Sem Runtime:

- manter PR como draft se houver mudança executável;
- declarar `RUNTIME STATUS: DEFERRED`;
- não taggear release.

## S3.1 — Hooks + Dependency Injection

**Estado: Runtime PASS; PR #23 validado e integrado.**

Entregas preparadas:

- `PortalHooks` com constructor injection;
- `AccountShellBuilder` como serviço;
- breadcrumb sem service locator;
- Domain purpose subscriber com collaborators injetados;
- política funcional preservada;
- hooks procedurais do `.module` deixados para S3.7.

Gates e escopo:
[S3-1-HOOKS-DI.md](S3-1-HOOKS-DI.md).

O código desta subfase passou no Homelab antes do merge.

## S3.5 — Domain policy

**Estado: Runtime PASS; validação R1.2B concluída, integração do PR #30 em andamento.**

Documento:
[S3-5-DOMAIN-POLICY.md](S3-5-DOMAIN-POLICY.md).

Entregas:

- ContentPurposeResolver;
- subscriber reduzido a enforcement;
- Domain entity clonada antes de scheme local;
- FORUM continua não ativado.

## S3.2 — Minha Conta -> SDC

A evolução da Conta deve separar semântica Portal de implementação visual do
tema. Contrato Portal não equivale a SDC aprovado.

Executar em pequenos drafts, preferencialmente nesta ordem:

1. status badge;
2. empty state;
3. summary card;
4. action list;
5. course card;
6. account shell;
7. identity;
8. data section;
9. security/integration cards;
10. support cards;
11. participation cards;
12. photo editor.

Cada conversão deve remover CSS/markup antigo somente depois de paridade.

A matriz normativa está em
[ACCOUNT-SDC-AJAX.md](ACCOUNT-SDC-AJAX.md).

## S3.2B — Shared presentation semantics

**Estado: concluída documentalmente.**

Define os view-models semânticos compartilhados da Conta sem antecipar SDCs que
o tema ainda não aprovou.

Contratos:

- status;
- action;
- empty state;
- summary;
- action list.

A auditoria H3 do tema continua autoritativa sobre quais desses contratos
realmente viram SDC.

Documento:
[ACCOUNT-PRESENTATION-MODEL.md](ACCOUNT-PRESENTATION-MODEL.md).

## S3.2C — Segurança e Conexões

**Estado: próxima.**

Objetivo:

extrair presenters para:

- Social Auth/Google;
- estados de conexão;
- segurança;
- disponibilidade de mudança de e-mail;
- alteração de e-mail pendente.

Preservar:

- OAuth redirect/callback;
- Form API;
- Email Confirmer;
- SMTP readiness;
- progressive enhancement.

## S3.6 — Public CSS ownership

**Estado: concluída documentalmente.**

Documento:
[S3-6-CSS-OWNERSHIP.md](S3-6-CSS-OWNERSHIP.md).

Nenhum CSS foi movido. Ownership e ordem de migração foram definidos para evitar
conflito com a Fase H do tema.

## S3.7 — Procedural hooks

**Estado: próxima.**

Objetivo:

preparar migração por grupos para Hook classes OOP, sem conversão massiva.

## S3.8 — Lifecycle/install

**Estado: concluída documentalmente.**

Documento:
[S3-8-LIFECYCLE-INSTALL.md](S3-8-LIFECYCLE-INSTALL.md).

Decisão: nenhum update hook histórico será reescrito sem fresh-install e
upgrade-path tests.

## S3.9 — Assets/avatar inventory

**Estado: concluída documentalmente.**

Documento:
[S3-9-AVATAR-ASSETS.md](S3-9-AVATAR-ASSETS.md).

Resultado:

- 107 PNGs / ~107,7 MiB;
- nenhum consumidor versionado encontrado por busca estática;
- nenhuma remoção autorizada sem inventário Runtime;
- ownership futuro definido como decisão separada.

## S3.3A — Minha Conta AJAX boundary

**Estado: concluída documentalmente.**

Documento:
[S3-3A-AJAX-BOUNDARY.md](S3-3A-AJAX-BOUNDARY.md).

Preservar como requisito:

- navegação parcial entre seções;
- Básicos/Endereço;
- CEP;
- Views AJAX de participação;
- behaviors contrib como Flag.

Migrar gradualmente `account-navigation.js` para APIs Core.

Não transformar OAuth, Commerce checkout, confirmação de e-mail ou navegação de
curso em AJAX custom.

## S4 — Feature specifications and draft branches

Preparar especificações e, quando seguro, branches draft das capacidades
futuras.

### Portal 0.11 — Forum Foundation

- `drupal/forum`;
- Domain FORUM;
- topic/reply;
- isolation;
- shared session;
- Bootstrap/SDC presentation contracts.

Composer/config/runtime ficam pendentes da janela R.

### Portal 0.12 — Forum Participation

- meus tópicos;
- minhas respostas;
- Views;
- participation presenters;
- SDC contracts.

### Portal 0.13 — Participation Hub

- Fórum;
- Wiki;
- Cursos;
- activity aggregation sem storage paralelo.

### Portal 0.14 — Admin Hub

**Especificação concluída.**

Documento: [S4-0.14-ADMIN-HUB.md](S4-0.14-ADMIN-HUB.md).

- atalhos/status;
- access-first;
- sem CRUD paralelo;
- admin theme permanece Drupal-native;
- diagnóstico sem segredos.

### Portal 0.15 — AJAX Consolidation

**Especificação concluída.**

Documento: [S4-0.15-AJAX-CONSOLIDATION.md](S4-0.15-AJAX-CONSOLIDATION.md).

- preservar UX assíncrona;
- migrar `account-navigation.js` por fluxo, não em massa;
- Views AJAX;
- Form API AJAX;
- Drupal Ajax;
- Core HTMX quando apropriado;
- CEP permanece AJAX específico.

### Portal 0.16 — Search

**Especificação concluída.**

Documento: [S4-0.16-SEARCH.md](S4-0.16-SEARCH.md).

- Search API + Views;
- backend Database Search como candidato inicial;
- Wiki;
- Fórum;
- access/grants/revision-aware;
- substituição da busca LIKE somente após paridade.

### Portal 0.17 — Engagement

**Especificação concluída.**

Documento: [S4-0.17-ENGAGEMENT.md](S4-0.17-ENGAGEMENT.md).

- Flag ^5.1;
- Comment Notify ^1.6;
- favoritos/follows/notificações como conceitos separados;
- privacidade/SMTP/opt-in;
- usar AJAX/upstream, sem toggle/storage paralelo.

### Portal 0.18 — Deduplication

**Especificação concluída.**

Documento: [S4-0.18-DEDUPLICATION.md](S4-0.18-DEDUPLICATION.md).

- breadcrumb;
- Schema Metatag;
- menus;
- busca antiga;
- AJAX antigo;
- Support tables;
- CEP somente onde upstream atingir paridade;
- service locator.

Nenhuma remoção sem substituto e teste de paridade.

### Portal 0.18.1 — Friendly Portuguese Slugs

**Requisito transversal adicionado ao roadmap.**

Documento:
[FRIENDLY-PORTUGUESE-SLUGS.md](FRIENDLY-PORTUGUESE-SLUGS.md).

Objetivo:

- inventariar todas as rotas públicas humanas;
- padronizar slugs amigáveis em português em todos os purposes;
- manter a mesma estrutura de path entre Homelab e produção;
- preservar Core/contrib callbacks, AJAX, OAuth, webhooks e admin internals;
- criar redirects para slugs públicos substituídos;
- alinhar canonical, sitemap, menus, breadcrumbs e Search;
- usar Pathauto/config antes de PHP custom quando possível.

Purposes cobertos:

- MAIN;
- ACCOUNT;
- SUPPORT;
- MAGAZINE;
- WIKI;
- SHOP;
- COURSES;
- FORUM.

Essa fase deve ocorrer depois que as principais features/rotas estiverem
definidas e antes do hardening/release final.

# Macrofase R — retorno ao Runtime/Codex

## R0 — Clean baseline

**Runbook concluído.**

Documento: [R0-CLEAN-BASELINE.md](R0-CLEAN-BASELINE.md).

Primeira ação no Homelab:

- descartar trabalho local antigo;
- sincronizar exatamente com `origin/main`;
- não recuperar a antiga 9.3B;
- validar baseline Apache + SQLite;
- parar se o baseline puro falhar.

## R1 — Dependency and config integration

**Runbook concluído.**

Documento: [R1-INTEGRATION-QUEUE.md](R1-INTEGRATION-QUEUE.md).

Aplicar um conjunto preparado por vez.

Primeiro reconciliar/validar os drafts estruturais existentes (#23, #30, #24,
#26, #27, #28, #29, #32–#35) respeitando suas dependências.

Depois seguir as features 0.11 -> 0.19 na ordem do roadmap.

## R2 — Functional validation

**Runbook concluído.**

Documento: [R2-FUNCTIONAL-VALIDATION.md](R2-FUNCTIONAL-VALIDATION.md).

Executar matriz por feature/purpose/identidade:

- Drupal bootstrap;
- config/updatedb;
- Domain/HTTP;
- shared session;
- User A/User B;
- access/cache;
- AJAX/fallback;
- failure modes;
- mobile/a11y;
- regressão dos subsistemas.

## R3 — Hardening / Portal 0.19

**Especificação e runbook concluídos.**

Documentos:

- [S4-0.19-HARDENING.md](S4-0.19-HARDENING.md)
- [R3-HARDENING-EXECUTION.md](R3-HARDENING-EXECUTION.md).

Executar no Runtime:

- access/cache;
- proteção de mutações;
- session/Domain;
- higiene de configuração sensível;
- integrações;
- performance;
- cron/queues;
- logs/headers;
- dependency audit;
- SQLite/MariaDB portability;
- failure modes;
- rollback.

## R4 — Releases

**Runbook concluído.**

Documento: [R4-RELEASE-TAGGING.md](R4-RELEASE-TAGGING.md).

Somente depois de PASS Runtime:

- merge dos PRs funcionais;
- CHANGELOG;
- tags `portal-vX.Y.Z`;
- evidence pack;
- deploy/rollback;
- GitHub Release;
- smoke pós-deploy.

# Trilha paralela — Google

Mantida fora do SemVer Portal:

`G0 Prepared -> G1 Production Minimum -> G2 Google for Nonprofits -> G3 Learning Integration`.

Ver [Integrações Google](../integrations/GOOGLE.md).

# Portal 1.0.0 — Stable

**Gates especificados.**

Documento: [PORTAL-1.0-RELEASE-GATES.md](PORTAL-1.0-RELEASE-GATES.md).

Gates finais:

- Conta;
- dados/CEP;
- apoio;
- cursos;
- Wiki;
- Fórum;
- Participation Hub;
- Admin Hub;
- Search API;
- AJAX consolidado;
- slugs públicos amigáveis em português em todos os purposes;
- canonical/sitemap/redirects coerentes com os slugs públicos;
- Component Design System aplicado às experiências públicas;
- Domain isolation;
- Apache Homelab;
- SQLite Runtime;
- compatibilidade MariaDB;
- security review;
- runbook/deploy;
- documentação completa.

## Disciplina

No modo atual:

**GitHub prepara; Runtime comprova.**

Nenhum draft Runtime-deferred conta como versão concluída.
