# Editorial card SDC

Piloto de Single Directory Component do tema `aculta`.

O componente contém somente o markup interno reutilizável do destaque editorial. O template Drupal `node--editorial-highlight.html.twig` continua responsável pelo elemento `<article>`, pelos attributes do node e pela integração com o sistema de temas.

## Contrato

Entradas são slots renderáveis:

- `category`;
- `title`;
- `summary`;
- `complement`;
- `cta`.

Não há props funcionais, estado, consulta de dados, CSS ou JavaScript próprios neste piloto.

## Decisão do piloto

O CSS permanece na cascade existente para que o Commit F valide descoberta/uso do SDC sem misturar mudança de attachment de assets. Se o padrão provar valor, a eventual co-localização de CSS deve ser feita em commit separado e testável.
