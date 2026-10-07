# Mapa da documentação

Esta é a página inicial da documentação técnica do ACULTA.

A organização prioriza **estado atual e contratos duráveis**. Histórico de PR,
logs de execução e fases concluídas permanecem no Git/GitHub e não competem com
a documentação canônica.

## Por onde começar

| Preciso entender... | Referência |
| --- | --- |
| arquitetura geral | [architecture/overview.md](architecture/overview.md) |
| quem é dono de cada dado | [architecture/data-ownership.md](architecture/data-ownership.md) |
| multidomínio | [architecture/multidomain.md](architecture/multidomain.md) |
| ambientes | [architecture/environments.md](architecture/environments.md) |
| regras que não podem regredir | [ANTI-REGRESSION.md](ANTI-REGRESSION.md) |
| módulos instalados e função | [modules/README.md](modules/README.md) |
| autenticação/Google/CAPTCHA | [integrations/README.md](integrations/README.md) |
| arquitetura do Portal | [portal/README.md](portal/README.md) |
| roadmap atual | [portal/ROADMAP.md](portal/ROADMAP.md) |
| testes/hardening/release | [operations/README.md](operations/README.md) |
| decisões arquiteturais | [decisions/](decisions/) |
| tema/design system | [../web/themes/custom/aculta/README.md](../web/themes/custom/aculta/README.md) |
| Homelab | [../scripts/homelab/README.md](../scripts/homelab/README.md) |
| Estados SQLite | [../estados/README.md](../estados/README.md) |

## Camadas

```text
architecture/   modelo estrutural e ownership
decisions/      ADRs — decisões estáveis e contexto
modules/        inventário de Core/contrib e responsabilidades
integrations/   comportamento de integrações externas/sensíveis
portal/         contratos e produto aculta_portal
operations/     testes, hardening, release e operação
tema aculta/    apresentação e Component Design System
```

## Referências canônicas por assunto

### Conta e autenticação

- [Módulos de autenticação](modules/AUTHENTICATION.md)
- [Comportamento de autenticação](integrations/AUTHENTICATION.md)
- [Google OAuth](integrations/GOOGLE.md)
- [CAPTCHA / Turnstile](integrations/CAPTCHA.md)
- [Conta, AJAX e Views](modules/ACCOUNT-UI.md)
- [Semântica de apresentação da Conta](portal/ACCOUNT-PRESENTATION-MODEL.md)
- [Matriz SDC/AJAX da Conta](portal/ACCOUNT-SDC-AJAX.md)

### Domain e URLs

- [Multidomínio](architecture/multidomain.md)
- [Módulos Domain/SEO](modules/DOMAIN-SEO.md)
- [Slugs públicos em português](portal/FRIENDLY-PORTUGUESE-SLUGS.md)

### Conteúdo e produto

- [Wiki420](portal/WIKI.md)
- [Revista](portal/MAGAZINE.md)
- [Loja](portal/SHOP.md)
- [Fórum](portal/FORUM.md)
- [Editorial e mídia](modules/EDITORIAL-MEDIA.md)
- [Cursos / LMS / Group](modules/LMS-GROUP.md)
- [Commerce / apoio](modules/COMMERCE.md)

### UI e tema

- [Integração Portal ↔ Design System](portal/COMPONENT-DESIGN-SYSTEM.md)
- [Documentação do tema](../web/themes/custom/aculta/README.md)
- [Design System](../web/themes/custom/aculta/docs/component-design-system.md)
- [Componentes](../web/themes/custom/aculta/docs/components.md)
- [Desenvolvimento do tema](../web/themes/custom/aculta/docs/development.md)

### Operação

- [Estratégia de testes](operations/TESTING.md)
- [Hardening](operations/HARDENING.md)
- [Release e versionamento](operations/RELEASES.md)
- [Homelab](../scripts/homelab/README.md)

## Decisões arquiteturais

- [ADR-001 — Tema versus Portal](decisions/ADR-001-theme-vs-portal.md)
- [ADR-002 — Domain purposes](decisions/ADR-002-domain-purposes.md)
- [ADR-003 — Sessão compartilhada](decisions/ADR-003-shared-session.md)
- [ADR-004 — SQLite no desenvolvimento](decisions/ADR-004-sqlite-development.md)
- [ADR-005 — LMS como fonte de verdade](decisions/ADR-005-lms-integration.md)
- [ADR-006 — Apache como baseline](decisions/ADR-006-web-servers.md)
- [ADR-007 — Bootstrap Component Design System](decisions/ADR-007-bootstrap-component-design-system.md)

## Política

Leia [DOCUMENTATION.md](DOCUMENTATION.md) antes de criar um documento novo.

A regra principal é: **atualize uma referência canônica existente antes de criar
outro arquivo**.
