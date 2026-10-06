# Documentação técnica

## Arquitetura

- [Visão geral](architecture/overview.md)
- [Responsabilidade pelos dados](architecture/data-ownership.md)
- [Multidomínio](architecture/multidomain.md)
- [Ambientes e deploy](architecture/environments.md)

## Decisões

- [ADR-001 - Tema versus Portal](decisions/ADR-001-theme-vs-portal.md)
- [ADR-002 - Domain purposes](decisions/ADR-002-domain-purposes.md)
- [ADR-003 - Sessão compartilhada](decisions/ADR-003-shared-session.md)
- [ADR-004 - SQLite no desenvolvimento](decisions/ADR-004-sqlite-development.md)
- [ADR-005 - Drupal LMS como fonte de verdade](decisions/ADR-005-lms-integration.md)
- [ADR-006 - Apache nos ambientes web](decisions/ADR-006-web-servers.md)

## Tema aculta

A documentação detalhada da camada de apresentação fica em [`web/themes/custom/aculta/docs`](../web/themes/custom/aculta/docs/), incluindo inventário, design system, componentes, templates, acessibilidade, JavaScript, branding e desenvolvimento.

## Referências estudadas

A organização aproveita ideias de projetos Drupal maduros: Domain para documentação de multidomínio; Varbase Core para divisão por features; Open Social para separar arquitetura/desenvolvimento/testes; Vartheme BS5 para tratar o tema como produto técnico. Copiamos disciplina e organização, não armazenamento, dependências ou build tooling.
