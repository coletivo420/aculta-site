# Mapa da documentação

Esta é a entrada canônica da documentação técnica da plataforma.

A organização prioriza **estado atual, contratos e runbooks duráveis**.
Snapshots de PR/fase, SHAs e logs pertencem ao Git/GitHub.

## Por onde começar

| Preciso entender... | Referência |
| --- | --- |
| arquitetura geral | [architecture/overview.md](architecture/overview.md) |
| ownership de dados | [architecture/data-ownership.md](architecture/data-ownership.md) |
| multidomínio | [architecture/multidomain.md](architecture/multidomain.md) |
| ambientes | [architecture/environments.md](architecture/environments.md) |
| regras que não podem regredir | [ANTI-REGRESSION.md](ANTI-REGRESSION.md) |
| política de documentação | [DOCUMENTATION.md](DOCUMENTATION.md) |
| módulos Drupal | [modules/README.md](modules/README.md) |
| integrações | [integrations/README.md](integrations/README.md) |
| ACULTA Portal | [portal/README.md](portal/README.md) |
| roadmap do Portal | [portal/ROADMAP.md](portal/ROADMAP.md) |
| operação/testes/releases | [operations/README.md](operations/README.md) |
| decisões arquiteturais | [decisions/](decisions/) |
| ACULTA420 | [../web/themes/custom/aculta420/README.md](../web/themes/custom/aculta420/README.md) |
| roadmap ACULTA420 | [../web/themes/custom/aculta420/docs/roadmap.md](../web/themes/custom/aculta420/docs/roadmap.md) |
| Homelab | [../scripts/homelab/README.md](../scripts/homelab/README.md) |
| Estados SQLite | [../estados/README.md](../estados/README.md) |

## Camadas

```text
architecture/   modelo estrutural e ownership
decisions/      ADRs
modules/        inventário Core/contrib
integrations/   integrações externas/sensíveis
portal/         contratos e produto aculta_portal
operations/     testes, hardening e releases
ACULTA420/      apresentação e Component Design System
```

## Conta e autenticação

- [Módulos de autenticação](modules/AUTHENTICATION.md)
- [Comportamento de autenticação](integrations/AUTHENTICATION.md)
- [Google OAuth](integrations/GOOGLE.md)
- [CAPTCHA / Turnstile](integrations/CAPTCHA.md)
- [Conta, AJAX e Views](modules/ACCOUNT-UI.md)
- [Apresentação da Conta](portal/ACCOUNT-PRESENTATION-MODEL.md)
- [Matriz SDC/AJAX](portal/ACCOUNT-SDC-AJAX.md)

## Domain e URLs

- [Multidomínio](architecture/multidomain.md)
- [Domain/SEO](modules/DOMAIN-SEO.md)
- [Slugs públicos](portal/FRIENDLY-PORTUGUESE-SLUGS.md)

## Conteúdo e produto

- [Wiki420](portal/WIKI.md)
- [Revista](portal/MAGAZINE.md)
- [Loja](portal/SHOP.md)
- [Fórum](portal/FORUM.md)
- [Editorial e mídia](modules/EDITORIAL-MEDIA.md)
- [LMS / Group](modules/LMS-GROUP.md)
- [Commerce](modules/COMMERCE.md)

## UI e tema

- [Integração Portal ↔ ACULTA420](portal/COMPONENT-DESIGN-SYSTEM.md)
- [Tema ACULTA420](../web/themes/custom/aculta420/README.md)
- [Arquitetura ACULTA420](../web/themes/custom/aculta420/docs/architecture.md)
- [Componentes ACULTA420](../web/themes/custom/aculta420/docs/components.md)
- [Desenvolvimento ACULTA420](../web/themes/custom/aculta420/docs/development.md)

## Operação

- [Testes](operations/TESTING.md)
- [Hardening](operations/HARDENING.md)
- [Releases](operations/RELEASES.md)
- [Homelab](../scripts/homelab/README.md)

## Decisões

- [ADR-001 — Tema versus Portal](decisions/ADR-001-theme-vs-portal.md)
- [ADR-002 — Domain purposes](decisions/ADR-002-domain-purposes.md)
- [ADR-003 — Sessão compartilhada](decisions/ADR-003-shared-session.md)
- [ADR-004 — SQLite](decisions/ADR-004-sqlite-development.md)
- [ADR-005 — LMS como fonte de verdade](decisions/ADR-005-lms-integration.md)
- [ADR-006 — Apache](decisions/ADR-006-web-servers.md)
- [ADR-007 — ACULTA420 Component Design System](decisions/ADR-007-bootstrap-component-design-system.md)

## Regra documental

Mudanças de arquitetura, módulo, integração, tema ou comportamento devem atualizar
a documentação canônica correspondente na mesma alteração.

Não criar novo snapshot de fase como fonte de verdade. Ver
[DOCUMENTATION.md](DOCUMENTATION.md).
