# Changelog — ACULTA Portal

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
