# Loja ACULTA

## Estado atual

O purpose `shop` e seus hosts já estão reservados:

- produção: `loja.aculta.org`;
- Homelab: `loja.aculta.toca.net.br`.

Drupal Commerce já é dependência do projeto e existe uma Store usada pela
arquitetura financeira. Entretanto, no baseline documental atual **não existem
Product Types / Product Variation Types versionados para um catálogo de loja**.

Portanto:

**SHOP é um Domain preparado, não uma loja de catálogo considerada concluída.**

O HTTP 404 observado no baseline anterior deve ser tratado como estado atual,
não como implementação final.

## Fonte de verdade

Quando a Loja for implementada:

| Conceito | Fonte |
| --- | --- |
| store | Commerce Store |
| produto | Commerce Product |
| variação | Commerce Product Variation |
| preço | Commerce Price |
| carrinho | Commerce Cart |
| pedido | Commerce Order |
| checkout | Commerce Checkout |
| pagamento | Commerce Payment |
| gateway | plugin Commerce Payment |

O Portal não cria catálogo, carrinho, pedido ou pagamento paralelo.

## Relação com Apoio

Apoio financeiro já usa Commerce/Donation Flow.

"Doação/Apoio" e "Compra em Loja" são experiências distintas, embora compartilhem
infraestrutura Commerce.

Não transformar Donation Order Items em produtos de loja apenas para
reaproveitar UI.

Não transformar compra de produto em "apoio" para contornar modelagem Commerce.

## Papel do Portal

No painel ACCOUNT, o Portal poderá futuramente mostrar:

- pedidos do usuário;
- status;
- link para detalhes;
- atalhos para a Loja.

No Admin Hub:

- Store;
- produtos;
- pedidos;
- pagamentos;
- gateways;
- relatórios.

Sempre como integração com Commerce.

## Domain

SHOP deve usar `DomainPurposeManager`.

Não hardcodar `loja.aculta.org` em controllers.

Produtos e páginas específicas da loja devem ter canonical SHOP quando essa
arquitetura for implementada.

## Implementação futura

Antes de ativar catálogo:

1. definir finalidade e tipos de produto;
2. definir política comercial/fiscal/entrega;
3. definir Product Types/Variation Types;
4. definir estoque, se aplicável;
5. definir checkout;
6. revisar gateway;
7. validar LGPD e retenção;
8. testar SQLite/MariaDB;
9. testar Domain isolation;
10. exportar configuração.

Nenhuma dessas decisões deve ser inventada pelo Codex sem especificação.

## Google

Uma futura Loja pode enviar eventos de e-commerce ao GA4, mas isso só entra
depois da integração Analytics/consentimento e da existência real do catálogo.

Não adicionar eventos `purchase`, `add_to_cart` etc. antes de existir o fluxo
Commerce correspondente e um plano de mensuração revisado.

## Testes futuros

- SHOP front;
- catálogo;
- produto;
- variação/preço;
- cart;
- checkout;
- pagamento;
- User A/User B;
- canonical;
- host incorreto;
- cancelamento/falha;
- cache;
- acessibilidade;
- eventos Analytics sem dados pessoais.
