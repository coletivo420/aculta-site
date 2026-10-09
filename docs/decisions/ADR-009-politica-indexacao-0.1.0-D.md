# Política de indexação multidomínio (microfase 0.1.0-D)

Data: 2026-10-09. Status: **aceita pelo responsável como política da linha 0.1.x.**
Decisão de arquitetura: adaptador mínimo em `aculta_portal_sitemap` (aprovado), no lugar
do `domain_simple_sitemap` (não adotado, ver ADR-009 e a avaliação da microfase C).
Esta microfase não contém código.

## Fontes de verdade reutilizadas (sem nova lista de hostnames)

| Necessidade | Serviço existente do Portal | Observação |
| --- | --- | --- |
| Purpose de um domínio | `DomainPurposeManager::getPurposeForDomainId()` | Autoridade de purposes. |
| Domínio de um purpose | `DomainPurposeManager::getDomain()` | Usado para o host da URL. |
| URL canônica de conteúdo | `DomainPurposeManager::canonicalRouteUrl()` | Canonical sempre pelo host de produção. |
| Purpose de origem do conteúdo | `field_domain_source` + `getPurposeForDomainId()` | Mesmo critério de `PortalHooks` e `ContentPurposeResolver`. |

O mapa `DOMAIN_IDS` em `DomainPurposeManager` é a única lista de domínios existente;
o submódulo não cria outra.

## Regras de inclusão (todas obrigatórias)

- **R1 — Origem.** Um conteúdo só é indexável no sitemap do purpose indicado por seu
  `field_domain_source`. Conteúdo sem origem válida não é indexado.
- **R2 — Unicidade.** Cada URL canônica aparece em um único sitemap: o do seu purpose de
  origem. Não há cópia entre domínios.
- **R3 — Publicação.** Somente nós publicados (`status = 1`).
- **R4 — Acesso anônimo.** Somente conteúdo que um usuário anônimo pode visualizar.
- **R5 — Noindex.** Conteúdo com diretiva `noindex` (metatag) é excluído. A verificação
  de metatag é feita na microfase E, quando o padrão de metatag do projeto estiver confirmado.
- **R6 — Tipos.** Somente os bundles listados na matriz da fase E (`page`, `project`,
  `activity`, `document`, `wiki_entry`, `article`). `editorial_highlight` não é página.
- **R7 — Hosts de teste.** Aliases `*.aculta.toca.net.br` nunca são URL pública; a URL
  canônica sempre usa o host de produção.

## Matriz por purpose (política; a implementação é das fases E e F)

| Purpose | Sitemap público | Conteúdo elegível (regra) | Estado no runtime |
| --- | --- | --- | --- |
| MAIN | Sim | `page`, `project`, `activity`, `document` com origem MAIN | 9 páginas, 7 projetos, 2 atividades e 1 documento com origem MAIN; apenas publicados entram |
| SUPPORT | Sim | Conteúdo público de apoio (nenhum na origem atual) | Vazio: a página de apoio é rota, não nó |
| MAGAZINE | Sim | `page` com origem MAGAZINE | 1 página publicada |
| WIKI | Sim | `wiki_entry` com origem WIKI | 4 verbetes publicados |
| COURSES | Sim | Catálogo público de cursos (fora do conteúdo Drupal atual) | Sem nós |
| SHOP | Sim | Páginas e produtos públicos | Sem nós |
| ACCOUNT | **Não** | Privacidade e autenticação: nunca | — |
| FORUM | Futuro | Somente após ativação e decisão específica | — |

## Exclusões observadas no runtime

- `activity` (2) e `document` (1) de MAIN estão **não publicados** e não entram no sitemap (R3).
- `wiki_entry` sem `field_domain_source` (1) não é indexado (R1).
- `editorial_highlight` (MAGAZINE e MAIN) não é elegível (R6).

## Consequências para a implementação

- O adaptador (fase E) precisa filtrar por `field_domain_source` **antes** de gerar URLs.
  Sem esse filtro o problema da avaliação C reaparece.
- O sitemap institucional `default` continua como está até a fase G (ADR-009, decisão 4).
- A verificação de metatag `noindex` (R5) fica para a fase E, porque o módulo `metatag` tem
  padrões por entidade que precisam ser confirmados no runtime antes da regra.

## Próxima microfase

0.1.0-E: sitemaps piloto para MAIN e SUPPORT, usando o adaptador mínimo. Não iniciada.
