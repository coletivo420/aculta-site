# Revisão do primeiro Estado Integral ACULTA

Data local: 2026-10-04. Esta revisão não contém valores pessoais nem credenciais.

## Snapshot e proveniência

- Tipo: Estado Integral (sem sanitização, anonimização ou remoção de registros).
- Fonte: banco MariaDB local de desenvolvimento ACULTA; leitura feita pelo Drupal Database API.
- Conversão: 293 tabelas, 35.255 linhas; contagem e SHA-256 lógico de cada tabela conferidos contra SQLite.
- Arquivo: `estados/2026-10-04_aculta_estado_fase8-integral-v1.sqlite`.
- Tamanho: 35.794.944 bytes (aprox. 34 MiB; abaixo do limite de 50 MB).
- SHA-256: `7a6804e83236019b4f247733b86d360a5584d91c319f7b22e046bce63f14a39f`.
- SQLite `quick_check` e `integrity_check`: `ok`.
- O Estado é somente leitura no ambiente Windows e não foi usado diretamente pelo Drupal.
- `scripts/institution/ESTADO-FASE8-INTEGRAL-TABLES.json` registra todas as tabelas, contagens e hashes lógicos sem conteúdo de linhas.
- `scripts/institution/ESTADO-FASE8-INTEGRAL-ENTITIES.json` registra 19 tipos de entidades com contagens, hashes de conjuntos de IDs/UUIDs e revisões; todos coincidem entre fonte e alvo.

## Contagens da fonte e do alvo

Todas as tabelas listadas no JSON têm `source_rows == target_rows` e hash lógico correspondente.

| Área | Fonte | SQLite | Resultado |
| --- | ---: | ---: | --- |
| Usuários | 2 | 2 | MATCH |
| Nodes | 17 | 17 | MATCH |
| Revisões de Nodes | 48 | 48 | MATCH |
| Custom blocks | 13 | 13 | MATCH |
| Revisões de custom blocks | 27 | 27 | MATCH |
| Taxonomy terms | 22 | 22 | MATCH |
| Arquivos (metadata) | 0 | 0 | MATCH |
| Profiles | 0 | 0 | MATCH |
| Webform submissions | 0 | 0 | MATCH |
| Social Auth entities | 0 | 0 | MATCH |
| Email Confirmer records | 14 | 14 | MATCH |
| Commerce Stores | 1 | 1 | MATCH |
| Commerce Orders / Order Items / Payments | 0 / 0 / 0 | 0 / 0 / 0 | MATCH |
| Path aliases / Redirects / Menu links | 30 / 7 / 17 | 30 / 7 / 17 | MATCH |
| Sessions | 64 | 64 | MATCH |
| Cache rows | 1.664 | 1.664 | MATCH |
| Watchdog / Flood / Queue / Batch | 1.197 / 0 / 378 / 18 | iguais | MATCH |
| Key-value / expiring key-value | 1.440 / 46 | iguais | MATCH |

Store UUID `32f75ad2-5d45-48bb-898e-d657d47d04f8` e bloco institucional UUID
`80f3fc02-39b5-4386-8a32-78301b635007`: MATCH. Nenhum valor de campo foi
impresso nesta revisão.

## Drupal e configuração

- Drupal 11.4.8; PHP local 8.5.10.
- Runtime: `var/database/aculta-runtime.sqlite`.
- Restore do Estado para Runtime: PASS; os SHA-256 do arquivo Estado antes e depois coincidiram.
- Bootstrap completo, `updatedb:status` sem atualizações e `config:status` sem diferenças, antes de `drush cr` no Runtime.
- Commerce SQLite: Store carregado; 2 plugins de gateway descobertos; Orders, Order Items e Payments consultados sem erro.
- Domain: 7 Domains e 7 aliases carregados. Os 7 aliases locais `.test:8080` estão presentes; hosts Homelab `.aculta.toca.net.br` não estavam na fonte examinada.
- `config/sync` não foi exportado nem sobrescrito durante a migração. A configuração ativa preservada e o sync reportaram sem diferenças.
- Arquivos físicos `public://` e `private://` não fazem parte do banco nem deste Estado; somente metadata File seria copiada.
- Dados pessoais existentes foram preservados por instrução explícita. Credenciais potencialmente persistidas no banco: UNKNOWN; nenhum valor foi exibido ou copiado de arquivos externos.

## Gates ainda pendentes

- Visibilidade do repositório verificada pela API autenticada: **PUBLIC**. Por regra do adendo definitivo, nenhum commit/push contendo o Estado foi feito; não alterei a visibilidade.
- Sessão autenticada cross-host e logout global: pendentes.
- HTTP no servidor local: MAIN 200, SUPPORT 200, MAGAZINE 200; ACCOUNT `/` retornou 403 anônimo; WIKI/SHOP/COURSES retornaram 404 reservados. Testes de login/cookie compartilhado permanecem pendentes.
- O ambiente disponível foi Windows, não o Homelab Debian informado; portanto, scripts de bootstrap/restore em Debian ainda precisam de execução nesse ambiente.
- Homelab `.toca.net.br`, Security Review, sitemap multidomínio e teste de concorrência SQLite não foram aprovados nesta sessão.
- O Security Review foi executado no Runtime: 12 checks SUCCESS, 5 FAILED e 2 INFO (`admin_user`, `executable_php`, `file_permissions`, `input_formats`, `views_access`). Não foram aplicados autofixes.
- Cookie options configuradas: Domain `.aculta.test`, SameSite `Lax`; nenhuma resposta anônima de login emitiu `Set-Cookie`, então o nome/atributos do cookie em sessão autenticada não foram observados.
- HTTP nos sete hosts `.test:8080`: MAIN 200; ACCOUNT `/` 403 anônimo e `/entrar`/`/recuperar-senha` 200; SUPPORT 200; MAGAZINE 200; WIKI/SHOP/COURSES 404 reservados. MAIN `/entrar`, `/seguranca`, `/apoie` e `/noticias` retornaram 404; MAGAZINE `/painel-administrativo` 404; host arbitrário retornou 400.
- MAGAZINE `/noticias` retorna 301 para a raiz editorial no mesmo host.
- Login autenticado cross-host, UID nos sete hosts e logout global continuam pendentes.
- Busca estática no tema/módulos custom não encontrou APIs SQL específicas de MariaDB/MySQL.

## Decisão

O arquivo local alcançou fidelidade integral tabular e de entidades na conversão
MariaDB → SQLite. A Fase 8.5 permanece PARCIAL: o repositório público bloqueia
a publicação dos dados, e testes específicos do Homelab e da sessão ainda
faltam. Produção permanece MariaDB e não foi acessada ou alterada.
