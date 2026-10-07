# Roadmap atual do ACULTA Portal

Data da revisão: 2026-10-07.

Este documento contém apenas trabalho futuro/relevante. Fases concluídas e
snapshots de execução ficam no histórico Git/PR.

## Baseline atual

Já consolidados no `main`:

- Drupal 11 + Domain com sete purposes ativos;
- Apache + PHP-FPM no Homelab;
- SQLite no Runtime de desenvolvimento;
- Portal como camada de integração;
- Bootstrap Component Design System no tema;
- Turnstile como único CAPTCHA, fail-closed;
- Social Auth Google integrado;
- documentação modular por domínio.

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

## Prioridade 4 — Component Design System

Tema:

- executar H3 `category-label`;
- depois H4 família de cards;
- preservar Bootstrap/Form API;
- não converter Twig em massa.

Referência:
[../../web/themes/custom/aculta/docs/component-design-system.md](../../web/themes/custom/aculta/docs/component-design-system.md).

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
