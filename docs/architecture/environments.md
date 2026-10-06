# Ambientes e deploy

## Desenvolvimento / Homelab

Debian, Apache, PHP-FPM, SQLite, aliases `*.aculta.toca.net.br` e noindex.

O Homelab foi migrado de Nginx para Apache em 2026-10-06. Documentos históricos de fases anteriores podem mencionar Nginx ao descrever o ambiente existente no momento daqueles testes; eles não definem mais a arquitetura atual.

## Produção

Hostinger, Apache, PHP, MariaDB e hosts `*.aculta.org`.

## Regra

Os dois ambientes usam Apache, mas configuração não é cópia cega entre eles.

Preservar o `.htaccess` Drupal e validar por ambiente:

- VirtualHosts e aliases;
- `mod_rewrite`;
- `mod_headers`;
- política de `AllowOverride`;
- PHP-FPM/integração PHP;
- proteção de public/private files;
- redirects e headers;
- certificados e canonical hosts.

O código da aplicação e o tema não devem depender de comportamento exclusivo da configuração local.

Ordem lógica: dependências lockadas, settings/segredos do ambiente, updates necessários, Configuration Sync, provisionamento idempotente aprovado, cache e smoke tests.
