# Changelog — ACULTA Portal

## 2026-10-08 — P5.4-D: PortalRequirementsController DI

- `\Drupal::service('theme_handler')` ×2 e `\Drupal::root()` → `ThemeHandlerInterface` e parâmetro `app.root` injetados por `#[Autowire]`; helpers lazy `currentUser()`/`moduleHandler()`/`config()` → serviços explícitos; `strict_types=1`; `Composer\InstalledVersions` importado.
- Homelab (admin uid 1): `/painel-administrativo/configuracoes/aculta/portal` e `/requisitos` com região `<main>` idêntica antes/depois (22 linhas de tabela, 16 OK).
- Gate: teto de locator e dívida de `strict_types` zerados para o controller; invariantes de injeção.

## 2026-10-09 — P10-R B.1: enumeração de contas bloqueadas no login

- Achado: o login do Core responde "O nome de usuário … não foi ativado ou está bloqueado." antes da verificação de senha, para qualquer conta existente bloqueada ou não ativada. Contas inexistentes e senhas erradas recebem a mensagem genérica. O módulo `username_enumeration_prevention` cobre apenas o formulário de recuperação de senha. Com o cadastro aberto e a confirmação por e-mail obrigatória, cada cadastro não confirmado virava um endereço revelado.
- Correção: a etapa `validateAuthentication` do formulário de login é substituída por `aculta_portal.form_callbacks:validateLoginAuthentication`. Contas bloqueadas pulam essa etapa; `validateFinal` produz a mesma mensagem genérica das contas inexistentes e registra a tentativa no controle de flood. As demais contas seguem a lógica do Core.
- Verificação (executada): cadeia de validação real do formulário, em transação revertida, com três estados: inexistente, bloqueada e ativa com senha errada. Os três retornam "Nome de usuário ou senha incorretos. Esqueceu sua senha?". Testes unitários (`LoginBlockedAccountTest`) e mutação confirmam que a regra é protegida.
- Não verificado por HTTP: o CAPTCHA Turnstile bloqueia envio por script, como esperado.

## 2026-10-09 — Resolução final dos bloqueios da PR #90

- **Webform via SMTP:** `system.mail` `webform: SMTPMailSystem` e `smtp.settings` `smtp_allowhtml: true`. Verificado sem envio (formatação em transação revertida): o transporte SMTP não aplica o modelo HTML do webform, então as notificações de contato chegam como fragmento HTML sem o template `webform_email_html`. Registrado como diferença conhecida. Nenhuma outra mensagem do Portal usa HTML.
- **Vocabulário `tags` removido** (decisão do responsável), no Runtime e em `config/sync`: `field.field.node.article.field_tags`, `field.storage.node.field_tags` e `taxonomy.vocabulary.tags`. Nenhum termo e nenhum nó usavam o campo. A exclusão do campo fez o Drupal adicionar o widget `moderation_state` ao formulário do artigo; esse efeito colateral foi revertido no Runtime e no sync, e o formulário ficou idêntico ao anterior, menos `field_tags`. Backup antes da exclusão: `~/aculta-runtime-backup-before-tags-removal.sqlite`.
- **`validate-admin-cleanup`:** passa. Linha de base regenerada em 2026-10-09 (`tmp/`, ignorada pelo git) e verificações de rota por nome estável (os caminhos administrativos estão localizados em `/painel-administrativo/...`).
- **Validadores de e-mail:** `smtp.settings` e `system.mail` saem da comparação com o Runtime (ambiente sem credenciais) e são afirmados pelos valores versionados.
- **`validate-final-contact`:** PENDENTE (mensagem `PENDING:`, saída 2; o Drush reporta 1 para qualquer saída diferente de zero).
- Ajustes de `validate-portal-commerce-security` e `validate-final-drupal` para o novo conjunto de chaves.

## 2026-10-09 — Resolução dos bloqueios da PR #90 (validadores)

- Chaves de e-mail (`smtp.settings`, `system.mail`) são específicas do ambiente: saem da comparação com o Runtime do Homelab. Em vez disso, os validadores afirmam os valores versionados (`smtp_on: true`, `SMTPMailSystem`). Aplicado em `validate-final-drupal` e `validate-portal-commerce-security`.
- `validate-final-contact`: marcado como **PENDENTE** (mensagem `PENDING:` e saída 2). Motivo: Turnstile bloqueia envio por script; a exceção de teste não foi autorizada. Observação: o Drush reporta qualquer saída diferente de zero como 1; a distinção se faz pela mensagem.
- `validate-admin-cleanup`: a linha de base `tmp/admin-structure-audit.json` foi gerada hoje (2026-10-09) a partir do estado atual; não é a linha de base da limpeza original. Ao rodar, a verificação encontra `taxonomy.vocabulary.tags`, removido pela limpeza (registrado no estado) e presente em `config/sync` desde `d08e8dd`. Pendente de decisão: remover o vocabulário e `node.article.field_tags` (sem conteúdo marcado) ou aceitar a restauração e atualizar o registro.

## 2026-10-09 — SMTP habilitado na configuração versionada

- `smtp.settings` `smtp_on: true` e `system.mail` `interface.default: SMTPMailSystem` em `config/sync`. Host e porta (SMTP2GO) já estavam versionados; usuário e senha permanecem vazios no arquivo e são fornecidos pelo ambiente via Drupal Key (`SMTP2GO_USERNAME`, `SMTP2GO_PASSWORD`). Nenhum segredo entra no repositório.
- Remetente: `system.site` `mail` já configurado.
- Não alterado no Runtime do Homelab (sem credenciais SMTP; habilitar lá faria cada envio falhar). Por isso `validate-final-drupal` e `validate-portal-commerce-security` acusam drift em `smtp.settings` e `system.mail` até a importação em produção.
- Pendente: o formulário de contato (webform) continua com `interface.webform: webform_php_mail`; as notificações de webform não passam pelo SMTP. Decidir se devem passar.
- Verificação em produção ainda obrigatória: variáveis de ambiente presentes e um envio de teste real para endereço controlado pelo responsável.

## 2026-10-08 — Cadastro público e cookie de sessão compartilhado

- Cadastro aberto: `user.settings` `register: visitors` (Runtime e `config/sync`). Sem aprovação administrativa (`register_no_approval_required: true`); confirmação de e-mail obrigatória (`verify_mail: true`); contas OAuth dispensam confirmação. Verificado no Homelab: `/criar-conta` no host da Conta exibe o formulário do Core com Turnstile; no host principal responde 404.
- **Entrega de e-mail não verificada.** No Homelab, `smtp.settings:smtp_on` está `false` e o transporte é `php_mail`: os e-mails de confirmação não são entregues localmente. Antes de abrir o cadastro em produção, confirmar SMTP ativo e entrega real.
- Cookie de sessão compartilhado: Homelab usa `services.homelab.yml` (`cookie_domain: '.aculta.toca.net.br'`, `cookie_samesite: Lax`), ativo no container. Produção: novo `services.hostinger.yml.example` com `.aculta.org` e `settings.hostinger.php.example` carrega esse arquivo; o arquivo real do servidor não é versionado.
- `validate-cross-domain-request-policy` deve ser executado com o host do ambiente (`--uri=https://aculta.toca.net.br` no Homelab); sem isso, a CLI usa `localhost` e a validação falha por design. Passa com 17 checks.
- `validate-portal-commerce-security`: a regra antiga "cadastro fechado até comprovar entrega de e-mail" foi substituída pela regra vigente: cadastro público exige `verify_mail` e aprovação desativada.
- Pendentes: `validate-final-contact` (CAPTCHA) e `validate-admin-cleanup` (linha de base ausente).

