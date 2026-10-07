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

`locale.settings` usa fonte `remote_and_local`, importação habilitada e
preserva traduções customizadas. A verificação automática permanece manual
(`update_interval_days: 0`) para não introduzir drift silencioso em Configuration
Sync; atualizações oficiais são executadas deliberadamente durante manutenção/release.

## Autenticação

Os formulários continuam pertencendo a Drupal User e aos módulos contrib.

- Drupal User: login, senha e recuperação;
- Login Email or Username: autenticação por usuário ou e-mail;
- User Registration Password: cadastro;
- CAPTCHA + Turnstile: proteção automatizada.

O Portal e o tema não recriam esses formulários apenas para traduzir strings.
As traduções oficiais devem ser importadas pelo Locale conforme as versões
realmente instaladas.

## Overrides locais versionados

Quando o catálogo oficial pt-BR não cobre uma string visível confirmada no
Runtime, o Portal pode manter um override mínimo em:

`web/modules/custom/aculta_portal/translations/aculta_portal.pt-br.po`

O módulo declara o catálogo via propriedades nativas do Locale em
`aculta_portal.info.yml`. O arquivo não substitui os catálogos oficiais e não
deve virar uma cópia de Core/contrib.

Baseline atual cobre somente lacunas dos formulários Core de login e recuperação
de senha observadas no Homelab, incluindo título, instruções, senha e submit.

Ao adicionar uma string:

1. confirmar a fonte exata em Core/contrib;
2. confirmar que o catálogo oficial pt-BR realmente deixou a string visível em
   inglês no Runtime;
3. preservar placeholders como `@s`, `%email` e URLs;
4. registrar o motivo na documentação/changelog;
5. remover o override se upstream passar a fornecer tradução equivalente.


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

### Diretório `translations://`

O caminho resolvido por `translations://` precisa ser gravável pelo usuário que
executa Drupal/Drush quando houver download de catálogos. Por padrão o Core usa
`public://translations`, salvo override por `locale_translation_path`.

No Homelab, corrigir ownership/permissões do diretório real; não persistir um
diretório temporário em Configuration Sync e não ampliar permissões além do
necessário.


Após importar a configuração:

```sh
vendor/bin/drush config:import -y
vendor/bin/drush locale:check
vendor/bin/drush locale:update --langcodes=pt-br -y
# O catálogo local aculta_portal.pt-br.po deve ser descoberto pelo Locale.
vendor/bin/drush cr
vendor/bin/drush config:status
vendor/bin/drush updatedb:status
```

A atualização de catálogos depende de acesso ao servidor oficial de traduções.

### Configuration Sync após `locale:update`

Importar catálogos pode alterar traduções de configuração. Portanto, uma
atualização de traduções é uma operação de manutenção controlada:

1. executar `locale:check` / `locale:update`;
2. revisar `drush config:status`;
3. separar traduções de configuração legítimas de drift não relacionado;
4. exportar e revisar somente as mudanças pretendidas;
5. exigir `config:status` limpo antes do merge/deploy.

Não habilitar atualização semanal automática num ambiente governado por
Configuration Sync.

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
