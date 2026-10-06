# Design system

O design system do tema deve representar a identidade já aprovada. Refatoração não autoriza redesign.

## Paleta oficial

| Token conceitual | Valor | Uso |
| --- | --- | --- |
| verde estrutural | `#689427` | estrutura institucional |
| verde escuro | `#0c3c29` | contraste e interação |
| amarelo | `#f2ca36` | ação |
| vermelho | `#d4452d` | editorial e ênfase |
| creme | `#fbf4e8` | superfície |
| branco | `#ffffff` | superfície/contraste |

Não adotar Bootstrap blue, cinzas arbitrários, gradientes como identidade ou `color-mix()` como nova cor oficial.

Gradientes puramente técnicos usados para desenhar controles não são, por si, identidade visual.

## Tipografia

### Inter

Usar em:

- corpo;
- formulários;
- metadados;
- UI;
- textos auxiliares.

Pesos carregados atualmente: 400, 500, 600 e 700.

### Oswald

Usar em:

- H1-H6;
- navegação;
- botões e CTAs;
- categorias;
- destaques.

Pesos carregados atualmente: 400, 500, 600 e 700.

Não introduzir outra família sem decisão explícita.

## Botões

### Primary

Normal:

- fundo amarelo;
- texto vermelho;
- borda amarela.

Hover, focus e active:

- fundo verde escuro;
- texto branco;
- borda verde escuro.

### Secondary em par de CTA

Normal:

- fundo transparente/creme;
- texto verde escuro;
- borda de 2px verde escuro.

Hover/focus:

- fundo verde escuro;
- texto branco.

Widgets externos, como Google, Turnstile e Mercado Pago, não devem ser forçados a seguir esse contrato quando isso interferir em sua integração.

## Links editoriais

Links editoriais são opt-in.

Normal:

- vermelho;
- underline.

Hover/focus:

- verde escuro;
- underline.

Visited:

- vermelho.

Aplicar apenas em contextos semânticos como `.aculta-prose`, `.aculta-richtext` ou seletores explícitos. Não criar regras globais para `a`, `.region-content a`, `.node a` ou `.view-content a`.

Não contam como links editoriais:

- header/footer/nav;
- botões;
- Conta;
- formulários;
- Commerce;
- widgets;
- headings clicáveis.

## Header

Estados aprovados:

- inativo: verde estrutural + texto branco;
- current: amarelo + texto vermelho + underline vermelho;
- hover/focus não-current: verde escuro + texto branco;
- hover current: permanece amarelo/vermelho.

A fonte de estado deve ser Drupal: `.is-active`, `.active` e `[aria-current="page"]`. Não comparar URLs em JavaScript.

## Bootstrap

Bootstrap5 continua sendo a base estrutural e comportamental.

Preferir:

- custom properties;
- tokens;
- component scopes;
- classes/markup compatíveis com o base theme.

Evitar uma guerra de especificidade seletor por seletor e não duplicar Bootstrap no tema.

## Tokens

A evolução de `style.css` deve centralizar conceitos realmente reutilizados, como:

- cores oficiais;
- famílias e pesos tipográficos;
- spacing recorrente;
- container;
- radius;
- focus ring;
- borders;
- estados de botão.

Não criar centenas de variáveis apenas para aumentar abstração. Um token deve representar um conceito estável e reutilizado.
## Autenticação

As rotas Drupal de login, registro e recuperação reutilizam exclusivamente os tokens oficiais de cor e tipografia do tema. Não introduzir literais duplicados em `css/components/auth.css`; mudanças visuais devem ocorrer nos tokens ou em regras semânticas explicitamente justificadas.

