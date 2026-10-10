# Estados ACULTA

Um **Estado** é um snapshot SQLite imutável do banco de desenvolvimento. O
primeiro Estado ACULTA é **integral**: preserva todas as tabelas e registros
do banco MariaDB fonte, incluindo sessões, caches, logs, tokens já persistidos,
usuários e dados pessoais. Ele não é um fixture, nem backup de produção.

**Os Estados são backups privados dos servidores e não são versionados.** Os
arquivos `estados/*.sqlite` ficam somente no disco (ignorados pelo `.gitignore`)
e não entram no repositório, nem em release, issue ou artefato de CI. O
repositório público contém apenas este README e `manifesto.yml`, com o
inventário e o SHA-256 de cada Estado. Guarde os arquivos físicos com restrição
de acesso e retenção definida pelo responsável.

O arquivo em `estados/` nunca é aberto pelo Drupal. Para restaurar, carregue-o
no Runtime MariaDB (`aculta_runtime`); o Drupal só usa o Runtime.
Arquivos físicos `public://` e `private://` são complementares e não ficam
dentro do SQLite.

Produção continua MariaDB. Nunca envie `estados/*.sqlite` para produção nem os
use como banco de produção.

Veja `manifesto.yml` para o inventário e SHA-256 de cada Estado.
