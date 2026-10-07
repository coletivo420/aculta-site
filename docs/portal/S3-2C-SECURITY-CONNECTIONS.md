# S3.2C — Segurança + Conexões

Data de preparação: 2026-10-06

RUNTIME STATUS: **DEFERRED**

## Objetivo

Retirar do `PortalController` a descoberta e tradução semântica de estado de:

- Google/Social Auth;
- conexão/desconexão;
- configuração Google pendente;
- prontidão de alteração de e-mail;
- confirmação de e-mail pendente;
- disponibilidade de senha local.

Sem reconstruir:

- OAuth;
- Social Auth;
- Change Mail;
- Email Confirmer;
- SMTP;
- Drupal User;
- Form API.

## Arquitetura

```text
Social Auth / Google config
          ↓
AccountConnectionsPresenter
          ↓
view-model semântico
          ↓
PortalController
          ↓
render atual / futuro integration-card
```

```text
User + SMTP + Change Mail + Email Confirmer
                   ↓
        AccountSecurityPresenter
                   ↓
          view-model semântico
                   ↓
           PortalController
                   ↓
 Form API + render atual / futuro security-card
```

## AccountConnectionsPresenter

Novo service:

`aculta_portal.presentation.account_connections`

Dependências:

- EntityTypeManager;
- ConfigFactory;
- ModuleHandler;
- Translation.

Responsabilidades:

- verificar se Social Auth está disponível;
- verificar se Google possui client ID/secret configurados;
- localizar vínculo Google real do usuário;
- traduzir estado em `connected / disconnected / unavailable`;
- fornecer label/tone semântico;
- verificar entity access antes da ação de disconnect;
- informar quando o bloco de login pode ser exibido;
- orientar usuário sem senha local para Segurança.

Não:

- cria OAuth URL;
- intercepta callback;
- salva vínculo Social Auth;
- desconecta entidade;
- cria botão/SDC.

O bloco `social_auth_login` continua construído pelo controller.

## AccountSecurityPresenter

Novo service:

`aculta_portal.presentation.account_security`

Dependências:

- ConfigFactory;
- ModuleHandler;
- UserData;
- EmailConfirmerManager;
- Translation.

Responsabilidades:

- verificar readiness SMTP atual;
- exigir Email Confirmer User + Change Mail antes de liberar o form;
- ler pending address pelo storage oficial `user.data`;
- validar que existe confirmação pendente do realm `email_confirmer_user`;
- detectar flag `social_auth_password_unset`;
- informar se o form de senha local pode ser apresentado;
- preparar labels/notices/status semânticos.

Não:

- envia e-mail;
- confirma endereço;
- muda e-mail;
- salva senha;
- cria Form API.

## PortalController

Depois da preparação:

- carrega a conta atual;
- solicita o view-model ao presenter;
- constrói o render array;
- inclui `ChangeMailForm` somente quando autorizado pelo presenter;
- inclui o user form de senha somente quando autorizado;
- cria o bloco Social Auth somente quando `login_available` for TRUE.

Foram removidos do controller:

- leitura direta de config Google;
- query direta de vínculos Social Auth;
- readiness SMTP;
- leitura de pending e-mail;
- consulta direta ao serviço Email Confirmer;
- leitura direta de `social_auth_password_unset`.

Permanece fora de escopo um uso de `\Drupal::routeMatch()` em
`buildPortalAccountForm()`; ele pertence à continuação da refatoração do
controller, não a esta fase.

## Access

### Social Auth

A ação "Desconectar Google" só entra no view-model quando:

1. existe vínculo Social Auth;
2. a conta possui senha local;
3. a entidade Social Auth concede `delete` para a conta atual.

Isso preserva e torna explícita a regra que antes vivia como `#access` no
render array.

### Dados privados

Nenhum presenter aceita UID arbitrário de request.

Ambos recebem a entidade User já resolvida para a conta atual pelo controller.

