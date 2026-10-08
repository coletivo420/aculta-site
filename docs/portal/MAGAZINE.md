# Revista / Observatório Coletivo 420

## Identidade

O purpose `magazine` corresponde ao **Observatório da Maconha Coletivo 420**,
a camada editorial/revista da plataforma ACULTA.

Host canônico:

`coletivo420.aculta.org`

Homelab:

`coletivo420.aculta.toca.net.br`

O termo "Revista" pode ser usado como descrição funcional da experiência
editorial; o nome público atualmente configurado é "Observatório da Maconha
Coletivo 420".

## Fonte de verdade

A publicação editorial permanece Drupal-native.

| Conceito | Fonte |
| --- | --- |
| notícia/artigo | Node `article` |
| autor editorial | Taxonomy `editorial_author` |
| categoria | Taxonomy `editorial_category` |
| tags | Taxonomy |
| imagem | Media |
| metadados | Metatag / Schema Metatag |
| publicação/revisão | Node/revisions/workflow |
| origem | Domain Source |
| listagens | Views |

O Portal não deve criar CMS editorial paralelo.

## Papel do Portal

A integração pode:

- construir URLs canônicas para MAGAZINE;
- adaptar links de autores/categorias;
- compor destaques em outros purposes;
- expor status/resumos no Admin Hub;
- integrar conteúdos relacionados a projetos/atividades;
- aplicar metadados que realmente sejam específicos da arquitetura ACULTA.

## Home institucional x Revista

A Home MAIN pode consumir destaques da Revista através de Views/blocos, sem
copiar Nodes.

MAGAZINE continua responsável pela experiência editorial completa.

## Workflow

Conteúdo editorial deve preservar:

- revisões;
- autoria editorial separada da conta Drupal responsável pelo cadastro;
- data real da primeira publicação quando configurada;
- categorias/tags;
- referências;
- relações com projeto/atividade;
- Domain Source.

## SEO

O host editorial deve ter:

- canonical correto;
- sitemap;
- Metatag;
- Schema.org quando aplicável;
- Open Graph.

Plugins Schema custom do `aculta_portal` são dívida auditável: só permanecem
quando a versão upstream instalada não cobre a propriedade necessária com a
mesma semântica.

## Google

Analytics e Search Console são integrações de plataforma, não lógica editorial.

O reporting inicial deve permitir distinguir o hostname/purpose MAGAZINE sem
inserir snippet específico no tema da Revista.

Search Console deve observar URLs canônicas e sitemap reais.

## Administração

O Admin Hub pode fornecer atalhos/status para:

- conteúdos;
- autores;
- categorias;
- workflow;
- mídia;
- SEO;
- Views.

Não recriar formulários de Node/Taxonomy.

## Testes

- front MAGAZINE;
- article canonical no host correto;
- host incorreto conforme política Domain;
- unpublished/restricted;
- author/category links;
- revisions/workflow;
- Meta/Schema;
- sitemap;
- relações projeto/atividade;
- destaques consumidos no MAIN sem duplicação.
