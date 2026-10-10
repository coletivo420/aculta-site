# Mercado Pago

Gateway de pagamento do Commerce (`commerce_mercado_pago` 3.0.0-rc3, plugin `mercado_pago_checkout_pro`).
O gateway está **desativado** (`status: false`) e assim permanece até as credenciais de teste estarem no
ambiente de testes e a validação de ponta a ponta ser feita.

## Credenciais

Documentação oficial: [Credenciais](https://www.mercadopago.com.br/developers/pt/docs/your-integrations/credentials).

| Credencial | Uso | Ambiente | Variável (Key) |
| --- | --- | --- | --- |
| Public Key | frontend (meios de pagamento, criptografia dos dados do cartão) | teste e produção, valores diferentes | `MERCADOPAGO_TEST_PUBLIC_KEY` / `MERCADOPAGO_PRODUCTION_PUBLIC_KEY` |
| Access Token | backend (gera pagamentos); chave privada | teste e produção, valores diferentes | `MERCADOPAGO_TEST_ACCESS_TOKEN` / `MERCADOPAGO_PRODUCTION_ACCESS_TOKEN` |
| Assinatura do webhook | valida notificações recebidas | hoje só produção | `MERCADOPAGO_WEBHOOK_SECRET` |

Observações:
- Client ID e Client Secret não são usados pela integração Checkout Pro: o Public Key e o Access Token bastam.
  Não entram no contrato nem no painel. Só existem no formulário de configuração do módulo, que os deixa vazios.
- Credenciais de teste não precisam de ativação. Credenciais de produção exigem ativação na aplicação.
- A documentação não diz se o webhook tem segredo separado por ambiente. Enquanto não houver teste de
  notificação, `MERCADOPAGO_WEBHOOK_SECRET` continua só no ambiente de produção. Decisão pendente.

## Ambiente e modo do gateway

O ambiente vem do ACULTA Deployer (`var/deployer/environment.json`). Nenhuma parte do código troca de ambiente.

| Ambiente (Deployer) | Modo do gateway | Campos preenchidos a partir de |
| --- | --- | --- |
| `test` | `test` (conta de teste) | `MERCADOPAGO_TEST_PUBLIC_KEY`, `MERCADOPAGO_TEST_ACCESS_TOKEN` |
| `production` | `live` (conta e credenciais de produção) | `MERCADOPAGO_PRODUCTION_PUBLIC_KEY`, `MERCADOPAGO_PRODUCTION_ACCESS_TOKEN` |

- O modo `stage` do módulo (conta de produção com credenciais de teste) não é usado.
- Os valores entram por `MercadoPagoEnvironmentOverride` (`aculta_portal`) em memória. Não são exportados
  nem gravados na configuração do gateway.
- Campo ausente fica vazio no gateway. Com a validação `EntitySaveHooks` (`entity_presave`), o gateway não pode
  ser ativado num ambiente sem Public Key e Access Token do modo correspondente.

## Dados de teste

Fonte: [Realizar compras de teste](https://www.mercadopago.com.br/developers/pt/docs/checkout-pro/integration-test/test-purchase).

### Cartões de teste (Brasil)

| Tipo | Bandeira | Número | CVV | Validade |
| --- | --- | --- | --- | --- |
| Crédito | Mastercard | 5480 8328 0103 3311 | 123 | 11/30 |
| Crédito | Visa | 4235 6477 2802 5682 | 123 | 11/30 |
| Crédito | American Express | 3753 651535 56885 | 1234 | 11/30 |
| Débito | Elo | 5067 7667 8388 8311 | 123 | 11/30 |

### Status de pagamento (nome do titular do cartão)

Para simular o resultado, use o status no nome do titular. Documento de teste (CPF): `12345678909`.

| Código | Resultado |
| --- | --- |
| APRO | Pagamento aprovado |
| OTHE | Recusado por erro geral |
| CONT | Pagamento pendente |
| CALL | Recusado com validação para autorizar |
| FUND | Recusado por quantia insuficiente |
| SECU | Recusado por código de segurança inválido |
| EXPI | Recusado por problema com a data de vencimento |
| FORM | Recusado por erro no formulário |
| CARD | Rejeitado por falta de card_number |
| INST | Rejeitado por parcelas inválidas |
| DUPL | Rejeitado por pagamento duplicado |
| LOCK | Rejeitado por cartão desabilitado |
| CTNA | Rejeitado por tipo de cartão não permitido |
| ATTE | Rejeitado por excesso de tentativas de PIN |
| BLAC | Rejeitado por estar na lista negra |
| UNSU | Não suportado |
| TEST | Usado para aplicar regra de valores |

### Usuário de teste comprador

| Campo | Valor |
| --- | --- |
| Nome | Test User |
| País | Brasil |
| User ID | 373592987 |
| Usuário (login) | TESTUSER5593837592941390773 |
| Senha | **não versionada**: fica na variável `MERCADOPAGO_TEST_BUYER_PASSWORD`, no painel "Credenciais do ambiente", seção Ambiente de Testes |
| Código de verificação | **não guardado**: é temporário e vem do painel da conta de teste (Suas integrações > aplicação > Contas de teste). Para Pix ou Boleto, são os últimos 6 dígitos do User ID |

Como usar: entrar no Mercado Pago Developers com o usuário e a senha da conta de teste, em janela anônima do
navegador, para evitar erros de duplicidade de credenciais.

## Pendências

- Informar, no painel "Credenciais do ambiente" (seção Ambiente de Testes), o Public Key e o Access Token de teste.
- Validar o Checkout Pro de ponta a ponta no ambiente de testes, com os cartões e status acima, antes de ativar o gateway.
- Decidir se o webhook terá segredo próprio no ambiente de testes.
- Provisionar as credenciais de produção pelo ACULTA Deployer (não pelo painel do Runtime de teste).
