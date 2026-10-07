# Associação Cultural Antiproibicionista — plataforma Drupal

Plataforma Drupal 11 multidomínio da Associação Cultural Antiproibicionista.

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
| `forum` *(planejado)* | `forum.aculta.org` | comunidade |

## Componentes próprios

- [aculta_portal](web/modules/custom/aculta_portal/README.md) — integração e
  orquestração.
- [ACULTA420](web/themes/custom/aculta420/README.md) — tema público e Bootstrap
  Component Design System.

## Arquitetura

```text
Core + contrib -> aculta_portal -> ACULTA420 -> Bootstrap5
```

User/Profile/Commerce/LMS/Group/Node/Views/Domain continuam fontes de verdade.
O Portal integra. ACULTA420 apresenta.

## Ambientes

- Homelab: Debian + Apache + PHP-FPM + SQLite.
- Produção: Hostinger + Apache + PHP + MariaDB.

Apache é o baseline de servidor web. Configuração específica de ambiente não
pertence ao tema.

## Documentação

Comece em [docs/README.md](docs/README.md).

Referências transversais:

- [Camadas anti-regressão](docs/ANTI-REGRESSION.md)
- [Política de documentação](docs/DOCUMENTATION.md)
- [Operação, testes e releases](docs/operations/README.md)

- Portal: [docs/portal](docs/portal/README.md)
- Módulos: [docs/modules](docs/modules/README.md)
- Tema/design system: [ACULTA420 docs](web/themes/custom/aculta420/docs/README.md)

Segredos e settings locais nunca pertencem ao Git.
