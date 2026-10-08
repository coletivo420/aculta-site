# Scripts do Sistema de Estados

O primeiro Estado é **integral**. Os scripts não anonimizarão nem removerão
registros. Não inclua o arquivo em commit enquanto o repositório não estiver
confirmadamente privado.

Em Debian com PHP `pdo_sqlite`:

```sh
./scripts/estados/listar-estados.sh
./scripts/estados/validar-estado.sh estados/2026-10-04_aculta_estado_fase8-integral-v1.sqlite
./scripts/estados/restaurar-estado.sh estados/2026-10-04_aculta_estado_fase8-integral-v1.sqlite
```

O restore preserva o Runtime anterior com sufixo `.pre-restore-*`, valida a
cópia e não roda `updatedb`, `config:import` ou `cr` automaticamente. Primeiro
registre `drush status`, `drush config:status` e `drush updatedb:status`; só
depois faça alterações operacionais no Runtime. O arquivo original em
`estados/` permanece imutável.

`criar-estado.sh <marco> <versão>` faz checkpoint/VACUUM no Runtime, copia todos
os dados sem sanitização e deixa o manifesto para atualização e validação
explícitas. Não use em um Runtime que não represente o snapshot desejado.

`importar-mariadb-sqlite.php` é o conversor one-shot usado para o primeiro
Estado. Ele lê a conexão Drupal MariaDB ativa, copia cada tabela e calcula
hashes lógicos por tabela; não altera a fonte. Campos DECIMAL são guardados como
BLOB SQLite para preservar sua representação decimal exata e evitar perda em
FLOAT/IEEE-754.