## 2026-10-08 — Cadastro sem aprovação e destino pós-login

- `user.settings` `notify.register_no_approval_required: true` (decisão do responsável): o cadastro não exige aprovação administrativa; a confirmação é feita por e-mail (Email Confirmer), e contas OAuth dispensam confirmação. A rota de cadastro continua `register: admin_only` até decisão separada sobre abertura pública.
- Destino pós-login: os links de "Entrar" já levavam o destino (`PortalHooks::preprocessLinks`), mas o bloco de menu da Conta (`system_menu_block:account`) era armazenado em cache sem `url.path` e `url.query_args`, e exibia o destino da primeira página que o renderizou. Novo `BlockBuildHooks` adiciona esses contextos ao bloco. Verificado intercalado: 9/9 links com o destino da própria página.
- Login social (`/oauth/google`) preserva o destino. Sem destino, o fallback `post_login` continua `/user` (Conta).
- Validadores: `validate-portal-commerce-security` removia a referência a `user_registrationpassword`, módulo removido em `b4b4389`; manifesto corrigido (56 objetos). `validate-final-drupal` passa com a configuração sincronizada.
- Pendentes: `validate-final-contact` (CAPTCHA), `validate-admin-cleanup` (linha de base ausente), `validate-cross-domain-request-policy` (cookie_domain do Homelab).

## 2026-10-08 — Configuração: exportação do Runtime para config/sync (opção A)

- `drush cex` exportou o Runtime: 40 objetos novos (Commerce, Views e campos de doação, que existiam só no banco) e 12 alterados. Nenhum objeto foi removido do sync.
- Alterações relevantes: `user.role.authenticated` deixa de conceder `skip CAPTCHA` a usuários autenticados; `metatag.metatag_defaults.front` passa de `https://aculta.org/` fixo para `[site:url]`; traduções pt-BR de idioma, mensagens de conta e Views restauradas; `core.extension` inclui `config_translation`.
- **Mantido sem exportar:** `user.settings` `register_no_approval_required` (Runtime `true`, sync `false`). Abrir cadastro sem aprovação é decisão de política; o sync permanece `false` até o responsável decidir. Por isso `validate-final-drupal` e `validate-portal-commerce-security` continuam falhando, apenas nesta chave.
- `social_auth.settings` `post_login` do Runtime (`/user`, redirecionado para a Conta) não foi alterado: o sync continha `/`. Confirmar qual destino é desejado.

## 2026-10-08 — Validadores: correções de expectativas obsoletas

- `validate-home-carousel.php`: a view `home_editorial_highlights` pagina em 3 itens (`items_per_page: 3` no Runtime e em `config/sync`). A expectativa de 4 itens era obsoleta; a checagem de ordenação por peso permanece.
- `validate-admin-cleanup.php`: o inventário de Views era fechado em 12 itens e não incluía as Views de Commerce, LMS, Wiki, Cursos e Social Auth, exigidas pelo Portal. O inventário passa a ser o revisado (40); qualquer View fora dele continua falhando. A exigência de descrição passa a valer apenas para as 14 Views curadas.
- Pendentes, não alterados: `validate-admin-cleanup` depende de `tmp/admin-structure-audit.json` (linha de base da limpeza, não versionada e ausente); `validate-final-contact` falha porque o CAPTCHA Turnstile do formulário rejeita envio por script (correto); `validate-cross-domain-request-policy` exige `session.storage.options.cookie_domain` no Homelab; `validate-final-drupal` e `validate-portal-commerce-security` dependem de sincronização de configuração.

## 2026-10-08 — P10: homologação no Homelab e auditoria final

- Lint de 71 arquivos PHP: PASS. Gate: PASS (361 checks). `composer audit`: sem advisories. `check-platform-reqs`: PASS para PHP 8.4.26.
- `drush cr`, `updatedb:status`: PASS (nenhuma atualização pendente). `config:status`: FAIL pré-existente (drift Runtime × `config/sync`), não causado por este branch.
- Validadores: 9 PASS; 7 FAIL idênticos aos de `main`.
- Smoke HTTP por purpose, autenticação (CAPTCHA ativo: envio por script rejeitado, esperado), isolamento de conta e Views: PASS.
- Não executado: PHPUnit do módulo (inexistente), PHP 8.5, enumeração de contas (CAPTCHA), Mercado Pago com segredo real.
- Não merge. Registro completo e pendências em `docs/portal/RELEASE-P10.md`.

## 2026-10-08 — P9: hardening, segurança, desempenho e rollback

- Segredos: nenhum literal de credencial em código rastreado nem nos padrões de credencial do histórico git. Configurações de Key usam provedor `env`. O loader `aculta.secrets.php` rastreado não contém valores.
- Erros: nenhuma exposição de exceção, trace ou saída de depuração em `src/`.
- Webhook Mercado Pago: falha fechada verificada (503 sem segredo; 401 sem ou com assinatura inválida), in-process com segredo fictício e no endpoint real.
- Desempenho medido no Homelab: anônimo 23–27 ms; Conta logado 124 ms.
- Órfãos: nenhuma classe sem referência; serviços sem referência direta são tags ou consumidos pelo contrib.
- Rollback: código apenas. Nenhuma alteração de config, install ou update desde `052a212`. Runbook e pontos de retorno em `docs/portal/HARDENING-P9.md`.
- Gates: sem regressão em relação à P5-R; falhas pré-existentes permanecem e também existem em `main`.

## 2026-10-08 — P8: deprecações e prontidão para D12/D13

- Novo `scripts/audit-portal-deprecations.py` (biblioteca padrão): indexa `@deprecated` do `web/core` e cruza com o módulo (imports de classe, chamadas estáticas e de instância, funções procedurais). Validado com chamadas plantadas: detecta `views_embed_view()` e `SessionManagerInterface::delete()`. Resultado no módulo: 0 achados.
- Matriz de classificação (CURRENTLY RECOMMENDED IN D11 / DEPRECATED IN D11 / REMOVED IN D12 / ANNOUNCED FOR D13) em `docs/portal/DEPRECATION-MATRIX-P8.md`.
- Nenhuma atualização de Core; Upgrade Status/Rector não instalados (nova dependência sem necessidade comprovada nesta fase).
- Achados pendentes de decisão: `composer.json` sem `require.php` (alvo 8.5 não declarado); `composer/semver` usado sem declaração direta; `mercadopago/dx-php` usado via contrib; teste em PHP 8.5 não executado (Homelab tem 8.4.26).

## 2026-10-08 — P7: subscribers, prioridades e multidomínio

**P7.1 — inventário:** 7 listeners do Portal (4 de request, 1 de response, 1 de alteração de rota, 2 de Social Auth) mais o webhook do Mercado Pago. Prioridades medidas no dispatcher: `onRequestBeforeRouter` 33 (antes do `router_listener` 32), `onRequest` 31, `AccountRouteSubscriber` 29, webhook 29, CEP 28, `onResponse` 1, alteração de rota −2049.

