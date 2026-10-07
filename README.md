# Associação Cultural Antiproibicionista — plataforma Drupal

Plataforma Drupal 11 multidomínio da Associação Cultural Antiproibicionista.

Uma única instalação compartilha usuários e subsistemas especializados sem
duplicar as fontes de verdade de Drupal Core e contrib.

## Contextos

| Purpose | Produção | Responsabilidade |
| --- | --- | --- |
| `main` | `aculta.org` | institucional |
| `account` | `conta.aculta.org` | identidade e conta |
| `support` | `apoio.aculta.org` | apoio |
| `magazine` | `coletivo420.aculta.org` | editorial |
| `wiki` | `wiki420.aculta.org` | Wiki420 |
| `shop` | `loja.aculta.org` | comércio |
| `courses` | `cursos.aculta.org` | aprendizagem |
| `forum` *(planejado)* | `forum.aculta.org` | participação comunitária |

## Arquitetura em uma linha

```text
Drupal Core/contrib
      ↓
aculta_portal — integração, domínio, access, presenters
      ↓
tema aculta — Bootstrap Component Design System / apresentação
```

Fontes de verdade principais:

- User: autenticação/conta;
- Profile + Address: dados pessoais;
- Social Auth: identidades OAuth;
- Commerce: pedidos/pagamentos;
- LMS + Group: cursos, matrícula e progresso;
- Node + Taxonomy + Views: editorial/Wiki;
- Domain: host/contexto.

## Mapa da documentação

Comece pelo **[Mapa da documentação](docs/README.md)**.

Referências principais:

- [Arquitetura](docs/architecture/overview.md)
- [Camadas anti-regressão](docs/ANTI-REGRESSION.md)
- [Módulos Drupal](docs/modules/README.md)
- [Integrações externas](docs/integrations/README.md)
- [ACULTA Portal](docs/portal/README.md)
- [Roadmap atual](docs/portal/ROADMAP.md)
- [Operação, testes e releases](docs/operations/README.md)
- [Decisões arquiteturais](docs/decisions/ADR-001-theme-vs-portal.md)
- [Tema aculta](web/themes/custom/aculta/README.md)
- [Homelab](scripts/homelab/README.md)

## Ambientes

- Homelab: Debian + Apache + PHP-FPM + SQLite.
- Produção: Apache + PHP + MariaDB.

Apache é o baseline. Nginx não é alvo de compatibilidade.

Segredos, chaves e settings locais nunca pertencem ao Git.

## Componentes próprios

- [`aculta_portal`](web/modules/custom/aculta_portal/README.md) — camada de integração.
- [tema `aculta`](web/themes/custom/aculta/README.md) — apresentação e design system.

## Regra documental

Mudança de arquitetura, módulo, integração, tema ou comportamento deve atualizar
a documentação canônica correspondente na mesma alteração.

Não criar snapshots de PR/fase como nova fonte de verdade; histórico pertence ao
Git/PR. Ver [Política de documentação](docs/DOCUMENTATION.md).
