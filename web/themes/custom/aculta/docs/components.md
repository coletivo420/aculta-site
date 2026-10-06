# Componentes visuais

Este catálogo pertence ao **ACULTA Bootstrap Component Design System**. A arquitetura canônica está em [component-design-system.md](component-design-system.md).

Ele descreve componentes existentes ou reconhecidos no tema. Não é autorização para criar storage, regras de negócio ou novos subsistemas no tema.

## Taxonomia

| Camada | Exemplos/estado atual |
| --- | --- |
| Foundations | `tokens.css`, `base.css`, `layout.css`, integração Bootstrap |
| Primitives | button como contrato CSS/Bootstrap; `category-label` aprovado para H3; heading/media adiados |
| Components | `aculta:editorial-card`, project card, breadcrumb; course/product cards planejados |
| Patterns | hero atual; carousel/rail, content-grid e content-section planejados |
| Shell | header, navigation, utility/account, footer |

A classificação é de responsabilidade, não obrigação de converter cada item em SDC.

## Auditoria de primitives

Resultado H3:

- `category-label`: candidato aprovado a SDC experimental;
- button: primitive global via Bootstrap/Form API, sem SDC neste momento;
- section-heading: adiado para o pattern `content-section` em H5;
- media: adiado até a família de cards H4 fornecer requisitos comuns;
- icon: sem caso de uso suficiente.

Ver [h3-primitives-audit.md](h3-primitives-audit.md).

## Contrato geral

Todo componente visual deve:

- receber dados do Drupal/Core/contrib/`aculta_portal`;
- preservar atributos, acesso e cache metadata quando aplicável;
- continuar utilizável com progressive enhancement;
- respeitar o design system e acessibilidade;
- não persistir estado funcional;
- não consultar diretamente banco de dados.

## Arquivos CSS atuais

| Arquivo | Responsabilidade |
| --- | --- |
| `components/header.css` | header e branding textual |
| `components/navigation.css` | navegação principal, estados active/hover/focus e progressive enhancement visual |
| `components/breadcrumb.css` | breadcrumb e tratamento de links editoriais nesse contexto |
| `components/content.css` | espaçamento principal, títulos de seção, superfícies e cards genéricos |
| `components/buttons.css` | botões públicos e CTAs |
| `components/footer.css` | base visual do footer |
| `drupal-bootstrap.css` | integração visual com componentes Drupal/Bootstrap |
| `components/institutional.css` | composição institucional, projetos e conteúdo relacionado |
| `components/editorial-carousel.css` | pattern/engine visual VVJB; sem styling exclusivo do card |
| `components/editorial-card/editorial-card.css` | CSS exclusivo do SDC `aculta:editorial-card`, auto-carregado |
| `responsive.css` | ajustes responsivos globais remanescentes |
| `components/auth.css` | login, registro e recuperação de senha Drupal |
| `style.css` | trecho residual ainda intercalado de footer-layout, formulários, Conta/segurança e participação |

Esta divisão é física, não uma alteração de contrato visual. A ordem de carregamento é parte do comportamento e deve ser preservada.

## Catálogo atual

| Componente | Implementação principal | Fonte de dados/estado |
| --- | --- | --- |
| Header | `page.html.twig`, CSS | regions/blocks Drupal |
| Branding | branding block Twig + preprocess | System Branding + assets do tema |
| Navegação desktop/mobile | `page.html.twig`, `js/navigation.js`, CSS | Menu Drupal + Bootstrap Collapse |
| Menu utilitário/Conta | page preprocess + region render arrays | blocks/menu Drupal |
| Breadcrumb | Portal builder + Twig/preprocess atual | routing, Domain purpose, entities |
| Hero | CSS/Twig de conteúdo existente | conteúdo Drupal |
| CTA pair | classes visuais existentes | links/conteúdo renderizado |
| Project card | `node--project--teaser.html.twig` | fields do node |
| Editorial highlight | presenter `node--editorial-highlight.html.twig` + SDC `aculta:editorial-card` | fields do node + VVJB |
| Editorial list/prose | field preprocess + CSS | fields/Views Drupal |
| Institution block | block Twig + preprocess | custom block fields |
| Forms | Bootstrap5/Core markup herdado + CSS do tema | Form API |
| Alerts/tables | Bootstrap + CSS do tema | render arrays Drupal |
| Footer | `page.html.twig`, bloco institucional, menus | regions/blocks Drupal |
| Account shell | `page.html.twig` + apresentação Portal | User/Profile/Portal |
| Course cards/resumo | apresentação Portal + tema | Drupal LMS/Group |
| Wiki lists | apresentação Drupal/Portal | Node/Taxonomy/Views |

## Limites

O fato de um componente apresentar Conta, Cursos, Wiki ou Commerce não transfere propriedade funcional ao tema.

Exemplos:

- course card pode exibir progresso preparado; não calcula nem salva progresso;
- account shell pode exibir ações; não autentica usuário;
- institution block apresenta fields; não cria registro institucional paralelo;
- breadcrumb apresenta a trilha; regras de Domain purpose pertencem ao Portal.

## Single Directory Components

Drupal 11 possui SDC estável no Core. A adoção continua seletiva e segue o contrato presenter -> SDC documentado em `component-design-system.md`.

### Componente-modelo: `aculta:editorial-card`

O Commit F criou o piloto; o H2 o promove a primeiro SDC `stable` do design system, com markup, metadata e CSS exclusivo co-localizados.

Contrato:

- slots: `category`, `title`, `summary`, `complement`, `cta`;
- sem props funcionais;
- sem estado;
- sem consulta de dados;
- CSS exclusivo em `components/editorial-card/editorial-card.css`;
- sem JavaScript próprio.

O presenter `node--editorial-highlight.html.twig` mantém o `<article>` e os attributes Drupal, e chama o componente com `include('aculta:editorial-card', ..., with_context = false)`.

O Drupal carrega `editorial-card.css` automaticamente quando o SDC é renderizado. Tokens e `.aculta-category` permanecem compartilhados; VVJB continua dono do carousel.

### Critério para próximos SDCs

Um novo candidato deve:

- ter reutilização real ou fronteira visual clara;
- possuir contrato simples de props/slots;
- reduzir duplicação ou acoplamento;
- preservar attributes/cache/access no presenter quando aplicável;
- melhorar legibilidade/testabilidade.

Não migrar todo Twig apenas para uniformizar tecnologia.
