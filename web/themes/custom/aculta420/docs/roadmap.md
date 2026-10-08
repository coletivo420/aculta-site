# Roadmap versionado — ACULTA420

## 0.1.0 — Foundation

Status: **concluído em 2026-10-07**.

Objetivo: estabelecer o novo provider `aculta420` sobre a base madura do antigo
tema `aculta`.

Inclui:

- rename completo do provider funcional do tema;
- nenhum alias, shim ou provider legado de compatibilidade;
- versionamento SemVer próprio;
- documentação reestruturada;
- config sync apontando para `aculta420`;
- SDC namespace `aculta420:*`;
- components organizados por domínio visual;
- `enforce_prop_schemas: true`;
- primeiro SDC stable: editorial-card;
- Bootstrap5 4.0.8 como infraestrutura;
- assets globais/contextuais já separados onde comprovado;
- contrato arquitetural do shell multidomínio documentado sem executar o redesign;
- provider `aculta420` tratado como único contrato corrente em código, config e documentação normativa.

Gate de release:

- tema descoberto/instalável;
- `system.theme default = aculta420`;
- `core.extension` sem provider legado do tema;
- diretório `web/themes/custom/aculta/` ausente;
- config import/status clean no Runtime validado;
- blocks continuam posicionados;
- SDC e libraries descobertos;
- todos os domains representativos passam smoke test;
- `validate-aculta420-foundation` passa no Runtime;
- documentação corrente não instrui compatibilidade com provider legado.

## 0.2.0 — Multidomain Shell Foundations

Esta linha prepara e implementa o shell Design B em etapas revisáveis. O tema
recebe somente contexto de apresentação do Portal; nenhuma etapa autoriza
resolução de purpose por hostname.

### 0.2-A — Semantic Foundations

- tokens semânticos de superfície, texto, borda, interação e shell;
- valores light/dark preparados via `data-bs-theme`, sem seletor ou persistência;
- superfície geral verde suave para a direção Design B;
- Bootstrap mapeado aos tokens semânticos;
- motion e reduced-motion preservados.

### 0.2-B — Domain Presentation Contract

- **0.2-B.1 — inventário e fronteira normativa:** concluído documentalmente; ver `docs/portal/DOMAIN-PRESENTATION-CONTRACT.md`;
- **0.2-B.2 — builder/presenter:** implementado no `aculta_portal`; Runtime validation pendente antes do fechamento;
- **0.2-B.3 — integração de shell:** entregar o view-model ao tema sem redesign estrutural;
- **0.2-B.4 — gate/fixtures:** provar ausência de Domain/hostname/service no tema e validar fallbacks/cache;
- fallback de branding: purpose → ACULTA → texto;
- nenhum acesso a entidade Domain, storage ou serviço pelo Twig/SDC.

### 0.2-C — Institution Bar

- faixa institucional global, compacta e discreta;
- marca ACULTA e ações globais preparadas pelo Portal.

### 0.2-D — Domain Header

- cabeçalho visual principal com marca, título e navegação preparados pelo Portal;
- tokens estruturais sem identidade codificada por purpose.

### 0.2-E — Mobile/Sticky Shell

- adaptação mobile e comportamento sticky;
- revisão de navegação, account dropdown e search trigger;
- reutilizar Bootstrap Collapse/Offcanvas conforme o contrato validado.

### 0.2-F — Design B QA

- validar shell e estados em desktop/mobile, teclado/foco, reduced-motion e
  modos light/dark;
- corrigir contraste e regressões antes de fechar a linha 0.2.0.

Não criar button SDC. `category-label` permanece candidato experimental e fica
deferido até existir consumidor comprovado.

## 0.3.0 — Card System v1

- project-card;
- course-card;
- consolidar contratos recorrentes;
- avaliar primitive media com evidência;
- usar variants SDC quando a família é a mesma;
- product-card somente com catálogo Commerce real.

Gate: editorial/project/course sem regra de negócio no componente e com estados
mobile/empty/long-title validados.

## 0.4.0 — Patterns v1

