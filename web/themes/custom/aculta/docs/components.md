# Componentes visuais

Este catálogo descreve componentes existentes ou reconhecidos no tema. Não é autorização para criar storage, regras de negócio ou novos subsistemas no tema.

## Contrato geral

Todo componente visual deve:

- receber dados do Drupal/Core/contrib/`aculta_portal`;
- preservar atributos, acesso e cache metadata quando aplicável;
- continuar utilizável com progressive enhancement;
- respeitar o design system e acessibilidade;
- não persistir estado funcional;
- não consultar diretamente banco de dados.

## Catálogo atual

| Componente | Implementação principal | Fonte de dados/estado |
| --- | --- | --- |
| Header | `page.html.twig`, CSS | regions/blocks Drupal |
| Branding | branding block Twig + preprocess | System Branding + assets do tema |
| Navegação desktop/mobile | `page.html.twig`, `aculta.js`, CSS | Menu Drupal + Bootstrap Collapse |
| Menu utilitário/Conta | page preprocess + region render arrays | blocks/menu Drupal |
| Breadcrumb | Portal builder + Twig/preprocess atual | routing, Domain purpose, entities |
| Hero | CSS/Twig de conteúdo existente | conteúdo Drupal |
| CTA pair | classes visuais existentes | links/conteúdo renderizado |
| Project card | `node--project--teaser.html.twig` | fields do node |
| Editorial highlight | `node--editorial-highlight.html.twig` | fields do node + VVJB |
| Editorial list/prose | field preprocess + CSS | fields/Views Drupal |
| Institution block | block Twig + preprocess | custom block fields |
| Forms | Bootstrap/Core markup + CSS + input override | Form API |
| Alerts/tables | Bootstrap + CSS do tema | render arrays Drupal |
| Footer | `page.html.twig`, bloco institucional, menus | regions/blocks Drupal |
| Account shell | `page.html.twig` + apresentação Portal | User/Profile/Portal |
| Course cards/resumo | apresentação Portal + tema | Drupal LMS/Group |
| Wiki lists | apresentação Drupal/Portal | Node/Taxonomy/Views |

## Limites

O fato de um componente apresentar Conta, Cursos, Wiki ou Commerce não transfere propriedade funcional ao tema.

Exemplos:

- course card pode exibir progresso preparado; não calcula nem salva progresso;
- account shell pode exibir ações; não autentica usuário;
- institution block apresenta fields; não cria registro institucional paralelo;
- breadcrumb apresenta a trilha; regras de Domain purpose pertencem ao Portal.

## Single Directory Components

Drupal 11 permite SDC, mas a adoção deve ser seletiva.

Um candidato a SDC deve:

- ter reutilização real;
- possuir contrato claro de props/slots;
- reduzir duplicação;
- melhorar legibilidade/testabilidade.

Não migrar todo Twig apenas para usar uma API moderna. Um piloto só deve ser criado em commit próprio após a modularização básica e validação dos contratos reais.
