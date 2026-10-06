# Ambientes e deploy

## Homelab

Debian, Apache, PHP-FPM, SQLite, aliases `*.aculta.toca.net.br` e noindex.

Apache é o baseline definitivo do servidor web de desenvolvimento. O Homelab
deve exercitar o mesmo modelo de `.htaccess`, rewrite, headers e PHP-FPM que a
aplicação espera em produção, sem manter uma camada paralela de compatibilidade
com Nginx.

O VirtualHost do Homelab deve apontar o DocumentRoot para `web/`, preservar o
`.htaccess` fornecido pelo Drupal e permitir as diretivas necessárias ao
Drupal. Os módulos Apache exigidos pelo projeto devem ser verificados no
ambiente, em especial `mod_rewrite`, `mod_headers` e a integração PHP-FPM
via `proxy_fcgi`.

## Produção

Hostinger, Apache, PHP, MariaDB e hosts `*.aculta.org`.

Produção compartilha a família de servidor web com o Homelab, mas não se deve
copiar VirtualHosts, caminhos, certificados, permissões, usuários de processo
ou configuração do Virtualmin/Hostinger literalmente entre os ambientes.

## Regra

Apache é o único baseline de servidor web mantido pelo projeto. Configuração
Nginx legada não deve orientar novos recursos, testes ou documentação
normativa. Referências a Nginx em relatórios antigos são evidência histórica da
migração e não constituem suporte atual.

Deploy não é cópia cega do Homelab. Preservar o `.htaccess` Drupal e revisar
as diferenças específicas da Hostinger para redirects, headers, TLS,
AllowOverride e PHP-FPM. Regras de noindex pertencem somente ao Homelab.

Ordem lógica: dependências lockadas, settings/segredos do ambiente, updates
necessários, Configuration Sync, provisionamento idempotente aprovado, cache e
smoke tests.
