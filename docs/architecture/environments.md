# Ambientes e deploy

## Desenvolvimento / Homelab

Debian, Apache, PHP-FPM, SQLite, aliases `*.aculta.toca.net.br` e noindex.

O Homelab foi migrado de Nginx para Apache em 2026-10-06. Documentos
históricos de fases anteriores podem mencionar Nginx ao descrever o ambiente
existente no momento daqueles testes; eles não definem mais a arquitetura
atual.

Apache é o baseline definitivo do servidor web de desenvolvimento. O Homelab
deve exercitar o mesmo modelo de `.htaccess`, rewrite, headers e PHP-FPM que a
aplicação espera em produção, sem manter uma camada paralela de compatibilidade
com Nginx.

O VirtualHost do Homelab deve apontar o DocumentRoot para `web/`, preservar o
`.htaccess` fornecido pelo Drupal e permitir as diretivas necessárias ao
Drupal.

## Produção

Hostinger, Apache, PHP, MariaDB e hosts `*.aculta.org`.

Produção compartilha a família de servidor web com o Homelab, mas configuração
não é cópia cega entre os ambientes.

## Regra

Apache é o único baseline de servidor web mantido pelo projeto. Configuração
Nginx legada não deve orientar novos recursos, testes ou documentação
normativa. Referências a Nginx em relatórios antigos são evidência histórica da
migração e não constituem suporte atual.

Preservar o `.htaccess` Drupal e validar por ambiente:

- VirtualHosts e aliases;
- `mod_rewrite`;
- `mod_headers`;
- política de `AllowOverride`;
- PHP-FPM/integração PHP, incluindo `proxy_fcgi` no Homelab;
- proteção de public/private files;
- redirects e headers;
- certificados e canonical hosts.

O código da aplicação e o tema não devem depender de comportamento exclusivo
da configuração local. VirtualHosts, caminhos, usuários, certificados e
configuração Virtualmin/Hostinger não devem ser copiados literalmente entre os
ambientes. Regras de noindex pertencem somente ao Homelab.

Ordem lógica: dependências lockadas, settings/segredos do ambiente, updates
necessários, Configuration Sync, provisionamento idempotente aprovado, cache e
smoke tests.
