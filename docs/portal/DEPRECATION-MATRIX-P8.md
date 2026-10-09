# Matriz de deprecações — P8 (aculta_portal)

Status: **revisão estática**, Drupal Core **11.4.8** instalado, PHP **8.4.26** local (alvo do projeto: 8.5). Nenhuma atualização de Core foi feita.

Ferramenta: `scripts/audit-portal-deprecations.py` (biblioteca padrão; indexa `@deprecated` do `web/core` e cruza com o código do módulo). Resultado desta revisão: **0 achados** no módulo; o script detecta uma chamada plantada de `views_embed_view()` e `SessionManagerInterface::delete()` (validação do próprio detector). O script não substitui Upgrade Status/Rector; nenhum desses foi instalado, por não haver necessidade comprovada de nova dependência nesta fase.

## Classificação

| Item | Onde | Classificação | Situação no código |
| --- | --- | --- | --- |
| `#[Hook]` OOP | `src/Hook/*` | CURRENTLY RECOMMENDED IN D11 | Em uso |
| `#[Autowire]` em construtores e `ControllerBase` | controllers, hooks, subscribers | CURRENTLY RECOMMENDED IN D11 | Em uso |
| `TrustedRedirectResponse` | subscribers, controllers | CURRENTLY RECOMMENDED IN D11 | Em uso |
| Render element `#type => view` | `CoursesController`, `WikiController` | CURRENTLY RECOMMENDED IN D11 | Em uso (P5.2) |
| `views_embed_view()` | — | DEPRECATED IN D11 (11.4.0); removido em 13.0.0 → **ANNOUNCED FOR D13** | Removido (P5.2-A) |
| `SessionManagerInterface::delete()` | — | DEPRECATED IN D11 (11.4.0); removido em 12.0.0 → **REMOVED/CHANGED IN D12** | Não usado |
| `Views::getView()` estático | — | Não deprecado no Core; dívida normativa ACULTA (locator) | Removido (P5.2-B) |
| `#[FormAlter]` | — | Removido no Drupal 11.2; tratado como deprecado pelo projeto | Não usado (gate proíbe) |
| `hook_requirements()` procedural | — | Deprecado em 11.3 segundo `DRUPAL-11-STANDARDS.md` (não re-verificado nesta revisão) | Não usado (gate proíbe) |
| Funções procedurais removidas em PHP 8.4/8.5 (`strftime`, `FILTER_SANITIZE_STRING`, etc.) | — | PHP deprecado/removido | Nenhuma ocorrência |
| Parâmetros nullable implícitos (`Tipo $x = NULL`) | — | Deprecado no PHP 8.4 | Nenhuma ocorrência |

## Achados de dependência (P8.5)

- **Sem `require.php` no `composer.json`.** O alvo PHP 8.5 não está declarado; `composer check-platform-reqs` (P10) não tem referência. Correção exige alteração de `composer.json` e `composer update`: pendente de decisão.
- **`composer/semver` usado sem declaração.** `PortalRequirementsController` importa `Composer\Semver\Semver`; o pacote entra apenas via `drupal/core` (locked 3.4.4). Opções: declarar o pacote (nova dependência direta, exige justificativa) ou remover a dependência de comparação de versões. Pendente de decisão.
- **`mercadopago/dx-php` (3.16.0)** é usado diretamente (`MercadoPago\Exceptions`, `MercadoPago\Webhook`) mas entra via `commerce_mercado_pago`. Acoplamento a biblioteca transitiva; registrado.
- Symfony (http-foundation e dependency-injection, v7.4.20) é usado via Core; sem ação.

## Contrib (P8.4)

- `cep_autocomplete`: aviso já registrado no `PortalRequirementsController` (fora da política de segurança do Drupal). Não há análise de deprecações de contrib nesta revisão; a matriz completa de contrib fica pendente.

## Pendências declaradas

- Matriz de deprecações de contrib não auditada.
- Teste em PHP 8.5 não executado: o Homelab tem PHP 8.4.26.
- Decisão do responsável: declarar `php` e/ou `composer/semver` em `composer.json`.
