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

`src/Hook/ThemeHooks.php` com `#[Hook]` e DI/autowiring. A Foundation não usa
arquivo `.theme` procedural.

Libraries:

`aculta420/<library>`

SDCs:

`aculta420:<component>`

Settings:

`aculta420.settings`

Não reintroduzir provider `aculta`.

## Estrutura de CSS

O CSS global é dividido por responsabilidade explícita:

- tokens;
- base;
- layout;
- shell/component CSS;
- formulários;
- Conta/apresentação;
- Drupal/Bootstrap integration.

Não existem `css/style.css` ou `css/responsive.css` genéricos/catch-all. Regra nova deve entrar no arquivo
da responsabilidade que a possui; se nenhuma responsabilidade existente servir,
criar uma unidade nomeada e documentada em vez de recriar um arquivo residual.

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

## Allowlist de overrides Twig

A Foundation mantém somente overrides com delta comprovado em relação a
Core/Bootstrap5/contrib:

| Override | Motivo atual |
| --- | --- |
| `page.html.twig` | compor o shell público e regiões existentes |
| `block--system-branding-block.html.twig` | wrapper visual ACULTA420 + fallback textual |
| `navigation/breadcrumb.html.twig` | apresentar o título atual preparado pelo Portal |
| `block--block-content--type--aculta-institution.html.twig` | view modes institucionais específicos |
| `node--editorial-highlight.html.twig` | presenter do SDC `editorial-card` |
| `node--project--teaser.html.twig` | teaser de projeto existente até o Card System v1 |
| `views-view-vvjb.html.twig` | delta de integração/acessibilidade e library contextual do VVJB |

Novo override exige comparação com o template upstream da versão instalada e
uma justificativa documental. Override sem delta real deve ser removido.

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

## Shell multidomínio

> Light e dark são o mesmo ACULTA420; muda a luz, não a arquitetura.

> Color mode no ACULTA420 é uma variação de tokens, não uma variação de layout.

Ao evoluir o shell:

- usar Domain purpose como chave funcional; nunca hostname hardcoded;
- manter `DomainPurposeManager` e resolução funcional no `aculta_portal`;
- passar ao tema apenas contexto de apresentação preparado;
- nunca passar entidade `Domain` diretamente para Twig/SDC;
- manter branding de purpose opcional com fallback ACULTA/texto;
- manter um único shell e variar dados, não criar headers paralelos;
- componentes novos usam semantic tokens quando já houver token para sua função;
- palette primitives permanecem em `tokens.css`, sem espalhar semântica contextual;
- color modes trocam tokens, nunca markup; não adicionar seletor/persistência antes da fase prevista;
- purpose não escolhe cores e branding por hostname no tema;
- color modes alteram tokens, não markup/geometria;
- modos compartilham tipografia, espaçamento, dimensões, grid, breakpoints,
  posicionamento, shell, navegação e comportamento;
- componentes não consultam modo de cor ou `prefers-color-scheme`; a escolha
  visual é resolvida em `css/tokens.css`;
- seletores dark fora de `css/tokens.css` são proibidos pelo gate;
- asset de logo light/dark só pode variar se necessário à legibilidade, mantendo
  espaço, dimensão e layout iguais;
- preferir `position: sticky` a `fixed` como ponto de partida;
- reutilizar Bootstrap Collapse/Offcanvas em vez de criar engine JS própria.

A Foundation 0.1.0 não autoriza o redesign visual completo. A fase 0.2-A prepara
somente semantic tokens; o shell visual segue as etapas documentadas em
[roadmap.md](roadmap.md) e [shell.md](shell.md).

## Configuração

Renomear machine name do tema exige sincronizar:

- `core.extension`;
- `system.theme`;
- theme settings;
- block placements;
- libraries/component provider IDs.

IDs históricos de conteúdo/config não são renomeados sem benefício funcional.
Para placements de blocos customizados, `plugin`, `settings.id` e a dependência
de conteúdo devem apontar para o mesmo UUID existente; tema e região também
precisam corresponder ao provider e às regiões declaradas pelo ACULTA420. O gate
da Foundation verifica tanto a configuração ativa quanto `config/sync`.

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

## Gate de Foundation

Após qualquer mudança estrutural do tema:

```sh
vendor/bin/drush php:script validate-aculta420-foundation --script-path=../scripts
```

O gate falha se reaparecerem provider legado, arquivo `.theme`, catch-all CSS,
asset web sem contrato, dependência direta do tema no Portal ou library quebrada.

Para tokens e fronteiras visuais da linha 0.2:

```sh
php scripts/validate-aculta420-design-foundations.php
php scripts/tests/validate-aculta420-design-foundations-test.php
```

O validator protege o color mode como token-only. Fora de `css/tokens.css`, ele
reconhece seletores CSS com `[data-theme="dark|light"]`,
`[data-bs-theme="dark|light"]`, atributos equivalentes de color-mode/scheme,
classes `.dark`/`.light` e variantes explícitas como `.dark-theme`,
`.theme-dark`, `.dark-mode`, `.is-dark` e suas formas light. Também verifica
condições Twig `if`/`elseif` e ternárias, condições PHP
`if`/`elseif`/`switch`/`match` e condições JavaScript `if`/`switch`/ternárias
que comparam uma variável de modo a `dark`/`light` ou usam um sinalizador
`isDark`/`darkMode` equivalente.
O gate também bloqueia scripts que alternem classes ou atributos de color mode.

O parser CSS cobre as regras planas e os blocos de tokens usados pela foundation;
não é um parser CSS completo. `var()` com fallback, seletores aninhados não
convencionais e formas dinâmicas de decisão que não exponham um identificador de
modo reconhecido ficam fora do subconjunto. Tokens `--aculta-*`/`--bs-*` fora
dos blocos root/light e dark são rejeitados. Antes de ampliar a sintaxe aceita,
adicione fixtures positivas e negativas ao teste do validator.

Para tokens dark, o gate resolve aliases recursivamente, usa a última declaração
na cascata suportada e falha com duplicatas, referências ausentes ou ciclos.
As superfícies charcoal/graphite são valores explícitos aprovados no teste;
não dependem de uma heurística subjetiva RGB.

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
