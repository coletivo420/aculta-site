# Decisões arquiteturais ACULTA420

## D-001 — novo provider em 0.1.0

O antigo tema `aculta` é base histórica. A fundação atual usa:

- nome: **ACULTA420**;
- machine name: `aculta420`;
- diretório: `web/themes/custom/aculta420`.

Não manter alias runtime do provider antigo.

## D-002 — prefixo visual ACULTA permanece

Classes `.aculta-*` e custom properties `--aculta-*` representam o design
language/brand e não o machine name do Drupal.

Renomeá-las para `aculta420-*` adicionaria churn e acoplamento sem benefício.

## D-003 — Bootstrap é infraestrutura

Bootstrap5 fornece grid, utilities e behaviors. ACULTA420 fornece identidade,
contratos e composição.

Não instalar segundo Bootstrap nem reimplementar componentes só para declarar
ownership.

## D-004 — SDC é seletivo

Nem todo primitive vira SDC.

Exemplo: button continua primitive CSS/Bootstrap porque Form API/contrib geram
markup que deve receber o mesmo design sem migração manual.

## D-005 — schemas são obrigatórios

`enforce_prop_schemas: true` faz parte da fundação 0.1.0.

Novo SDC sem contrato de schema é regressão.

## D-006 — config IDs históricos podem permanecer

Block placement IDs e outros config IDs `aculta_*` não são renomeados apenas
por estética quando isso amplia o risco de config import.

O campo/dependency `theme` deve apontar para `aculta420`.

## D-007 — VVJB continua engine de carousel

O design system pode criar um pattern visual comum, mas VVJB não será removido
enquanto cumprir bem seu papel.

## D-008 — ecossistema entra por capacidade

Planejamento atual:

- UI Icons: sistema de ícones;
- UI Patterns 2: exposição seletiva de nossos SDCs;
- UI Examples/UI Patterns Library: catálogo;
- Canvas: autoria visual pós-estabilização;
- Display Builder: pesquisa enquanto beta.

UI Suite Bootstrap e Canvas Bootstrap são referência, não design system
concorrente.

## D-009 — sem build tooling por padrão

CSS nativo + JS nativo + Drupal libraries + behaviors + once().

Node/Vite/Sass/PostCSS/Storybook exigem ganho demonstrável.

## D-010 — versionamento é do tema

ACULTA420 usa SemVer próprio. Tags são namespaced:

`aculta420-theme-v0.1.0`.

O versionamento do tema não substitui o versionamento do site ou do Portal.
