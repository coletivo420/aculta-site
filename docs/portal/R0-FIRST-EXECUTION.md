# R0 — Primeira execução: blocker Apache TLS

Data: 2026-10-06/07

Status: **FAIL — infraestrutura Apache**

Baseline testado:

`053aae886e2343a85fa4913f4e272d2f8c0b2c32`

## Resultado

- Git/main/worktree: PASS;
- PHP-FPM: PASS;
- SQLite Runtime: PASS;
- Drupal bootstrap: PASS;
- Composer validate: PASS, com avisos preexistentes;
- Composer audit: PASS;
- config status: CLEAN;
- updatedb status: NONE;
- nginx ativo: NO;
- smoke HTTP dos purposes atuais: sem erro 5xx;
- Apache gate: FAIL.

## Finding R0-APACHE-001

### Sintoma 1 — apache2ctl fora do PATH

A sessão usada na execução não possuía `/usr/sbin` no `PATH`.

O script:

`scripts/homelab/verify-aculta-homelab.sh`

dependia exclusivamente de `command -v apache2ctl/apachectl`.

No host, o binário existe em:

`/usr/sbin/apache2ctl`

Portanto o verificador deve aceitar os caminhos administrativos usuais mesmo
quando a sessão não herdar `/usr/sbin`.

### Sintoma 2 — configtest Apache

Após executar com `/usr/sbin` disponível, o gate real falhou em:

`apache2ctl configtest`

porque um VirtualHost referencia:

`/etc/ssl/virtualmin/179115646510665/ssl.combined`

e esse arquivo não existe ou está vazio.

### Classificação

Infraestrutura/configuração Apache do Homelab.

Não é:

- falha de bootstrap Drupal;
- falha SQLite;
- config drift Drupal;
- update pendente;
- regressão nginx;
- justificativa para iniciar R1.

### Estado HTTP observado

- MAIN: 200;
- ACCOUNT: `/entrar` 200;
- ACCOUNT: `/meus-cursos` anônimo 403;
- MAIN/MAGAZINE: `/meus-cursos` 404;
- SUPPORT: 200;
- MAGAZINE: 200;
- WIKI: 200;
- SHOP: 404 esperado no estado atual;
- COURSES: 200.

O Apache atualmente ativo continuar respondendo não invalida o finding:
`configtest` precisa passar antes de considerar o baseline reiniciável e
operacionalmente íntegro.

## Próxima ação

Antes de R1:

1. identificar o VirtualHost que referencia o certificado ausente;
2. determinar se o VHost é ativo, legado ou incorretamente apontado;
3. corrigir a referência usando a configuração/certificado real do Homelab, sem
   inventar arquivo placeholder;
4. executar `apache2ctl configtest`;
5. fazer reload controlado somente após Syntax OK;
6. repetir o verificador;
7. repetir HTTP smoke;
8. confirmar logs;
9. marcar R0 PASS somente depois disso.

Não criar certificado vazio e não desabilitar SSL apenas para satisfazer o
teste.

## Stop condition

Enquanto `apache2ctl configtest` falhar:

`R0 READY FOR R1 = NO`
