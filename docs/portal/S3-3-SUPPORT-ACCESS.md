# S3.3 — Apoio: access first

Data de preparação: 2026-10-06

RUNTIME STATUS: **DEFERRED**

## Objetivo

Resolver o principal finding de privacidade da auditoria estática: pedidos eram
carregados por UID e seus itens/valor/pagamentos eram lidos antes de uma
verificação explícita de entity access.

## Mudanças preparadas

- novo `SupportHistoryPresenter`;
- `commerce_order->access('view', account)` acontece antes de metadata;
- pagamentos passam a usar `commerce_payment` storage
  `loadMultipleByOrder($order)`;
- status financeiro é traduzido para semântica Portal;
- controller fica responsável apenas pelo render atual;
- Commerce continua fonte de verdade;
- nenhuma tabela/ledger paralelo é criado.

## Ordem de segurança

```text
pedido por UID
  ↓
order access(view)
  ↓
itens donation
  ↓
total + pagamentos
  ↓
view-model
  ↓
render
```

## Estados

O presenter preserva os estados já exibidos:

- aprovado;
- em processamento;
- reembolso parcial;
- reembolsado;
- cancelado;
- não concluído;
- aguardando pagamento.

Também prepara `tone` semântico para futuro consumo pelo design system, sem
criar SDC nesta fase.

## AJAX

Nenhuma alteração.

Meu Apoio continua navegável pelo mecanismo atual da Conta e pelo fallback
full-page.

## Gates Runtime

```sh
php -l web/modules/custom/aculta_portal/src/Support/Presentation/SupportHistoryPresenter.php
php -l web/modules/custom/aculta_portal/src/Support/Controller/SupportController.php
composer validate
vendor/bin/drush status
vendor/bin/drush cr
vendor/bin/drush config:status
vendor/bin/drush updatedb:status
```

Validar:

- sem pedidos;
- pedido donation acessível;
- pedido não-donation;
- pedido sem access;
- cada estado de payment;
- order canceled;
- total presente/ausente;
- User A/User B;
- navegação AJAX/fallback;
- nenhum dado de pedido proibido exposto.

## Merge policy

Manter draft até Runtime PASS.

## Próxima fase

S3.4 — Wiki boundary.
