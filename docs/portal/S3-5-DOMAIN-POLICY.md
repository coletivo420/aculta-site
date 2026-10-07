# S3.5 — Domain policy

Data de preparação: 2026-10-06

RUNTIME STATUS: **PASS**

## Objetivo

Separar resolução de ownership de rota/conteúdo da aplicação da política HTTP
que bloqueia host incorreto.

## ContentPurposeResolver

Novo service:

`aculta_portal.content_purpose_resolver`

Responsável por traduzir para purpose:

- Group `lms_course`;
- Wiki node add/edit/revisions/Diff;
- Domain Source de node canonical;
- wiki_category;
- editorial_author/editorial_category.

Não produz Response e não negocia Domain.

## DomainPurposeRequestSubscriber

Continua dono de:

- pre-router match;
- wrong-host 404;
- no-store/noindex da resposta 404;
- exception de password reset;
- redirect pós-logout.

O subscriber passa a delegar ownership ao resolver.

## DomainPurposeManager

- RequestStack deixa de ter fallback global;
- `routeUrl()` e `pathUrl()` clonam a Domain entity antes de adaptar scheme
  em qualquer environment de alias não-default, inclusive `homelab`;
- canonical URL continua restaurando hostname/scheme da config;
- mapa de purposes ativos não ganha FORUM nesta fase.

## FORUM

A arquitetura está pronta para um novo purpose, mas `forum_aculta_org` não é
adicionado aqui.

Ativação pertence ao Portal 0.11 com Domain/config/Runtime.

## Gates Runtime

Validar:

- MAIN/ACCOUNT/SUPPORT/MAGAZINE/WIKI/SHOP/COURSES;
- wrong-host 404;
- node canonical por Domain Source;
- editorial taxonomy;
- Wiki add/edit/revisions/Diff;
- LMS course public/admin;
- password reset;
- logout;
- URL local HTTP;
- canonical production HTTPS;
- nenhuma mutação persistente de Domain.

## Merge policy

PR #23 foi validado no Runtime e mergeado. O blocker independente da Wiki foi
corrigido no PR #60 e add/edit foram repetidos neste runtime antes da conclusão
do PR #30.

## Execução Runtime inicial — 2026-10-07 (bloqueada antes do PR #60)

**Main antes:** `edfc2278221f8f1fe5574630ac743842d301b6a0`

**Head da branch antes da sincronização:** `037aa7ea4d413ea99c371dfa1f0dbf47a2e6bd6e`

**Merge normal com main:** `9afcf7594c3c4ad28afe5a06969cd1d857a97a2b`; sem conflitos.

Esta execução inicial ficou bloqueada pelo erro de Media Library descrito
abaixo. O finding foi resolvido no PR #60; a evidência pós-correção está na
seção R1.2B.

### Gates executados

- PHP lint dos três arquivos S3.5, `git diff --check` e busca de service
  locator: PASS; zero `\Drupal::` nos alvos;
- Composer validate e audit: PASS; validate mantém avisos preexistentes sobre
  constraints exatas de Bootstrap 5 e Pathauto;
- Drush status/bootstrap e `drush cr`: PASS; container construiu
  `DomainPurposeManager`, `ContentPurposeResolver` e
  `DomainPurposeRequestSubscriber`;
- config status limpo; updatedb sem atualizações;
- verificador Homelab: PASS; Apache Syntax OK, SQLite e Drupal bootstrap PASS;
- mapa de Domain ativo contém somente main/account/support/magazine/wiki/shop/
  courses; FORUM não foi adicionado.

### Matriz e ownership

- os sete hosts responderam conforme o baseline: MAIN 200, ACCOUNT anônimo
  403, SUPPORT 200, MAGAZINE 200, WIKI 200, SHOP 404 esperado, COURSES 200;
- node 42 tem Domain Source `wiki420_aculta_org`; node 9 tem
  `coletivo420_aculta_org`; nenhum Domain Source foi alterado;
- resolver classificou node canonical e `wiki_category` como WIKI,
  `editorial_author` como MAGAZINE; `editorial_category` não possui termos e
  ficou `DEFERRED_FIXTURE`;
- verbete WIKI no host correto: 200; nos hosts MAIN/MAGAZINE: 403 sem
  exposição de conteúdo;
- `wiki_category` canonical: 200 no WIKI; `editorial_author` canonical: 200
  no MAGAZINE e 404 no WIKI;
- node add/edit/history/revision/Diff no host errado retornam 404 com
  `Cache-Control: no-store, private` e `X-Robots-Tag: noindex, nofollow`;
- node history, revision view e Diff com fixture existente: 200 no WIKI; as
  rotas retornam 403 para anônimo no host correto;
- course Group canonical: 200 no COURSES e 404 no MAIN; start/answer e canonical
  resolvem para COURSES; Group edit route resolve para MAIN e retorna 404 no
  host COURSES. No MAIN a tentativa autenticada recebeu 403 por acesso de
  Group, portanto a autorização administrativa não foi declarada PASS.

### URLs e integridade de Domain

O teste inicial identificou que o código reconhecia somente environment
`local`, enquanto os aliases reais do Homelab usam `homelab`. A condição foi
corrigida para aliases não-default e retestada:

- route URL ACCOUNT, path URL WIKI/COURSES: seguem o scheme da requisição
  (`http` e `https`) e o hostname Homelab correto;
- canonical MAIN/WIKI/COURSES: `https://aculta.org/`,
  `https://wiki420.aculta.org/verbete/proibicionismo` e
  `https://cursos.aculta.org/group/1`;
- captura de `toArray()` antes/depois das gerações de URL: id, hostname, scheme
  e demais propriedades sem mudança; `config:status` permaneceu limpo.

