# Associação Cultural Antiproibicionista - plataforma Drupal

Plataforma Drupal 11 da Associação Cultural Antiproibicionista. Uma única instalação atende sete contextos por meio de Domain, compartilhando usuários e subsistemas especializados sem duplicar suas fontes de verdade.

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

## Componentes próprios

- [aculta_portal](web/modules/custom/aculta_portal/README.md) - integração entre Drupal, Domain, conta, Commerce, LMS e conteúdo.
- [tema aculta](web/themes/custom/aculta/README.md) - identidade visual e apresentação.

## Fontes de verdade

User cuida de autenticação; Profile/Address de dados pessoais; Social Auth de identidades externas; Commerce de pedidos e pagamentos; Drupal LMS/Group de cursos, matrículas e progresso; Nodes/Taxonomy/Views de conteúdo; Domain do contexto de host.

## Ambientes

- Homelab: Debian + Nginx + PHP-FPM + SQLite.
- Produção: Hostinger + Apache + PHP + MariaDB.

Configuração de Nginx não é copiada literalmente para Apache.

## Documentação

Comece em [docs/README.md](docs/README.md). Segredos e settings locais nunca pertencem ao Git.
