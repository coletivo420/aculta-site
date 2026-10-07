# Legacy migration shim: aculta

Este diretório **não é o tema público atual**.

O tema atual é [ACULTA420](../aculta420/README.md), machine name `aculta420`.

## Por que este shim existe

Ambientes existentes podem ter `aculta` registrado em `core.extension` e como
tema default antes do primeiro import da configuração 0.1.0. Remover o código da
extensão antes de migrar a configuração pode deixar o Drupal com um tema
instalado ausente do filesystem.

Este shim mantém apenas a extensão antiga descobrível durante a janela de
migração. Ele não contém CSS, JS, templates, SDCs ou lógica do tema histórico.

## Remoção

Depois que **todos** os ambientes confirmarem:

- `system.theme:default = aculta420`;
- `core.extension` não contém `aculta`;
- config import está clean;
- smoke tests passaram;

o shim pode ser removido em patch posterior.

Não adicionar funcionalidades aqui.
