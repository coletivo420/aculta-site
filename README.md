# Associação Cultural Antiproibicionista - plataforma Drupal

Plataforma Drupal 11 da Associação Cultural Antiproibicionista. Uma única instalação atende sete contexts ativos por meio de Domain, com um oitavo context de Fórum planejado, compartilhando usuários e subsistemas especializados sem duplicar suas fontes de verdade.

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
| `forum` *(planejado)* | `forum.aculta.org` | fórum e participação comunitária |

## Componentes próprios

- [aculta_portal](web/modules/custom/aculta_portal/README.md) - integração entre Drupal, Domain, conta, Commerce, LMS e conteúdo.
- [tema aculta](web/themes/custom/aculta/README.md) - identidade visual e apresentação.

## Fontes de verdade

User cuida de autenticação; Profile/Address de dados pessoais; Social Auth de identidades externas; Commerce de pedidos e pagamentos; Drupal LMS/Group de cursos, matrículas e progresso; Nodes/Taxonomy/Views de conteúdo; Domain do contexto de host.

## Ambientes

- Homelab: Debian + Apache + PHP-FPM + SQLite.
- Produção: Hostinger + Apache + PHP + MariaDB.

Apache é o baseline definitivo de servidor web do projeto. Homelab e produção
usam a mesma família de servidor, mas VirtualHosts, módulos disponíveis,
permissões, certificados e integrações continuam específicos de cada ambiente.
Nginx não é alvo de compatibilidade; referências remanescentes servem apenas
como histórico de migração.

## Roadmap

O projeto opera temporariamente em modo **GitHub-first / Runtime-last** para
economizar a janela do Codex/Homelab.

A base autoritativa é `origin/main`; trabalho local antigo não publicado é
descartado. Documentação, arquitetura, inventários e preparação avançam pelo
GitHub. Código funcional que depende do Drupal pode ser preparado em draft, mas
só é integrado/released depois da validação Runtime.

| Macrofase | Objetivo |
| --- | --- |
| **S1 — Component contracts** | alinhar o Portal ao ACULTA Bootstrap Component Design System |
| **S2 — Static Portal Audit** | mapear DI, access, cache, markup, queries, AJAX e duplicações |
| **S3 — Behavior-preserving preparation** | preparar presenters/services/refactors em drafts pequenos |
| **S4 — Feature preparation** | preparar Fórum, Participation Hub, Admin, AJAX, Search e Engagement |
| **R0 — Clean baseline** | retornar ao Codex descartando o worktree antigo e sincronizando `origin/main` |
| **R1 — Dependencies/config** | aplicar Composer, módulos e configuração um conjunto por vez |
| **R2 — Functional validation** | Drush, Domain/HTTP, User A/B, AJAX, access/cache e regressão |
| **R3 — Hardening** | segurança, performance, logs e portabilidade SQLite/MariaDB |
| **R4 — Releases** | merge/tag somente do que passou no Runtime |

O roadmap detalhado está em
[docs/portal/ROADMAP.md](docs/portal/ROADMAP.md), e o modo de entrega em
[docs/portal/DELIVERY-MODE.md](docs/portal/DELIVERY-MODE.md).

O Portal consome o
[ACULTA Bootstrap Component Design System](docs/portal/COMPONENT-DESIGN-SYSTEM.md);
o tema avançado é preservado, não reiniciado.

A trilha Google continua paralela:
`G0 Prepared → G1 Production Minimum → G2 Google for Nonprofits → G3 Learning Integration`.

## Documentação

Comece em [docs/README.md](docs/README.md). Segredos e settings locais nunca pertencem ao Git.
