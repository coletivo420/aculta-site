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

O desenvolvimento do Portal segue versões independentes da refatoração do tema.
Cada versão funcional deve ser implementada em uma branch curta, testada no
Homelab, documentada e integrada antes da próxima.

| Etapa | Objetivo |
| --- | --- |
| **Fechamento 9.3B** | consolidar o Estado Integral pós-Fase 9 e sincronizar o ambiente de desenvolvimento |
| **Portal 0.10.0 — Foundation** | arquitetura, fontes de verdade, módulos upstream, AJAX, Wiki, Revista, Loja, Fórum, Google, testes e versionamento |
| **Portal 0.11.0 — Forum Foundation** | ativar `forum.aculta.org` / `forum.aculta.toca.net.br` usando Drupal Forum + Node + Comment + Taxonomy |
| **Portal 0.12.0 — Forum Participation** | integrar meus tópicos, minhas respostas e navegação do Fórum à Conta |
| **Portal 0.13.0 — Participation Hub** | centralizar participação em Fórum, Wiki e cursos sem storage paralelo |
| **Portal 0.14.0 — Admin Hub** | consolidar atalhos, status e administração dos subsistemas já existentes |
| **Portal 0.15.0 — AJAX Consolidation** | migrar infraestrutura AJAX genérica própria para Views AJAX, Form API, Drupal Ajax e HTMX quando apropriado |
| **Portal 0.16.0 — Search** | adotar Search API para Wiki e Fórum e retirar busca textual custom após teste de paridade |
| **Portal 0.17.0 — Engagement** | avaliar/adotar Flag e Comment Notify para acompanhamento, favoritos e notificações |
| **Portal 0.18.0 — Deduplication** | remover apenas código custom comprovadamente substituído por Core/contrib |
| **Portal 0.19.0 — Hardening** | permissions, access, cache, CSRF, Domain isolation, desempenho, logs e portabilidade SQLite/MariaDB |
| **Portal 1.0.0 — Stable** | baseline estável com Conta, CEP, apoio, cursos, Wiki, Fórum, participação, administração, busca e segurança integrados |

A trilha de integrações Google evolui em paralelo ao SemVer do Portal:

`G0 Prepared → G1 Production Minimum → G2 Google for Nonprofits → G3 Learning Integration`.

No lançamento mínimo, a arquitetura prevê Search Console, sitemap e
Google Tag/GA4 somente com consentimento e sem PII. Google Classroom é uma
integração futura e **não substitui Drupal LMS**, que continua sendo a fonte de
verdade de cursos e progresso.

O roadmap detalhado e normativo está em
[docs/portal/ROADMAP.md](docs/portal/ROADMAP.md). A arquitetura Google está em
[docs/integrations/GOOGLE.md](docs/integrations/GOOGLE.md).

## Documentação

Comece em [docs/README.md](docs/README.md). Segredos e settings locais nunca pertencem ao Git.
