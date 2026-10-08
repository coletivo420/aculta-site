# CAPTCHA e Cloudflare Turnstile

## Política

- Pessoas anônimas recebem Turnstile nos formulários públicos. O token válido é
  obrigatório para enviar; submissão sem token válido é recusada.
- Turnstile é o único desafio e fica ativo globalmente para visitantes anônimos.
  Pessoas autenticadas podem ignorá-lo pela permissão `skip CAPTCHA`. Não existe
  fallback para outro CAPTCHA quando JavaScript ou o serviço falha.
- O widget é apresentado em português brasileiro (`pt-br`) e usa aparência
  `always`, para ficar visível ao usuário anônimo.
- O papel base `authenticated` recebe a permissão `skip CAPTCHA`. Pessoas
  autenticadas não recebem CAPTCHA nem widget nos formulários.
- Login e criação de conta via OAuth ocorrem fora dos formulários Drupal
  protegidos; o callback OAuth não exige CAPTCHA. Não há bypass novo no Portal.

## Configuração

`captcha.settings.enable_globally` fica habilitado (`1`) para visitantes
anônimos, com rotas administrativas excluídas. O papel `authenticated` possui
`skip CAPTCHA`. CAPTCHA points explícitos também usam `turnstile/Turnstile`; nenhum ponto deve
selecionar outro desafio.

As chaves do serviço continuam fornecidas pelo mecanismo de Key/environment do
ambiente. `TURNSTILE_KEYS_JSON` contém JSON codificado em Base64 e o provider
Key faz a decodificação. Nunca versionar segredos ou habilitar um desafio
alternativo como contingência. Se as chaves estiverem ausentes, o desafio
falha fechado e não troca para CAPTCHA matemático.

## Validação funcional

Verificar no Homelab:

1. Em sessão anônima, os formulários públicos apresentam Turnstile visível e
   localizado em `pt-br`.
2. Submeter sem token ou com token inválido é recusado.
3. Em sessão autenticada, os mesmos formulários não renderizam CAPTCHA ou
   widget.
4. Login/cadastro OAuth continua iniciando e retornando sem exigir CAPTCHA.
5. Se Turnstile/JavaScript não funcionar para uma pessoa anônima, o formulário
   permanece protegido e informa a falha sem trocar para outro desafio.