## Cache

O comportamento atual do controller é preservado:

- `user`;
- `user.permissions` em Segurança;
- cache tags do User;
- `max-age: 0`.

Melhorias adicionais de cache não fazem parte desta fase.

## Design System

Os view-models seguem a semântica de
[ACCOUNT-PRESENTATION-MODEL.md](ACCOUNT-PRESENTATION-MODEL.md).

Possíveis consumidores futuros:

- `integration-card`;
- `security-card`;
- status visual;
- action list;
- pending state.

Nenhum desses SDCs é criado nesta fase.

A Fase H do tema continua autoritativa para decidir quando existe maturidade
visual suficiente.

## AJAX

Nenhuma alteração de transporte.

- página Conexões continua participando da navegação assíncrona da Conta;
- página Segurança idem;
- OAuth continua redirect/callback completo;
- Change Mail continua Form API;
- confirmação continua link externo por e-mail;
- senha continua Form API.

## APIs upstream preservadas

### Social Auth

Social Auth continua fonte dos vínculos externos.

A ação de delete usa Entity API access antes de ser exposta.

### Email Confirmer

O serviço `email_confirmer` continua fonte da confirmação pendente.

Não existe storage paralelo no Portal.

## Revisão estática

Confirmado por inspeção:

- presenters não renderizam forms;
- presenters não criam bloco Social Auth;
- presenter de Conexões verifica entity access antes do disconnect;
- presenter de Segurança usa Config/UserData/Email Confirmer;
- controller não consulta mais Social Auth storage diretamente;
- controller não consulta mais SMTP/pending e-mail diretamente;
- nenhum arquivo do tema alterado;
- nenhuma dependência Composer adicionada;
- nenhuma configuração Drupal adicionada;
- nenhum AJAX alterado.

A assinatura concreta dos services/construtores ainda precisa ser comprovada
pela compilação do container Drupal no Homelab.

## Gates Runtime obrigatórios

### Sintaxe/container

```sh
php -l web/modules/custom/aculta_portal/src/Presentation/AccountConnectionsPresenter.php
php -l web/modules/custom/aculta_portal/src/Presentation/AccountSecurityPresenter.php
php -l web/modules/custom/aculta_portal/src/Controller/PortalController.php

composer validate

vendor/bin/drush status
vendor/bin/drush cr
vendor/bin/drush config:status
vendor/bin/drush updatedb:status
```

`drush cr` é gate crítico porque comprova os novos service definitions e
tipos injetados.

### Conexões

Validar:

- Google não configurado;
- Google configurado sem vínculo;
- Google conectado;
- conta Google-only sem senha local;
- conta com senha local;
- usuário sem permission de delete não recebe ação;
- disconnect continua abrindo rota Entity API;
- bloco Social Auth continua funcionando;
- OAuth redirect;
- OAuth callback;
- logout/login multidomínio;
- User A não vê vínculo de User B.

### Segurança

Validar:

- SMTP indisponível;
- SMTP pronto;
- Change Mail indisponível;
- Change Mail disponível;
- sem pending e-mail;
- pending e-mail válido;
- pending e-mail de outro UID não aparece;
- senha local disponível;
- conta Social Auth sem senha local;
- formulário de senha;
- envio da alteração de e-mail;
- confirmação externa;
- cache/User A/User B.

### Regressão

Validar também:

- dashboard;
- Meus Dados;
- Endereço/CEP;
- Cursos;
- Meu Apoio;
- navegação AJAX;
- fallback full-page.

## Merge policy

Manter o PR da S3.2C em **draft** até Runtime PASS.

## Próxima subfase

S3.2D — Identidade + Dados.

Objetivo:

separar do `PortalController`:

- display name;
- avatar state;
- Profile participante;
- tabs Básicos/Endereço;
- dados semânticos da seção;

preservando Image Widget Crop, Profile/Address Form API e CEP.
