# S3.2D — Identidade + Dados

Data de preparação: 2026-10-06

RUNTIME STATUS: **DEFERRED**

## Objetivo

Retirar do `PortalController` a resolução semântica de identidade e a seleção
dos Profiles usados pelas seções Básicos/Endereço, sem alterar User, Profile,
Commerce Address, Form API, Image Widget Crop ou CEP.

## Entregas preparadas

### AccountProfileManager

Novo service:

`aculta_portal.account_profiles`

Centraliza exclusivamente as regras já existentes para:

- Profile `participante`;
- Profile `customer` do Commerce;
- criação de entidade Profile ainda não salva quando não existe perfil canônico.

Não cria storage paralelo.

### AccountIdentityPresenter

Novo service:

`aculta_portal.presentation.account_identity`

Prepara:

- display name;
- e-mail;
- presença/URI da foto;
- alt text;
- inicial de fallback;
- cache tags de User/Profile/File.

Não renderiza Image Style e não monta o editor de foto.

### AccountDataPresenter

Novo service:

`aculta_portal.presentation.account_data`

Prepara:

- seção `basics|address`;
- título;
- descrição;
- tabs e URLs;
- Profile correto;
- e-mail somente na aba Básicos;
- cache tags.

O Profile continua sendo passado ao Entity Form Builder pelo controller.

## PortalController

Continua responsável por:

- render arrays;
- Image Style;
- photo editor;
- Entity Form Builder;
- atributos necessários ao AJAX atual;
- `buildPortalAccountForm()`.

Foram removidos do controller:

- regra de nickname/first name/display name;
- lookup de Profile participante;
- lookup/fallback de Profile customer;
- decisão semântica das tabs Básicos/Endereço.

## Access e privacidade

Os presenters recebem somente a entidade User já resolvida para a conta atual.
Nenhum UID arbitrário de request é aceito.

A fase não altera permissions nem routing.

## Cache

A identidade passa a carregar explicitamente cache tags de:

- User;
- Profile participante;
- File de avatar quando existente.

As seções de dados carregam tags de:

- User;
- Profile selecionado.

Os contexts/max-age atuais do controller são preservados.

## Design System

Contratos futuros possíveis:

- account-identity;
- avatar;
- data-section;
- action/tabs.

Nenhum SDC é criado nesta fase.

## AJAX

Preservado:

- navegação parcial da Conta;
- troca Básicos/Endereço;
- `data-aculta-account-data-*`;
- CEP;
- Form API submit;
- fallback full-page.

## Gates Runtime

```sh
php -l web/modules/custom/aculta_portal/src/AccountProfileManager.php
php -l web/modules/custom/aculta_portal/src/Presentation/AccountIdentityPresenter.php
php -l web/modules/custom/aculta_portal/src/Presentation/AccountDataPresenter.php
php -l web/modules/custom/aculta_portal/src/Controller/PortalController.php
composer validate
vendor/bin/drush status
vendor/bin/drush cr
vendor/bin/drush config:status
vendor/bin/drush updatedb:status
```

Validar funcionalmente:

- nickname;
- first name fallback;
- username fallback;
- avatar existente;
- avatar vazio;
- photo editor;
- Profile participante existente/ausente;
- customer Profile existente/ausente;
- Básicos;
- Endereço;
- CEP válido/inválido/stale;
- submit/reload;
- User A/User B;
- navegação AJAX;
- fallback full-page.

## Merge policy

PR empilhado sobre S3.2C.

Manter draft até S3.2C passar e até os gates Runtime desta fase passarem.

## Próxima fase

S3.3 — Support access first.