**P7.2 — tags legadas (executado):** `kernel.event_subscriber` substituído por `event_subscriber` nos 6 serviços. O `RegisterEventSubscribersPass` do Core renomeia as duas tags de forma equivalente. Verificado: lista de listeners do dispatcher (classe, método e prioridade) idêntica antes e depois (9 entradas).

**P7.3 — subrequests:** os 4 listeners de request verificam `isMainRequest()`; agora há invariante no gate.

**P7.4 — DI:** listeners recebem dependências por serviço; nenhum `\Drupal::` em `src/` (já verificado em P5).

**P7.5 — purposes e rotas (executado no Homelab):** GET de administração em `conta` → 302 confiável para o host principal; POST na mesma rota → 404 (falha fechada, sem redirect entre Domains); `/dados` anônimo em `conta` → 403; rotas públicas do purpose errado → 404.

**P7.6 — eventos Conta/OAuth/Commerce:** `social_auth.user.login` e `social_auth.user.created` com um listener cada; webhook Mercado Pago guarda subrequests. Sem alteração de comportamento.

**P7.7 — deduplicação:** `entity.user.edit_form` é tratado por `AccountRouteSubscriber` (dono da conta e token one-time) e por `DomainPurposeRequestSubscriber` (purpose). A sobreposição é defesa em profundidade e foi mantida; fundir os dois mudaria a ordem que o padrão protege.

**P7-R — revisão:** gate PASS (361 checks; teste de mutação confirmou a falha ao reintroduzir a tag legada). Smoke de páginas públicas e logadas após a migração: mesmos códigos HTTP.

## 2026-10-08 — P6: cacheability e Render API (inventário e verificação no Homelab)

**P6.1 — inventário de saídas dependentes de Domain**
- Links (`#type => link`) e menus com `Url` do `DomainPurposeManager`: cobertos. `LinkGenerator` usa `toString(TRUE)` e propaga a metadata para o render.
- `DomainPresentationBuilder` (`home_url` como string): cobre `domain`, `languages:language_interface`, `url.site` e depende da entidade Domain do purpose.
- Tokens `[node:canonical]` e `[node:image]`: corrigidos em P6.2.
- Strings de `routeUrl()`/`pathUrl()` usadas em redirects (`TrustedRedirectResponse`) e em metatags: não são cacheadas; redirects não passam pelo cache de página. Canonical/og das metatags verificados por host (abaixo).

**P6.2 — tokens:** ver entrada anterior. Imagem continua **DEFERRED** (Runtime sem entidades `media`).

**P6.3 — conta privada (executado no Homelab):** duas sessões (uid 1 e uid 51) intercaladas em `/dados`: cada resposta contém apenas o próprio e-mail e nunca o do outro usuário.

**P6.4 — Views e LMS/Group (executado):** páginas `/` e `/wiki/busca?q=maconha` (wiki420) e `/` e `/meus-cursos` (cursos), anônimo e administrador intercalados, 3 rodadas: 0 divergências. Anônimo e administrador recebem páginas diferentes, como esperado (barra de administração e vínculos).
- Observação: em uma primeira passada, uma resposta divergiu de sua referência isolada e não se reproduziu em 3 rodadas seguintes. Não foi explicada; fica registrada como transitória.

**P6.5 — Form API e AccessResult:** revisão sem alteração. Acesso de entidade e cache por DomainPurposeManager/Domain já revisados em P4; formulários públicos (busca da Wiki, login) usam GET ou fluxo sem estado do usuário.

**P6.6 — contrato Portal → ACULTA420 (executado):** `preprocess_page` propaga a cacheability do `DomainPresentation` para o render do `page`. Canonical por host verificado intercalado: `aculta.toca.net.br` → `https://aculta.toca.net.br/`, `apoio` → `https://apoio.aculta.toca.net.br/`, estável em requisições alternadas. Homepages de 5 hosts intercaladas (3 rodadas): 0 divergências.

**P6-R — revisão:** gate PASS (357 checks). Nenhuma regressão identificada nas superfícies verificadas.

**Pendências declaradas:** imagem de token (sem media no Runtime); divergência transitória única em P6.4 não explicada; `preprocess_page` depende da ordem de render do tema (verificada por teste, sem teste automatizado).

## 2026-10-08 — P6.2: cacheability dos tokens de URL e imagem

- `[node:canonical]` lia o host/esquema da request (e a opção `https` em ambiente `local`) sem declarar contexto: metadata original trazia só `languages:language_interface`. Agora declara `url.site` (esquema+host+base) e `domain` (ambiente de alias ativo).
- `[node:image]` gera a URL a partir da entidade Domain do purpose e do ambiente de alias, e `pathUrl()` devolve `Url` sem metadata. Agora depende da entidade Domain do purpose (`getDomain()`) e declara `url.site` e `domain`.
- Saída dos tokens preservada: `[node:canonical]` de `wiki420` continua `https://wiki420.aculta.org/verbete/proibicionismo` antes e depois.
- Gate: invariantes de contextos e de dependência da Domain; mutation test confirmou a falha ao remover o contexto.
- Verificação de runtime: **parcial**.
  - CLI: metadata de cache medida antes/depois (`token_meta`).
  - Imagem: o Runtime não tem nenhuma entidade `media` nem nó com imagem; o ramo de imagem é verificado apenas estaticamente. **DEFERRED**.
  - HTTP do Homelab: **BLOQUEADO** — o PHP-FPM roda como `bdtgn`, mas `web/sites/default/files` pertence a `aculta:www-data` com ACL sem acesso para `bdtgn`. Todas as requisições web retornam 500 (inclusive `/`). Não alterei permissões; aguarda decisão do responsável pelo Homelab.

## 2026-10-08 — P5.7 / P5-R: revisão formal da fase P5

**Escopo revisado:** Views, EntityQuery/accessCheck, DI, storage, access e gates de P5.1–P5.6.

**Estático (executado):**
- Lint PHP de todo `src/` e dos arquivos alterados: 0 falhas.
- `\Drupal::` em `src/`: 0. Views wrappers ativos: 0 (a única ocorrência é texto de docblock). Cargas estáticas de entidade: 0. `getQuery()` sem `accessCheck` explícito: 0.
- `strict_types` ausente: 1 (`PortalHooks.php`, dívida carregada para P6, pois o arquivo é reescrito pelos hooks de cache/Domain).
- Subscribers `kernel.event_subscriber` legados: 6 (teto P1 mantido; redução é escopo de P7). Tag canônica `event_subscriber` presente: 1.
- Tetos do gate de locator, Views wrapper e carga estática: todos zerados.
- Gate `validate-aculta-portal-drupal11.php`: PASS, 355 checks.

**Validadores (executados, comparados com `main`):**
- PASS no branch: admin-domain-policy, payment-domain-policy, domain-presentation-contract, aculta420-foundation (corrigido nesta fase), aculta420-shell-contract, institution, aculta420-design-foundations, secrets-contract, gate Drupal 11+.
- FAIL também em `main` (sem regressão introduzida por P5): portal-commerce-security (apenas deriva de configuração no Runtime — 5 checks de `config/sync`), cross-domain-request-policy (host do Runtime fora do cookie compartilhado), admin-cleanup (Views retidas), final-drupal, final-contact, final-sitemap, home-carousel (exigem ambiente "local only").