- content-section;
- content-grid;
- hero;
- carousel/rail universal.

Carousel define apresentação/acessibilidade. Engine permanece VVJB ou Bootstrap
conforme o contexto.

Gate: editorial e cursos compartilham linguagem de carousel sem JS duplicado.

## 0.5.0 — Icons + Color mode UI

- adicionar UI de seleção light/dark/auto somente após a validação visual do shell;
- persistência de preferência usando `data-bs-theme`;
- Core Icon API;
- UI Icons 2.x;
- Bootstrap Icons por API, não markup espalhado;
- mega menu somente após shell básico estável.

## 0.6.0 — Search + Feedback

- Search API;
- Search API Autocomplete quando backend suportar;
- busca transversal por domínio de conteúdo;
- facets na página de resultados;
- toast para confirmações não críticas;
- alerts para mensagens críticas;
- skeletons apenas onde layout de loading é previsível.

## 0.7.0 — Drupal UI integration

Adotar UI Patterns 2.x seletivamente para expor nossos SDCs maduros em:

- Views;
- Manage Display;
- field formatters;
- Block/Layout Builder quando houver caso de uso.

Não adotar UI Suite Bootstrap como design system concorrente.

## 0.8.0 — Component Library + QA

Preferência:

1. UI Patterns Library;
2. UI Examples.

Estados documentados:

- normal;
- hover/focus;
- disabled;
- empty;
- long content;
- no media;
- mobile;
- light/dark;
- reduced motion.

Storybook só entra se a stack Drupal não cobrir o objetivo.

## 0.9.0 — Stabilization / API freeze

- WCAG 2.2 audit;
- asset/performance audit;
- reduzir CSS/JS global remanescente;
- revisar residual CSS;
- finalizar dark/auto;
- deprecation policy;
- congelar contratos stable;
- nenhuma arquitetura nova.

## 1.0.0 — Stable Design System

Critérios:

- components críticos stable;
- schemas obrigatórios;
- card/pattern system consolidado;
- shell acessível;
- icon system;
- search integrada;
- UI Patterns seletivo validado;
- component library disponível;
- compat/deprecation policy ativa;
- documentação íntegra.

1.0 significa API do design system estável, não “todo HTML virou SDC”.

## 1.1.0+ — Canvas pilot

Canvas é estável no ecossistema atual, mas fica pós-1.0 para não ditar a API dos
componentes antes dela estabilizar.

Escopo inicial:

- landing pages;
- campanhas;
- institucionais;
- hotsites.

Conteúdo editorial estruturado continua em entidades Drupal.

## Experimental — Display Builder

Display Builder permanece beta em outubro de 2026. Avaliar em sandbox, nunca
como requisito de 1.0.

## Matriz tecnológica — outubro de 2026

| Tecnologia | Estado | Decisão |
| --- | --- | --- |
| Bootstrap5 4.0.8 | stable | base |
| Core SDC | stable | fundação |
| SDC variants | Core 11.2+ | usar seletivamente |
| SDC Devel 1.0.3 | stable | avaliar dev-only |
| UI Icons 2.0.1 | stable | planejar 0.5 |
| Search API Autocomplete 1.12 | stable | planejar 0.6 |
| UI Patterns 2.0.21 | stable | planejar 0.7 |
| UI Examples 2.1.0 | stable | planejar 0.8 |
| Canvas 1.12.0 | stable | pós-1.0 |
| Display Builder 1.0.0-beta8 | beta | pesquisa |
| UI Suite Bootstrap | stable | referência, não dependência |

Referências:

- https://www.drupal.org/docs/develop/theming-drupal/using-single-directory-components
- https://www.drupal.org/node/3517062
- https://www.drupal.org/project/sdc_devel
- https://www.drupal.org/project/ui_icons
- https://www.drupal.org/project/search_api_autocomplete
- https://www.drupal.org/project/ui_patterns
- https://www.drupal.org/project/ui_examples
- https://www.drupal.org/project/canvas
- https://www.drupal.org/project/display_builder
