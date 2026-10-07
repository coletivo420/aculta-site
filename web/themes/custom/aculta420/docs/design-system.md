# Design system

## Identidade

ACULTA420 usa Bootstrap como infraestrutura e mantém identidade própria por
tokens e contratos de componentes.

### Paleta

| Token | Valor | Uso |
| --- | --- | --- |
| `--aculta-green-dark` | `#0c3c29` | texto, contraste, interação |
| `--aculta-green` | `#689427` | estrutura e marca |
| `--aculta-yellow` | `#f2ca36` | ação/destaque |
| `--aculta-red` | `#d4452d` | ênfase editorial |
| `--aculta-cream` | `#fbf4e8` | superfície |
| `--aculta-white` | `#ffffff` | superfície/contraste |

O prefixo `--aculta-*` é mantido por decisão arquitetural: representa o
design language, não o machine name do tema.

## Tipografia

- display/headings/navigation/CTA: Oswald;
- corpo/forms/metadados/UI: Inter.

Fontes são declaradas na library global atual. Uma futura estratégia de
self-hosting pode ser avaliada separadamente; não misturar com componentização.

## Bootstrap mapping

Tokens ACULTA alimentam `--bs-*` para evitar duas paletas paralelas.

Sempre manter pares `color` / `color-rgb` semanticamente equivalentes.

Não redefinir Bootstrap em cada componente quando uma custom property global
resolve o problema.

## Foundations

0.1.0 possui:

- color tokens;
- font tokens;
- border/focus tokens;
- section spacing;
- Bootstrap semantic mapping.

Próximos foundations previstos:

- semantic surface/text/interactive tokens;
- semantic tokens específicos do shell, sem amarrá-los a um purpose;
- motion durations/easings;
- color modes;
- spacing scale mais explícita quando houver uso comprovado.

Vocabulário alvo do shell inclui conceitos como:

```css
--aculta-surface-page: ...;
--aculta-surface-raised: ...;
--aculta-surface-header: ...;
--aculta-text-primary: ...;
--aculta-text-secondary: ...;
--aculta-border-subtle: ...;
--aculta-shell-institution-bg: ...;
--aculta-shell-domain-bg: ...;
```

Os nomes semânticos definem função, não cor literal.

## Color modes

Bootstrap 5.3 usa `data-bs-theme` para color modes. ACULTA420 seguirá o mesmo
contrato.

Ordem de implementação:

1. semantic tokens independentes de modo;
2. valores light;
3. valores dark;
4. `auto` baseado em `prefers-color-scheme`;
5. seletor e persistência;
6. alto contraste somente após auditoria.

Não ativar dark mode enquanto componentes dependerem de cores literais que não
tenham equivalente semântico.

Modo de cor troca tokens, não geometria nem markup do shell. Logos específicos de
purpose podem futuramente ter variantes light/dark, mas o contrato inicial não
as torna obrigatórias.

Referência:
https://getbootstrap.com/docs/5.3/customize/color-modes/

## Motion

Motion deve ser foundation, não decisão local de cada componente.

Roadmap:

```css
--aculta-motion-fast: ...;
--aculta-motion-normal: ...;
--aculta-motion-slow: ...;
--aculta-ease-standard: ...;
```

Todos os componentes animados devem respeitar `prefers-reduced-motion`.

## Primitives

Primitive é uma categoria do design system, não sinônimo de SDC.

Exemplos:

- Button: primitive Bootstrap/CSS global;
- Category label: candidato a SDC;
- Icon: futuramente via Core Icon API/UI Icons;
- Media: só abstrair quando cards comprovarem contrato comum.

## Variants

Drupal Core suporta variants SDC em 11.2+.

Usar variant quando:

- semântica é a mesma;
- estrutura principal é a mesma;
- diferença é uma apresentação nomeada e finita.

Não usar variants para codificar regra de negócio ou criar combinatória
arbitrária de opções.

## Assets

- foundations realmente globais ficam em `aculta420/global`;
- CSS global não significa CSS sem ownership: cada arquivo representa uma responsabilidade;
- não manter `css/style.css` ou outro catch-all residual;
- CSS/JS exclusivo de SDC fica no diretório do componente;
- integrations/patterns podem usar libraries contextuais;
- evitar asset global “por conveniência”.

## Branding assets

Assets técnicos do tema ficam em:

`assets/branding/aculta420/`

Originais e exports devem permanecer separados. Alterar nome de arquivo não
significa redesenhar identidade visual.

Branding de purpose é opcional e deve respeitar o fallback documentado em
[shell.md](shell.md).

## Anti-regressão

Não:

- introduzir cor literal duplicada quando existe token;
- criar nova fonte/tipografia sem decisão;
- embarcar Bootstrap novamente;
- usar utilitário Bootstrap para substituir contrato semântico necessário;
- criar motion incompatível com reduced-motion.
