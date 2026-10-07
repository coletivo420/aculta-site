# Features atuais — 0.1.0

## Foundations

- paleta institucional centralizada em `css/tokens.css`;
- semantic mapping para `--bs-*`;
- Inter para corpo/UI;
- Oswald para display/navigation/CTA;
- spacing estrutural;
- focus token;
- Bootstrap color variables preparadas para evolução futura;
- CSS nativo, sem etapa de build.

## Shell

- header institucional;
- branding com logo e fallback;
- menu principal responsivo;
- Bootstrap Collapse;
- menu disponível sem JavaScript como progressive enhancement;
- utility/account strip;
- conteúdo + sidebar via grid Bootstrap;
- footer institucional.

## Navegação e acessibilidade

- keyboard/focus;
- Escape no menu mobile;
- `aria-current`;
- focus return;
- `:focus-visible`;
- reduced-motion no carrossel;
- headings e landmarks preservados.

## Conteúdo

- prose opt-in para bundles editoriais;
- project teaser;
- editorial highlight;
- institution block;
- headings/sections;
- CTA/buttons;
- breadcrumbs.

## SDC

### `aculta420:editorial-card`

Status: `stable`.

Slots:

- category;
- title;
- summary;
- complement;
- cta.

Características:

- não possui JavaScript;
- CSS exclusivo vive no próprio SDC;
- presenter Drupal mantém wrapper/Theme API;
- não conhece Node, storage ou services.

## Carrossel editorial

- engine: VVJB;
- tema não reimplementa a engine;
- singleton fica estático via options VVJB;
- JS de foco/integração carrega contextualmente;
- reduced motion respeitado;
- CSS do card não fica no CSS do carousel.

## Forms/Auth

- markup herdado de Bootstrap5/Core;
- override redundante de input não existe;
- styling ACULTA420 permanece no tema;
- autenticação continua funcionalidade Drupal/contrib.

## Assets

Global:

- tokens/base/layout;
- shell;
- integrações Drupal/Bootstrap;
- componentes globais com ownership explícito, incluindo forms/account/footer;
- `navigation.js`.

A Foundation não mantém `css/style.css` nem `css/responsive.css` catch-all.

Contextual:

- `editorial-carousel.css` + `editorial-carousel.js`, anexados somente pela View VVJB correspondente;
- assets SDC como `editorial-card.css`.

## Ainda não existe

Não documentar como feature pronta:

- dark mode completo;
- seletor auto/light/dark;
- Icon API/UI Icons;
- mega menu;
- busca global Search API;
- UI Patterns;
- Canvas;
- Display Builder;
- product-card real;
- carrossel universal;
- style guide interativo.

Esses itens pertencem ao roadmap.
