# Inventário técnico do tema aculta

Este documento registra o estado encontrado no início da refatoração behavior-preserving. Ele descreve o código atual; itens de dívida técnica listados aqui não são alterações já realizadas.

Linha de base auditada: `main` em `dbc114d91854f65f75b1b79af47c6ca48ab26426` (2026-10-06).

## Mapa de arquivos

```text
web/themes/custom/aculta/
├── aculta.info.yml
├── aculta.libraries.yml
├── aculta.theme
├── README.md
├── assets/
│   └── branding/aculta/
│       ├── source/
│       └── web/
├── config/
│   ├── install/aculta.settings.yml
│   └── schema/aculta.schema.yml
├── css/
│   ├── tokens.css
│   ├── base.css
│   ├── layout.css
│   ├── components/
│   │   ├── header.css
│   │   ├── navigation.css
│   │   ├── breadcrumb.css
│   │   ├── content.css
│   │   ├── buttons.css
│   │   └── auth.css
│   └── style.css
├── js/
│   ├── navigation.js
│   └── editorial-carousel.js
└── templates/
    ├── block--block-content--type--aculta-institution.html.twig
    ├── block--system-branding-block.html.twig
    ├── feed-icon.html.twig
    ├── node--editorial-highlight.html.twig
    ├── node--project--teaser.html.twig
    ├── page.html.twig
    ├── views-view-vvjb.html.twig
    ├── form/input.html.twig
    └── navigation/breadcrumb.html.twig
```

## Theme metadata e libraries

`aculta.info.yml`:

- exige Drupal `^11`;
- usa `bootstrap5` como base theme;
- anexa `aculta/global`;
- declara header, primary_menu, highlighted, content, sidebar e footer.

`aculta.libraries.yml` possui uma única library global com:

- Inter 400/500/600/700;
- Oswald 400/500/600/700;
- `css/tokens.css`;
- `css/base.css`;
- `css/layout.css`;
- `css/components/header.css`;
- `css/components/navigation.css`;
- `css/components/breadcrumb.css`;
- `css/components/content.css`;
- `css/components/buttons.css`;
- `css/style.css`;
- `css/components/auth.css`;
- `js/navigation.js`;
- `js/editorial-carousel.js`;
- `bootstrap5/global-styling`;
- `bootstrap5/bootstrap5-js-latest`;
- `core/drupal`;
- `core/once`.

A redução de assets globais é objetivo posterior. O Commit A não altera carregamento.

## CSS

Na linha de base auditada, todo o CSS estava concentrado em `css/style.css`, com aproximadamente 1.029 linhas.

Após o Commit B, as fundações foram separadas em `tokens.css`, `base.css` e `layout.css`. O Commit C inicia a componentização sem reordenar a cascade: `header.css` → `navigation.css` → `breadcrumb.css` → `content.css` → `buttons.css` → `style.css` → `auth.css`, sempre depois das três fundações. `style.css` permanece com os blocos ainda intercalados que exigem uma segunda onda de extração.

O conjunto CSS contém:

- paleta e custom properties `--aculta-*`;
- integração com variáveis Bootstrap;
- base e tipografia;
- layout/container;
- header e navegação;
- botões e formulários;
- componentes editoriais e institucionais;
- footer;
- Conta e telas Core de autenticação;
- integração visual com VVJB;
- media queries e `prefers-reduced-motion`.

Observações auditadas para commits posteriores:

- o arquivo concentra responsabilidades demais;
- há usos de `!important`, principalmente em utilities Bootstrap;
- o bloco recente de autenticação repete cores literais já existentes como tokens;
- há breakpoints equivalentes escritos com valores diferentes;

## Correções após o inventário inicial

### A0 — token do footer

O token inválido `--aculta-dark-green` identificado no inventário inicial foi corrigido para o token oficial já existente `--aculta-green-dark` nas três declarações do footer.

A varredura do `style.css` após a correção não encontrou outras custom properties `--aculta-*` usadas sem definição no próprio tema.

Esta correção não altera a paleta nem introduz novo token; apenas restaura a aplicação do verde escuro oficial onde a declaração CSS antes ficava inválida.

## B — fundações CSS

O Commit B separa, sem reescrever regras:

- `tokens.css`: comentário de identidade, paleta oficial, custom properties do tema e integração com tokens Bootstrap;
- `base.css`: base, tipografia global e famílias aplicadas a elementos de UI;
- `layout.css`: container e layout estrutural geral;
- `style.css`: começa no antigo bloco de header e mantém todo o restante na ordem original.