### Fluxos e finding bloqueante

- password reset `/recuperar-senha`: 200;
- sessão temporária autenticada foi reconhecida em ACCOUNT e COURSES; logout
  pelo link real com token redirecionou para `/entrar` e encerrou ambas as
  sessões;
- não há fixture User A/B; `DEFERRED_FIXTURE`;
- **Finding bloqueante naquela execução:** com a sessão autenticada existente, GET em
  `/node/add/wiki_entry` e `/node/42/edit` no WIKI retorna 500. Watchdog registra
  `InvalidArgumentException` de `MediaLibraryState`: parâmetro `allowed_types`
  ausente; isso ocorre durante a construção do formulário, fora dos arquivos e
  da responsabilidade da S3.5. Não foi alterado Core, contrib nem configuração
  editorial para contornar o erro. As rotas wrong-host continuam retornando o
  404 protegido esperado;
- houve uma ocorrência anterior de TypeError de construtor do subscriber em
  `/group/1`, com container antigo passando EntityTypeManager; após `drush cr`,
  o container construiu os serviços novos e o erro não voltou. Não há entrada
  correspondente recente no journal Apache/PHP-FPM.

Sem alteração intencional de conteúdo, configuração, estrutura ou State SQLite;
os requests de teste geraram apenas os registros operacionais de log/sessão
esperados. Não houve alteração em tema, Composer, `config/sync` ou produção. A
correção de environment não altera o mapa de Domain nem adiciona purpose.

## R1.2B — reteste e conclusão do PR #30 — 2026-10-07

**Main usado:** `d153242dc32f763d66f099d3f8be2d08d4592b9e`

**Head local testado após merge normal:** `ccf996172ffbf352e9fb7528c9b16f618b7e0bdd`

**Merge com main:** `ccf996172ffbf352e9fb7528c9b16f618b7e0bdd`; sem conflitos.

### Gatilhos e gates

- PHP lint dos três alvos S3.5, `git diff --check` e busca de service locator:
  PASS; zero `\Drupal::` nos alvos;
- Composer validate e audit: PASS; permanecem apenas os avisos preexistentes
  sobre constraints exatas Bootstrap 5 e Pathauto;
- `drush cr`: PASS; `config:status` limpo e `updatedb:status` sem atualizações;
- verificador Homelab: PASS com `apache2ctl` executado como root e PHP/Drush
  como `piradopirata`. A execução integral como usuário não privilegiado não
  consegue ler os certificados root-only sob `/etc/ssl/virtualmin` (diretórios
  0700); as permissões TLS não foram ampliadas. `apache2ctl configtest` como
  root retornou `Syntax OK`;
- regressão de sete hosts: MAIN 200, ACCOUNT root 403 e `/entrar` 200, SUPPORT
  200, MAGAZINE 200, WIKI 200, SHOP 404 esperado e COURSES 200.

### Wiki e Media Library após o PR #60

- configuração ativa `full_html.media_embed.allowed_media_types` preserva as
  chaves `image`, `document` e `remote_video`, sem diff em
  `filter.format.full_html.yml` nesta branch;
- autenticado no WIKI: `/node/add/wiki_entry` 200 e `/node/43/edit` 200;
  CKEditor 5 e `drupalMedia` carregaram e a marcação contém os três tipos;
- histórico e revisão Wiki retornaram 200. Diff retornou 200 com layouts
  válidos `unified_fields` e `split_fields`; wrong-host continuou protegido;
- a validação foi HTTP/HTML, sem automação de navegador para clicar e selecionar
  um item no modal da Media Library;
- não surgiram novos erros de `MediaLibraryState`, `allowed_types`, serviços ou
  subscriber nas requisições válidas posteriores. O TypeError de Cursos foi
  registrado antes do `drush cr`, quando o container compilado ainda tinha a
  assinatura anterior; após o rebuild, Cursos retornou 200 sem recorrência.

### Domain policy e URL generation

- node canônico/Wiki term funcionam no WIKI; Wiki add, edição, histórico,
  revisão e Diff no MAIN/MAGAZINE retornam 404 do subscriber com
  `Cache-Control: private, no-store` e `X-Robots-Tag: noindex, nofollow,
  noarchive`;
- canonical node no MAIN retornou 403 por access da entidade Core, sem expor o
  conteúdo; editorial author funciona no MAGAZINE; `editorial_category` segue
  `DEFERRED_FIXTURE` por falta de termos;
- Group `lms_course`: canonical no COURSES 200; resolver classifica rotas
  públicas de curso como `courses` e edição/admin como `main`. A rota de edição
  teve 403 por autorização da fixture, separado do purpose. Start/activity
  também tiveram 403 por autorização/membership, enquanto wrong-host foi 404;
- `routeUrl()` e `pathUrl()` geraram account/wiki/courses nos hosts Homelab
  corretos para contexts HTTP e HTTPS. O teste expôs e corrigiu `pathUrl()`:
  agora monta o URI absoluto pelo Domain clonado, em vez do host ativo;
- `canonicalRouteUrl()` manteve HTTPS nos domínios de produção `*.aculta.org`;
  snapshots de hostname/scheme dos Domains permaneceram iguais antes/depois;
- password reset 200; sessão temporária foi reconhecida em ACCOUNT/COURSES e o
  link real tokenizado de logout encerrou ambas as sessões;
- user A/B continua `DEFERRED_FIXTURE` por não haver fixture adequada.

Sem alterações em tema, Composer, `config/sync`, SQLite/runtime ou produção.
O site de Cursos voltou a responder depois de `drush cr`; não foi necessária
mudança de conteúdo nem de configuração do LMS.

## Próxima fase

S3.6 — Public CSS ownership.
