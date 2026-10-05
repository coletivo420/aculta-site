# Tema aculta

Tema público da plataforma Drupal da Associação Cultural Antiproibicionista. `aculta` é o machine name técnico.

## Base theme

`bootstrap5`. Bootstrap pertence ao base theme; não embarcar segunda cópia.

## Responsabilidades

O tema possui identidade visual, tipografia, design tokens, layout, header/footer, navegação, Twig overrides, apresentação de formulários, foco, responsividade e componentes.

Não possui autenticação, pagamentos, cursos, matrícula/progresso, Domain access ou regras de negócio.

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

Tipografia: Oswald para display/headings/nav/CTAs; Inter para corpo, formulários e UI.

Semântica: verde = estrutura, verde escuro = contraste/interação, amarelo = ação, vermelho = ênfase editorial, creme/branco = superfície.

## Estrutura

- `assets/branding` - fontes e exports de marca.
- `css/style.css` - sistema visual atual.
- `js/aculta.js` - progressive enhancement.
- `templates` - overrides Twig.
- `config` - defaults/schema de theme settings.

## Componentes reconhecidos

Header, branding, navegação desktop/mobile, breadcrumb, hero, pares de CTA, cards, listas editoriais, bloco institucional, formulários, alerts, tables, footer e apresentação integrada de Conta/Cursos.

Componentes visuais nunca armazenam estado funcional.

## Acessibilidade

WCAG AA, foco visível, teclado, headings, labels, alt text e `prefers-reduced-motion`. Links editoriais vermelhos são opt-in, não regra global para todo `a`.

## JavaScript

Usar Drupal behaviors e `once`; manter progressive enhancement; deixar Bootstrap/contrib responsáveis por suas engines. O tema adiciona integração de teclado/foco sem reimplementar o componente.

## Templates

Twig recebe dados preparados. Não consulta banco, não processa pagamento, não decide matrícula e não persiste dados. Overrides devem preservar atributos e metadata de acesso/cache.

## Evolução do CSS

Quando necessário, dividir gradualmente `style.css` via Drupal Libraries em tokens/base/layout/components/utilities. Não introduzir Sass, Webpack ou Node sem necessidade concreta.

Veja também [a arquitetura geral](../../../docs/README.md).
