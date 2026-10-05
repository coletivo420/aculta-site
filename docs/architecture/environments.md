# Ambientes e deploy

## Homelab

Debian, Nginx, PHP-FPM, SQLite, aliases `*.aculta.toca.net.br` e noindex.

## Produção

Hostinger, Apache, PHP, MariaDB e hosts `*.aculta.org`.

## Regra

Deploy não é cópia cega do Homelab. Preservar o `.htaccess` Drupal e revisar equivalentes Apache para redirects, headers e regras atualmente implementadas em Nginx. `location`, `try_files` e `add_header` não devem ser copiados literalmente.

Ordem lógica: dependências lockadas, settings/segredos do ambiente, updates necessários, Configuration Sync, provisionamento idempotente aprovado, cache e smoke tests.
