# Módulos — autenticação e identidade

Data da revisão: 2026-10-07.

## Baseline ativo

| Módulo | Papel no ACULTA |
| --- | --- |
| Drupal User | conta, senha, recuperação, sessão e status |
| login_emailusername | permite login por usuário ou e-mail |
| username_enumeration_prevention | reduz enumeração de contas no fluxo público |
| social_auth + social_auth_google | OAuth Google e vínculo com Drupal User |
| email_confirmer + email_confirmer_user | confirmação de alterações de e-mail |
| change_mail_page | UI para alteração de e-mail |
| captcha + turnstile | proteção de formulários anônimos; Turnstile é o único challenge |
| aculta_portal | integração, Domain ACCOUNT e apresentação da área de conta |

## user_registrationpassword — REMOVIDO

`drupal/user_registrationpassword` foi removido em 2026-10-07.

Motivo imediato: o próprio módulo reporta incompatibilidade com
`username_enumeration_prevention`. Ambos alteram o fluxo de conta/recuperação e
não devem coexistir no ACULTA.

Além disso, o projeto usa:

```yaml
user.settings:
  register: admin_only
```

Logo o cadastro público convencional que o módulo `user_registrationpassword`
especializava não é a fonte de verdade do produto.

A experiência atual é:

- login tradicional: Drupal User + login_emailusername + Turnstile;
- cadastro/vínculo Google: Social Auth Google;
- área autenticada: ACULTA Portal;
- mitigação de enumeração: username_enumeration_prevention.

### Anti-regressão

Não reinstalar `user_registrationpassword` enquanto
`username_enumeration_prevention` fizer parte do baseline.

Não reativar seu arquivo de configuração antigo
`user_registrationpassword.settings`.

Não trocar `user.settings:register` de `admin_only` como efeito colateral da
remoção.

## Ordem segura no Runtime existente

A remoção do repositório não substitui a desinstalação no banco ativo.

Antes de executar `composer install` que remova o código do módulo em um
Runtime onde ele ainda está habilitado:

```sh
vendor/bin/drush pm:uninstall user_registrationpassword -y
vendor/bin/drush cr
```

Depois:

```sh
composer install
vendor/bin/drush config:import -y
vendor/bin/drush updatedb:status
```

Em instalação nova, o módulo já não faz parte do Composer nem de
`core.extension`.
