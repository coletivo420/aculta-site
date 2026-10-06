# Estratégia de testes do ACULTA Portal

O projeto opera temporariamente em modo
[GitHub-first / Runtime-last](DELIVERY-MODE.md).

Isso separa **revisão estática** de **validação Runtime**.

## Gate A — GitHub/static

Pode ser executado sem Homelab.

Aplicável a:

- docs;
- ADRs;
- contratos;
- inventários;
- planos de teste;
- análise de código;
- preparação de draft PR.

Verificar conforme aplicável:

- diff;
- conflitos;
- referências internas;
- consistência com source-of-truth;
- APIs upstream documentadas;
- ausência de segredos;
- ausência de alteração acidental no tema;
- separação Portal/tema;
- contratos do Bootstrap Component Design System.

Gate A nunca autoriza declarar comportamento funcional como PASS.

## Gate B — Runtime

Obrigatório antes de merge/release de mudança funcional.

Executar conforme escopo:

```sh
php -l caminho/alterado.php
composer validate
composer audit
vendor/bin/drush status
vendor/bin/drush cr
vendor/bin/drush config:status
vendor/bin/drush updatedb:status
git diff --check
```

Adicionar PHPCS/PHPStan/testes automatizados quando estiverem configurados.

## Drafts sem Runtime

Mudança executável preparada sem Homelab deve permanecer draft e declarar:

```text
RUNTIME STATUS: DEFERRED
```

O PR deve listar os gates faltantes.

Não taggear release.

## Configuração Drupal

Mudanças `config/sync` exigem Runtime antes do merge funcional:

1. aplicar no Runtime;
2. validar;
3. `drush cex -y`;
4. revisar somente arquivos esperados;
5. testar import quando aplicável;
6. terminar com `config:status` CLEAN.

Não fabricar YAML de configuração e tratá-lo como validado apenas por revisão.

## Matriz de Domains

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

FORUM é planejado até a implementação Runtime.

Produção não é modificada durante desenvolvimento.

## Isolamento

Toda feature especializada deve provar no Runtime:

```text
host correto -> comportamento esperado
host incorreto -> 404 ou política documentada
```

## User A / User B

Aplicar a dados privados:

- perfil;
- endereço;
- apoio;
- cursos;
- participação;
- flags;
- notificações.

Wiki/Fórum públicos verificam autoria/access, não isolamento artificial.

## Component Design System

Para UI pública nova validar:

- contrato presenter -> SDC;
- componente Bootstrap usado corretamente;
- keyboard;
- focus-visible;
- headings/landmarks;
- loading/empty/error;
- mobile;
- reduced motion;
- titles longos;
- cache/access não vazando para a camada visual.

## AJAX

Validar:

- JS;
- fallback sem JS quando essencial;
- focus;
- aria-live/mensagens;
- erro;
- permissions;
- behavior reattach;
- history/back;
- BigPipe/AJAX quando aplicável.

## Subsistemas

### Fórum

- landing;
- tópico;
- resposta;
- canonical;
- isolation;
- shared login;
- permissions;
- cache.

### Wiki

- published/unpublished;
- revisions;
- Diff;
- Domain Source;
- search;
- canonical;
- metadata access.

### Cursos

- membership;
- access;
- progresso;
- needs evaluation;
- ACCOUNT;
- COURSES;
- shared logout.

### CEP

- Profile customer;
- CEP válido/inválido;
- stale response;
- Address fields;
- submit/reload;
- AJAX reinjection;
- User A/User B.

## Estados

Criar novo Estado somente quando a janela Runtime demonstrar necessidade.

Não recuperar ou promover o trabalho local antigo descartado apenas para manter
continuidade histórica.
