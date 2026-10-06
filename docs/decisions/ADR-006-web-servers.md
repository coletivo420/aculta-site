# ADR-006: Apache nos ambientes web

Status: Accepted

Data da revisão: 2026-10-06

## Contexto

A decisão original registrava Nginx no Homelab e Apache na produção Hostinger. O servidor de desenvolvimento foi posteriormente migrado para Apache para reduzir diferenças operacionais relevantes entre desenvolvimento e produção.

## Decisão

Desenvolvimento/Homelab usa Apache com PHP-FPM.

Produção Hostinger usa Apache com sua configuração própria de hospedagem.

Usar o mesmo servidor web não torna as configurações intercambiáveis. VirtualHosts, aliases, módulos, permissões, integração PHP, certificados, redirects, headers e proteção de arquivos continuam específicos de cada ambiente.

O Drupal deve manter seu `.htaccess` e o deploy deve validar pelo menos `mod_rewrite`, `mod_headers`, `AllowOverride` e proteção de arquivos no ambiente alvo.

O tema `aculta` e os módulos custom não devem depender de comportamento exclusivo de uma configuração Apache local.

## Consequência histórica

Registros de testes executados antes da migração podem mencionar Nginx corretamente como estado daquele momento. Esta ADR substitui a decisão arquitetural anterior para o estado atual; não é necessário reescrever evidência histórica apenas para trocar o nome do servidor.
