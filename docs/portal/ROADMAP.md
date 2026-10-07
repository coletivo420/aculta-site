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

**Estado: próxima.**

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

- plano de migração de `account-navigation.js`;
- Views AJAX;
- Form API AJAX;
- Drupal Ajax;
- Core HTMX quando apropriado.

### Portal 0.16 — Search

- Search API;
- Wiki;
- Fórum;
- substituição futura da busca LIKE.

### Portal 0.17 — Engagement

- Flag;
- Comment Notify;
- privacidade/SMTP/opt-in.

### Portal 0.18 — Deduplication

- breadcrumb;
- Schema Metatag;
- menus;
- busca antiga;
- AJAX antigo;
- Support tables;
- CEP override excessivo.

Não remover customização necessária do CEP sem paridade comprovada.

# Macrofase R — retorno ao Runtime/Codex

## R0 — Clean baseline

Primeira ação no Homelab:

- descartar trabalho local antigo;
- sincronizar exatamente com `origin/main`;
- não recuperar a antiga 9.3B;
- validar baseline Apache + SQLite.

## R1 — Dependency and config integration

Aplicar um conjunto preparado por vez.

Começar por Portal 0.11:

- Composer;
- Forum;
- Domain;
- config export/import;
- cache;
- bootstrap.

Depois seguir versões na ordem do roadmap.

## R2 — Functional validation

Executar:

- Drupal bootstrap;
- config/updatedb;
- Domain matrix;
- HTTP;
- shared session;
- User A/User B;
- access;
- cache;
- AJAX;
- mobile/a11y onde aplicável;
- regressão Wiki/Cursos/Commerce/Conta.

## R3 — Hardening

Equivale ao alvo Portal 0.19:

- permissions;
- CSRF;
- Security Review;
- performance;
- cron;
- logs;
- SQLite/MariaDB portability;
- falhas externas;
- deploy/runbook.

## R4 — Releases

Somente depois de PASS Runtime:

- merge dos PRs funcionais;
- CHANGELOG;
- tags `portal-vX.Y.Z`;
- snapshots/Estados quando realmente necessários;
- preparação de produção.

# Trilha paralela — Google

Mantida fora do SemVer Portal:

`G0 Prepared -> G1 Production Minimum -> G2 Google for Nonprofits -> G3 Learning Integration`.

Ver [Integrações Google](../integrations/GOOGLE.md).

# Portal 1.0.0 — Stable

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
