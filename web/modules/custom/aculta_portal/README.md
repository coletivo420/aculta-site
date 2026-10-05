# ACULTA Portal

`aculta_portal` é a camada de integração da plataforma Drupal da Associação Cultural Antiproibicionista.

> O Portal organiza, apresenta e conecta capacidades Drupal sem substituir suas fontes de dados e regras de negócio.

## Integra

Domain, Drupal User, Profile/Address, Social Auth, Image/Crop, Commerce, Donation Flow, Mercado Pago via Commerce, Drupal LMS/Group, Wiki420, conteúdo editorial, canonical/metatag e shell da Conta.

## Não é

Não é sistema próprio de autenticação, pagamento, LMS, matrícula, progresso, CMS ou tema visual.

## Arquitetura

```text
módulos funcionais -> aculta_portal -> tema aculta
```

## Diretórios

- `src/Controller` - controllers de integração.
- `src/Domain` - purposes, URLs e contexto.
- `src/EventSubscriber` - integração de request/rotas/eventos.
- `src/Commerce` - glue de Commerce.
- `src/Support` - apoio institucional.
- `src/Plugin` - plugins/conditions/metatag.
- `templates`, `css`, `js` - apresentação específica do Portal.

## Contratos

- trabalhar com Domain purpose, não hostname;
- preferir DI, Entity API e serviços públicos dos módulos;
- não consultar tabelas contrib diretamente quando houver API;
- dados privados devem variar por usuário e não usar cache compartilhado;
- segredos ficam fora de Configuration Sync e Git;
- toda nova feature documenta sua fonte de verdade.

## Subsistemas

### Conta
User, Profile, Address e Social Auth continuam fontes de verdade. O Portal fornece a experiência integrada.

### Apoio
Commerce é fonte financeira. Donation Flow fornece o fluxo. O Portal não mantém ledger paralelo.

### Cursos
Drupal LMS e Group são fontes de verdade de cursos, matrícula e progresso. O Portal apenas integra e apresenta.

### Wiki/editorial
Nodes, Taxonomy e Views mantêm conteúdo. Domain/Domain Source controlam contexto.

## Desenvolvimento

Antes de implementar: identificar fonte de verdade, purpose, API oficial, cache metadata, privacidade, canonical e comportamento em host incorreto. Atualizar esta documentação quando a arquitetura mudar.

Veja também [a documentação geral](../../../docs/README.md).
