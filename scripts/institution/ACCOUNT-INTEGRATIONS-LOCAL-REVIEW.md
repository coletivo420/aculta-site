# Revisão local — Integrações da conta

Data: 3 de outubro de 2026. Escopo exclusivamente local. Nenhum acesso a
produção, pagamento, commit, push ou deploy.

> **Estado atual — Fase 4:** as seções das Fases 2 e 3 abaixo são registros
> históricos. A configuração completa foi reconciliada em `config/sync`:
> 650 objetos, nenhuma diferença no `drush config:status`. Commerce 3.3.10,
> Donation Flow 1.2.0, uma Store BRL e um gateway Mercado Pago desabilitado
> estão preparados localmente. O gateway não está homologado; veja a seção
> “Fase 4 — configuração completa, Commerce e Apoio” ao final.

## Estado inicial e arquitetura

O portal AJAX e Profile já existiam. As integrações de conta foram compostas
em `aculta_portal`, sem manter módulos custom paralelos. Após a Fase 3, o único
módulo custom ativo desta árvore é `aculta_portal`; as funções editoriais
necessárias de `aculta_editorial` e a apresentação institucional do Apoio foram
consolidadas nele. Preservamos o shell progressivo e as rotas canônicas listadas em
`PORTAL-AJAX-LOCAL-REVIEW.md`.

## Pacotes Composer resolvidos

| Pacote | Versão |
| --- | ---: |
| `drupal/profile` | 1.14.0 |
| `drupal/agreement` | 3.0.3 |
| `drupal/captcha` | 2.0.10 |
| `drupal/crop` | 2.6.0 |
| `drupal/image_widget_crop` | 3.0.0 |
| `drupal/key` | 1.22.0 |
| `drupal/login_emailusername` | 3.0.1 |
| `drupal/smtp` | 1.4.0 |
| `drupal/social_api` | 4.0.2 |
| `drupal/social_auth` | 4.1.2 |
| `drupal/social_auth_google` | 4.0.3 |
| `drupal/turnstile` | 1.2.0 |
| `drupal/user_registrationpassword` | 2.0.4 |
| `drupal/commerce` | 3.3.10 |
| `drupal/commerce_donation_flow` | 1.2.0 |
| `drupal/commerce_mercado_pago` | 3.0.0-rc3 |

Drupal Commerce e Commerce Donation Flow são releases estáveis no lock.
`commerce_mercado_pago` 3.0.0-rc3 é release candidate e não está coberto pela
Drupal Security Advisory Policy. `composer validate --no-check-publish`
passou com os avisos já conhecidos para constraints exatas de Bootstrap5 e
Pathauto. `composer audit --no-dev` não encontrou advisories.

Habilitados nesta fase: Agreement, CAPTCHA, Crop, Image Widget Crop, Key, Login
Email or Username, SMTP, Social API, Social Auth, Social Auth Google, Turnstile
e User Registration Password. Honeypot está ausente/desabilitado. Não foram
instalados reCAPTCHA, TFA, módulo de CEP, Legal, OpenID Connect, One Tap,
Social Auth Account Verification, Avatar Kit, Avatar Uploader, Focal Point ou
User Email Verification.

## Minha Conta, Perfil, foto e endereço

`aculta_portal` apresenta foto privada, apelido, e-mail, apoio, dados,
conexões e segurança dentro do shell existente. O nome de apresentação usa
apelido, nome e, como fallback, display name do usuário. O Perfil privado
`participante` contém apelido, nome, sobrenome, WhatsApp, cidade, UF e endereço
opcional. E-mail permanece no Drupal User. O `field_phone` exclusivo do Profile
participante foi removido após confirmar zero valores; o campo distinto do bloco
institucional permanece.

O campo de imagem de usuário existente foi reutilizado. Storage está privado,
crop institucional 1:1 e image style de avatar configurados; não foi criado
segundo campo no Profile. Endereço usa Address e é opcional, privado e sem
integração com Commerce. Nenhuma chamada ViaCEP/BrasilAPI nem preenchimento
automático foi implementado. CEP permanece uma possibilidade futura.

As rotas de Meus Dados e Endereço funcionam diretamente e dentro do shell; os
forms continuam usando Form API, ownership e CSRF. O fetch do shell chama
detach/attach behaviors. Não foi possível repetir visualmente crop/save por
navegador automatizado nesta máquina; isso fica para revisão manual.

## Login, cadastro e e-mail

`login_emailusername` habilita identificação por e-mail ou username sem mudar
nomes internos. O tema aplica o layout visual do login. A integração social
aparece como separador e bloco somente quando OAuth está configurado; o botão
Google permanece fornecido pelo Social Auth Google, sem redesenho.

Cadastro usa Drupal User + User Registration Password, com senha definida no
cadastro e verificação de e-mail habilitada. O cadastro público permanece
fechado (`admin_only`) até SMTP real enviar e-mail e o link ser validado. Por
isso `/user/register` retorna 403 agora. Recuperação usa o formulário Core de
senha.

Verificação por e-mail está PREPARADA, não validada ponta a ponta. Não há envio
real, clique de ativação ou teste de reset de senha enquanto as credenciais de
SMTP2GO não forem fornecidas e testadas.

