# Roadmap versionado — ACULTA Bootstrap Component Design System

## Estado atual: 0.1.0

A versão **0.1.0** representa o baseline consolidado existente em outubro de
2026. Ela encerra a refatoração defensiva A–G4 e incorpora H1/H2:

- Drupal 11.4.x;
- Bootstrap5 base theme 4.0.8;
- tokens ACULTA mapeados para variáveis Bootstrap;
- CSS/JS separados por responsabilidade;
- JavaScript editorial contextual;
- fronteira tema/Portal documentada;
- primeiro SDC `stable`: `aculta:editorial-card`;
- CSS exclusivo do editorial card co-localizado no SDC;
- auditoria inicial de primitives concluída.

0.1.0 **não** representa API final. É a fundação versionada a partir da qual o
design system passa a evoluir.

## Princípios do roadmap

1. Bootstrap é infraestrutura; ACULTA é a linguagem visual.
2. SDC não consulta storage, serviços de domínio ou entidades.
3. Presenter Drupal preserva Theme API/cache/access e alimenta SDC.
4. Primitive não implica SDC.
5. Components podem ser organizados em subdiretórios; IDs permanecem
   namespaced pelo provider/componente.
6. Variants SDC são preferíveis a componentes duplicados quando semântica e
   estrutura são a mesma família.
7. Engine funcional existente não é reescrita apenas para padronizar tecnologia.
8. Assets específicos deixam `aculta/global` quando ownership estiver provado.
9. Acessibilidade é parte do contrato do componente.
10. Ferramentas editoriais entram depois que os componentes estão maduros.

## Visão de releases

| Versão | Entrega principal | Resultado esperado |
| --- | --- | --- |
| **0.1.0** | baseline do design system | arquitetura e primeiro SDC stable |
| **0.2.0** | Foundations 2.0 | schemas, motion, semantic tokens, taxonomy SDC |
| **0.3.0** | Card System v1 | editorial/project/course em família coerente |
| **0.4.0** | Patterns v1 | section, grid, hero e carousel/rail universais |
| **0.5.0** | Shell + Icons + Color mode UI | navbar/offcanvas/account/icon system |
| **0.6.0** | Search + Feedback | busca transversal, autocomplete, toast/skeleton |
| **0.7.0** | Drupal UI integration | SDCs expostos seletivamente via UI Patterns 2 |
| **0.8.0** | Component Library + QA | catálogo visual, exemplos e validação isolada |
| **0.9.0** | estabilização / API freeze | acessibilidade, performance, deprecações |
| **1.0.0** | design system estável | contratos públicos e baseline de produção |
| **1.1.0+** | autoria visual seletiva | Canvas para landing pages/campanhas |
| experimental | Display Builder | somente avaliação até release estável adequada |

---

## 0.2.0 — Foundations 2.0

### Objetivo

Tornar contratos, tokens e validação fortes o suficiente para suportar dezenas
de componentes sem dívida estrutural.

### Escopo

- habilitar `enforce_prop_schemas: true`;
- validar todos os SDCs existentes antes da ativação;
- avaliar `sdc_devel` como dependência somente de desenvolvimento;
- adotar organização recursiva de componentes:

```text
components/
├── primitives/
├── content/
├── navigation/
├── sections/
└── feedback/
```

- mover `editorial-card` para `content/` somente se a mudança for
  comprovadamente transparente ao component ID;
- criar motion tokens: durations e easings;
- criar semantic tokens para surface/text/border/interactive;
- preparar tokens por `data-bs-theme` sem ativar modo escuro incompleto;
- criar `category-label` como primeiro primitive SDC experimental;
- manter button como primitive CSS/Bootstrap global;
- documentar target sizes, focus e reduced-motion como foundations;
- substituir durações hardcoded por motion tokens onde behavior for equivalente.

### Não fazer

- não criar SDC de button apenas para substituir `.btn`;
- não ativar dark mode parcial;
- não adicionar UI Patterns/Canvas ainda;
- não reorganizar markup funcional de Form API.

### Gate de saída

- schemas obrigatórios sem falha;
- `editorial-card` continua stable;
- `category-label` usado por pelo menos editorial e project presenter;
- zero regressão visual nos consumidores atuais.

---

## 0.3.0 — Card System v1

### Objetivo

Criar uma linguagem comum para conteúdo sem acoplar cards às entidades que os
alimentam.

### Escopo

