# Hardening P9 — aculta_portal

Status: revisão e verificação no Homelab (Drupal 11.4.8, PHP 8.4.26 local). Não é homologação em produção.

## Resultados

| Item | Verificação | Resultado |
| --- | --- | --- |
| P9.1 segredos (código) | Literais de credencial em código rastreado; padrões de credencial no histórico git (APP_USR, AKIA, AIza, ya29, chaves privadas, xox, ghp) | Nenhum achado |
| P9.1 segredos (config) | Objetos `key.key.*` de Google e Mercado Pago | Provedor `env`; nenhum valor exportado |
| P9.1 loader | `web/sites/default/aculta.secrets.php` (rastreado) | Apenas lê caminho e variáveis de ambiente; nenhum valor |
| P9.2 erros | `getMessage()`, `getTraceAsString()`, `var_dump`, `print_r`, `dpm`, `error_log` em `src/` | Nenhum achado |
| P9.2 webhook Mercado Pago | Sem segredo: 503. Sem assinatura ou assinatura inválida: 401. Teste in-process com segredo fictício, sem chamada externa | Falha fechada |
| P9.2 webhook HTTP | POST sem assinatura no endpoint real | 503 (esperado: o Homelab não tem segredo configurado) |
| P9.3 confiabilidade | Subscribers de request com `isMainRequest()`; redirects cross-domain falham fechado | Verificado (P7) |
| P9.4 desempenho | Mediana de 5 requisições, quente | Anônimo 23–27 ms (home, cursos, busca Wiki); Conta anônimo 64 ms; Conta logado 124 ms |
| P9.5 gates | Validadores do Portal e do tema | Mesmo resultado da revisão P5-R: 9 PASS; falhas pré-existentes também em `main` |
| P9.6 código órfão | Classes sem referência; serviços sem consumidor | Nenhum órfão real. Os serviços sem referência direta são tags de subscriber/override ou consumidos pelo contrib (`logger.channel.cep_autocomplete`) |
| P9.7 documentação | Matriz de deprecações e este documento | Atualizados |
| P9.8 rollback | Ver runbook abaixo | Rollback apenas de código |

## Runbook de rollback

Pontos de retorno:
- **Baseline da modernização:** `052a212` (`origin/main` antes da PR #90).
- **Último commit antes de P9:** `fc381c8` (P7). Use-o para voltar apenas a P8/P9.

Este branch **não altera** configuração (`config/`), `.install`/hooks de update ou banco. Verificado com `git diff 052a212..HEAD` sobre `config/` e `aculta_portal.install`: nenhuma alteração. Portanto:

1. Não é necessário `drush updb`, `cim` ou `cex` para reverter.
2. Reverter código: criar um novo commit que restaure o estado do ponto de retorno (`git revert` do intervalo) ou publicar um branch a partir do ponto de retorno. **Não usar force-push nem reescrever `main`.**
3. Após o código: `php vendor/drush/drush/drush.php cr`.
4. Smoke: home dos purposes ativos, `/conta-interna` logado, `/wiki/busca?q=…`, `/cursos`.

Fora do git: as permissões ACL aplicadas no host (`aculta` em `/home/piradopirata` e nos arquivos de settings/secrets/var; `bdtgn` em `web/sites/default/files`) não fazem parte do código. A reversão é feita com `setfacl -x` correspondente, quando houver decisão para isso.

## Pendências declaradas

- `composer.json` sem `require.php` (alvo PHP 8.5 não declarado).
- `composer/semver` usado sem declaração direta.
- Teste em PHP 8.5 não executado.
- Fluxo completo do Mercado Pago com segredo real não testado (deliberado: sem credenciais reais nos testes).
- Segredo de root exposto no chat em uma fase anterior: deve ser rotacionado pelo responsável.
- Permissão ACL `bdtgn` em `web/sites/default/files` ainda aplicada; recomendada a remoção.
