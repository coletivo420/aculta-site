# Uso

Todos os comandos rodam a partir da raiz do repositório, com a CLI do submódulo.

```
CLI=web/modules/custom/aculta_portal/modules/aculta_deployer/bin/aculta-deployer
```

## Comandos

- `$CLI check [--strict]`: valida o registro, as fronteiras e o escopo, e lista as
  entradas abertas. Com `--strict`, sai com código 2 se houver entrada bloqueante aberta.
- `$CLI boundaries`: só a verificação de fronteiras.
- `$CLI list`: lista as entradas do registro.
- `$CLI register --kind=K --page=P --current=C --expected=E --reason=R --owner=O [--blocking]`:
  acrescenta uma entrada. Os campos são validados antes de gravar.
- `$CLI build --out=DIR [--allow-open-blocking]`: gera a árvore de produção em `DIR`.
  - `DIR` não pode ficar dentro do repositório.
  - Com entradas bloqueantes abertas, o build é recusado. `--allow-open-blocking` serve
    só para ensaio, e não para publicar.
  - Grava `DIR/deploy-report.json` com os arquivos, as remoções e as substituições.
- `$CLI version`.

## Procedimento de deploy

1. `$CLI check --strict`. Precisa passar; se não, resolva ou registre as entradas.
2. `$CLI build --out=/caminho/de/producao`.
3. Confira `deploy-report.json`: nenhum `toca.net.br` deve restar nos arquivos gerados.
4. Aplique a árvore gerada no ambiente de produção, pelo processo do responsável.
5. Após o deploy, rode o validador de navegador contra produção e atualize o registro:
   entradas resolvidas passam para `status: resolved`.

## Testes

```
php web/modules/custom/aculta_portal/modules/aculta_deployer/tests/run.php
```