**Runtime Homelab (executado):** HTTP de Conta (8 páginas), Wiki, Cursos e painel administrativo idênticos antes/depois de cada subfase, com normalização de IDs aleatórios; matriz de acesso e isolamento conforme P5.6.

**Pendências declaradas (não é PASS de homologação completa):**
- Sessão HTTP real de usuário comum: sem conta não administradora no Runtime (ver P5.6).
- Drift de Configuration Sync no Runtime: exige decisão de sincronização, fora do escopo autorizado (`cim`/`cex` não executados).
- `PortalHooks.php` sem `strict_types`: carregado para P6.

**Veredito P5:** concluída estaticamente e validada no Homelab nas superfícies tocadas; pronta para seguir para P6.

## 2026-10-08 — P5.5 / P5.6: storage contracts e matriz de acesso

**P5.5 — storage**
- `AccountCoursesManager` deixa de chamar `GroupMembership::loadByUser()` (carga estática) e usa o serviço público `group.membership_loader` injetado. O método estático delegava ao mesmo cache `cache.group_memberships_chained`; o wrapper expõe `getGroup()` e cacheability idênticos.
- Auditoria: todo acesso a storage em `src/` passa por `entity_type.manager` injetado ou pelo serviço de domínio. Não há `\Drupal::entityTypeManager()`/`entityQuery()` restantes.
- Gate: invariantes do serviço de membership e varredura recursiva proibindo locators estáticos de entidade. Mutation tests confirmaram que ambas as violações injetadas falham o gate.
- Homelab: `/meus-cursos` do administrador (1 vínculo LMS) idêntico antes/depois da troca; demais páginas de Conta idênticas.

**P5.6 — matriz de acesso (executada)**
- Matriz de rotas `aculta_portal.*` para anônimo, usuário autenticado comum (fixture não persistida, uid 999991) e administrador: contas privadas negadas a anônimos, administração negada ao comum, `/apoio` e demais páginas de Conta permitidas apenas a autenticados.
- Isolamento entre usuários: `EntityHooks::entityAccess` com rota `entity.user.edit_form` retorna FORBIDDEN para comum sobre outro usuário e sobre si mesmo; administrador segue neutro (permissão decidida pelo Core).
- Isolamento por Domain (HTTP): `/wiki` retorna 404 em `aculta`, `cursos`, `conta`; `wiki420` serve Wiki; `/painel-administrativo` retorna 403 a anônimo no principal e redireciona para o principal a partir de `conta`.
- `/user/N` e `/user/N/edit` retornam 404 em todos os hosts para todas as contas testadas (não há superfície de edição genérica).

**Lacuna declarada**
- O Runtime **não possui conta ativa não administradora** (verificado: 0 usuários). Sessões HTTP de usuário comum não foram testadas; a matriz de usuário comum é in-process. Testar a sessão real exige criar conta de teste no Runtime — pendente de autorização.
- Nota de correção: as primeiras sondagens usaram uid 51 como "usuário comum", mas ele tem papel administrator. Essas sondagens não provam isolamento de usuário comum e foram descartadas como evidência.

## 2026-10-08 — P5-strict: strict_types em todo src/ (exceto PortalHooks)

- Adiciona `declare(strict_types=1)` a 13 arquivos runtime (AccountShellBuilder, AuthIntegrationManager, SettingsForm, tags metatag). Dívida de tipagem reduzida de 14 para 1 (`PortalHooks`, a ser tratado na P5.4/P6 junto de seus hooks).
- Homelab: páginas de Conta (8) idênticas após normalizar apenas IDs aleatórios do toolbar; `/apoio` (Schema WebPage) e home com schema preservados; formulário de Apoio carrega.
- Gate: lista de dívida `strictTypesDebt` reduzida a `PortalHooks.php`.

## 2026-10-08 — P5.4-A/B/C/E + P5.6: DI dos controllers de Conta e guarda de Apoio

- `PortalController`: `\Drupal::service('plugin.manager.block')` (P5.4-A), `\Drupal::service('email_confirmer')` (P5.4-B) e `\Drupal::routeMatch()` (P5.4-C) → `BlockManagerInterface`, `EmailConfirmerManagerInterface` (dependência hard do `.info.yml`) e `current_route_match` injetados; helpers lazy `currentUser()`/`moduleHandler()`/`config()` → `current_user`, `module_handler`, `config.factory` explícitos. `strict_types=1` e parâmetros `UserInterface` tipados. O swap temporário do parâmetro `user` continua restaurado em `finally`.
- `SupportController` (P5.4-E): `strict_types=1`, `current_user` injetado; histórico de apoio nunca consulta `uid = 0` (que casaria com todos os checkouts de convidados).
- P5.6: rota `aculta_portal.support_my` ganha `_user_is_logged_in: 'TRUE'` (defesa em profundidade; hoje só `authenticated`/`administrator` têm `access aculta portal`, então o 403 anônimo observável não muda).
- `validate-portal-commerce-security.php`: fixture autenticada não salva recebe uid sintético (999990, nunca persistido), porque usuários sem uid são anônimos para `_user_is_logged_in`.
- Homelab (HTTP real, sessões uid 1 com Google vinculado e uid 51 sem vínculo): `/`, `/conta-interna`, `/dados`, `/dados/endereco`, `/conexoes`, `/seguranca`, `/apoio`, `/meus-cursos` com HTML idêntico antes/depois (exceto IDs aleatórios da toolbar Navigation e hashes de agregação CSS); anônimo segue 403.
- Gate: teto de locator e dívida de `strict_types` dos dois controllers zerados; injeções exactly-once, `finally`, guarda `uid > 0` e requisito de rota; mutation test confirmou que cada regressão injetada falha o gate.

## 2026-10-08 — P5.2-B / P5.3 / P5.4-E: WikiController

- `Views::getView()` (não deprecado oficialmente; dívida normativa ACULTA por ser locator estático) → storage `view` + `views.executable` injetados; `buildRenderable()` mantido para paridade de `#embed`, cache keys e propriedades. Display ausente passa a render vazio em vez de `TypeError` (retorno `NULL` em método `: array` com `strict_types`).
- `\Drupal::entityQuery('node')` ×2 → `getStorage('node')->getQuery()` com `accessCheck(TRUE)` explícito e justificado (listagem pública) e checagem `access('view')` por entidade preservada; filtros WIKI Domain/status/bundle inalterados.
- `\Drupal::service()`/`\Drupal::database()` ×4 e helpers lazy (`entityTypeManager()`, `currentUser()`) → DI explícita por `#[Autowire]`.
- Corrige bug visível: busca sem resultados exibia "1 verbete encontrado." junto do aviso de vazio (regra de plural pt-BR usa o singular para 0); a contagem agora só aparece com resultados visíveis e conta apenas itens acessíveis.
- Homelab: HTML de `/` (Views categorias/recentes + alterações recentes) e `/wiki/busca?q=maconha` idênticos antes/depois; buscas vazia e `%` diferem só pela remoção da contagem falsa; `/wiki` em MAIN segue 404.
- Gate: tetos de locator e wrapper Views do Wiki zerados; invariantes de query/Domain/access.

