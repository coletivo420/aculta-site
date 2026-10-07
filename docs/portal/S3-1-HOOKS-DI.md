# S3.1 — Hooks + Dependency Injection

Data de preparação: 2026-10-06

RUNTIME STATUS: **PASS**

## Objetivo

Reduzir o uso de service locator (`\Drupal::...`) na camada de hooks e
políticas de domínio sem alterar comportamento funcional.

Drupal 11.1 introduziu hooks orientados a objeto com classes `#[Hook]`
registradas como serviços autowired. A S3.1 usa essa capacidade já disponível no
baseline Drupal 11.4 do projeto.

## Escopo preparado

### PortalHooks

Antes:

- criava `AccountShellBuilder` manualmente;
- resolvia current user/route/path/request por globals;
- buscava DomainPurposeManager pelo container;
- buscava EntityTypeManager, BlockManager, ConfigFactory, ModuleHandler,
  Translation e ModuleExtensionList via `\Drupal::...`.

Depois:

- todas essas dependências entram por construtor;
- serviços ambíguos usam `#[Autowire(service: ...)]`;
- `AccountShellBuilder` é serviço explícito;
- nenhum `\Drupal::...` permanece em `PortalHooks`.

A assinatura e a lógica dos hooks foram preservadas.

### AccountShellBuilder

Passa a ser serviço:

`aculta_portal.account_shell_builder`

Dependências:

- current route match;
- menu link manager;
- string translation.

O builder continua responsável pela composição do shell existente. A conversão
para SDC pertence à S3.2, não a esta fase.

### AcultaBreadcrumbBuilder

Foram injetados:

- `TitleResolverInterface`;
- `PathMatcherInterface`.

Saem os dois acessos globais anteriores.

A política de breadcrumb não foi alterada.

### DomainPurposeRequestSubscriber

Foram injetados:

- `RouteProviderInterface`;
- `EntityTypeManagerInterface`.

Saem os acessos globais a:

- route provider;
- entity type manager;
- current user estático.

A política fail-closed de 404 por host incorreto foi preservada.

## Fora do escopo

Não foi alterado nesta fase:

- `aculta_portal.module`;
- os 18 hooks/funções procedurais;
- `PortalController`;
- `WikiController`;
- `PortalRequirementsController`;
- CSS;
- templates;
- SDC;
- AJAX;
- Composer/config sync;
- Domain records;
- CEP;
- Mercado Pago;
- Twig storage.

A migração dos hooks procedurais continua prevista em S3.7.

## Revisão estática realizada

A preparação confirmou:

- zero chamadas `\Drupal::...` em `PortalHooks`;
- zero chamadas `\Drupal::...` em `AcultaBreadcrumbBuilder`;
- zero chamadas `\Drupal::...` em `DomainPurposeRequestSubscriber`;
- service definitions atualizadas para os novos construtores;
- nenhum arquivo do tema alterado;
- nenhuma dependência Composer adicionada.

## Validação Runtime — 2026-10-07

**Main usado:** `e7ecc05e9e9ff401eaacb728f3c122d987d503b8`

**Head da S3.1 antes da sincronização:** `9b264bf1e2aa3414b5eb9a38614e2a0b86b6a355`

**Merge de sincronização:** `253aef581de2f73fdd47c4fc2116d7f777327072`

Gates estáticos e de container:

- `php -l` nos quatro arquivos PHP da S3.1: PASS;
- `bash -n scripts/homelab/verify-aculta-homelab.sh`: PASS;
- `git diff --check`: PASS;
- zero `\Drupal::` nos três arquivos-alvo: PASS;
- Composer validate e audit: PASS; validate mantém avisos preexistentes sobre
  constraints exatas de Bootstrap 5 e Pathauto;
- Drush status/bootstrap: PASS em Drupal 11.4.8, PHP 8.4.26 e SQLite;
- container Drupal construiu `PortalHooks`, `AccountShellBuilder`,
  `AcultaBreadcrumbBuilder` e `DomainPurposeRequestSubscriber`: PASS;
- `drush cr`: PASS; config status limpo; updatedb sem atualizações;
- `scripts/homelab/verify-aculta-homelab.sh`: PASS no host real; Apache Syntax
  OK, SQLite Runtime válido, Drupal bootstrap PASS, config limpa e updatedb
  sem atualizações.

Smoke HTTP sem erro 5xx inesperado:

- MAIN, SUPPORT, MAGAZINE, WIKI e COURSES: 200;
- ACCOUNT `/entrar` e `/recuperar-senha`: 200; páginas autenticadas do shell,
  incluindo `/meus-cursos`, `/dados`, `/seguranca`, `/conexoes` e `/apoio`:
  200;
- SHOP: 404 esperado;
- Wiki home e `/verbete/proibicionismo`: 200;
- wrong-host em `/wiki`, `/cursos` e `/meus-cursos`: 404;
- a rota de verbete no MAIN/MAGAZINE respondeu 403 sem expor conteúdo, em
  conformidade com o comportamento fail-closed;
- sessão Drupal autenticada foi reconhecida em ACCOUNT e COURSES; o link de
  logout emitido pelo Portal continha token, e o logout encerrou a sessão nos
  dois hosts;
- password reset: 200;
- breadcrumbs MAIN e MAGAZINE mantiveram labels e links para a raiz do próprio
  Domain.

Limitações de cobertura registradas, sem classificar como PASS:

- login com senha e Turnstile não foi repetido com interação de navegador; a
  página/widget carregou sem erro, e a sessão autenticada e logout foram
  exercitados separadamente via sessão Drupal temporária;
- fixture persistente User A/User B não existia; isolamento por usuário ficou
  `DEFERRED_FIXTURE`;
- a página inicial de SUPPORT suprime breadcrumb por ser front page; a página
  ACCOUNT testada também não renderizou breadcrumb. Não houve alteração de
  política ou tema;
- autenticação Google real não foi tentada; a página Conexões autenticada
  carregou sem erro;
- uma submissão automatizada do aceite de termos durante a preparação do
  usuário temporário retornou 500; não houve alteração de termos. A sessão de
  teste foi preparada pela API de Agreement do Drupal e removida ao final.

Não houve alteração de Composer dependencies, `config/sync`, tema, produção ou
conteúdo funcional. O diff S3.1 em `web/themes/custom/aculta/` é vazio.

## Próximo item da fila

PR #30 — S3.5 Domain policy, após a integração da S3.1 no `main`.