- consolidar slots semânticos recorrentes: media, category/meta, title,
  summary, actions e footer;
- usar SDC variants nativos para variações de apresentação quando pertencem à
  mesma família;
- criar/migrar:
  - `editorial-card`;
  - `project-card`;
  - `course-card`;
- criar primitive/media apenas quando project + course comprovarem contrato
  compartilhado;
- `product-card` fica condicionado à existência de Product Types/Variations e
  catálogo Commerce reais;
- presenters permanecem específicos de Node/LMS/Commerce;
- nenhuma entidade é acessada dentro dos SDCs.

### Variants

Preferir, quando a semântica for a mesma:

```text
card
├── default
├── compact
├── horizontal
└── featured
```

em vez de quatro componentes independentes. Variants não devem virar depósito
de combinações arbitrárias; diferenças de domínio continuam em presenters e
componentes especializados.

### Gate de saída

- editorial/project/course compartilham tokens/contratos sem duplicação grave;
- nenhum card calcula regra de negócio;
- long title, missing media, empty summary e mobile validados.

---

## 0.4.0 — Patterns v1

### Objetivo

Compor componentes em estruturas universais reutilizáveis.

### Patterns alvo

- `content-section`;
- `content-grid`;
- `hero`;
- `carousel` / `rail`.

### Carousel

O pattern ACULTA define:

- heading/description/actions;
- items;
- spacing;
- controles visuais;
- foco;
- reduced motion;
- regras de acessibilidade.

A engine continua sendo um detalhe de integração:

```text
ACULTA carousel pattern
        |
        +-- editorial-card
        +-- course-card
        +-- product-card (quando existir)
        |
        +-- engine adapter
              +-- VVJB
              +-- Bootstrap quando fizer sentido
```

VVJB não será substituído apenas para uniformizar implementação.

Autoplay deve ser evitado por padrão. Quando existir, deve possuir controle
explícito de pause/stop e respeitar reduced motion.

### Gate de saída

- carousel editorial e cursos compartilham pattern visual;
- SHOP entra somente quando houver catálogo Commerce real;
- não existem três engines JS proprietárias diferentes.

---

## 0.5.0 — Shell, Icon System e seletor de tema

### Objetivo

Modernizar a navegação transversal sem criar framework JS paralelo.

### Escopo

- Drupal Core Icon API como contrato;
- UI Icons 2.x como integração recomendada;
- pack ACULTA/Bootstrap Icons definido via Icon API, sem `<i class="bi ...">`
  espalhado;
- navbar desktop revisada;
- Bootstrap Offcanvas no mobile;
- account dropdown;
- search trigger;
- seletor acessível `auto / light / dark`;
- persistência local da preferência;
- `data-bs-theme` como mecanismo de aplicação;
- mega menu somente depois da navegação básica estabilizar.

### Regra

Bootstrap Collapse/Offcanvas/Dropdown continuam sendo as engines. O tema só
adiciona integração Drupal, identidade e acessibilidade complementar.

---

## 0.6.0 — Search + Feedback

### Busca

- Search API como índice;
- Search API Autocomplete 1.x quando o backend suportar autocomplete;
- experiência transversal para editorial, cursos e Commerce quando disponível;
- resultados agrupados por domínio somente se houver relevância real;
- facets/filtros para página de resultados, não obrigatoriamente no popup.

### Feedback

- Alerts permanecem para mensagens críticas;
- Toast para confirmações não críticas;
- skeleton/placeholder para Views AJAX, busca, cursos e Commerce onde houver
  espera real;
- live regions e foco tratados por severidade;
- spinner não deve ser substituído por skeleton quando não houver layout
  previsível.

---

## 0.7.0 — Integração com Drupal UI

### Tecnologia

Adotar **UI Patterns 2.x**, que usa SDC Core.

### Uso seletivo

Expor componentes maduros em:

- Views;
- Manage Display / field formatters;
- Block/Layout Builder onde houver caso de uso.

O editor escolhe opções controladas e variants existentes; não escreve CSS.

### Não adotar

- UI Suite Bootstrap como theme/design system concorrente;
- duplicação dos nossos SDCs por componentes Bootstrap externos.

UI Suite Bootstrap continua apenas como referência de arquitetura.

---

## 0.8.0 — Component Library + QA

### Catálogo

Preferência:

1. UI Patterns Library para catálogo de componentes;
2. UI Examples para exemplos curados/estados de uso.

