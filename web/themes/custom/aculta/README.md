# Tema aculta

Tema público da plataforma Drupal da Associação Cultural Antiproibicionista. `aculta` é o machine name técnico.

## Papel arquitetural

`aculta` é a camada de apresentação:

```text
Drupal Core + módulos especializados
                |
                v
          aculta_portal
     integração/orquestração
                |
                v
             aculta
          apresentação
```

O tema possui identidade visual, tipografia, design tokens, layout, header/footer, navegação, Twig overrides, apresentação de formulários, foco, responsividade e componentes visuais.

O tema não é fonte de verdade de autenticação, pagamentos, Commerce, cursos, matrícula/progresso, Domain access, Wiki ou outras regras de negócio. Essas responsabilidades permanecem no Core, módulos especializados e `aculta_portal`.

## Base theme e assets

- Drupal Core: `^11`.
- Base theme: `bootstrap5`.
- Bootstrap pertence exclusivamente ao base theme; não embarcar uma segunda cópia.
- A library global atual é `aculta/global`.
- CSS atual: fundações em `tokens.css`/`base.css`/`layout.css`, componentes em `css/components/` e integrações ainda residuais em `css/style.css`.
- JavaScript atual: `js/aculta.js`.
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

Detalhes: [docs/design-system.md](docs/design-system.md).

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
- `css/style.css` - footer, integrações Bootstrap/Drupal, institucional, editorial/VVJB e responsividade ainda não extraídos.
- `js/aculta.js` - progressive enhancement da navegação e do carousel editorial.
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

Veja [docs/templates.md](docs/templates.md).

## Evolução

A refatoração é incremental e behavior-preserving. A divisão de CSS/JS será feita sem introduzir Sass, Webpack, Vite, Node, PostCSS ou outra cadeia de build sem benefício técnico concreto e aprovado.

Ambientes de desenvolvimento e produção usam Apache; a configuração continua específica por ambiente e o tema não pode depender de comportamento exclusivo do servidor web.

Veja [docs/development.md](docs/development.md) e [a arquitetura geral](../../../../docs/README.md).
