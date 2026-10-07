# Editorial card SDC

Primeiro componente `stable` do ACULTA420 Bootstrap Component Design System.

O SDC contém o markup interno reutilizável e seu CSS exclusivo. O presenter Drupal `node--editorial-highlight.html.twig` continua responsável pelo elemento `<article>`, pela classe de integração `.aculta-editorial-card`, pelos attributes do node, por `title_prefix`/`title_suffix` e pela integração com o Theme API.

## Contrato

Entradas são slots renderáveis:

- `category`;
- `title`;
- `summary`;
- `complement`;
- `cta`.

Não há props funcionais, estado, consulta de serviços, storage, Node, Commerce ou LMS.

## Assets

`editorial-card.css` pertence ao SDC e é carregado automaticamente pelo Drupal quando `aculta420:editorial-card` é renderizado.

O componente não possui JavaScript.

Continuam globais apenas contratos compartilhados, como tokens e a base `.aculta-category`. Regras de layout/controles do VVJB permanecem em `css/components/editorial-carousel.css`, pois pertencem ao pattern/engine de carousel e não ao card.

## Presenter

O presenter deve:

- preservar `attributes`, `title_prefix` e `title_suffix`;
- adicionar `.aculta-editorial-card` ao wrapper;
- encaminhar renderables sem consultar storage paralelo;
- incluir o SDC com `with_context = false`.

Esse contrato é o modelo para futuros `project-card`, `course-card` e `product-card`.
