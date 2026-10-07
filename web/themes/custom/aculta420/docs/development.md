# Desenvolvimento

## Stack

- Drupal 11;
- Bootstrap5 base theme;
- Twig;
- Core SDC;
- CSS nativo;
- ES6;
- Drupal behaviors;
- `once()`;
- Drupal libraries.

Sem Node/Vite/Sass/PostCSS por padrão.

## Machine name

Tema: `aculta420`.

Hooks:

`aculta420_preprocess_*`

Libraries:

`aculta420/<library>`

SDCs:

`aculta420:<component>`

Settings:

`aculta420.settings`

Não reintroduzir provider `aculta`.

## Estrutura de CSS

O CSS global existente permanece dividido por responsabilidade:

- tokens;
- base;
- layout;
- shell/component CSS;
- Drupal/Bootstrap integration;
- residual legacy controlado.

Mover CSS para SDC somente quando ownership exclusivo estiver provado.

## JavaScript

Regras:

- Drupal behaviors;
- `once()`;
- attach/detach compatible;
- progressive enhancement;
- Bootstrap/contrib continuam donos de suas engines;
- nada de listener global duplicado;
- assets específicos carregam contextualmente quando possível.

## Twig

Twig apresenta. Não decide regra de negócio.

Overrides devem:

- existir por delta real;
- preservar attributes/cache/access;
- preferir herança upstream quando markup custom não agrega valor;
- evitar service calls e entity loading.

## PHP do tema

Preprocess é aceitável para adaptação de apresentação.

Integração de domínio/business rules pertence a `aculta_portal`.

Se a lógica começa a conhecer:

- Domain purpose complexo;
- Commerce;
- LMS;
- storage;
- autorização;

ela provavelmente está no lugar errado.

## Configuração

Renomear machine name do tema exige sincronizar:

- `core.extension`;
- `system.theme`;
- theme settings;
- block placements;
- libraries/component provider IDs.

IDs históricos de conteúdo/config não são renomeados sem benefício funcional.

## Workflow

Para cada mudança:

1. atualizar docs relevantes;
2. fazer inventário de consumidores;
3. alterar uma responsabilidade por commit;
4. validar staticamente;
5. `drush cr`;
6. validar config;
7. testar páginas representativas;
8. comparar visual quando houver mudança visual.

## Testes mínimos

Para mudanças runtime:

- PHP lint;
- Twig/YAML sanity;
- cache rebuild;
- config status/import;
- MAIN;
- ACCOUNT;
- SUPPORT;
- COLETIVO420;
- WIKI420;
- SHOP;
- COURSES;
- desktop/mobile;
- teclado/foco;
- reduced-motion quando houver animação.

Para SDC:

- schema;
- empty/long content;
- slots ausentes opcionais;
- asset attachment;
- accessibility states.

## Release

Toda release do tema atualiza:

- `aculta420.info.yml`;
- `CHANGELOG.md`;
- `docs/roadmap.md`;
- documentação afetada.

Tags:

`aculta420-theme-vX.Y.Z`
