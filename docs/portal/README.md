# ACULTA Portal

Esta pasta é a fonte de verdade documental do `aculta_portal`.

O módulo existe para integrar capacidades nativas do Drupal e módulos contrib em
uma experiência única para usuários e administradores. Ele não substitui as
fontes de verdade funcionais dessas ferramentas.

## Documentos

- [Arquitetura](ARCHITECTURE.md)
- [Fontes de verdade](SOURCE-OF-TRUTH.md)
- [Módulos upstream](UPSTREAM-MODULES.md)
- [Política AJAX](AJAX.md)
- [Política de slugs amigáveis em português](FRIENDLY-PORTUGUESE-SLUGS.md)
- [S3.3A — Minha Conta AJAX boundary](S3-3A-AJAX-BOUNDARY.md)
- [Integração com o Bootstrap Component Design System](COMPONENT-DESIGN-SYSTEM.md)
- [Minha Conta — matriz SDC, integrações e AJAX](ACCOUNT-SDC-AJAX.md)
- [Minha Conta — semântica compartilhada de apresentação](ACCOUNT-PRESENTATION-MODEL.md)
- [S3.2A — Course presentation boundary](S3-2-ACCOUNT-SDC.md)
- [Fórum](FORUM.md)
- [Wiki420](WIKI.md)
- [Revista / Observatório Coletivo 420](MAGAZINE.md)
- [Loja](SHOP.md)
- [Modo de entrega GitHub-first / Runtime-last](DELIVERY-MODE.md)
- [S2 — Static Portal Audit](STATIC-AUDIT.md)
- [Mapa de refatoração](REFACTOR-MAP.md)
- [S3.5 — Domain policy](S3-5-DOMAIN-POLICY.md)
- [S3.1 — Hooks + Dependency Injection](S3-1-HOOKS-DI.md)
- [S3.8 — Lifecycle/install audit](S3-8-LIFECYCLE-INSTALL.md)
- [S3.9 — Inventário de assets de avatar](S3-9-AVATAR-ASSETS.md)
- [S3.6 — Public CSS ownership](S3-6-CSS-OWNERSHIP.md)
- [Testes](TESTING.md)
- [Versionamento](VERSIONING.md)
- [Portal 0.14 — Admin Hub](S4-0.14-ADMIN-HUB.md)
- [Portal 0.15 — AJAX Consolidation](S4-0.15-AJAX-CONSOLIDATION.md)
- [Portal 0.16 — Search](S4-0.16-SEARCH.md)
- [Portal 0.17 — Engagement](S4-0.17-ENGAGEMENT.md)
- [Portal 0.18 — Deduplication](S4-0.18-DEDUPLICATION.md)
- [Portal 0.19 — Hardening](S4-0.19-HARDENING.md)
- [Portal 1.0 — Release Gates](PORTAL-1.0-RELEASE-GATES.md)
- [R0 — Clean Baseline Runbook](R0-CLEAN-BASELINE.md)
- [R0 — primeira execução / blocker Apache](R0-FIRST-EXECUTION.md)
- [R0 — PASS / fechamento](R0-PASS.md)
- [R1 — Dependency/Config Integration Queue](R1-INTEGRATION-QUEUE.md)
- [R1.1 — PR #23 PASS](R1-1-PR23-PASS.md)
- [R1.2 — blocker Wiki/Media Library](R1-2-WIKI-MEDIA-BLOCKER.md)
- R1.2A — corrigido no PR #60; S3.5 / PR #30 passou Runtime em R1.2B
- [R2 — Functional Validation Matrix](R2-FUNCTIONAL-VALIDATION.md)
- [R3 — Hardening Execution Runbook](R3-HARDENING-EXECUTION.md)
- [R4 — Release and Tagging Runbook](R4-RELEASE-TAGGING.md)
- [Fechamento da Macrofase S](S-MACROPHASE-CLOSURE.md)
- [Roadmap](ROADMAP.md)
- [Integrações Google](../integrations/GOOGLE.md)

## Estado atual

- baseline web do Homelab: Apache + PHP-FPM;
- Runtime de desenvolvimento: SQLite;
- produção: Apache + MariaDB;
- sete purposes ativos: MAIN, ACCOUNT, SUPPORT, MAGAZINE, WIKI, SHOP e COURSES;
- oitavo purpose planejado: FORUM;
- `aculta_portal` continua sendo a camada de integração;
- o tema `aculta` já está em fase avançada do ACULTA Bootstrap Component Design System;
- o Portal opera temporariamente em modo GitHub-first / Runtime-last;
- trabalho local antigo não publicado não é baseline; `origin/main` é autoritativo.

## Regra principal

Antes de escrever código para uma capacidade nova, identificar:

1. qual módulo já é fonte de verdade;
2. qual API pública esse módulo oferece;
3. qual Domain purpose recebe a experiência;
4. qual parte realmente precisa de adaptação pelo Portal;
5. como acesso, cache e privacidade serão preservados;
6. como a feature será testada no Homelab.

Código custom deve existir para **orquestrar, adaptar e integrar**, não para criar
uma segunda implementação da mesma capacidade.
