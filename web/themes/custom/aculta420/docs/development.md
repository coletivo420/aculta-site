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

Ao evoluir o shell:

- usar Domain purpose como chave funcional; nunca hostname hardcoded;
- manter `DomainPurposeManager` e resolução funcional no `aculta_portal`;
- passar ao tema apenas contexto de apresentação preparado;
- nunca passar entidade `Domain` diretamente para Twig/SDC;
- manter branding de purpose opcional com fallback ACULTA/texto;
- manter um único shell e variar dados, não criar headers paralelos;
- color modes alteram tokens, não markup/geometria;
- preferir `position: sticky` a `fixed` como ponto de partida;
- reutilizar Bootstrap Collapse/Offcanvas em vez de criar engine JS própria.

A Foundation 0.1.0 não autoriza o redesign visual completo. Ver
[shell.md](shell.md).

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
