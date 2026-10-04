# Revisão local — Portal da conta

Data: 3 de outubro de 2026. Ambiente local, Drupal 11.4.8, PHP 8.5.10,
Drush 13.8. Produção não acessada; nenhum commit, push ou deploy.

## Estado atual

O portal usa um shell único fornecido por `aculta_portal`, com navegação
progressiva e conteúdo de cada área integrado no mesmo layout. Rotas canônicas:

- `/minha-conta`
- `/minha-conta/apoio`
- `/minha-conta/meus-dados`
- `/minha-conta/meus-dados/endereco`
- `/minha-conta/conexoes`
- `/minha-conta/seguranca`

`/minha-conta/perfil` foi removida e retorna 404. Todas as rotas privadas
exigem `access aculta portal`; o conteúdo usa o usuário atual,
metadados privados de cache e formulários Drupal normais. Anônimos recebem 403
nas rotas privadas.

## Estrutura e navegação

A ordem da barra lateral é Visão geral, Meu Apoio, Meus Dados, Conexões e
Segurança, com Sair separado. O perfil aparece uma única vez na Visão Geral
(foto privada, apelido/nome e e-mail); não existe card separado de Perfil nem
saudação duplicada. A rota Meu Apoio é apresentada no shell, enquanto seus
dados e regras permanecem na função de domínio `src/Support/` de
`aculta_portal`.

JavaScript opcional usa Drupal behaviors, `fetch`, detach/attach behaviors,
History API, `pushState`/`popstate`, atualização de título, item ativo,
`aria-busy`, anúncio e foco. Erro de fetch volta à navegação normal. Sem
JavaScript, URLs diretas, refresh e formulários continuam sendo atendidos pelo
servidor. Os formulários Profile/User não foram convertidos em SPA.

## Dados e segurança

O Profile `participante` é privado e contém Apelido, Nome, Sobrenome, WhatsApp,
Cidade, UF e endereço opcional. O campo legado `field_phone` do Profile foi
removido após confirmar zero valores; WhatsApp permanece como único campo
telefônico do participante. O endereço não tem integração de CEP. A imagem usa o campo
`user_picture` existente, storage privado e o widget de crop 1:1; nenhum segundo
campo de avatar foi criado.

Não há links futuros vazios para pedidos, cursos, certificados ou atividades.
Conexões usa Social Auth Google, sem tabela OAuth custom. Desconexão usa
confirmação/CSRF do módulo e depende de existir outra forma de acesso. Sem
credenciais OAuth, nenhum botão Google é apresentado.

Os papéis comuns não recebem permissão administrativa de Apoio, Commerce,
segredos, pagamentos, perfis ou configurações. A verificação read-only atual
executou 326 assertions de acesso, isolamento, configuração e remoção dos
legados financeiros.

## Verificações desta retomada

- Drush: Drupal 11.4.8, PHP 8.5.10, banco conectado; `updatedb:status` sem
  atualizações pendentes; cache rebuild executado.
- Smoke HTTP anônimo: Home, login, recuperação de senha, Contato, Faça Parte,
  Apoie, Termos e Política retornaram 200. Cadastro retorna 403 porque o SMTP
  não foi homologado. Todas as rotas privadas da conta retornam 403 sem sessão.
- Form IDs públicos confirmados no HTML: `user_login_form`, `user_register_form`,
  `user_pass`, `webform_submission_aculta_contact_add_form` e
  `webform_submission_aculta_participation_add_form`. CAPTCHA não é global.
- Turnstile está anexado a login, recuperação, Contato e Faça Parte. O cadastro
  continua fechado e protegido por configuração. A validação server-side é do
  módulo contrib Turnstile; foi exercitada localmente com chaves oficiais de
  teste Cloudflare, sem salvar chaves em configuração. Não foi usado Siteverify
  custom.
- Composer validate passou com os avisos preexistentes de constraints exatas
  em Bootstrap5 e Pathauto; Composer audit não encontrou advisories.
