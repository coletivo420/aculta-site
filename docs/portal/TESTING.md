# Estratégia de testes do ACULTA Portal

O Homelab é o ambiente de desenvolvimento executável. Mudanças funcionais
devem ser testadas antes do commit.

## Regra de commit

```text
código/config
   ↓
testes
   ↓
PASS
   ↓
documentação mínima
   ↓
commit
```

Teste inconclusivo não é PASS.

## Testes mínimos para PHP/config

Executar conforme o escopo:

```sh
php -l caminho/alterado.php
composer validate
vendor/bin/drush status
vendor/bin/drush cr
vendor/bin/drush config:status
vendor/bin/drush updatedb:status
git diff --check
```

Se PHPCS/PHPStan estiverem configurados para o escopo, executar também.

`composer audit` deve ser executado quando a rede permitir. Se falhar somente
por indisponibilidade externa, registrar a limitação e confirmar se
`composer.json`/`composer.lock` mudaram.

## Configuração Drupal

Quando a feature muda configuração:

1. implementar/testar no Runtime;
2. `drush cex -y`;
3. revisar somente os arquivos esperados;
4. importar em contexto limpo quando aplicável;
5. deixar `config:status` CLEAN.

Nunca aceitar um export massivo não relacionado.

## Matriz de Domains

Purposes atuais:

- MAIN;
- ACCOUNT;
- SUPPORT;
- MAGAZINE;
- WIKI;
- SHOP;
- COURSES.

Quando Forum for ativado:

- FORUM.

Matriz planejada de hosts:

| Purpose | Homelab | Produção |
| --- | --- | --- |
| MAIN | aculta.toca.net.br | aculta.org |
| ACCOUNT | conta.aculta.toca.net.br | conta.aculta.org |
| SUPPORT | apoio.aculta.toca.net.br | apoio.aculta.org |
| MAGAZINE | coletivo420.aculta.toca.net.br | coletivo420.aculta.org |
| WIKI | wiki420.aculta.toca.net.br | wiki420.aculta.org |
| SHOP | loja.aculta.toca.net.br | loja.aculta.org |
| COURSES | cursos.aculta.toca.net.br | cursos.aculta.org |
| FORUM | forum.aculta.toca.net.br | forum.aculta.org |

Produção não é modificada durante desenvolvimento.

## Isolamento

Toda feature especializada deve provar:

```text
host correto -> comportamento esperado
host incorreto -> 404 ou política explicitamente documentada
```

Não usar redirect silencioso como substituto de isolamento sem ADR.

## Testes de usuário

Para dados privados:

- User A vê somente seus dados;
- User B vê somente seus dados;
- anônimo não recebe dados privados;
- cache não cruza usuários.

Aplicar a:

- dados pessoais;
- endereço;
- apoio;
- cursos;
- participação;
- flags;
- notificações.

Wiki/Fórum podem conter conteúdo público; nesse caso o teste verifica autoria e
permissões, não isolamento artificial de conteúdo público.

## AJAX

Toda feature AJAX precisa provar:

- fluxo com JS;
- fallback sem JS quando essencial;
- teclado;
- focus;
- aria-live/mensagens;
- erro de servidor;
- permissions;
- behaviors após substituição parcial;
- back/history quando aplicável.

## Cache

Revisar:

- cache contexts;
- cache tags;
- max-age;
- dependências de entidades;
- `user` / `user.permissions` / `domain` quando necessário.

Nenhuma tela privada pode depender apenas de cache compartilhado.

## Fórum

Na primeira versão:

- landing;
- containers/fóruns;
- tópico;
- resposta;
- canonical;
- host incorreto;
- login compartilhado;
- permissões;
- moderação básica;
- cache.

## Wiki

Continuar validando:

- published/unpublished;
- revisions;
- Diff;
- Domain Source;
- busca;
- canonical;
- access antes de metadata.

## Cursos

Continuar validando:

- membership;
- access;
- progresso;
- needs evaluation;
- account summary;
- course host;
- logout compartilhado.

## CEP/endereço

Validar:

- Profile customer real;
- CEP válido;
- CEP inválido;
- resposta atrasada/stale;
- bairro;
- estado/cidade/logradouro;
- complemento manual;
- submit;
- reload;
- AJAX reinjection;
- User A/User B.

## Estados

Mudança importante do Portal deve continuar compatível com o processo de
snapshot/restore do Homelab.

Quando uma fase exigir novo Estado, usar os scripts oficiais; não copiar SQLite
vivo manualmente.
