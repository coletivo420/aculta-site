# H3 — Auditoria de primitives reutilizáveis

> Documento histórico de auditoria. O plano de execução H3 foi absorvido pelo roadmap SemVer em `roadmap.md`. As conclusões técnicas permanecem válidas, mas a ordem atual é 0.2.0 Foundations 2.0 → 0.3.0 Card System → 0.4.0 Patterns.

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

## Mapeamento para o roadmap versionado

- 0.2.0: `enforce_prop_schemas`, `category-label`, foundations/motion/semantic tokens;
- 0.3.0: família de cards e eventual primitive de media se houver contrato real;
- 0.4.0: `content-section`/section heading e patterns compostos;
- 0.5.0: Icon API/UI Icons substituem a conclusão antiga de “não criar icon” por um sistema de ícones baseado em API, não por um SDC inventado isoladamente.

## Anti-regressão

H3 não deve:

- substituir Form API por markup customizado de botão;
- introduzir prop de variante sem consumidor real;
- mover View header para Twig apenas para usar SDC;
- criar abstração de media antes de H4;
- acoplar `category-label` a TaxonomyTerm ou `node.field_category`;
- alterar classes Bootstrap globais em nome de componentização.
