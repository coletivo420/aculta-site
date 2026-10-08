# Roadmap atual do ACULTA Portal

Data da revisão: 2026-10-08.

Este documento contém apenas trabalho futuro/relevante. Fases concluídas e
snapshots de execução ficam no histórico Git/PR.

## Baseline atual

Já consolidados no `main`:

- Drupal 11 + Domain com sete purposes ativos;
- Apache + PHP-FPM no Homelab;
- SQLite no Runtime de desenvolvimento;
- Portal como camada de integração;
- ACULTA420 Bootstrap Component Design System 0.1.0 como fundação do tema;
- Turnstile como único CAPTCHA, fail-closed;
- Social Auth Google integrado;
- documentação modular por domínio.

## Modernização técnica Drupal 11+ em curso

Esta sequência é executada em subfases pequenas, sempre com revisão formal antes
de avançar para a próxima família:

1. **P4-R — revisão OOP/DI**: revisar `form_alter`, `entity_access` e
   `entity_presave`, remover resíduos procedurais e consolidar o gate.
2. **P5 — EntityQuery / Views / access**: eliminar service locators e static
   entity loads das áreas-alvo; toda EntityQuery declara `accessCheck()`;
   wrappers de Views só são substituídos com paridade comprovada.
3. **P6 — cacheability**: revisar `max-age: 0`, contexts/tags, tokens
   Domain/alias, dados privados e contratos Portal → tema.
4. **P7 — subscribers / services / multidomínio**: modernizar tags e DI,
   revisar `isMainRequest()`, prioridades/event races e preservar MAIN como
   autoridade para admin/cart/checkout/payment.
5. **P8 — deprecações / Drupal 12 readiness**: classificar recomendado em D11,
   deprecado em D11, removido/mudado em D12 e anunciado para D13; validar
   contrib antes de declarar compatibilidade.
6. **P9 — hardening final**: documentação, gates, failure modes, segurança,
   SQLite/MariaDB, dependency audit e limpeza residual.
7. **Finalização Codex/Homelab**: lint integral, gate real, `drush cr`,
   Composer validate/audit, `updatedb:status`, `config:status` e smokes;
   somente depois revisar/mergear a PR cumulativa.

P1, P2 e P3 já possuem revisões formais. A P4-R fecha a família OOP/DI antes
da P5.

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
- portabilidade SQLite/MariaDB;
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