A validação estática do commit reconstrói byte a byte o `style.css` anterior pela concatenação `tokens.css + base.css + layout.css + style.css`. A única mudança adicional de runtime é `aculta.libraries.yml` carregar os quatro arquivos nessa mesma ordem.

Este passo não componentiza header, navegação, formulários, Conta ou editorial; isso fica para o Commit C.

## C — primeira onda de componentes CSS

O Commit C extrai blocos semanticamente contínuos do CSS residual sem reescrever seletores ou declarações:

- `components/header.css`;
- `components/navigation.css`;
- `components/breadcrumb.css`;
- `components/content.css`;
- `components/buttons.css`;
- `components/auth.css`.

A concatenação `header + navigation + breadcrumb + content + buttons + style + auth` reconstrói byte a byte o `style.css` anterior ao Commit C. Footer, formulários genéricos, integrações Bootstrap/Drupal, composição institucional e VVJB continuam no `style.css` porque ainda aparecem intercalados; serão movidos apenas quando a separação puder preservar a ordem sem fragmentação artificial.

## JavaScript

Após o Commit D, os dois behaviors foram separados sem alterar seus blocos internos:

- `js/navigation.js` contém `Drupal.behaviors.acultaNavigation`;
- `js/editorial-carousel.js` contém `Drupal.behaviors.acultaEditorialFocus`.

Os dois arquivos continuam na library global e preservam as mesmas dependências.


1. `Drupal.behaviors.acultaNavigation`
   - usa o Collapse do Bootstrap;
   - habilita Escape no menu mobile;
   - devolve foco ao toggle;
   - marca a navegação como pronta apenas quando Bootstrap e elementos necessários existem.

2. `Drupal.behaviors.acultaEditorialFocus`
   - integra foco com a API pública do VVJB;
   - pausa o carousel quando o foco entra;
   - não cria timer próprio;
   - trata `AbortError` de ViewTransition localmente.

Estado auditado:

- usa `once()`;
- não introduz jQuery;
- não usa `setTimeout` ou `setInterval`;
- não reimplementa Bootstrap ou VVJB.

O Commit D preserva esses contratos e não otimiza attachment/carregamento condicional.

## D — JavaScript por responsabilidade

O Commit D substitui o arquivo monolítico `js/aculta.js` por:

- `js/navigation.js`;
- `js/editorial-carousel.js`.

Os blocos dos dois Drupal behaviors foram preservados literalmente. A mudança estrutural duplica apenas o wrapper IIFE necessário para que cada arquivo seja executável de forma independente. A library `aculta/global` continua carregando ambos globalmente, com as mesmas dependências Bootstrap/Drupal/`once()`.

Otimização por rota, página ou attachment condicional fica fora deste commit.

## PHP do tema

`aculta.theme` contém preprocess hooks de apresentação para:

- VVJB;
- fields editoriais;
- label do login;
- page/header block routing;
- block institucional/branding;
- HTML/head title fallback;
- breadcrumb.

A auditoria identificou chamadas estáticas a serviços Drupal. Não serão removidas cosmeticamente: cada mudança deve melhorar uma fronteira real.

### Fronteira breadcrumb

O Portal já registra `AcultaBreadcrumbBuilder` e possui regras de purpose, cache contexts e hierarquia. O preprocess de breadcrumb do tema ainda conhece `aculta_portal.domain_purpose` e repete parte dessa decisão.

Essa duplicação é candidata a um commit posterior de cleanup tema/Portal. O Commit A apenas a documenta.

## Configuração do tema

`config/install/aculta.settings.yml` contém defaults herdados do Bootstrap5.

`config/schema/aculta.schema.yml` declara, além dos settings Bootstrap, referências de apresentação para:

- página de transparência;
- página inicial institucional;
- UUID do bloco institucional.

Esses settings não transformam o tema em fonte de verdade dos dados referenciados.

## Próximas fronteiras

A refatoração deve preservar:

- identidade visual;
- URLs;
- Domain purposes e canonical;
- Commerce;
- LMS/Group;
- Wiki;
- Conta/autenticação;
- sessão/logout;
- Turnstile;
- schemas e dados.

O objetivo é modularizar apresentação, não redesenhar aplicação.