## Turnstile / formulários públicos

CAPTCHA global e CAPTCHA em rotas administrativas estão desativados. O modo
Managed do Turnstile está preparado com aparência interaction-only e tamanho
flexible pelo módulo instalado. O módulo contrib faz Siteverify no servidor;
nenhum cliente ou validação custom foi criado. A configuração aponta para Key
ambiental `TURNSTILE_KEYS_JSON` e não contém a chave secreta.

| Form ID Drupal configurado | Rota | Anônimo | Alteração | Turnstile |
| --- | --- | --- | --- | --- |
| `user_login_form` | `/user/login` | sim | autenticação | sim |
| `user_register_form` | `/user/register` | sim, quando cadastro abrir | cria conta | sim, formulário preparado; rota fechada |
| `user_pass` | `/user/password` | sim | solicita reset | sim |
| `webform_submission_aculta_contact_add_form` | `/contato` | sim | cria submissão | sim |
| `webform_submission_aculta_participation_add_form` | `/faca-parte` | sim | cria submissão | sim |
| Forms Profile/User autenticados | `/minha-conta/*` | não | altera dados próprios | não |
| Admin, filtros, busca e formulários de conteúdo | rotas internas | variável | interna | não |
| Callback/login Social Auth | rotas do provider | OAuth | autenticação | não |

As rotas canônicas privadas são `/minha-conta`, `/minha-conta/apoio`,
`/minha-conta/meus-dados`, `/minha-conta/meus-dados/endereco`,
`/minha-conta/conexoes` e `/minha-conta/seguranca`; exigem conta autenticada e
não aparecem no sitemap (`/sitemap.xml` foi verificado sem essas URLs). O hook
Metatag aplica `noindex, nofollow` às rotas `aculta_portal.*`, exceto a página
pública `/apoie`. `/minha-conta/perfil` foi removida e retorna 404. Nenhum
avatar privado é usado como Open Graph.

IDs foram confirmados em runtime; o markup HTML mostra a representação com
hífens. Login popup foi auditado no portal: não existe popup separado a proteger
nesta configuração. O login de página inteira e recuperação de senha retornaram
200; Contato e Faça Parte retornaram 200 com widget. Cadastro retorna 403 por
decisão de segurança enquanto e-mail não estiver pronto.