Evitar criar rota customizada de style guide antes de avaliar essas opções.

### Estados obrigatórios

- normal;
- hover/focus;
- disabled quando aplicável;
- vazio;
- título longo;
- sem media;
- mobile;
- light/dark;
- reduced motion.

### Qualidade

- SDC Devel/validators;
- testes de schema;
- auditoria de attachments;
- matriz de acessibilidade;
- documentação para humanos e agentes de IA.

Storybook permanece opcional: não introduzir Node/build tooling apenas para ter
um catálogo visual se a stack Drupal já cobrir o objetivo.

---

## 0.9.0 — Estabilização e API freeze

### Objetivo

Parar de adicionar arquitetura nova e preparar 1.0.

### Escopo

- concluir light/dark/auto;
- avaliar high-contrast como experimental;
- reduzir CSS/JS global remanescente;
- remover/deprecar classes legadas somente com migração;
- revisar `style.css` residual;
- congelar contratos de componentes `stable`;
- auditoria WCAG 2.2;
- auditoria de performance/asset attachment;
- revisar documentação e exemplos;
- nenhum componente crítico pode depender de API experimental não documentada.

---

## 1.0.0 — ACULTA Design System estável

Critérios mínimos:

- versão e CHANGELOG coerentes;
- schemas obrigatórios;
- componentes críticos `stable`;
- cards e patterns principais consolidados;
- shell moderno e acessível;
- Icon API integrada;
- busca transversal em produção;
- integração UI Patterns seletiva validada;
- component library disponível para desenvolvimento/editorial;
- contratos de acessibilidade documentados;
- zero regra de negócio movida ao tema;
- política de compatibilidade/depreciação ativa.

1.0.0 significa **API do design system estável**, não que todo elemento visual
possível precise virar SDC.

---

## Pós-1.0

### 1.1.0 — Canvas pilot

Canvas já é tecnologia estável no Drupal 11.3+, mas entra somente depois do
design system estar estável.

Escopo inicial:

- landing pages;
- páginas institucionais;
- campanhas;
- hotsites.

Conteúdo editorial estruturado continua em entidades Drupal normais.

Preferir nossos próprios SDCs. Canvas Bootstrap não é dependência padrão.

### Display Builder

Display Builder permanece trilha experimental enquanto não houver release
estável e enquanto seu fluxo para site existente não estiver validado.

Não é requisito de 1.0 nem 1.1.

## Matriz de tecnologias pesquisadas

| Tecnologia | Estado em out/2026 | Decisão ACULTA |
| --- | --- | --- |
| Bootstrap5 4.0.8 | stable | manter como base |
| SDC Core | stable | fundação do design system |
| SDC variants | Core 11.2+ | usar seletivamente em cards/patterns |
| SDC Devel 1.0.3 | stable | avaliar como dev-only em 0.2 |
| UI Icons 2.0.1 | stable | adotar em 0.5 |
| Search API Autocomplete 1.12 | stable | adotar em 0.6 se backend suportar |
| UI Patterns 2.0.21 | stable | adotar em 0.7 |
| UI Examples 2.1.0 | stable | adotar em 0.8 |
| Canvas 1.12.0 | stable | pilotar pós-1.0 |
| Display Builder 1.0 beta8 | beta | pesquisa apenas |
| UI Suite Bootstrap 5.2.3 | stable | referência, não dependência |
| Canvas Bootstrap 1.0.8 | stable | referência, não dependência padrão |
| SDC Component Library 1.0.5 | stable/minimally maintained | não priorizar; overlap com UI Patterns/UI Examples |

## Referências upstream

- SDC Core: https://www.drupal.org/docs/develop/theming-drupal/using-single-directory-components
- SDC FAQ: https://www.drupal.org/docs/develop/theming-drupal/using-single-directory-components/frequently-asked-questions
- SDC variants: https://www.drupal.org/node/3517062
- Bootstrap color modes: https://getbootstrap.com/docs/5.3/customize/color-modes/
- UI Patterns: https://www.drupal.org/project/ui_patterns
- UI Icons: https://www.drupal.org/project/ui_icons
- Search API Autocomplete: https://www.drupal.org/project/search_api_autocomplete
- UI Examples: https://www.drupal.org/project/ui_examples
- Canvas: https://www.drupal.org/project/canvas
- Display Builder: https://www.drupal.org/project/display_builder
