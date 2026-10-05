# ADR-006: Nginx no Homelab e Apache em produção

Status: Accepted

Homelab usa Nginx; Hostinger produção usa Apache. Configuração é específica por ambiente. Regras Nginx não são copiadas literalmente e o deploy deve validar `mod_rewrite`, `mod_headers` e o `.htaccess` Drupal.
