# H3 — Auditoria de primitives reutilizáveis

## Objetivo

Determinar quais primitives do ACULTA Bootstrap Component Design System justificam uma API própria agora, sem converter CSS/Twig por obrigação.

Baseline auditada: `main` em `77dfc44b1cc020ecb9f960fb8f8a4ac1ba0c63bd`.

## Critérios

Um primitive só deve virar SDC quando houver simultaneamente:

- reutilização real em mais de um contexto;
- responsabilidade visual pequena e clara;
- contrato estável de slot/prop;
- baixo acoplamento a Drupal/Core/contrib;
- benefício de manutenção maior que o custo de criar uma segunda API.

SDC não é sinônimo de primitive. Alguns primitives devem continuar como contrato CSS/Bootstrap global.

## Resultado

| Candidato | Decisão H3 | Motivo |
| --- | --- | --- |
| `category-label` | **aprovar como primeiro primitive SDC** | já aparece em editorial e project card; semântica simples; conteúdo renderável; baixo acoplamento |
| button | **primitive CSS, não SDC agora** | precisa cobrir Form API, Bootstrap `.btn`, `.button` e submits nativos; SDC cobriria apenas markup explicitamente migrado |
| section-heading | **adiar para H5** | uso confirmado está embutido em header de View/config; melhor resolver junto de `content-section` |
| media | **adiar para H4** | uso atual é específico do project card; ainda não existe contrato transversal suficiente |
| icon | **não criar** | não existe sistema ACULTA de ícones com reutilização comprovada |
| editorial-link | **observar** | há reutilização, mas pode convergir melhor como CTA/link dentro de cards/patterns H4–H5 |

## `category-label`

### Evidência

O papel de categoria existe em:

- `aculta:editorial-card`;
- `node--project--teaser.html.twig`.

A base visual compartilhada já está em `.aculta-category`, enquanto variações de tamanho/letter-spacing vivem nos contextos editorial e project.

Isso indica uma API visual real, sem dependência de Node, Views, Commerce ou LMS.

### Contrato proposto

Nome recomendado: `aculta:category-label`.

Evitar `badge` como nome do componente porque Bootstrap já possui `.badge` e o significado ACULTA aqui é taxonomia/categoria editorial, não status genérico.

Entrada principal:

- slot `content`: renderable da categoria.

Começar sem props de cor/size. Variações só devem entrar quando dois ou mais consumidores exigirem explicitamente a mesma variação.

### Integração prevista

```text
Node/View/Commerce/LMS
        |
     presenter
        |
        v
aculta:category-label
        |
 editorial-card / project-card / future cards
```

## Button

`css/components/buttons.css` hoje é uma ponte do design system para markup que o Drupal e Bootstrap geram automaticamente:

- `.btn`;
- `.button`;
- `button.link`;
- `input[type=submit]`;
- `.form-submit`;
- variantes Bootstrap.

Transformar isso em `aculta:button` não substituiria esses consumidores e criaria duas fontes de verdade.

Decisão:

- manter `buttons.css` global;
- tratá-lo documentalmente como **primitive CSS/Bootstrap**;
- um futuro SDC de CTA só deve existir se houver necessidade de markup composto próprio, ícone/label/slot ou uso fora do Form API.

## Section heading

`.aculta-section-title` possui styling claro, mas o uso auditado está no header textual de `views.view.aculta_projects`.

Mover isso para SDC agora exigiria alterar a forma como Views produz o header, o que é desproporcional para H3.

Decisão:

- manter styling global;
- revisar em H5 como parte de `content-section`, onde heading, intro e conteúdo podem formar um pattern coerente.

## Media

A classe `.aculta-project-image` é específica do teaser de projeto. Branding e outros media existentes têm contratos diferentes.

Decisão:

- não criar primitive `media` agora;
- reavaliar em H4 depois que `project-card`, `course-card` e `product-card` tiverem requisitos reais de imagem/aspect ratio/fallback.

## Schema enforcement

Drupal permite que temas usem `enforce_prop_schemas: true` para exigir schemas de props dos seus componentes.

Como o design system agora possui contrato formal e o primeiro SDC já tem schema, essa flag é recomendada como **pré-requisito técnico do H3**, mas deve ser ativada em commit próprio e validada contra todos os componentes existentes.

Referência:
https://www.drupal.org/docs/develop/theming-drupal/using-single-directory-components/creating-a-single-directory-component

## Ordem recomendada de execução H3

1. ativar `enforce_prop_schemas: true` e validar o componente existente;
2. criar `aculta:category-label` como `experimental`;
3. migrar somente `editorial-card` e project presenter para consumi-lo;
4. manter button como primitive CSS/Bootstrap;
5. não criar heading/media/icon nesta fase;
6. validar markup, cache/access, desktop/mobile e long labels;
7. só promover `category-label` a `stable` após uso real em pelo menos dois componentes.

## Anti-regressão

H3 não deve:

- substituir Form API por markup customizado de botão;
- introduzir prop de variante sem consumidor real;
- mover View header para Twig apenas para usar SDC;
- criar abstração de media antes de H4;
- acoplar category-label a TaxonomyTerm ou `node.field_category`;
- alterar Bootstrap classes globais em nome de componentização.
