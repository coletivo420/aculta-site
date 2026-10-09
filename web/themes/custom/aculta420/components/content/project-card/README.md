# Project card SDC

Status: **experimental** (0.3.0). Promove a `stable` só após consumo real estável e validação, conforme `docs/components.md`.

O SDC contém a marcação interna do card de projeto. O presenter `node--project--teaser.html.twig` continua dono do `<article>`, dos attributes do node, de `title_prefix`/`title_suffix` e dos links (título e CTA), que são montados com `url` e `label` do Drupal.

## Slots

- `media`: imagem já renderizada; omitida quando o campo está vazio (ausência de mídia);
- `category`: categoria do projeto, quando houver;
- `title`: link do título;
- `summary`: corpo do projeto;
- `cta`: link de ação com o rótulo do projeto.

Não há props, JavaScript, storage, consulta de serviços, Commerce ou LMS.

## Diferenças conhecidas em relação ao teaser anterior

- o `title_attributes` do Drupal não é repassado ao `<h3>`, porque o teaser não usa contextual links nem Layout Builder;
- o CSS do card continua em `css/components/institutional.css` e `content.css`. Consolidar essas propriedades num arquivo do SDC fica para uma fase que mexa no visual do card.

## Pendente antes de `stable`

- variante para destaque (`featured`), quando houver consumidor;
- revisão de estado vazio e título longo em navegador com projetos reais.
