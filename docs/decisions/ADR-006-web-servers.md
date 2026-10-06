# ADR-006: Apache como baseline de servidor web

Status: Accepted

Data da decisão atual: 2026-10-06

## Contexto

O Homelab originalmente usava Nginx enquanto a produção Hostinger usava
Apache. Essa diferença exigia traduzir regras de rewrite, headers e proteção de
arquivos entre duas famílias de servidor web e aumentava o risco de divergência
entre desenvolvimento e produção.

O servidor de desenvolvimento foi migrado para Apache com PHP-FPM.

## Decisão

Apache é o baseline definitivo de servidor web do ACULTA no Homelab e em
produção.

O Homelab usa Debian + Apache + PHP-FPM + SQLite. Produção usa Hostinger +
Apache + PHP + MariaDB.

O projeto não mantém compatibilidade operacional com Nginx. Novos recursos,
testes, exemplos e documentação normativa devem partir de Apache. Configuração
Nginx remanescente pode ser preservada apenas como evidência histórica de
migração até sua limpeza segura.

O `.htaccess` do Drupal faz parte do caminho operacional nos ambientes Apache
e deve permanecer preservado. Rewrites, headers, proteção de arquivos privados,
AllowOverride e integração PHP-FPM devem ser validados em Apache.

## Consequências

- o Homelab pode validar comportamento Apache antes do deploy;
- não é mais necessário traduzir `location`, `try_files` ou `add_header`
  de Nginx para Apache;
- VirtualHosts, caminhos, usuários, certificados e módulos continuam
  específicos de cada ambiente;
- configurações do Virtualmin e da Hostinger não devem ser copiadas
  literalmente entre si;
- relatórios históricos que mencionam Nginx continuam válidos como registro da
  época, mas não definem a arquitetura atual;
- testes de segurança que antes aceitavam uma exceção porque Nginx ignorava
  `.htaccess` precisam ser reavaliados sob Apache.

## Não objetivos

Esta decisão não altera o banco de desenvolvimento, o Sistema de Estados
SQLite, a arquitetura Domain, a produção MariaDB ou o roadmap futuro de
migração do Runtime do Homelab para MariaDB.
