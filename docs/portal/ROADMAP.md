# Modernização Drupal 11+ Aculta Portal

Atualizado em 2026-10-08. Plano técnico de modernização do módulo `aculta_portal`, separado das prioridades gerais do produto.

Repositório: `coletivo420/aculta-site`; branch `refactor/aculta-portal-p1-drupal11-standards`; PR [#90](https://github.com/coletivo420/aculta-site/pull/90).
Referência normativa: [DRUPAL-11-STANDARDS.md](DRUPAL-11-STANDARDS.md). Transferência entre agentes: [guia de continuidade](MODERNIZACAO-DRUPAL-11-HANDOFF.md).

## Objetivo e restrições

Core Drupal 11.3+ com verificação de APIs no Core instalado; preferir práticas modernas e registrar separadamente deprecações oficiais e dívidas normativas ACULTA. Arquitetura: Core/contrib → Portal → contrato neutro → ACULTA420. Evitar novos service locators, storage paralelo, bypass de access, URLs hardcoded e lógica financeira custom redundante. MAIN mantém administração, cart, checkout e payment.

## Todas as fases

| Fase | Entrega | Estado |
| --- | --- | --- |
| P0 | Auditoria e inventário | Concluída |
| P1 + P1-R | Baseline Drupal 11+, documentação, IA, gates | Revisada estaticamente |
| P2.1–P2.3 + P2-R | Token/Editorial/Library OOP e DI | Revisada estaticamente |
| P3.1–P3.3 | Form API/CallableResolver/callbacks exactly-once | Revisada estaticamente |
| P4.1–P4.3 + P4-R | form_alter, entity_access, entity_presave OOP; .module vazio removido | Revisada estaticamente |
| P5.1 | SupportForm: storage e BlockManager injetados | Concluída estaticamente |
| P5.2–P5.7 e P5-R | Views, EntityQuery, controllers, storage e access | Revisada estaticamente; Homelab parcial (sessão de usuário comum pendente) |
| P6 | Render API, cache, privacidade, Domain | P6.2 concluída (imagem DEFERRED); P6.1/P6.3–P6.6 verificadas no Homelab |
| P7 | Subscribers, serviços, multidomínio | Concluída e verificada no Homelab; sobreposição de `entity.user.edit_form` mantida por defesa em profundidade |
| P8 | Deprecações e prontidão D12/D13 | Revisada estaticamente; 0 achados em código; pendências de Composer (`require.php`, `composer/semver`) e PHP 8.5 |
| P9 | Hardening, segurança, documentação e gates | Revisada e verificada; rollback de código documentado em `HARDENING-P9.md` |
| P10 | Codex/Homelab, homologação, correções e merge | Planejada |

Na P1 havia 18 funções runtime procedurais no `.module`; após P4, zero e arquivo removido. Lifecycle procedural exigido pelo Core é exceção legítima. Testes completos em runtime ainda NÃO foram executados.

## P5-extra-2 — economia de tokens e roteamento de modelos (CONCLUÍDA)

Política para agentes: pesquisa, inventário e edição simples usam modelo econômico; implementação moderada usa capacidade proporcional; revisão final e atividades sensíveis (access, cache privado, Domain, Auth e Commerce) exigem modelo de maior capacidade. Utilitário somente leitura em `scripts/portal-agent-budget.py`, política em [AGENT-TOKEN-ECONOMY.md](AGENT-TOKEN-ECONOMY.md). Não há mudança automática de modelo nem preços presumidos. A próxima execução continua P5.2-A.

## P5-extra-1 — remoção de escopo de portabilidade (CONCLUÍDA)

Portabilidade, conversão, migração e compatibilidade entre motores SQLite/MariaDB **não pertencem** ao módulo `aculta_portal` nem à iniciativa **Modernização Drupal 11+ Aculta Portal**. Essa responsabilidade é exclusiva do projeto independente **DBTNG-2**. Não incluir testes de migração/portabilidade entre bancos, adaptadores de banco ou conversores nas fases P5, P9 ou P10 deste roadmap. É permitido documentar qual SGBD cada ambiente utiliza, sem atribuir ao Portal responsabilidade de migração ou compatibilidade entre motores. As consultas do Portal continuam obrigadas a usar APIs públicas do Drupal.

## P5 — EntityQuery, Views, Storage, DI e Access

### P5.1 — concluída
`src/Support/Form/SupportForm.php`: troca de `PaymentGateway::load()` por `EntityTypeManagerInterface` e de `\\Drupal::service('plugin.manager.block')` por `BlockManagerInterface`; strict_types, gate e changelog. Commit `75362a35c14156be9274ed1a29181e71a7bcf6e6`. Mantido fail-closed e Commerce Donation Flow; homologação runtime pendente.

### P5.2-A — Cursos (PRÓXIMA)
`src/Controller/CoursesController.php`: avaliar `views_embed_view('courses_catalog', 'block_1')` e confirmar a API do Core instalado; quando suportado, trocar por render element `#type => 'view'`. Preservar View/display, argumentos, empty state, cache, access, pager, filtros, attachments. Não pré-renderizar HTML ou introduzir fábrica de Views sem necessidade. Atualizar gate, changelog e docs no mesmo commit; smoke Homelab pendente.

### P5.2-B — Wiki420
`src/Controller/WikiController.php`: inventariar `views_embed_view` e `Views::getView`; distinguir renderização simples de execução programática. Migrar cada ponto apenas com paridade de display, filtros, argumentos, paginação, access, cache e isolamento do purpose WIKI.

### P5.2-R — revisão de Views
Comparação pré/pós para cada display, critérios de access/cache, output vazio e registro de testes pendentes; teto do gate zerado somente para wrappers eliminados.

### P5.3 — EntityQuery/access
Inventariar `getQuery()`, `entityQuery()`, `loadByProperties()`; impor `accessCheck(TRUE/FALSE)` explícito e justificado onde aplicável; validar filtros de UID, bundle, status, idioma, Domain, ownership. `accessCheck(TRUE)` não substitui verificações individuais de entity access.

### P5.4 — Controllers/DI
- P5.4-A: `PortalController`, BlockManager e Social Auth.
- P5.4-B: `PortalController`, Email Confirmer.
- P5.4-C: `PortalController`, route match/form de conta e preservação de parâmetros com try/finally.
- P5.4-D: `PortalRequirementsController`, ThemeHandler, Composer/root path.
- P5.4-E: controllers residuais, inclusive Wiki.
Cada recorte tem paridade funcional, DI e redução de teto no gate.

### P5.5 — Storage
Uniformizar storages injetados; revisar Profile `loadByUser`, `loadByProperties`, `loadMultiple`, Commerce customer e Social Auth, sem storage paralelo ou acesso direto a tabelas internas contrib.

### P5.6 — Access
Testar A vs B, operações view/update/delete, Wiki fora do Domain correto, conta privada, vínculos OAuth, perfis Commerce e cacheability de AccessResult. Preservar fail-closed do Mercado Pago.

### P5.7 / P5-R — revisão formal
Validar APIs modernas, EntityQuery, Views, DI, access, cache, storage, invariantes e gates, documentando resultados realmente executados e pendências de runtime.

## P6 — Render/cache/privacidade
P6.1 inventário render arrays; P6.2 tokens de URL/imagem e cache Domain/alias/host/scheme (`domain`, `url.site`); P6.3 Conta privada; P6.4 Views/LMS/Group; P6.5 Form API/AccessResult; P6.6 contrato Portal → ACULTA420; P6-R revisão de contexts, tags, max-age e isolamento.

## P7 — Subscribers/serviços/multidomínio
P7.1 inventário listeners; P7.2 tags legadas e API atual; P7.3 isMainRequest e subrequests; P7.4 DI; P7.5 Domain purpose/rotas; P7.6 eventos Conta/OAuth/Commerce; P7.7 deduplicação; P7-R revisão de prioridades/segurança.

## P8 — Deprecações/prontidão D12/D13
P8.1 inventário; P8.2 substituições seguras; P8.3 Upgrade Status/Rector conforme pertinência; P8.4 matriz contrib; P8.5 PHP/Symfony/Composer; P8.6 matriz CURRENTLY RECOMMENDED IN D11 / DEPRECATED IN D11 / REMOVED/CHANGED IN D12 / ANNOUNCED FOR D13; P8-R revisão. Nunca declarar compatibilidade major sem validar Core e contrib.

## P9 — Hardening final
P9.1 segredos/segurança; P9.2 erros/failure modes; P9.3 segurança operacional e confiabilidade; P9.4 performance; P9.5 gates; P9.6 código órfão; P9.7 documentação/IA; P9.8 rollback/release readiness; P9-R revisão acumulada.

## P10 — Codex/Homelab
Verificar branch, HEAD e main; lint PHP integral e gate; Composer validate/audit; `drush cr`; `updatedb:status` e `config:status`; smoke Conta/Wiki/Cursos/Social Auth/Commerce/Domain e isolamento A/B; correções finas e revisão final. Não executar updb/cim/cex automaticamente. Merge só após validação e autorização.

## Protocolo de execução
Uma subfase por vez na **mesma branch/PR #90**; sem merge/rebase/force push prematuro. Sempre código + gate + changelog + documentação; ao fim de cada família revisão -R. Reportar separadamente estático, lint, gate real e Homelab. Não assumir que histórico de chat substitui código Git.

---

## Prioridades do produto independentes desta modernização

## Prioridade 1 — fechar internacionalização pt-BR

PR #63:

- Language/Locale/Config Translation;
- catálogo local mínimo;
- UI de login/recuperação em pt-BR;
- gate Runtime versionado;
- classificar drift de Configuration Sync antes do merge.

## Prioridade 2 — Conta: segurança, conexões e dados

Reimplementar sobre a `main` atual — não reutilizar cegamente as antigas drafts
superseded.

Objetivos:

- presenters de Segurança/Conexões;
- identidade/dados;
- access/cache explícitos;
- Social Auth/Email Confirmer/Profile como fontes de verdade;
- preservar Form API, OAuth e AJAX/fallback.

## Prioridade 3 — AJAX da Conta

Reduzir `account-navigation.js` por fluxo, sem big-bang.

Direção:

- Drupal AJAX / Core HTMX / Views AJAX / Form API quando apropriado;
- preservar URL/history/focus/status/behaviors;
- full-page fallback obrigatório;
- VVJT apenas dentro de Views específicas.

## Prioridade 4 — ACULTA420 Design System

O tema evolui por releases próprias, começando em 0.1.0:

- 0.2.0 — Foundations 2.0;
- 0.3.0 — Card System v1;
- 0.4.0 — Patterns v1;
- preservar Bootstrap/Form API;
- não converter Twig em massa.

Referência:
[../../web/themes/custom/aculta420/docs/roadmap.md](../../web/themes/custom/aculta420/docs/roadmap.md).

## Prioridade 5 — Fórum e Participation Hub

Quando ativado:

- Domain FORUM;
- Forum/Node/Comment/Taxonomy como fontes;
- tópicos/respostas;
- participação agregada sem storage paralelo;
- shared session;
- access/cache/Domain.

## Prioridade 6 — Search e Engagement

Avaliar/implementar somente com necessidade e Runtime:

- Search API + Views para Wiki/Fórum;
- Flag para favoritos/follows;
- Comment Notify para notificações;
- opt-in/privacy/SMTP;
- remover busca/integrações antigas somente após paridade.

## Prioridade 7 — deduplicação e hardening

- remover código custom quando Core/contrib já cobrir com paridade;
- revisar service locator residual;
- access/cache;
- cron/queues/logs/headers;
- dependency audit;
- auditar cacheability dos tokens de URL/imagem derivados de `DomainPurposeManager`, incluindo entidade Domain/alias e variação de host/scheme (`domain` / `url.site`) antes de considerar a P6 de cache concluída;
- failure modes/rollback.

Ver [../operations/HARDENING.md](../operations/HARDENING.md).

## Verificação de conta/e-mail

Planejada de forma **condicional**.

Antes de desenvolver ferramenta própria, procurar alternativa Core/contrib.
Se nenhuma atender, implementar solução para:

- contas criadas fora de OAuth;
- confirmação de novo e-mail;
- token único/expirável;
- flood control;
- proteção contra enumeração.

Ver [../modules/AUTHENTICATION.md](../modules/AUTHENTICATION.md).

## Portal 1.0

1.0 significa baseline integrado, testado, operável e documentado; não significa
implementar todas as ideias do roadmap.

Gates: [../operations/RELEASES.md](../operations/RELEASES.md).
