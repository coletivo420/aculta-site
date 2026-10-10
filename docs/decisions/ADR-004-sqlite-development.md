# ADR-004: Banco de desenvolvimento (Runtime) em MariaDB

Status: Accepted (revisada em 2026-10-10; substitui a decisão original de SQLite)

## Contexto

A decisão original usava SQLite no Homelab para simplificar o desenvolvimento.
Produção usa MariaDB, e o Runtime do Homelab precisava refletir o mesmo motor
para reduzir divergências entre ambientes.

## Decisão

- O Runtime do Homelab usa **MariaDB** (banco `aculta_runtime`, usuário próprio
  com privilégio apenas nesse banco). Credenciais ficam em `secrets/`, fora do Git.
- SQLite não é mais o Runtime. O arquivo `var/database/aculta-runtime.sqlite`
  é apenas cópia de segurança da migração e pode ser descartado após a
  verificação do responsável.
- Produção continua em MariaDB, com credenciais próprias fora do repositório.
- Código customizado continua usando as APIs de Entity/Database do Drupal;
  nenhum código deve depender do motor específico.
- Estados (`estados/`) são backups privados dos servidores, não são versionados
  e não substituem backup de produção.

## Consequências

- Portabilidade e procedimentos de migração entre motores pertencem ao projeto
  **DBTNG-2**, não ao `aculta_portal`.
- A carga do SQLite para MariaDB exigiu correção de `AUTO_INCREMENT` com `uid 0`
  (anônimo): o carregamento usa `NO_AUTO_VALUE_ON_ZERO`.