As chaves oficiais de teste Cloudflare foram passadas somente em memória ao
cliente de validação do módulo contrib; nenhum valor foi gravado em ambiente,
configuração ou arquivo. O mesmo cliente passou o caso de token de teste válido
e recusou os segredos oficiais de teste para falha e token já consumido. Isso
valida o caminho Siteverify do contrib, não a integração com chaves/domínio de
produção. Cloudflare documenta tokens de teste, validade de cinco minutos e
uso único: [Testing](https://developers.cloudflare.com/turnstile/troubleshooting/testing/)
e [validação server-side](https://developers.cloudflare.com/turnstile/get-started/server-side-validation/).

## SMTP2GO

SMTP contrib está configurado para `mail.smtp2go.com`, porta 2525 e TLS; envio
está desligado, usuário/senha/remetente estão vazios. Key/environment override
está preparado por `SMTP2GO_USERNAME` e `SMTP2GO_PASSWORD`, sem segredo em
config exportável. `system.site:mail` não foi substituído por remetente
inventado. Estado: PRONTO PARA CREDENCIAIS, não funcional/homologado.

Antes de abrir cadastro, a equipe precisa criar SMTP user, verificar o domínio
remetente, aplicar DNS oficial do SMTP2GO, fornecer remetente institucional
existente e configurar credenciais privadas. Depois deve enviar e receber
teste real de cadastro, ativação e password reset.

## Google / Conexões

Social Auth e Google estão instalados e a rota Conexões utiliza o bloco padrão
do provider. O código não duplica tabela OAuth, não mostra tokens nem IDs
internos. Desconectar passa pelo formulário de confirmação do Social Auth,
exige usuário atual/CSRF e não é oferecido se não existir senha local. Isso
evita bloquear conta exclusivamente social. Nenhum provider externo está
configurado e nenhum OAuth real foi iniciado. Estado: PRONTO PARA CREDENCIAIS.

Scopes do módulo são os mínimos de identidade (`openid`, `email`, `profile`);
não há acesso a Gmail, Drive, Calendar, Contacts ou YouTube.

### Ação humana futura — Google

- Criar/selecionar projeto Google Cloud e OAuth consent screen.
- Criar aplicação OAuth Web e cadastrar callback de produção
  `https://aculta.org/user/login/google/callback`.
- Fornecer Client ID/Secret por ambiente/Key, nunca pelo Git.
- Testar usuário novo/existente, associação, desconexão, conta só Google e
  definição/recuperação de senha antes da desconexão.

Social Auth declara `/user/login/{network}/callback`; para Google, produção
deve cadastrar exatamente `https://aculta.org/user/login/google/callback`.
Drush local gera host `default`, então a URL absoluta não pode ser homologada
neste ambiente; nenhum OAuth foi iniciado.

## Key e secrets

Key 1.22 usa provider `env` para Turnstile, Client ID/Secret Google e usuário/
senha SMTP. Variáveis identificadoras podem aparecer na configuração, valores
não. A precedência é runtime/environment via Key; não foi introduzida
credencial em settings.php, config/sync, Twig, JS, HTML, logs ou relatório.
Nenhum Client ID, segredo OAuth, segredo Turnstile ou senha SMTP está configurado
nesta execução.

Contrato auditado nas entidades Key (nomes e providers; sem valores):

| Integração | Key | Variável | Tipo | Segredo? | Consumidor |
| --- | --- | --- | --- | --- | --- |
| Turnstile | `turnstile` | `TURNSTILE_KEYS_JSON` | `authentication_multivalue`, JSON com `site_key` e `secret_key` | Site Key pública; Secret Key secreta | Turnstile contrib |
| Google Client ID | `google_oauth_client_id` | `GOOGLE_OAUTH_CLIENT_ID` | `authentication` | não | Social Auth Google |
| Google Client Secret | `google_oauth_client_secret` | `GOOGLE_OAUTH_CLIENT_SECRET` | `authentication` | sim | Social Auth Google |
| SMTP2GO usuário | `smtp2go_username` | `SMTP2GO_USERNAME` | `authentication` | credencial | SMTP contrib |
| SMTP2GO senha | `smtp2go_password` | `SMTP2GO_PASSWORD` | `authentication` | sim | SMTP contrib |

`TURNSTILE_KEYS_JSON` segue o contrato multivalorado nativo exigido pelo
formulário do módulo: `{"site_key":"<SITE_KEY>","secret_key":"<SECRET>"}`.
Os valores seguem fora do config exportável.

### Ação humana futura — Cloudflare

- Criar widget para `aculta.org`, modo Managed e hostname restrito.
- Configurar Site Key e Secret via Key/environment fora do Git.
- Validar Siteverify real e revisar analytics do Turnstile.

## Agreement, Termos e Privacidade

Agreement apresenta confirmação curta com links aos documentos. O bypass está
concedido somente a Administrator para impedir bloqueio administrativo local;
papéis autenticados comuns precisam aceitar a versão aplicável.

`/termos-de-uso` e `/politica-de-privacidade` foram adaptados com base nas
versões 1.1 (28/07/2025) dos documentos indicados pelo responsável. Termos
declaram que conta, login social e apoio financeiro não tornam a pessoa
associada. A Política descreve conta/Profile privado, segurança, Turnstile,
Google opcional, Webforms, fontes externas e serviços somente no estado real;
SMTP2GO é identificado como preparado, ainda não usado para entrega. Ambos
estão marcados versão local 0.1 / 03-10-2026 e PENDENTES DE REVISÃO HUMANA FINAL
ANTES DA PRODUÇÃO.

## Cache, permissões e privacidade

As áreas privadas usam identidade atual, access control e cache privado/não
compartilhado. Endereço, WhatsApp, imagem e Profile não são públicos nem entram
no sitemap. Conexões só mostram estado da própria conta. Os papéis autenticados
não receberam permissões de configuração Key/SMTP/Social Auth, CAPTCHA,
Agreement administrativo, Commerce, Apoio administrativo ou relatórios.

## Fase 2 — configuração canônica e auditoria Support

`web/sites/default/settings.php` agora resolve o diretório pelo layout do
repositório (`root/config/sync`, fora de `root/web`) sem caminho de máquina:
Drush confirma `C:/Users/PiradoPirata/aculta-site/config/sync`. `settings.local.php`
não existe. O diretório gerado antigo em `sites/default/files` não é mais usado
e não foi removido. Não houve `cex`/`cim` amplo.

O manifesto seletivo atual contém **58 objetos**: inclui
`field.storage.user.user_picture` e exclui a configuração removida de
`field_phone`. Os 58 existem no active storage e em `config/sync`, são
semanticamente iguais e não têm dependência de módulos retirados nem de módulo ausente. O inventário completo,
sem valores de configuração, está em
`PORTAL-CONFIG-DIFF-INVENTORY.json`.

Restam **49 diferenças** active-vs-sync fora do manifesto; nenhuma é Only in
sync. Foram classificadas, sem exportação automática:

| Categoria | Only in DB | Different | Tratamento nesta fase |
| --- | ---: | ---: | --- |
| Commerce | 27 | 0 | Defaults do subsistema Commerce; preservar para decisão própria da futura fase Commerce. Inclui `profile.type.customer` e `field.storage.profile.address`, usados pelo perfil de cliente Commerce. |
| Core | 0 | 2 | Configuração base/estado local; estudar em reconciliação geral do site, sem importar/exportar agora. |
| Portal | 3 | 0 | Defaults de Crop/Image Widget Crop (`crop.settings`, `crop_thumbnail`, `image_widget_crop.settings`); manter como configuração local até seleção deliberada. |
| Views | 14 | 0 | Views administrativas de Agreement, Commerce, Profile e Social Auth; não são páginas públicas novas. |
| Outros contrib | 3 | 0 | Ações padrão de Profile; avaliar junto da reconciliação de config do módulo. |

Não foi identificado config órfão com dependência inválida nem valor de segredo
não vazio no `config/sync`. A configuração `core.extension` selecionada reflete
os módulos habilitados. Não há Store nem Payment Gateway ativo ou exportado.
Classificação por decisão: **A** — 58 itens revisados, reconciliados, incluindo
foto privada; **B** — defaults de Crop/Image Widget Crop, Views e ações padrão
de módulos contrib continuam fora do sync seletivo; **C** — defaults Commerce e
duas diferenças Core ficam para uma reconciliação própria; **D** — nenhum órfão
confirmado; **E** — nenhuma propriedade de segredo com valor foi encontrada.

**Auditoria Support (estado atualizado na Fase 3):** `aculta_portal/src/Support/`
contém somente a apresentação pública, configurações editoriais de introdução
e o painel de apoio do usuário. O painel pode ler Orders Commerce próprios
marcados como apoio; não existe storage financeiro custom, cliente HTTP,
captura, estorno, recorrência, webhook, cron ou processador custom. Na Fase 3,
os quatro registros locais `aculta_contribution`, os dois recibos legados, sua
entity type e suas tabelas foram apagados por decisão explícita do responsável;
nada foi migrado para Commerce. Não há bloqueador de dados legado pendente.

`field.storage.profile.address` **não é órfão**: permanece referenciado pelo
campo `profile.customer.address` do Commerce. O Profile participante do portal
usa o storage separado `field_address`. Ambos foram preservados.

O scanner read-only passou **326 verificações**. Drush status confirmou o
diretório canônico; `updatedb:status` não encontrou updates. `config:status`
mostra as 49 diferenças acima, sem diferença dentro dos 58 selecionados.
Smoke HTTP: 13 páginas públicas retornaram 200; cadastro e seis rotas privadas
retornaram 403 para anônimo. Sem navegador automatizado instalado, inspeção
visual autenticada permanece pendente.

Composer validate passou com avisos existentes para constraints exatas de
Bootstrap5/Pathauto; `composer audit --no-dev` não encontrou advisories. PHP
lint passou em 57 arquivos, YAML parse em 658 arquivos, `node --check` nos dois
JS customizados e `git diff --check` sem erro. O script de acesso e configuração
passou 326 checks. Cache foi reconstruído e não há updates pendentes.

## Pendências de ação humana

- SMTP2GO: domínio/remetente, DNS e credenciais, seguidos de teste real de
  entrega, ativação e recuperação de senha. Cadastro continua fechado até isso.
- Cloudflare: chaves reais restritas a `aculta.org` e teste Siteverify.
- Google: OAuth app, callback e credenciais privadas, seguido de testes de
  conta/associação/desconexão.
- Termos e Política: revisão humana final e aprovação institucional.
- Revisão visual no navegador: login/cadastro, widget Google, crop no shell AJAX
  e larguras 1440, 1200, 1024, 768, 480 e 360.
- CEP/autopreenchimento é futuro e não implementado.

## Mercado Pago

PAUSADO / NÃO ALTERADO nesta fase. Sem Store, Payment Gateway, checkout,
pagamento, assinatura, webhook, Pix ou alterações à dependência de gateway.

## Fase 3 — limpeza de legados e consolidação custom

`aculta_editorial` não possuía entities, fields, content types, Views, rotas,
services, tabelas, dados, cron ou filas. Fornecia sete tags Schema.org
Metatag, tokens institucionais/de Node, data de publicação no presave,
agrupamento editorial de formulários, validação de Activity e JSON-LD
condicional. Esses hooks, schema e plugins foram migrados para
`aculta_portal` (`Drupal\\aculta_portal`); Nodes, content types, fields e Views
continuam pertencendo ao Drupal. O módulo foi desinstalado e seu diretório
removido. O manager Metatag resolve os sete plugins custom migrados.

Removidos: entity `aculta_contribution`, quatro registros locais, tabela e dois
recibos webhook, referências de permission/admin, scripts exclusivos, classe
de compatibilidade, campo Profile `field_phone` (zero valores), configs desse
campo, rotas antigas de retorno financeiro e rota `/minha-conta/perfil` (agora
404). `profile.customer.address` e o storage Commerce `profile.address` foram
preservados; o endereço do participante continua em `field_address`.

`config/sync` segue canônico. O manifesto selecionado tem 58/58 objetos iguais
ao active storage. Restam 49 divergências fora do manifesto: Commerce 27 Only
in DB; Core 2 Different; Portal 3 Only in DB; Views 14 Only in DB; outros
contrib 3 Only in DB. Nenhuma divergência remanescente contém configuração
removida ou dependência inválida. O diretório local gerado em `files/config_*`
foi inspecionado (somente `.htaccess` e `README.txt`, sem valores candidatos a
segredo) e removido.

Auditoria do banco não encontrou tabelas `aculta_contribution*`, `aculta_apoio*`
ou recibos webhook; o entity manager não declara `aculta_contribution`.
Configuração e dependências ativas não apontam para `aculta_editorial` ou
`aculta_apoio`. Support não contém transporte financeiro custom. Composer
audit permanece sem advisories; 326 verificações read-only passaram. Smoke HTTP
retornou 200 para 13 páginas públicas, 403 para cadastro e seis rotas privadas
anônimas, e 404 para `/minha-conta/perfil`. Revisão visual autenticada continua
pendente.

Esta fase não homologou SMTP2GO, Turnstile real ou Google OAuth. Cadastro segue
fechado, SMTP desligado, Google sem credenciais e Mercado Pago pausado e
inalterado. Não houve Store, Gateway ou pagamento.

## Git

No fechamento, `git status --short` mostra seis entradas: `composer.json` e
`composer.lock` modificados, e `config/`, `scripts/`, `web/modules/custom/` e
`web/themes/custom/` não rastreados. As mudanças Composer já estavam no
working tree e não foram alteradas nesta fase. `git diff --stat` mostra apenas
os dois arquivos Composer porque conteúdo não rastreado não entra nesse
resumo. Nada foi staged, commitado, enviado ou publicado.

## Fase 4 — configuração completa, Commerce e Apoio

### Configuração canônica

`config/sync` representa agora o estado completo desejado do site. O diretório efetivo confirmado pelo Drush é `C:/Users/PiradoPirata/aculta-site/config/sync`; essa localização decorre do layout portátil `root/config/sync`. Um export completo temporário do active storage foi comparado antes da reconciliação; não houve importação cega. O sync contém **651 objetos** após incluir a Key sem valor do segredo de webhook; a validação final da Fase 5 confirmou que todos coincidem com o active storage e `drush config:status` reporta “No differences between DB and sync directory”.

As 49 diferenças inventariadas anteriormente foram resolvidas: Commerce 27, Portal/Crop 3, Views 14, outras actions contrib 3 e Core 2 (`core.entity_view_display.user.user.compact` e `user.role.anonymous`, com decisões registradas acima). Donation Flow e o gateway acrescentaram mais 38 configurações necessárias. O manifesto do Portal permanece um subconjunto crítico de 58 objetos para auditoria; não limita o escopo de deploy. Não usamos Config Split, Config Ignore nem importação parcial. Não há valores de credenciais exportados.

### Commerce e sistema de Apoio

Drupal Commerce 3.3.10 e Commerce Donation Flow 1.2.0 estão habilitados. Há uma única Store local padrão, tipo `online`, moeda BRL, provisionada pela API Commerce a partir dos dados institucionais canônicos publicados. O script idempotente `scripts/institution/provision-commerce-store.php` valida esses dados e evita duplicação; a Store é content entity e precisa ser provisionada separadamente após o import completo no futuro deploy.

Donation Flow fornece o Donation Order Item e o checkout dedicado; não foram criados produtos, Orders, Payments ou storage financeiro custom. A configuração usa BRL, apoio único, opções R$ 20/R$ 50/R$ 100 e valor personalizado. O componente contrib exige mínimo de R$ 5 e não define máximo; a validação server-side rejeita custom vazio, zero, negativo e qualquer tipo diferente de apoio único. Guest checkout é permitido e registro automático está desativado. Não há shipping. Testes locais criaram Orders guest e autenticada por APIs Commerce, verificaram ownership e apagaram as entidades de teste; a contagem final foi zero Orders e zero Payments.

`/apoie` continua institucional e, enquanto o gateway estiver desabilitado, explica que os pagamentos estão indisponíveis e não expõe CTA para checkout quebrado. `/minha-conta/apoio` consulta exclusivamente Orders de apoio do usuário atual e apresenta estado vazio sem inventar transações. Apoio recorrente está **desabilitado/futuro**; Commerce Recurring não foi instalado.

### Gateway Mercado Pago e segurança

O pacote instalado é `commerce_mercado_pago` 3.0.0-rc3, release candidate fora da cobertura da Drupal Security Advisory Policy. A entidade `mercado_pago` está exportada, desabilitada, em modo test, vazia de credenciais e limitada por condições à Store institucional, tipo de Order padrão e BRL. O formulário de edição do gateway foi bloqueado no módulo custom para todos os papéis: o formulário contrib persiste credenciais em configuração, então a mudança administrativa deve ocorrer por configuração revisada e ambiente. Campos secretos também são renderizados vazios como password. Um teste em memória com valores sintéticos aleatórios e efêmeros confirmou override do plugin sem persistir os valores na configuração ativa/exportada nem renderizá-los no HTML administrativo. O teste não criou chamada externa; os valores foram removidos do processo. O gateway falha fechado ao tentar habilitá-lo sem ambas as variáveis de runtime. Nenhuma credencial real foi configurada.

A versão local usa SDK PHP v3 e `PreferenceClient`/Preferences API para criar checkout; também usa Payment, Merchant Order e Refund clients. O Mercado Pago recomenda Orders API para integrações Checkout Pro novas, portanto há dívida técnica a rever antes da homologação. O handler contrib `CheckoutPro::onNotify()` lê `topic`/`id` e não valida `x-signature`; essa lacuna agora é coberta antes do controller por uma camada de integração em `aculta_portal`, descrita na Fase 5 abaixo. Notificações repetidas de payment consultam Order Commerce + remote ID e evitam criar segundo Payment. O retorno do navegador consulta a API server-side, exige status aprovado e confere a referência externa da Order antes de criar Payment; também verifica duplicidade. Esses mecanismos foram auditados estaticamente, sem API externa real. O retorno do browser não é aceito sozinho como prova de pagamento.

### Verificações finais e limites

- `composer validate --no-check-publish`: passou; permanecem avisos existentes de constraints exatas para Bootstrap5 e Pathauto.
- `composer audit --no-dev`: nenhum advisory encontrado.
- `composer install --dry-run`: lock instalável, nenhuma mudança de pacote.
- PHP lint no código custom e scripts: passou; 656 arquivos YAML parseados; `git diff --check`: passou.
- Drush `cr`, `status`, `updatedb:status`, `config:status` e `pml`: passaram; nenhum update pendente e nenhuma diferença de configuração.
- `scripts/validate-portal-commerce-security.php`: **1.019 verificações read-only aprovadas**, incluindo configuração integral, acesso, valores de apoio, ownership guest/autenticado, gateway desabilitado, segredo não persistido e HTML administrativo redigido.
- Store: 1. Orders: 0. Payments: 0. Gateway Mercado Pago: 1 config entity, desabilitada. Nenhum pagamento real ou sandbox foi realizado.
- Smoke HTTP local com Host Drupal `default`: 13 rotas públicas retornaram 200,
  cadastro e três rotas privadas verificadas retornaram 403. `/donate` não foi
  requisitada para evitar criar Order por GET. A revisão visual/manual de
  `/apoie` e checkout permanece pendente; nenhum browser automatizado foi
  instalado.

**Classificação ao fim da Fase 4 (histórica):** arquitetura pré-deploy localmente preparada; o bloqueador de assinatura do webhook ainda estava aberto naquele ponto. A Fase 5 abaixo registra a implementação posterior da proteção.

## Fase 5 — webhook hardening / release candidate

O SDK efetivamente resolvido é `mercadopago/dx-php` 3.16.0 e contém
`MercadoPago\\Webhook\\WebhookSignatureValidator` e
`MercadoPago\\Exceptions\\InvalidWebhookSignatureException`. O subscriber
`aculta_portal` roda após o routing do Drupal e antes do controller, somente
para `commerce_payment.notify` com gateway `mercado_pago`, no endpoint nativo
`/payment/notify/mercado_pago`. Nenhuma rota de retorno do navegador ou outro
gateway é interceptado.

A chave Drupal Key `mercadopago_webhook_secret` usa o provider `env` e lê
somente `MERCADOPAGO_WEBHOOK_SECRET`; a configuração não contém valor. O guard
exige POST, gateway habilitado, segredo, `x-signature`, `x-request-id`, um
único `data.id` positivo e tipo único. Ausência/assinatura inválida falha
fechado; gateway desabilitado ou segredo ausente retorna 503. IPN legado sem
assinatura é rejeitado. Eventos assinados diferentes de `payment` recebem
resposta neutra sem processamento. Somente após validação, o ID assinado é
normalizado em memória para `topic=payment` e `id=<mesmo data.id>` que o
handler contrib consome. IDs conflitantes são rejeitados. O guard usa o
validator oficial do SDK; não implementa HMAC, cliente HTTP ou processamento
financeiro. Nenhum segredo, assinatura ou payload pessoal é registrado.

A limitação para ID positivo compatível com inteiro decorre de o handler
contrib converter seu parâmetro `id` para inteiro. O SDK valida assinatura
localmente e os testes não chamam a rede. Não foi configurada tolerância de
timestamp/replay própria; a proteção contra reprocessamento depende da
idempotência do contrib (Order + remote payment ID), inspecionada no código,
sem notificação real. `onReturn()` não foi alterado.

Preferences API / Checkout Pro Preferences é o fluxo atual do contrib:
classificado como legacy/classic, ainda suportado. Orders API é recomendada
para novas integrações. Decisão desta fase: manter Preferences com o contrib
atual e registrar a migração Preferences → Orders como dívida técnica futura,
quando houver caminho contrib estável ou decisão explícita. O módulo
`commerce_mercado_pago` continua em 3.0.0-rc3 e fora da cobertura da Drupal
Security Advisory Policy. O gateway continua DESABILITADO; portanto o status é
PRONTO PARA HOMOLOGAÇÃO EXTERNA, não funcional/homologado.

Validação local desta fase: **1.056 checks read-only passaram**, incluindo
assinatura válida/inválida, headers e segredo ausentes, ID ausente/malformado/
conflitante, tipo não suportado, IPN legado, gateway desabilitado e isolamento
de outro gateway/rota, subscriber real e prioridade de execução. Nenhum host Mercado Pago foi contatado. Configuração:
651 objetos completos, 59 do manifesto crítico, zero diferenças. Store: 1;
Orders: 0; Payments: 0. A Store já existente foi validada duas vezes pelo
provisionador idempotente sem duplicação. Smoke HTTP local: 17 páginas públicas
retornaram 200; as seis rotas privadas retornaram 403 anonimamente e
`/minha-conta/perfil` retornou 404. Login e recuperação retornaram 200.
Composer validate/audit/install dry-run passaram (sem advisories; lock sem
alterações), Drush não encontrou updates e o secret scan não encontrou valor
real. A revisão visual humana e os testes autenticados no navegador continuam
pendentes. Nenhum host Mercado Pago foi contatado nem houve homologação externa.

Auditoria focalizada do logging do contrib: o dump de headers em `onNotify()`
está comentado; `debug_logging` da gateway exportada é `false`. A camada nova
não registra corpo, assinatura ou segredos. Manter debug logging desligado na
homologação.

Referências oficiais consultadas: [Checkout Pro Orders API](https://www.mercadopago.com.br/developers/pt/docs/checkout-pro-orders/create-order),
[notificações e validação de assinatura](https://www.mercadopago.com.br/developers/en/docs/links-and-debts/additional-content/your-integrations/notifications/webhooks)
e [visão geral Checkout Pro Orders](https://www.mercadopago.com.br/developers/en/reference/online-payments/checkout-pro-orders/overview).

**Release candidate local:** webhook protegido antes do contrib e pronto para
homologação, mas pagamentos permanecem bloqueados até testes externos reais e
decisão humana de habilitação do gateway.

Arquivos desta etapa: subscriber/validator e definições do módulo
`aculta_portal`; configuração Key sem valor; scanner read-only;
`PORTAL-ACCOUNT-CONFIG-MANIFEST.json` e inventário completo de configuração;
este relatório, README do Portal e `DEPLOY-RUNBOOK.md`.

## Fase 6 — segurança da conta e linguagem visual

O login público mantém autenticação Drupal e agora identifica o campo como
“Nome de usuário ou e-mail”. O módulo já adotado `login_emailusername` 3.0.1
continua sendo a única integração de login por e-mail; não muda usernames.
SMTP permanece desligado e o mail system efetivo continua `php_mail`.

`email_confirmer` 1.0.0 e `change_mail_page` 1.0.2 foram instalados em versões
estáveis. O submódulo real é `email_confirmer_user`. A compatibilidade foi
confirmada no fluxo de formulário: Change Mail Page exige a senha atual e
submete a alteração do User; o presave de Email Confirmer repõe o endereço
antigo enquanto a confirmação estiver pendente; o módulo só aplica o novo
endereço após o link/hash nativo válido. O formulário é apresentado em
`/minha-conta/seguranca` somente quando SMTP está habilitado, selecionado como
backend do Drupal e tem configuração de runtime; no estado local atual, a
interface mostra o aviso seguro e não inicia uma solicitação impossível de
entregar. A consulta de pendência filtra a confirmação pelo UID atual. Nenhum
token ou armazenamento de e-mail pendente foi criado no Portal.

A alteração de senha reutiliza o formulário e as validações do Drupal User
Core, exige senha atual e não aceita UID fornecido pelo cliente. Contas
marcadas como criadas por Social Auth sem senha local não recebem bypass da
senha atual. Uma integração de teste local, com coletor de mensagens em
memória e sem rede, passou 29 verificações de login/validação do formulário,
senha atual, endereço antigo mantido enquanto pendente, destinatário pendente,
duplicidade, confirmação inválida, confirmação de outro usuário, hash válido e
expiração, e montagem dos formulários User/Profile nas rotas Minha Conta.
Os logs identificaram que o Portal pedia a operação `edit` inexistente para a
entidade User; o Core usa `default`. As duas chamadas foram corrigidas. Durante
a montagem, o Portal fornece temporariamente a conta atual ao alter genérico
do Social Auth e remove a seção duplicada, pois Conexões é a interface própria.
O teste local dos controllers confirmou os formulários `user_form` e
`profile_participante_edit_form` nas rotas Meu Dados e Segurança, sem rede.
Isso não equivale à revisão visual de navegador nem à entrega SMTP real.

O aceite de termos permanece no fluxo de criação/aceite da conta e não é
apresentado dentro de Meus Dados. O modo de erro `verbose` está habilitado na
configuração ativa e em `config/sync/system.logging.yml`, conforme solicitado.

O `authenticated` não recebe permissões administrativas; a única permissão
adicional desta fase é `access email confirmation`, necessária para links
privados do próprio usuário. Guardas por nome exato de rota preservam as
ferramentas do administrador e excluem reset de senha, callbacks sociais,
confirmação, Agreement, checkout e notificações Commerce. A auditoria estática
e os checks de rota não substituem teste autenticado em navegador; a página
Security, os formulários e a apresentação móvel continuam pendentes de revisão
visual humana.

### Revisão visual: botões e chamadas com dois CTAs

Os estilos ficam centralizados em `web/themes/custom/aculta/css/style.css`.
Um botão isolado continua primário amarelo/vermelho; apenas pares semânticos
relacionados usam secundário outline verde. O Google provider e o widget
Cloudflare ficam excluídos da regra global, e o tema administrativo Claro não
foi alterado.

| Local | CTA primário | CTA secundário | Classes |
| --- | --- | --- | --- |
| Hero da Home | CONHEÇA A ASSOCIAÇÃO | NOSSOS PROJETOS | `.aculta-actions .btn-primary` / `.aculta-actions .btn-outline-primary` |
| Login | ENTRAR | Google oficial (branding externo) | `.btn-primary` / provider sem recoloração |
| Contato, Faça Parte, Apoie | ação única, quando habilitada | nenhum par atual | botão primário; Apoie não mostra pagamento desabilitado |
| Segurança | Alterar e-mail no próprio card | não pareado com o card de senha | botão primário independente em cada card |

No cabeçalho principal, `/institucional` emitiu um único item atual no próprio
menu, marcado por `.is-active` e `aria-current="page"`; a outra ocorrência na
página é a navegação distinta do rodapé. A Home, Institucional, Projetos,
Atividades, Notícias, Transparência, Apoie e Contato foram auditadas para
garantir um item atual no cabeçalho por rota. Itens normais são transparentes e
brancos; o atual é amarelo/vermelho com sublinhado fino vermelho; hover/focus
temporário em itens não atuais é verde escuro/branco com sublinhado branco. O
hover do item atual conserva a identificação persistente. Navegação utilitária
“Minha conta/Sair”, marca e layout responsivo não foram alterados.

A razão de contraste calculada para vermelho `#d4452d` sobre amarelo `#f2ca36`
é **2,83:1**. O cabeçalho usa Oswald, 19 px e peso 600; a proporção não atinge
3:1 (nem o mínimo para texto grande) nem 4,5:1 para texto normal. A combinação
aprovada foi mantida sem substituição silenciosa de cor; a aprovação de
acessibilidade dessa combinação fica registrada para decisão humana.

O CSS mantém foco visível, respeita `prefers-reduced-motion`, empilha o par de
CTAs no breakpoint móvel já existente e não usa CSS inline. Não há navegador
disponível nesta rodada; hover real, foco por teclado, computed styles e
dimensões 1440/1200/1024/768/480/360 px continuam como revisão visual humana.
Não foi instalado browser automation.

### Escopo dos links editoriais

Links editoriais usam contexto opt-in do tema, sem seletor global de âncoras,
`.region-content a`, `.node a` ou `.view-content a`. O preprocess do tema marca
somente o campo `body` dos bundles editoriais `page`, `article`, `activity`,
`project`, `editorial_highlight` e `document` com `.aculta-prose`; outros Nodes,
incluindo conteúdo de Commerce, não entram automaticamente. A cor se aplica a links inline em parágrafos,
listas, descrições e citações, nunca a headings. Resumos/complementos/CTAs de
editorial, links “mais” dentro de `.aculta-editorial-list`, links textuais
`aculta-editorial-link` e breadcrumbs clicáveis têm regras próprias e
delimitadas. Visited permanece vermelho; hover/focus usa verde escuro e mantém
sublinhado. A classe `.aculta-city` preserva links claros sobre fundo verde
escuro.

Não estão nesse contexto o cabeçalho e menu, links utilitários, rodapé, títulos
de cards, botões/CTAs, login/cadastro/reset, Minha Conta/sidebar, formulários,
Commerce/checkout/doação, Google, Turnstile, Mercado Pago, iframes, widgets e
tema administrativo. Essa mudança não exige configuração Drupal; o config
sync continua sem diferenças.

### Administração do ACULTA Portal e requisitos de runtime

As opções administrativas do módulo foram agrupadas em Configuração → ACULTA
Portal (`/admin/config/aculta/portal`). A página central lista configurações
disponíveis à conta atual e inclui o relatório protegido por
`administer site configuration` em
`/admin/config/aculta/portal/requisitos`. O relatório apresenta extensões
integradas, função, constraint mínima do Composer, versão instalada e estado
com símbolo e texto: OK, Atenção ou Erro. Também verifica o tema público
`aculta` e o tema base `bootstrap5`. Não mostra valores de chaves, tokens,
senhas ou outras configurações secretas.

Os requisitos versionados ficam declarados em `composer.json`; o relatório
deriva a versão instalada do Composer e confirma que os módulos esperados estão
habilitados. A lista funcional abrange Drupal Core, Profile, Address, CEP
Autocomplete, login por usuário/e-mail, Change Mail Page, Email Confirmer,
Social Auth/Google, Image/Crop, Commerce/Donation Flow/Mercado Pago, Key,
CAPTCHA/Turnstile, SMTP, Agreement, Metatag/Schema Metatag e Webform. Mínimos
exatos também estão documentados em `web/modules/custom/aculta_portal/README.md`.

Avisos intencionais de pré-deploy: CEP Autocomplete `1.0.0` não tem cobertura
da Drupal Security Advisory Policy; Commerce Mercado Pago `3.0.0-rc3` também
não tem essa cobertura e o gateway deve permanecer desabilitado; SMTP, Google
OAuth e Turnstile real aguardam credenciais/homologação. Esses estados não
significam que os serviços externos foram testados ou ativados.

Na validação local, o container do CEP Autocomplete não compilava porque o
serviço contrib `cep_autocomplete.viacep_client` referencia
`logger.channel.cep_autocomplete`, ausente no arquivo de serviços do pacote.
O `aculta_portal` fornece esse canal usando o logger padrão do Core, sem editar
`modules/contrib`; após isso, `drush cr` concluiu normalmente.
