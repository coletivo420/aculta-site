# Tema aculta

Tema público da plataforma Drupal da Associação Cultural Antiproibicionista. `aculta` é o machine name técnico.

## Papel arquitetural

`aculta` é a camada de apresentação e, desde a Fase H, o **ACULTA Bootstrap Component Design System**:

```text
Drupal Core + contrib
        |
        v
  aculta_portal
        |
        v
ACULTA Component Design System
 Foundations -> Primitives -> Components -> Patterns -> Shell
        |
        v
    Bootstrap 5
```

O tema possui identidade visual, tipografia, design tokens, layout, header/footer, navegação, Twig overrides, apresentação de formulários, foco, responsividade e componentes visuais.

O tema não é fonte de verdade de autenticação, pagamentos, Commerce, cursos, matrícula/progresso, Domain access, Wiki ou outras regras de negócio. Essas responsabilidades permanecem no Core, módulos especializados e `aculta_portal`.

## Base theme e assets

- Drupal Core: `^11`.
- Base theme: `bootstrap5`.
- Bootstrap pertence exclusivamente ao base theme; não embarcar uma segunda cópia.
- A library global atual é `aculta/global`.
- CSS atual: fundações em `tokens.css`/`base.css`/`layout.css`, componentes em `css/components/` e integrações ainda residuais em `css/style.css`.
- JavaScript atual: `js/navigation.js` global e `js/editorial-carousel.js` contextual no carrossel editorial da Home.
- Fontes web: Inter e Oswald via Google Fonts.

## Design system

Cores oficiais:

| Papel | Valor |
| --- | --- |
| verde escuro | `#0c3c29` |
| verde estrutural | `#689427` |
| amarelo | `#f2ca36` |
| vermelho | `#d4452d` |
| creme | `#fbf4e8` |
| branco | `#ffffff` |

Tipografia: Oswald para display/headings/nav/CTAs; Inter para corpo, formulários, metadados e UI.

Semântica: verde = estrutura, verde escuro = contraste/interação, amarelo = ação, vermelho = ênfase editorial, creme/branco = superfície.

Detalhes: [docs/design-system.md](docs/design-system.md) e [docs/component-design-system.md](docs/component-design-system.md).

## Estrutura atual

- `assets/branding` - originais e exports de marca.
- `config/install` e `config/schema` - defaults e schema de theme settings.
- `css/tokens.css` - paleta, tokens do tema e integração de custom properties Bootstrap.
- `css/base.css` - base tipográfica e regras globais de elementos.
- `css/layout.css` - container e geometria estrutural geral.
- `css/components/header.css` - header e branding textual.
- `css/components/navigation.css` - navegação principal e progressive enhancement visual.
- `css/components/breadcrumb.css` - breadcrumb e links editoriais associados.
- `css/components/content.css` - espaçamento principal, títulos de seção, superfícies e cards.
- `css/components/buttons.css` - botões/CTAs públicos e estados.
- `css/components/auth.css` - apresentação das rotas Drupal de login/registro/recuperação.
- `css/components/footer.css` - base visual do footer.
- `css/drupal-bootstrap.css` - integração visual com componentes Drupal/Bootstrap.
- `css/components/institutional.css` - composição institucional, projetos e conteúdo editorial relacionado.
- `css/components/editorial-carousel.css` - apresentação do pattern/engine VVJB; não contém mais CSS exclusivo do card editorial.
- `css/responsive.css` - ajustes responsivos globais remanescentes.
- `css/style.css` - trecho residual ainda misto de footer-layout, formulários, Conta/segurança e participação.
- `components/editorial-card` - primeiro SDC `stable`; Twig, metadata e CSS exclusivo carregado automaticamente pelo Drupal.
- `js/navigation.js` - progressive enhancement da navegação e integração com Bootstrap Collapse.
- `js/editorial-carousel.js` - integração de foco com o carousel editorial VVJB; carregado pela library contextual `aculta/editorial-carousel` apenas na View da Home.
- `templates` - overrides Twig.
- `docs` - contratos e inventário técnico do tema.

O inventário auditado está em [docs/inventory.md](docs/inventory.md).

## Componentes reconhecidos

Header, branding, navegação desktop/mobile, menu utilitário/Conta, breadcrumb, hero, pares de CTA, cards, listas editoriais, bloco institucional, formulários, alerts, tables, footer e apresentação integrada de Conta/Cursos.

Componentes visuais nunca armazenam estado funcional.

Veja [docs/components.md](docs/components.md).

## Acessibilidade

O tema deve preservar WCAG AA, foco visível, teclado, Escape no menu mobile, `aria-current`, headings, labels, alt text, landmarks e `prefers-reduced-motion`. Links editoriais vermelhos são opt-in, não regra global para todo `a`.

Veja [docs/accessibility.md](docs/accessibility.md).

## JavaScript

Usar Drupal behaviors e `once()`; manter progressive enhancement; deixar Bootstrap e módulos contrib responsáveis por suas engines. O tema adiciona integração de teclado/foco sem reimplementar componentes.

Veja [docs/javascript.md](docs/javascript.md).

## Templates

Twig recebe dados preparados para apresentação. Não consulta banco, não processa pagamento, não decide matrícula, Domain access ou autorização e não persiste estado. Overrides devem preservar atributos e metadata de acesso/cache.

O tema herda templates Bootstrap5/Core/contrib por padrão. Overrides existem apenas quando há delta visual/semântico ACULTA; o G4 removeu o override redundante de `input.html.twig` para restaurar a implementação do Bootstrap5 4.0.8.

No breadcrumb público, `aculta_portal` é dono da política e hierarquia; o tema apenas adapta o título atual para o Twig e renderiza a semântica visual/acessível.

Veja [docs/templates.md](docs/templates.md).

## Evolução

A refatoração estrutural defensiva A–G4 está encerrada. A evolução corrente é a **Fase H — ACULTA Bootstrap Component Design System**, mantendo Bootstrap como infraestrutura e ACULTA como linguagem visual/componentizada.

A divisão de CSS/JS continua sem introduzir Sass, Webpack, Vite, Node, PostCSS ou outra cadeia de build sem benefício técnico concreto e aprovado.

Ambientes de desenvolvimento e produção usam Apache; a configuração continua específica por ambiente e o tema não pode depender de comportamento exclusivo do servidor web.

Veja [docs/development.md](docs/development.md) e [a arquitetura geral](../../../../docs/README.md).
