# Estados ACULTA

Um **Estado** é um snapshot SQLite imutável do banco de desenvolvimento. O
primeiro Estado ACULTA é **integral**: preserva todas as tabelas e registros
do banco MariaDB fonte, incluindo sessões, caches, logs, tokens já persistidos,
usuários e dados pessoais. Ele não é um fixture, nem backup de produção.

O repositório que contém um Estado integral precisa permanecer **privado**.
Não publique esse arquivo em repositório público, release, issue ou artefato de
CI. O gate de privacidade precisa ser confirmado antes de qualquer commit que
inclua o banco.

O arquivo em `estados/` nunca é aberto pelo Drupal. Restaure-o para a cópia
mutável `var/database/aculta-runtime.sqlite`; o Drupal só usa o Runtime.
Arquivos físicos `public://` e `private://` são complementares e não ficam
dentro do SQLite.

Produção continua MariaDB. Nunca envie `estados/*.sqlite` para produção nem os
use como banco de produção.

Veja `manifesto.yml` para o inventário e SHA-256 do primeiro Estado.
