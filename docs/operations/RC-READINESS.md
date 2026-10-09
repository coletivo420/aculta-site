# Prontidão para RC — sitemap, descoberta e deployer

Estado do projeto em 2026-10-09, medido no servidor de testes (`aculta.toca.net.br`, Drupal
11.4.8, SQLite). Este documento registra a bateria executada, o resultado de cada item e o que
impede declarar RC para **todo o projeto**. Deploy de produção só acontece no RC de todos os
módulos, temas e subtemas (ver [DEPLOYMENT.md](DEPLOYMENT.md) e [RELEASES.md](RELEASES.md)).

## Escopo coberto por este documento

- Sitemap multidomínio (`aculta_portal_sitemap`, fases 0.1.0-A a J).
- Descoberta: `robots.txt` com diretiva `Sitemap:`, índice central em `/sitemap.xml`.
- `aculta_deployer` 0.1.3: verificação de sitemap, `robots` e caminhos privados por ambiente.
- Ajustes do Portal (`aculta_portal` 0.2.0-dev.8) necessários ao sitemap e ao `noindex`.

Não cobre: tema ACULTA420 como release, Commerce/LMS como release, nem produção.

## Bateria executada

| # | Verificação | Comando | Resultado |
| --- | --- | --- | --- |
| 1 | Gate Drupal 11+ do Portal | `php scripts/validate-aculta-portal-drupal11.php` | PASS (382 checks) |
| 2 | Slugs públicos | `php scripts/validate-public-slugs.php` | PASS (266 rotas; 20 violações de contrib na linha de base DT-P20) |
| 3 | Fundação do tema | `drush scr scripts/validate-aculta420-foundation.php` | PASS (310 checks) |
| 4 | Contrato de shell do tema | `drush scr scripts/validate-aculta420-shell-contract.php` | PASS (9 checks). Corrigido o validador: passava 2 argumentos a `ThemeHooks`, que exige 3 (`entity_type.manager`). O tema não foi alterado |
| 5 | Foundations de design (contraste) | `php scripts/validate-aculta420-design-foundations.php` | PASS |
| 6 | Schemas SDC | `php scripts/validate-aculta420-sdc-schemas.php` | PASS (7 componentes, 98 checks) |
| 7 | PHPUnit do Portal | `vendor/bin/phpunit -c web/core/phpunit.xml.dist web/modules/custom/aculta_portal/tests` | PASS (48 testes, 76 asserções) |
| 8 | Testes do submódulo de sitemap | `php web/modules/custom/aculta_portal/modules/aculta_portal_sitemap/tests/run.php` | PASS |
| 9 | Testes do deployer | `php web/modules/custom/aculta_portal/modules/aculta_deployer/tests/run.php` | PASS (com `FAIL` esperados de DEP-0001/0002 no `verify`, ver abaixo) |
| 10 | Fronteiras do deployer | `aculta-deployer boundaries` | PASS |
| 11 | Sitemap no servidor de testes | `aculta-deployer sitemap --env=test` | PASS (índice com 5 filhos; hosts de conteúdo respondem pelos equivalentes de teste) |
| 12 | Robots no servidor de testes | `aculta-deployer robots --env=test` | PASS (noindex em todos os hosts de teste) |
| 13 | Regressão de cache `url.site` | `tests/homologacao-url-site.sh 5` | PASS (6 hosts, 5 ciclos, sem vazamento) |
| 14 | Verificadores de assets | `scripts/verify-{baque-sativa,bloco-sativa420,podplant420}-assets.py` | PASS |
| 15 | Integridade de pacote | `scripts/audit-package-integrity.py` | PASS |
| 16 | Composer | `composer validate` / `composer audit` | PASS (exit 0; sem advisories) |
| 17 | Espaços em branco | `git diff --check` | PASS |
| 18 | Lint PHP dos arquivos alterados | `php -l` | PASS |
| 19 | Drupal runtime | `drush status`, `drush updatedb:status` | PASS (bootstrap ok; nenhuma atualização de banco pendente) |

## Pendências que impedem o RC do projeto

1. **Drift de configuração**: `drush config:status` lista 95 itens. A maior parte é anterior a esta
   sessão (Commerce, formulários, campos). Só `simple_sitemap.settings` é intencional e de
   runtime: `base_url` = host de teste. Exige decisão de release sobre a baseline de configuração
   (TESTING.md: "não usar `cex` em massa").
2. **Produção (DEP-0001, DEP-0002, DEP-0003)**: os hosts `*.aculta.org` de apoio, wiki, coletivo420 e
   cursos não respondem a partir do Homelab; o `verify` do deployer marca essas entradas como FAIL.
   Só fecham no RC/deploy.
3. **Canonical no servidor de testes (achado da fase J)**: o canonical de `aculta.toca.net.br` e de
   `apoio` aponta para o host de teste; o de WIKI, CURSOS e MAGAZINE aponta para produção. É estável
   (não é cache) e em produção o host da requisição é o canônico. Revisar no RC.
4. **Ambiente PHP**: o runtime local é PHP 8.4.26; o AGENTS.md exige PHP 8.5. Os testes não
   dependem de recursos específicos, mas a bateria deve ser repetida em 8.5 antes do RC.
5. **Search Console**: verificação de propriedade dos hosts é manual e fica fora do código (ADR-009).
6. **Decisões do responsável**: `base_url` de teste permanente (runtime ou `settings.local.php`);
   `/wiki/verbetes` no sitemap ou não; remoção dos nós de teste 69 e 70 (despublicados).

## Estado por componente

| Componente | Versão | Status |
| --- | --- | --- |
| `aculta_portal_sitemap` | 0.1.0 (fases A a J) | Pronto para RC no servidor de testes |
| `aculta_deployer` | 0.1.3 | Pronto para RC no servidor de testes; `verify` de produção depende do RC |
| `aculta_portal` | 0.2.0-dev.8 | Mudanças de sitemap/robots integradas; release depende do RC geral |
| `aculta420` (tema) | 0.4.x | Não avaliado como release; item 1 pendente |

## Como repetir a bateria

```sh
php scripts/validate-aculta-portal-drupal11.php
php scripts/validate-public-slugs.php
php vendor/drush/drush/drush.php --uri=https://aculta.toca.net.br scr scripts/validate-aculta420-foundation.php
php vendor/bin/phpunit -c web/core/phpunit.xml.dist web/modules/custom/aculta_portal/tests
php web/modules/custom/aculta_portal/modules/aculta_portal_sitemap/tests/run.php
php web/modules/custom/aculta_portal/modules/aculta_deployer/tests/run.php
cd web/modules/custom/aculta_portal/modules/aculta_deployer && php bin/aculta-deployer sitemap --env=test && php bin/aculta-deployer robots --env=test
web/modules/custom/aculta_portal/modules/aculta_portal_sitemap/tests/homologacao-url-site.sh 5
```
