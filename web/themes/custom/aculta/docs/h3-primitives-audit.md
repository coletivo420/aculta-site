# H3 — Auditoria de primitives reutilizáveis

## Objetivo

Determinar quais primitives do ACULTA Bootstrap Component Design System
justificam uma API própria agora, sem converter CSS/Twig por obrigação.

Baseline revisada contra `main@c0eb730` em 2026-10-07.

## Critérios

Um primitive só deve virar SDC quando houver simultaneamente:

- reutilização real em mais de um contexto;
- responsabilidade visual pequena e clara;
- contrato estável de slot/prop;
- baixo acoplamento a Drupal/Core/contrib;
- benefício de manutenção maior que o custo de criar uma segunda API.

SDC não é sinônimo de primitive. Alguns primitives devem continuar como contrato
CSS/Bootstrap global.

## Resultado

| Candidato | Decisão H3 | Motivo |
| --- | --- | --- |
| `category-label` | **aprovar como primeiro primitive SDC** | já aparece em editorial e project card; semântica simples; conteúdo renderável; baixo acoplamento |
| button | **primitive CSS, não SDC agora** | precisa cobrir Form API, Bootstrap `.btn`, `.button` e submits nativos |
| section-heading | **adiar para H5** | uso confirmado está embutido em header de View/config |
| media | **adiar para H4** | uso atual ainda é específico dos cards existentes |
| icon | **não criar** | não existe sistema ACULTA de ícones com reutilização comprovada |
| editorial-link | **observar** | pode convergir como CTA/link dos cards/patterns H4–H5 |

## `category-label`

O papel de categoria já aparece em:

- `aculta:editorial-card`;
- `node--project--teaser.html.twig`.

A base visual compartilhada está em `.aculta-category`, enquanto variações
contextuais continuam pertencendo aos consumidores.

Nome recomendado: `aculta:category-label`.

Evitar `badge` porque Bootstrap já possui `.badge` e o significado ACULTA é
categoria/taxonomia editorial, não status genérico.

Contrato inicial:

- slot `content`: renderable da categoria;
- sem props de cor/tamanho até existir reutilização comprovada.

## Button

`css/components/buttons.css` continua sendo a ponte do design system para
markup produzido por Drupal/Bootstrap/Form API. Criar `aculta:button` agora
geraria duas APIs concorrentes.

## Section heading e media

`section-heading` fica para H5 junto de `content-section`. `media` fica para
H4, quando project/course/product cards fornecerem requisitos comuns reais.

## Schema enforcement

`enforce_prop_schemas: true` é recomendado como pré-requisito técnico da
implementação H3, mas deve entrar em commit próprio e ser validado contra todos
os SDCs existentes.

## Ordem recomendada

1. ativar e validar `enforce_prop_schemas: true`;
2. criar `aculta:category-label` como experimental;
3. migrar apenas editorial-card e project presenter;
4. manter button como primitive CSS/Bootstrap;
5. não criar heading/media/icon nesta fase;
6. validar markup, cache/access, desktop/mobile e labels longas;
7. promover `category-label` apenas após uso real em pelo menos dois componentes.

## Anti-regressão

H3 não deve:

- substituir Form API por markup customizado de botão;
- introduzir prop de variante sem consumidor real;
- mover View header para Twig apenas para usar SDC;
- criar abstração de media antes de H4;
- acoplar `category-label` a TaxonomyTerm ou `node.field_category`;
- alterar classes Bootstrap globais em nome de componentização.
