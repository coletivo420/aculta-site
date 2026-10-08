# Política do domínio de checkout e pagamentos

Status: **implementada no hardening pré-ACULTA420 0.2-B.3; validação Runtime pendente**.

## Regra canônica

Origem comercial e domínio de pagamento são responsabilidades diferentes.

COURSES, SHOP e SUPPORT podem iniciar uma intenção de compra/apoio, mas todo
carrinho, checkout e pagamento Drupal Commerce pertence ao purpose `main`.

```text
COURSES → intenção de comprar curso ─┐
SHOP    → intenção de comprar item  ─┼→ MAIN → carrinho → checkout → pagamento → conclusão
SUPPORT → intenção de apoiar        ─┘
```

Os subdomínios não mantêm checkout ou processador de pagamento próprio.

Em produção, MAIN resolve para `aculta.org`. Em Homelab, resolve para o alias
MAIN do ambiente via `DomainPurposeManager`. Nenhum hostname é hardcoded.

## Rotas centrais

`DomainRoutePolicy::isCentralTransactionRouteName()` é a fonte única para famílias
de rotas centrais de carrinho/checkout/pagamento que pertencem a MAIN:

- `commerce_cart.*`;
- `commerce_checkout.*`;
- `commerce_payment.checkout.*`;
- `commerce_payment.notify`;
- `commerce_donation_flow.*`.

A política é reutilizada por:

- `DomainRouteSubscriber`, para classificar as rotas e anotar navegação browser-facing com `_aculta_cross_domain_canonical_purpose=main`;
- `PortalHooks`, para reescrever links renderizados diretamente para MAIN.

`ContentPurposeResolver` e `DomainPurposeRequestSubscriber` não reavaliam nomes `commerce_*`: eles consomem a metadata já gravada na rota. Isso mantém classificação e enforcement desacoplados e segue o padrão Drupal de alterar metadata de rotas em `RouteSubscriberBase`.

Não duplicar listas de rotas em outros arquivos.

## Entrada no checkout

Links de checkout/pagamento devem apontar diretamente para MAIN quando o Portal
os conhece como `Url` roteada.

Isso evita:

```text
SUPPORT/SHOP/COURSES
      ↓
checkout relativo no subdomínio
      ↓
302 corretivo
      ↓
MAIN
```

e prefere:

```text
SUPPORT/SHOP/COURSES
      ↓
link gerado para MAIN pelo Portal
      ↓
MAIN checkout
```

O redirect de canonicalização continua como defesa para URLs antigas, links
contrib que escapem da reescrita ou acesso manual.

Parâmetros de rota e query string são preservados. Host completo recebido do
usuário nunca é reaproveitado.

## Requests mutáveis

O checkout e a UI de gerenciamento do carrinho devem começar em MAIN antes de seus formulários mutáveis.

Isso não proíbe o `Add to cart` nativo na página de produto/curso em SHOP/COURSES: essa mutação faz parte da seleção comercial e usa a API/Form API do Commerce para atualizar a mesma order de carrinho.

Se um `POST`, `PUT`, `PATCH` ou `DELETE` de uma rota central `commerce_cart.*`, `commerce_checkout.*` ou payment chegar em outro purpose, ele **não é redirecionado**. O request falha fechado.

Não usar 307/308 para transferir corpo/método entre hosts.

Formulários carregados corretamente em MAIN submetem ao próprio MAIN por fluxo
normal do Drupal Commerce/Form API.

## Retorno/cancelamento de gateway

Rotas browser-facing de gateway, incluindo
`commerce_payment.checkout.return` e
`commerce_payment.checkout.cancel`, pertencem a MAIN.

Como o formulário de pagamento é construído em MAIN, Commerce/gateway deve
gerar return/cancel URLs a partir do contexto MAIN. O Portal não substitui a
lógica de gateway; apenas garante o ownership de Domain.

Retornos externos legítimos podem usar GET conforme contrato do gateway e
continuam no fluxo Commerce nativo.

## Webhook

`commerce_payment.notify`:

- pertence a MAIN;
- aceita somente POST;
- é machine-to-machine;
- nunca é redirecionado cross-domain;
- wrong-host falha fechado;
- assinatura continua validada pelo `WebhookGuard` antes do contrib.

Webhook não é navegação de usuário e não participa da canonicalização GET/HEAD.

## Carrinho e catálogo

O carrinho é centralizado em MAIN junto com checkout e pagamento.

SHOP e COURSES mantêm descoberta, catálogo e página de produto/curso nos seus
purposes. O formulário nativo `Add to cart` continua podendo ser submetido na página do produto/curso, pois no Drupal Commerce ele é um formulário do order item que cria/atualiza a mesma `commerce_order` de carrinho. Depois disso, ver/editar o carrinho navega para MAIN.

A entidade/order continua sendo do Drupal Commerce; não existe storage paralelo
por purpose. A centralização é de ownership de rota/experiência, não duplicação
de dados.

No futuro, a barra multidomínio poderá exibir um ícone de carrinho em qualquer
purpose. Preferir reutilizar o `commerce_cart` Cart Block/lazy builder do Commerce ou um presenter do Portal baseado em `CartProviderInterface`, preservando o cache context `cart` e as dependências das orders. O Portal entrega um contrato neutro com URL MAIN e estado/contagem autorizada; o tema não consulta Commerce diretamente, não calcula contagem, não resolve hostname e não cria URL do carrinho por conta própria.

Essa futura UI pertence à evolução do shell multidomínio; não é implementada
nesta correção.

## Donation Flow

A página institucional de apoio pode permanecer em SUPPORT.

Quando Commerce Donation Flow inicia seu fluxo dedicado, suas rotas
`commerce_donation_flow.*` pertencem a MAIN. Isso evita manter um checkout
financeiro paralelo em SUPPORT.

## Segurança

A centralização não altera autorização, ownership de order nem validação de
gateway. Core/contrib permanecem fontes de verdade.

Nunca:

- duplicar checkout por purpose;
- persistir pagamento fora do Commerce;
- mover lógica de gateway para Portal/theme;
- usar hostname hardcoded;
- replayar POST entre Domains;
- transformar webhook em redirect;
- confiar em query para escolher hostname;
- habilitar CORS para compartilhar checkout;
- alterar order/payment para “corrigir” Domain.

## Gate

Antes da B.3, validar:

1. todas as rotas reconhecidas pela política têm `_aculta_domain_purpose=main`;
2. `commerce_cart.page`, `commerce_checkout.checkout` e `commerce_checkout.form` existem e são MAIN;
3. return/cancel de pagamento, quando existentes, são MAIN;
4. Donation Flow instalado expõe somente rotas centrais marcadas MAIN;
5. notify é MAIN e POST-only;
6. GET/HEAD de checkout em purpose secundário canonicaliza para MAIN;
7. POST de checkout em purpose secundário falha fechado;
8. links Portal para checkout apontam diretamente a MAIN;
9. query/route parameters são preservados;
10. checkout normal em MAIN continua funcional;
11. return/cancel externo continua funcional;
12. nenhuma mudança de tema é necessária.
