# Acessibilidade

A refatoração deve manter ou melhorar acessibilidade sem alterar a identidade visual aprovada.

## Contratos mínimos

Auditar e preservar:

- `:focus-visible`;
- navegação por teclado;
- Escape no menu mobile;
- retorno de foco ao toggle;
- `aria-current`;
- labels e accessible names;
- hierarquia de headings;
- alt text;
- landmarks;
- `prefers-reduced-motion`;
- contraste WCAG AA quando aplicável.

## Navegação

O menu mobile usa Bootstrap Collapse como engine. O behavior do tema adiciona Escape e devolução de foco; não deve criar um segundo mecanismo de abrir/fechar menu.

Estado de página atual vem do Drupal (`.is-active`, `.active`, `aria-current`), nunca de comparação manual de URL no JavaScript.

## Carousel editorial

VVJB continua dono de layout, controles, tempo e transições.

O tema:

- pausa o carousel quando foco de teclado entra;
- não cria timer próprio;
- respeita reduced motion no CSS;
- fornece o label necessário ao `aria-labelledby`.

## Contraste e paleta

A paleta oficial não deve ser alterada automaticamente para “resolver contraste”. Corrigir aplicação semântica.

Cuidados conhecidos:

- verde escuro/branco: adequado para alto contraste;
- vermelho/branco: requer atenção em texto pequeno;
- vermelho/creme: não usar indiscriminadamente como texto pequeno;
- verde estrutural/branco: revisar tamanho/peso;
- amarelo/branco: não usar como combinação de texto.

## Formulários

Preservar:

- label associado;
- states/error messaging do Drupal;
- focus ring visível;
- attributes Core/Bootstrap;
- integração de widgets externos sem sobrescrever markup crítico.

## Teste manual mínimo

Depois de mudanças visuais:

1. navegar toda a página somente por teclado;
2. abrir e fechar menu mobile, incluindo Escape;
3. verificar foco após fechamento;
4. validar breadcrumb/current states;
5. percorrer forms e mensagens de erro;
6. testar reduced motion;
7. revisar headings/landmarks;
8. testar páginas representativas em viewport desktop e mobile.
