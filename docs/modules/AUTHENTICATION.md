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

## Roadmap — verificação de conta e e-mail ACULTA

### Estado

**PLANEJADO / CONDICIONAL. Não implementado.**

Antes de criar código próprio, pesquisar alternativa Core/contrib mantida e compatível com Drupal 11. Se nenhuma alternativa adequada existir, implementar ferramenta própria de verificação de e-mail.

Nome provisório documental: `aculta_email_verification`.

### Casos obrigatórios

1. **Conta criada fora de OAuth**
   - confirmar o endereço por link de uso único;
   - permitir reenvio controlado;
   - ativação/status continuam pertencendo ao Drupal User.

2. **Mudança de e-mail**
   - confirmar o novo endereço antes de torná-lo efetivo;
   - preservar o endereço anterior até a confirmação;
   - invalidar pedido anterior quando substituído.

3. **OAuth**
   - contas OAuth não entram automaticamente no mesmo fluxo;
   - considerar a informação de e-mail verificado fornecida pelo provedor;
   - não duplicar verificação pertencente ao provedor sem decisão explícita.

### Requisitos de segurança

- token de uso único e expirável;
- não armazenar token recuperável em texto puro;
- respostas genéricas para evitar enumeração;
- rate limit/flood para emissão e reenvio;
- invalidar tokens antigos quando o estado mudar;
- links canônicos no purpose ACCOUNT;
- não registrar token/segredo em logs;
- isolamento estrito por UID;
- cache privado/no-store em respostas sensíveis;
- testes User A/User B;
- usar Mail API/SMTP do Drupal, sem transporte paralelo.

### Relação com Email Confirmer e Change Mail

`email_confirmer`, `email_confirmer_user` e `change_mail_page` permanecem o baseline atual.

A ferramenta própria só deve ser criada se esses módulos deixarem de atender o projeto e nenhuma alternativa Core/contrib melhor existir. Se houver substituição, remover a solução anterior; nunca manter dois sistemas concorrentes de confirmação.

### Fora de escopo

A ferramenta não substitui recuperação de senha, autenticação, Social Auth/OAuth ou Drupal User.