- O script `scripts/validate-portal-commerce-security.php` passou com 326
  verificações read-only nesta estabilização. Drush usa o `config/sync`
  canônico do repositório; os 58 objetos do manifesto coincidem com o active
  storage. Nenhuma credencial, Store ou Payment Gateway foi exportado. As 49
  diferenças restantes estão inventariadas em
  `PORTAL-CONFIG-DIFF-INVENTORY.json` e não foram importadas/exportadas.
- O servidor de desenvolvimento foi usado para os smoke tests. Não há browser
  automatizado instalado nesta máquina para repetir screenshot visual; testes
  autenticados de Back/Forward, crop dentro do shell via AJAX e leitor de tela
  permanecem como revisão manual recomendada.

## Trabalho da fase financeira pausado

O domínio institucional de Apoio foi consolidado em `aculta_portal`. Na Fase 3,
por decisão do responsável, a entity `aculta_contribution`, os quatro registros
locais, os dois recibos webhook e respectivas tabelas foram removidos sem
migração para Commerce. Não há entity type, storage, tabela ou rota webhook
custom remanescente. `Meu Apoio` permanece como empty state até existirem Orders
Commerce vinculados; nenhuma transação fictícia é exibida.

Mercado Pago, credenciais, Store, gateway, order, payment e recorrência não
foram alterados nesta fase. A dependência Commerce Mercado Pago existente é
release candidate e permanece apenas como dependência previamente instalada;
nenhuma operação externa ocorreu. Apoio mensal permanece pendente da solução
de recorrência já documentada no relatório Commerce.

**Status:** pronto para revisão local do portal. Integrações Google e SMTP2GO
estão preparadas para credenciais; e-mail transacional ainda não foi validado.

## Fase 3 — consolidação dos módulos custom

`aculta_editorial` não definia conteúdo, entidades, fields, Views, rotas,
services, schema de dados, filas ou cron. Suas integrações necessárias de
Metatag/Schema.org, tokens, presave, agrupamento/validação de formulários e
JSON-LD foram migradas para `aculta_portal`; o conteúdo permanece em
Node/fields/Views do Drupal. O módulo separado foi desinstalado e removido. O
único módulo custom ativo agora é `aculta_portal`.

Smoke HTTP após limpar o container Drupal: 13 páginas públicas retornaram 200;
cadastro e seis rotas privadas retornaram 403 para anônimos;
`/minha-conta/perfil` retornou 404. Home, Projetos, Notícias, Atividades e VVJB
mantêm suas fontes e Views; nenhum conteúdo foi alterado. A auditoria final
confirmou ausência de entity/tabelas financeiras legadas, dependencies
inválidas e services/routes custom órfãos.

Commerce e `profile.customer.address` foram preservados. A configuração
canônica continua `config/sync`; os 58 objetos selecionados coincidem com o
active storage e as 49 divergências remanescentes estão inventariadas, sem
importação/exportação ampla. SMTP2GO, Turnstile real e Google OAuth continuam
não homologados; Mercado Pago permanece pausado.

## Atualização da Fase 4 — Commerce e configuração completa

A arquitetura do portal e seus testes read-only permanecem; o estado de apoio desta fase agora integra o Commerce Donation Flow. A seção `/minha-conta/apoio` consulta Orders Commerce do usuário atual, e `/apoie` não oferece CTA de pagamento enquanto o gateway Mercado Pago estiver desabilitado. Não existem Orders ou Payments locais.

`config/sync` foi reconciliado como estado completo: 650 objetos e zero diferenças no último `drush config:status`. A fotografia histórica de 58 objetos no manifesto e 49 diferenças restante pertence às fases anteriores; o manifesto é somente auditoria crítica, não mecanismo de deploy. Commerce 3.3.10, Donation Flow 1.2.0, Store BRL e gateway desabilitado estão descritos no relatório atual de integrações.

Smoke HTTP local com Host Drupal `default`: 13 rotas públicas responderam 200;
cadastro e três rotas privadas retornaram 403 para anônimos. A rota `/donate`
não foi requisitada para evitar criar Order por GET. A inspeção visual/manual
continua pendente; nenhum browser automatizado foi instalado. A validação Drush
read-only passou 1.019 checks. O Mercado Pago continua sem homologação externa
e seu webhook sem validação `x-signature` é um bloqueador de produção para
pagamentos.
