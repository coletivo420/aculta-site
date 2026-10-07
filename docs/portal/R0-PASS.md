# R0 — Clean Baseline: PASS

Data: 2026-10-06/07

Status: **PASS — READY FOR R1**

Baseline final validado:

`75436be8752b63e8b668a26e9b4bc7d26ea7f75c`

## Resultado final

- Git/main/worktree: PASS;
- Apache: PASS;
- Apache configtest: `Syntax OK`;
- PHP-FPM: PASS (`php8.4-fpm`);
- SQLite Runtime: PASS;
- Drupal bootstrap: PASS;
- Composer validate: PASS, com avisos preexistentes;
- Composer audit: PASS;
- config status: CLEAN;
- updatedb status: NONE;
- nginx ativo: NO;
- HTTP smoke: PASS nos contexts implementados;
- SHOP 404: esperado no estado atual.

## TLS/VirtualHost

VirtualHost validado:

`/etc/apache2/sites-enabled/aculta.toca.net.br.conf`

O certificado Let’s Encrypt referenciado em:

`/etc/ssl/virtualmin/179115646510665/ssl.combined`

estava presente e válido na reexecução.

SANs cobrem os hosts ACULTA testados e a chave correspondente foi confirmada.

A falha observada na execução anterior não se reproduziu.

Nenhuma alteração Apache foi necessária.

## Verificador Homelab

O PR #56 foi validado e integrado.

O script agora encontra `apache2ctl/apachectl` em `/usr/sbin` quando a sessão
administrativa não possui esse diretório no `PATH`.

O teste com PATH reduzido passou como root administrativo.

As permissões restritas dos arquivos TLS foram preservadas.

## HTTP smoke

Resultado observado:

- MAIN: 200;
- ACCOUNT: 403 anônimo no root; `/entrar` 200; `/meus-cursos` anônimo 403;
- SUPPORT: 200;
- MAGAZINE: 200;
- WIKI: 200;
- SHOP: 404 esperado;
- COURSES: 200.

HTTPS redirecionou uma vez preservando o hostname e os testes TLS públicos
passaram sem bypass de verificação.

## Logs

Sem novos erros Apache relacionados à execução.

Probes externos e caminhos inválidos observados nos logs não foram classificados
como regressão do projeto.

## Gate

`R0 READY FOR R1: YES`

Próxima unidade operacional:

**PR #23 — S3.1 Hooks + Dependency Injection**

Como o PR #23 está muito atrás do `main` e atualmente não é mergeable, sua
primeira tarefa no R1 é sincronizar a branch com o `main` atual preservando:

- refactor DI da branch;
- documentação nova do main;
- alterações do tema/main;
- correção Homelab do PR #56.

Depois executar os gates Runtime específicos da S3.1 antes de qualquer merge.
