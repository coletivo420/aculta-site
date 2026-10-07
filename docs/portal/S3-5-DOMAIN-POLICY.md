# S3.5 — Domain policy

Data de preparação: 2026-10-06

RUNTIME STATUS: **DEFERRED**

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

PR #23 já foi validado no Runtime e mergeado. Manter o PR #30 em draft até seus próprios gates passarem; nenhum merge é autorizado enquanto o erro de Wiki add/edit estiver aberto.

## Execução Runtime — 2026-10-07

**Main antes:** `edfc2278221f8f1fe5574630ac743842d301b6a0`

**Head da branch antes da sincronização:** `037aa7ea4d413ea99c371dfa1f0dbf47a2e6bd6e`

**Merge normal com main:** `9afcf7594c3c4ad28afe5a06969cd1d857a97a2b`; sem conflitos.

**Runtime status permanece DEFERRED.** Os gates estáticos e de baseline
passaram, mas a execução não está pronta para merge devido ao erro HTTP em Wiki
add/edit descrito abaixo.

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
- **BLOCKING:** com a sessão autenticada existente, GET em
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
correção de environment não altera o mapa de Domain nem adiciona purpose. O PR
deve continuar draft até o finding de Media Library ser resolvido em escopo
próprio e os testes de Wiki add/edit serem repetidos.

## Próxima fase

S3.6 — Public CSS ownership.