## 2026-10-08 — P5.2-A: catálogo de cursos com render element Views

- `views_embed_view()` é **DEPRECATED IN D11.4** e removido no D13 (CR https://www.drupal.org/node/3572594). `CoursesController` passa a retornar `'#type' => 'view'` para `courses_catalog`/`block_1`.
- Paridade: o wrapper retornava `NULL` sem acesso ao display; o controller mantém a checagem `access()` antes do render via storage `view` e `views.executable` injetados (`#[Autowire]`), preservando o fallback.
- Fallback ganha cacheability explícita (`user.permissions`, `config:views.view.courses_catalog`) e corrige o texto que exibia `\u00edvel` literal (string PHP com aspas simples).
- Homelab: HTML do display idêntico ao do wrapper (normalizado o `js-view-dom-id` aleatório); ramo sem permissão idêntico (`NULL` → fallback); `https://cursos.aculta.toca.net.br/` 200 com catálogo.
- Gate: teto de wrapper Views do controller zerado e invariantes de View/display/access/fallback.

## 2026-10-08 — P5.0: gates executáveis (pré-requisito do Bloco A)

- **O gate `validate-aculta-portal-drupal11.php` nunca havia executado**: erro de sintaxe PHP (escape `\\'` em string simples). Corrigido; agora roda de fato.
- Corrige interpolação acidental de `$entity` em string dupla no gate.
- Contagens exactly-once passam a valer na classe proprietária: `PortalHooks` (preexistente na `main`) implementa legitimamente `form_alter` (login/Profile/conta interna) e `metatags_alter` (rotas de apoio/noindex), escopos disjuntos de `FormHooks`/`EditorialHooks`. Totais do módulo ficam congelados (2/2/1/1) para detectar nova duplicação.
- Remove texto `#[Hook('metatags_alter')]` duplicado dentro de docblock em `PortalHooks` (sem efeito runtime).
- `validate-portal-commerce-security.php` e `validate-aculta420-foundation.php` liam o `.module` removido na P4-R e chamavam a função global removida na P3; apontados para `EntityHooks`, `TokenHooks` e o serviço `aculta_portal.form_callbacks`.
- Runtime Homelab: as 11 famílias de hooks OOP confirmadas registradas via `ModuleHandler::hasImplementations()`.

## 2026-10-08 — P5-extra-2: economia de tokens para agentes

- Política de roteamento proporcional ao risco: modelo econômico para pesquisa e tarefas simples, maior capacidade para revisão final e sistemas sensíveis.
- CLI Python somente leitura para sugerir tier e gerar contexto curto limitado em caracteres; seleção manual, sem APIs externas.
- Atualizados AGENTS, roadmap e handoff. Nenhuma alteração de runtime Drupal nem alteração de escopo DBTNG-2.

## 2026-10-08 — P5-extra-1: expurgo de portabilidade SQLite/MariaDB

- Remove a responsabilidade de portabilidade de bancos do roadmap P5.3, P9.3 e hardening geral do `aculta_portal`.
- Define **DBTNG-2** como projeto independente e único responsável por conversão/migração/portabilidade entre SQLite e MariaDB.
- Atualiza as regras para agentes, o handoff e o padrão normativo; preserva menções a SQLite/MariaDB que apenas descrevem ambientes.
- Não altera código runtime, dados, configuração do banco nem testes.

## 2026-10-08 — Modernização Drupal 11+ Aculta Portal: documentação

- Roadmap detalhado P0–P10 e P5.x consolidado com próximos passos.
- Novo documento de continuidade entre agentes e referências atualizadas em AGENTS e READMEs.
- Somente documentação; comportamento runtime inalterado.

## 2026-10-08 — P5.1: SupportForm Entity API + DI

- Substitui `PaymentGateway::load('mercado_pago')` por storage `commerce_payment_gateway` via `EntityTypeManagerInterface` injetado.
- Substitui `\Drupal::service('plugin.manager.block')` por `BlockManagerInterface` injetado; mantém `FormBase::create()` com as dependências explícitas.
- Adiciona `strict_types=1`, reduz a zero os tetos específicos de static load e service locator de `SupportForm` e o remove da lista de dívida de tipagem.
- Preserva a decisão fail-closed de prontidão do gateway, a chamada do bloco Commerce Donation Flow, os textos e o fallback de indisponibilidade; nenhuma alteração de rota, cobrança ou persistência.
- Gate passa a exigir as três dependências e proibir ambos os acessos estáticos nesta classe.
- Views e demais controllers não são alterados; revisão funcional com Drupal runtime fica para o Homelab.

## 2026-10-08 — P4-R: revisão formal OOP/DI

- Revisa P4.1–P4.3 contra os contratos Drupal 11.4.x de `hook_form_alter()`, `hook_entity_access()` e `hook_entity_presave()`.
- Confirma paridade pré/P4 → pós/P4 para Change Mail, troca de senha, doação, redação de credenciais Mercado Pago, Wiki por Domain, password reset e fail-closed do gateway.
- Confirma os três hooks OOP exatamente uma vez, `strict_types=1`, zero `\\Drupal::*` e ausência de definições YAML redundantes.
- Confirma `AccessResultInterface` em entity access e preserva metadata específica: Domain/entity para Wiki e route/user/max-age 0 para o token one-time de reset.
- Remove o `aculta_portal.module` vazio; módulos Drupal não precisam manter `.module` quando não há implementação procedural legítima.
- Endurece o gate para exigir a ausência do `.module`, bloquear retorno dos três hooks procedurais e proteger callbacks/invariantes da P4.
- Atualiza o roadmap canônico com P5–P9 e a finalização Codex/Homelab.
- Nenhuma nova feature é introduzida; P5 ainda não foi iniciada.
## 2026-10-08 — P4.3: hook_entity_presave em OOP

- Migra `aculta_portal_entity_presave()` para `src/Hook/EntitySaveHooks.php` com `#[Hook('entity_presave')]`, seguindo a assinatura Drupal 11 com `EntityInterface`.
- Preserva o guard fail-closed do gateway Mercado Pago: o gateway só pode permanecer habilitado quando `MERCADOPAGO_PUBLIC_KEY` e `MERCADOPAGO_ACCESS_TOKEN` existirem no runtime.
- Mantém segredos fora de Git e Configuration Sync; nenhum storage paralelo ou persistência de credencial é introduzido.
- A classe usa `strict_types=1`, zero `\\Drupal::*` e não precisa de DI porque depende apenas do entity argument e do ambiente de processo já usado pela política existente.
- Remove a última função runtime procedural do `aculta_portal.module`; o allowlist procedural do gate passa a vazio.
- O gate exige `entity_presave` exactly-once, assinatura Drupal 11, strict_types, zero service locator, ausência de YAML redundante e invariantes do fail-closed.
- Próximo passo obrigatório antes da P5: P4-R, revisão formal de toda a fase P4.
## 2026-10-08 — P4.2: hook_entity_access em OOP + DI

- Migra `aculta_portal_entity_access()` para `src/Hook/EntityHooks.php` com `#[Hook('entity_access')]` e retorno `AccessResultInterface`, seguindo a assinatura do Drupal 11.
- Injeta `aculta_portal.domain_purpose`, `current_route_match` e `request_stack`; a classe não usa `\\Drupal::*` nem definição YAML redundante.
- Preserva o bloqueio de Wiki fora do purpose WIKI, a proteção da rota genérica de edição de usuário, a exceção do token one-time de reset e o bloqueio de edição do gateway Mercado Pago.
- Corrige a cacheability do ramo neutro de password reset válido: a decisão depende de route/user/request/session e agora carrega `route`/`user`, `cachePerPermissions()` e `max-age: 0`, sem alterar o resultado lógico neutro.
- Remove `cachePerPermissions()` desnecessário do bloqueio Wiki e o `max-age: 0`/permission context desnecessários do bloqueio incondicional do gateway, deixando metadata proporcional às condições reais.
- Reduz o legado procedural do `.module` de 2 para 1 função: apenas `aculta_portal_entity_presave()` permanece.
- O gate exige implementação exactly-once, assinatura Drupal 11, `strict_types`, DI explícita, zero service locator, ausência de YAML redundante e invariantes de Domain/conta/password reset/Commerce.
## 2026-10-08 — P4.1: hook_form_alter em OOP + DI

- Migra `aculta_portal_form_alter()` para `src/Hook/FormHooks.php` com `#[Hook('form_alter')]`, a API suportada pelo Drupal 11.4.x.
- Não usa `#[FormAlter]`: o atributo experimental foi removido no Drupal 11.2; o padrão ACULTA passa a tratá-lo explicitamente como deprecado/inválido.
- Injeta `current_route_match`, `current_user` e `string_translation` de forma explícita; a nova classe contém zero `\\Drupal::*`.
- Preserva os fluxos existentes de Change Mail, troca de senha, validação/labels de doação e redação visual das credenciais Mercado Pago.
- Mantém os callbacks P3 como `aculta_portal.form_callbacks:method` e atualiza o gate exactly-once para apontar ao novo `FormHooks`.
- Reduz o legado procedural do `.module` de 3 para 2 funções: `entity_access` e `entity_presave`.
- O gate exige assinatura Drupal 11, `strict_types`, DI explícita, ausência de service locator, invariantes funcionais e ausência de definição YAML redundante para a Hook class.
- Documentação normativa e instruções de IA passam a considerar práticas runtime legadas contrárias ao padrão moderno Drupal 11+ como deprecadas no projeto, salvo exigência upstream comprovada.
## 2026-10-08 — P2-R: revisão e hardening dos hooks OOP

- Revisa P2.1–P2.3 contra o sistema OOP de hooks do Drupal 11.4.x, Token API, Metatag e Library API.
- Confirma descoberta/autowiring automático de classes em `Drupal\\aculta_portal\\Hook` pelo Core 11.1+.
- Confirma paridade da migração: Token/Metatag/Node/Library mantêm os comportamentos anteriores; `metatags_alter` usa o contexto por referência conforme o contrato do Metatag.
- Endurece o gate para exigir os seis hooks migrados exatamente uma vez, `strict_types`, zero `\\Drupal::`, assinaturas-chave, DI explícita e invariantes de Token/Schema/CEP/Domain.
- Confirma `addCacheableDependency($settings)` como substituição correta da cache tag manual da configuração institucional.
- Registra para a fase de cache a dívida preexistente de URLs de token derivadas de Domain/alias/request: revisar dependência da entidade Domain e contexts `domain`/`url.site`; não é regressão introduzida pela P2.
- Nenhum comportamento runtime é alterado nesta revisão.
## 2026-10-08 — P1-R: revisão e hardening do baseline Drupal 11+

- Revisa o padrão P1 contra a API/documentação atual do Drupal 11.4.x.
- Confirma `#[Hook]` como abordagem preferencial para hooks de módulo e `event_subscriber` como tag canônica.
- Confirma que as classes `Drupal\\<module>\\Hook` são descobertas como serviços autowired pelo Core 11.1+.
- Endurece o gate para validar a versão realmente travada no `composer.lock`: Drupal Core deve permanecer em `>=11.3 <12`.
- Registra a depreciação de `hook_requirements()` em Drupal 11.3+ e impede sua reintrodução no `aculta_portal.install`; install/runtime/update requirements devem usar as APIs atuais.
- Mantém lifecycle hooks que o Core ainda exige como procedurais.
- Nenhum comportamento runtime do Portal é alterado nesta revisão.
## 2026-10-08 — P3.3: revisão e hardening da Form API

- Revisa P3.1/P3.2 contra o Form API e CallableResolver do Drupal 11.4.x, sem iniciar P4.
- Confirma que `#validate`, `#submit` e `#after_build` resolvem callbacks por `CallableResolver`, e que `service.id:method` é compatível com DI.
- Confirma paridade 1:1 dos oito registros migrados: cada callback procedural antigo foi substituído uma única vez no mesmo pipeline.
- Confirma a assinatura de `form_node_form_alter` com `$form`, `FormStateInterface` e `$form_id`.
- Endurece o gate para rejeitar nomes legados em todos os arquivos runtime envolvidos, exigir definição única do serviço, conferir as cinco dependências DI e bloquear registro duplicado de callback.
- Nenhum comportamento de formulário, redirect, validação, Commerce, Profile ou autenticação foi alterado nesta fase de revisão.
- Lint/runtime completo continuam reservados para a validação final no Homelab.

## 2026-10-08 — P3.2: callbacks de Conta/Commerce em serviço

- Migra os sete callbacks procedurais restantes de Form API para `PortalFormCallbacks`: confirmação de e-mail, redirect/after-build de senha, redirects de foto/endereço, sincronização de nomes do endereço Commerce e validação de doação.
- Atualiza todos os registros em `aculta_portal.module` e `PortalHooks` para o formato `aculta_portal.form_callbacks:method`.
- Expande a DI do serviço somente com as dependências exigidas pelos callbacks migrados: messenger, current_user, user.data, entity_type.manager e string_translation.
- Preserva redirects, mensagens, flags de Social Auth, labels/autocomplete de senha, sincronização Profile/Commerce e validação de valor de apoio.
- Remove sete funções globais; o legado procedural cai de 10 para 3 funções.
- `hook_form_alter()`, `hook_entity_access()` e `hook_entity_presave()` permanecem procedurais para a fase de DI/OOP do próprio hook.
- O gate passa a rejeitar nomes legados e exigir cada método/registro de serviço migrado.

## 2026-10-08 — P3.1: Form API editorial e CallableResolver

- Migra `hook_form_node_form_alter()` para `EditorialHooks::formNodeFormAlter()` com `#[Hook]`.
- Preserva os grupos, pesos e rótulos editoriais existentes para article/activity/project.
- Substitui o callback procedural `aculta_portal_validate_activity` pelo serviço `aculta_portal.form_callbacks:validateActivity`, suportado pelo CallableResolver do Drupal 11.3+.
- Cria `PortalFormCallbacks` mínimo com DI apenas de `string_translation`; callbacks de conta/Commerce permanecem procedurais por enquanto.
- Preserva as três validações condicionais de Activity: local presencial, URL online e término posterior ao início.
- Reduz o allowlist procedural do gate em mais duas funções e passa a exigir o hook OOP, o serviço e o callback serializável.
- Entity access/presave, form_alter de conta/Commerce, controllers e subscribers permanecem fora desta subfase.

## 2026-10-08 — P2.3: library_info_alter em OOP

- Migra `hook_library_info_alter()` para `EditorialHooks::libraryInfoAlter()` com `#[Hook]`.
- Preserva literalmente a extensão da biblioteca `cep_autocomplete/viacep`: o Portal continua adicionando apenas `aculta_portal/cep-address` como dependência.
- Mantém endpoint, client e cache do contrib intactos; nenhum JavaScript ou comportamento CEP é reimplementado nesta subfase.
- Reduz o allowlist procedural do gate em mais uma função e passa a exigir `library_info_alter` em `EditorialHooks`.
- Form API, entity access/presave, Commerce, controllers e subscribers permanecem fora desta subfase.

## 2026-10-08 — P2.2: hooks editoriais e Metatag em OOP

- Migra `hook_metatag_tags_alter()`, `hook_node_presave()` e `hook_metatags_alter()` para `src/Hook/EditorialHooks.php` com `#[Hook]`.
- Mantém Metatag/Schema Metatag como renderers oficiais e preserva o override de `PostalAddressTag`, publicação inicial, canonical de cursos/Wiki e localização estruturada de atividades.
- `metatagsAlter()` segue a assinatura documentada do Metatag, incluindo o contexto por referência.
- Remove o service locator de `DomainPurposeManager` dessa família e passa a usar DI explícita.
- Reduz o allowlist procedural do gate em mais três funções e passa a exigir os três hooks editoriais OOP.
- Form API, entity access/presave, Commerce, controllers e subscribers permanecem fora desta subfase.

## 2026-10-08 — P2.1: Token API hooks em OOP

- Migra somente `hook_token_info()` e `hook_tokens()` de `aculta_portal.module` para `src/Hook/TokenHooks.php` com `#[Hook]`.
- Substitui service locators dessa família por DI explícita para config, entity storage, Domain purpose/negotiator, request stack, file URL e tradução.
- Preserva os tokens institucionais/editoriais, URL canônica por Domain Source, aliases locais, imagem, autoria e BubbleableMetadata.
- Reduz o allowlist procedural do gate em duas funções e passa a exigir explicitamente os dois hooks OOP.
- Nenhuma outra família de hooks, Form API callback, controller, subscriber ou regra multidomínio é alterada nesta subfase.

## 2026-10-08 — P1: padrão Drupal 11+ e gate progressivo

- Drupal Core 11.3+ passa a ser o baseline arquitetural explícito do módulo.
- Adicionada documentação normativa para humanos, Codex, ChatGPT e demais agentes de IA.
- Adicionado gate estático progressivo que impede expansão da dívida conhecida de service locators, hooks procedurais, wrappers de Views, static entity loads, ausência de strict_types e tag legado de subscriber.
- Nenhum hook, controller, form, subscriber, fluxo Domain/Commerce/LMS ou comportamento runtime foi refatorado nesta fase.
- Compatibilidade formal com Drupal 12/13 continua condicionada à validação das dependências contrib.


Todas as mudanças relevantes do `aculta_portal` devem ser registradas aqui.

O Portal usa tags `portal-vX.Y.Z`.

## [Unreleased]

### Administração multidomínio e redirects cross-domain

- Centraliza `/painel-administrativo/**` no purpose MAIN: GET/HEAD acessados por subdomínio são canonicalizados para o mesmo path/query no Domain MAIN via `DomainPurposeManager`.
- Mantém requests administrativos mutáveis em host errado fail-closed para não repetir POST/CSRF entre Domains.
- Normaliza para 404 também os métodos mutáveis não aceitos pelo Router em paths administrativos de purposes secundários; o gate ordena a verificação do guard executável antes da resolução Wiki.
- Mantém os testes de redirects de Conta alinhados ao contrato atual: comparar URLs absolutas produzidas pelo `DomainPurposeManager`, não URLs relativas do roteador genérico.
- Restringe a rota Commerce de notificação Mercado Pago ao método POST no route subscriber do Portal; o gate verifica método e ownership MAIN sem alterar contrib.
- Preserva a exceção one-time do Core para `entity.user.edit_form` no fluxo de reset em ACCOUNT, agora aplicada de forma consistente antes e depois do RouterListener.
- Wrong-purpose público/funcional continua retornando 404; a regra administrativa não vira redirect genérico.
- Redirects intencionais entre purposes agora usam `TrustedRedirectResponse`; o retorno pós-login/OAuth roda antes do safety subscriber do Core e preserva headers/cookies ao trocar o target.
- Redirects de `AccountRouteSubscriber` passam por `DomainPurposeManager`, e requests mutáveis não são encaminhados MAIN → ACCOUNT.
- `user.page` usa redirect explicitamente confiável para a raiz ACCOUNT. A sessão compartilhada (`cookie_domain`) passa a ser requisito Runtime documentado/gateado por ambiente.
- Carrinho, checkout e pagamentos passam a ser centralizados em MAIN por `DomainRoutePolicy`: `commerce_cart.*`, `commerce_checkout.*`, callbacks browser-facing `commerce_payment.checkout.*`, notify e `commerce_donation_flow.*` não mantêm zona transacional paralela em SHOP/COURSES/SUPPORT.
- Links renderizados de checkout/pagamento são reescritos diretamente para MAIN quando possível; acesso GET/HEAD wrong-host ainda canonicaliza como defesa, enquanto métodos mutáveis falham fechado.
- Métodos mutáveis não aceitos pelo Router em paths de transação central também falham com 404 `private, no-store` em purpose secundário, usando candidatos de rota e metadata Drupal em vez de uma lista Commerce duplicada.
- Metadados `noindex` e a remoção de canonical/OG nas rotas transacionais usam `DomainRoutePolicy`, sem repetir prefixos Commerce em `PortalHooks`.

### Domain Presentation Contract

- 0.2-B.2 adiciona `DomainPresentationBuilder` e `DomainPresentation` como fronteira única e cache-aware entre Domain/Portal e ACULTA420.
- `PortalHooks::preprocessPage()` exporta apenas o array neutro `domain_presentation`; objetos Domain/Portal não chegam ao tema.
- Cacheability é mesclada pela Renderer API; o Portal não instancia SDCs `aculta420:*` nem antecipa regiões visuais ainda sem consumidor.
- Gate Runtime read-only cobre os sete purposes, shape, URLs, cache contexts/tags e dependência unidirecional; valida no Homelab as tags reais da entidade Domain (`config:domain.record.*`) em vez de assumir prefixo `domain:`. Runtime B.2 passou com 127 checks; os sete aliases, saída `NULL` para purpose desconhecido e smoke HTTP foram validados.

### Fronteira de autenticação e tema

- Centraliza a leitura de disponibilidade da configuração Google em `AuthIntegrationManager`, compartilhada pelo login e por Conexões.
- Fornece o wrapper semântico `aculta-auth-provider` ao redor do bloco Social Auth; ACULTA420 estiliza o contrato sem selecionar markup interno do contrib.

### Correções da fundação ACULTA420

- Portal passa a preparar breadcrumb e URL de transparência institucional sem criar dependência reversa no tema.
- Atualiza o relatório de requisitos para reconhecer `aculta420` como tema público/default.
- Move dados institucionais funcionais para `aculta_portal.settings`; o tema mantém apenas configuração de apresentação.
- Tokens institucionais ignoram UUID ausente/vazio antes da consulta de entidade, evitando condições SQL `uuid IN ()` durante configurações incompletas; o gate da Foundation protege essa ordem.
- O contato público é servido pelo Webform `aculta_contact` em `/contato`; o gate deixa de exigir publicação do node histórico `contact`, e o instalador não cria mais esse node nem o formulário legado do módulo Contact.
- Atualiza o teste de redirects de Conta para comparar URLs geradas pelas rotas vigentes, sem exigir slugs antigos (`/minha-conta/...`).
- Freelinking permanece ativo porque o formato de texto Wiki o utiliza; Composer foi atualizado para 4.0.3, corrigindo SA-CONTRIB-2026-213 (CVE-2026-107310; versões afetadas `<4.0.3`).
- Separa o gate ACULTA420 do gate Portal/Commerce e acompanha os hooks OOP do tema.
- Alinha o gate de autenticação à remoção de `user_registrationpassword` e à permanência de `username_enumeration_prevention`.

### Autenticação

- Sincroniza em pt-BR os rótulos dos CAPTCHA de login/cadastro/recuperação e as mensagens globais do Turnstile, sem alterar formulários protegidos, permissão `skip CAPTCHA`, provider ou credenciais.
- Formaliza o ACULTA Secrets Contract: Drupal Key/env permanece a interface
  única e o provisioning fica desacoplado do sistema operacional. Homelab e
  Hostinger compartilham Keys e nomes de variáveis; config exportada continua
  sem credenciais. A R0.4 validou o Secure Bootstrap Adapter no Homelab e
  limpou o storage bruto Google; Hostinger ainda não foi provisionada.
- Corrige o gate Portal/Security para mapear explicitamente Key ID, variável de
  ambiente e item `client_id`/`client_secret` da configuração OAuth.
- Define a prova OAuth no gate pela separação entre storage bruto vazio, Keys Environment, Key Configuration Overrides ativos e configuração efetiva coincidente, sem exibir valores.
- Preserva query string no destination de login/OAuth e adiciona `url.query_args` ao cache do menu, mantendo buscas, filtros e paginação após autenticação.
- Corrige o identificador do usuário externo no callback Google para `SocialAuthUserInterface::getId()`.
- Impede desconexão Google quando a conta Social Auth ainda não possui senha local escolhida, reutilizando o marcador `social_auth_password_unset` via DI de `UserDataInterface`.
- Desabilita os endpoints JSON de login/recuperação do Core neste site para impedir uma rota paralela que não passa pelo Form API/Turnstile; OAuth continua sendo o fluxo alternativo suportado.
- Preserva `user.page` somente como redirect técnico compatível com Core Navigation/recuperação de senha, sempre apontando para a raiz ACCOUNT sem expor o perfil genérico.
- Limpa destinos de login abandonados e preserva o Domain purpose original durante a transição login -> OAuth.
- Aplica Turnstile globalmente aos formulários em páginas públicas para visitantes anônimos; o papel `authenticated` ignora CAPTCHA em todos os formulários. Login e cadastro concluídos pelo OAuth não submetem os formulários Drupal protegidos pelo CAPTCHA.
- Corrige o indicador de conexão Google em Minha Conta: a entidade Social Auth é gravada com o plugin ID `social_auth_google`; `google` é apenas o nome curto da rota e não encontra os vínculos salvos.
- Exibe o e-mail da conta Google em Conexões, guardando o endereço retornado pelo Google nos dados adicionais da entidade Social Auth após callback autenticado; vínculos antigos sem esse dado oferecem atualização da conexão.
- Define `/oauth/{provedor}` como início do Social Auth e `/oauth/{provedor}/retorno` como callback; Google usa `/oauth/google` e `/oauth/google/retorno`.
- Faz links de login preservarem a última página visitada e o Domain purpose para login tradicional e OAuth; o callback retorna ao host da página anterior. Sem destino anterior, o retorno usa a raiz do Domain ACCOUNT. `user.page` é preservada apenas para compatibilidade com redirects do Core e redireciona para a raiz da Conta sem renderizar o perfil genérico; `/identidade` não é publicada.

### Processo

- Macrofase S encerrada no limite seguro sem Runtime; drafts funcionais continuam DEFERRED e a próxima execução começa em R0.

### Refatoração preparada

- S3.2A introduz `AccountCoursePresenter` como fronteira semântica entre LMS/Group e a apresentação da Conta, usa a chave compartilhada `action` da S3.2B sem repassar `score`/`finished` brutos, mantém o render atual e o tema inalterado até Runtime PASS; o controller de Cursos usa DI explícita e não depende dos helpers lazy de `ControllerBase`.
- S3.5 separa resolução de purpose do enforcement HTTP e evita mutação de Domain em URLs locais.
- S3.5 passou os gates Runtime em R1.2B; `pathUrl()` agora respeita o Domain purpose e o alias de ambiente.

### Roadmap

- Portal 0.18.1 adiciona a padronização transversal de slugs públicos amigáveis em português para todos os Domains/purposes, com redirects/canonical/sitemap e preservação das rotas técnicas upstream.

### Documentação

- S2 Static Portal Audit concluído sem alterar runtime.
- S2.1 documenta a matriz Minha Conta: SDC, integrações, AJAX e fallback.
- S3.2B define semântica compartilhada de status, ações, empty states e summaries sem antecipar SDCs não aprovados pelo tema.
- Mapa de refatoração classifica KEEP, REFACTOR, UPSTREAM/CONFIG e RUNTIME-SENSITIVE.
- Bootstrap Component Design System definido como destino da apresentação pública do Portal.

### Planejado

- Portal 0.10.0: fundação documental e disciplina de desenvolvimento.
- Portal 0.11.0: fundação do Fórum.
- Portal 0.12.0: participação do Fórum na Conta.
- Portal 0.13.0: hub integrado de participação.
- Portal 0.14.0: hub administrativo — especificação concluída.
- Portal 0.15.0: consolidação AJAX — especificação concluída.
- Portal 0.16.0: Search API — especificação concluída.
- Portal 0.17.0: engagement — especificação concluída.
- Portal 0.18.0: deduplicação — especificação concluída.
- Portal 0.19.0: hardening — especificação concluída.
- Portal 1.0.0: gates de release especificados; release ainda bloqueado por Runtime.

## 0.10.0 — Foundation

Em preparação.

Objetivo: organizar responsabilidades, fontes de verdade, módulos upstream,
AJAX, Fórum, Wiki, Revista, Loja, integrações Google, Bootstrap Component Design
System, testes, versionamento e roadmap antes de novas features.

O desenvolvimento opera temporariamente em modo GitHub-first / Runtime-last;
features executáveis preparadas sem Homelab permanecem draft até validação.
