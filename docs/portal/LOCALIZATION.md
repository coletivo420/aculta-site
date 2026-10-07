# Internacionalização e tradução da interface

Data da revisão: 2026-10-07.

## Objetivo

Manter a interface do ACULTA em português brasileiro usando primeiro Drupal
Core e os catálogos oficiais de tradução de Core, módulos contrib e temas.

Esta política cobre **interface e configuração**. Tradução editorial de
conteúdo não faz parte desta fase.

## Fontes de verdade

```text
Language
  ↓
Locale / Interface Translation
  ↓
catálogos oficiais pt-br de Core, módulos e temas
  ↓
Config Translation para textos configuráveis
  ↓
configuração local apenas para texto específico do ACULTA
```

Regras:

- `language` define os idiomas;
- `locale` traduz strings de Core/contrib/tema;
- `config_translation` traduz configuração;
- não criar tradutor paralelo no `aculta_portal`;
- não copiar catálogos inteiros de Core/contrib para o repositório;
- overrides locais só cobrem texto específico do produto ou lacuna upstream
  comprovada.

## Baseline pt-BR

O idioma padrão é `pt-br`.

A entidade de idioma deve aparecer como `Português (Brasil)`.

`locale.settings` usa fonte `remote_and_local`, importação habilitada,
preserva traduções customizadas e verifica traduções oficiais semanalmente.

## Autenticação

Os formulários continuam pertencendo a Drupal User e aos módulos contrib.

- Drupal User: login, senha e recuperação;
- Login Email or Username: autenticação por usuário ou e-mail;
- User Registration Password: cadastro;
- CAPTCHA + Turnstile: proteção automatizada.

O Portal e o tema não recriam esses formulários apenas para traduzir strings.
As traduções oficiais devem ser importadas pelo Locale conforme as versões
realmente instaladas.

## CAPTCHA

`captcha.settings` contém texto local visível e fica versionado em pt-BR.

Baseline:

- título: `Verificação de segurança`;
- descrição em português;
- mensagem de erro em português;
- CAPTCHA points de login, recuperação e cadastro com `langcode: pt-br`.

A lógica do módulo CAPTCHA não é duplicada no Portal.

## Turnstile

O widget usa explicitamente `widget.language: pt-br`.

Cloudflare Turnstile requer JavaScript para produzir o token de validação. O
projeto **não** deve criar bypass automático ou selecionar um CAPTCHA mais fraco
apenas porque o cliente desativou/bloqueou JavaScript.

Isso é diferente do fallback full-page da Minha Conta: a navegação da Conta deve
continuar funcional sem seu JavaScript próprio, mas uma sessão de teste pode ser
preparada server-side para validar esse requisito sem contornar CAPTCHA em
produção.

## Config Translation

`config_translation` é Core e faz parte do baseline para textos de
configuração. Isso não habilita tradução editorial de conteúdo.

`content_translation` continua fora de escopo até existir requisito
multilíngue e política editorial/canonical/Domain correspondente.

## Runtime / deploy

Após importar a configuração:

```sh
vendor/bin/drush config:import -y
vendor/bin/drush locale:check
vendor/bin/drush locale:update --langcodes=pt-br -y
vendor/bin/drush cr
vendor/bin/drush config:status
vendor/bin/drush updatedb:status
```

A atualização de catálogos depende de acesso ao servidor oficial de traduções.

## Testes

Validar:

- `/entrar`;
- `/recuperar-senha`;
- cadastro quando habilitado;
- labels/botões/mensagens do User e Login Email or Username;
- CAPTCHA fallback/textos locais em português;
- Turnstile em pt-BR com JavaScript habilitado;
- mensagem de erro pt-BR quando não existe token;
- nenhum bypass de CAPTCHA sem JavaScript;
- sem regressão em sessão, access ou Domain policy.

## Anti-regressão

Não:

- desabilitar `locale`;
- voltar a atualização para manual sem justificativa;
- usar `widget.language: auto` num site deliberadamente pt-BR sem decisão
  explícita;
- hardcodar traduções de Core/contrib em controllers/templates;
- manipular o iframe do Turnstile para traduzir conteúdo;
- criar storage próprio de traduções;
- implementar fallback automático de CAPTCHA que possa ser escolhido pelo
  cliente para reduzir a proteção.
