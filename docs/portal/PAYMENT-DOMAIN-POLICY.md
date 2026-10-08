# Política do domínio de checkout e pagamentos

Status: **implementada no hardening pré-ACULTA420 0.2-B.3; validação Runtime pendente**.

## Regra canônica

Origem comercial e domínio de pagamento são responsabilidades diferentes.

COURSES, SHOP e SUPPORT podem iniciar uma intenção de compra/apoio, mas todo
checkout/pagamento Drupal Commerce pertence ao purpose `main`.

```text
COURSES → intenção de comprar curso ─┐
SHOP    → intenção de comprar item  ─┼→ MAIN → checkout → pagamento → conclusão
SUPPORT → intenção de apoiar        ─┘
```

Os subdomínios não mantêm checkout ou processador de pagamento próprio.

Em produção, MAIN resolve para `aculta.org`. Em Homelab, resolve para o alias
MAIN do ambiente via `DomainPurposeManager`. Nenhum hostname é hardcoded.

## Rotas centrais

`DomainRoutePolicy::isCentralPaymentRouteName()` é a fonte única para famílias
de rotas que pertencem a MAIN:

- `commerce_checkout.*`;
- `commerce_payment.checkout.*`;
- `commerce_payment.notify`;
- `commerce_donation_flow.*`.

A política é reutilizada por:

- `DomainRouteSubscriber`, para classificar as rotas;
- `ContentPurposeResolver`, para ownership funcional;
- `DomainPurposeRequestSubscriber`, para canonicalização/fail-closed;
- `PortalHooks`, para reescrever links renderizados diretamente para MAIN.

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

O checkout deve começar em MAIN antes de qualquer formulário mutável.

Se um `POST`, `PUT`, `PATCH` ou `DELETE` de checkout/pagamento chegar em
outro purpose, ele **não é redirecionado**. O request falha fechado.

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

Esta política não obriga catálogo ou carrinho a MAIN.

SHOP e COURSES podem manter experiência de descoberta/seleção em seus próprios
purposes. O boundary obrigatório começa na entrada do checkout/pagamento.

Se no futuro houver decisão de centralizar também o carrinho, isso deve ser uma
mudança arquitetural explícita e separada.

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
2. `commerce_checkout.checkout` e `commerce_checkout.form` existem e são MAIN;
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
