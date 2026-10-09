# P10-R — Auditoria final pós-merge (aculta_portal)

Status: **auditoria concluída; homologação total NÃO declarada.** Existem bloqueadores fora do alcance do Homelab (produção, PHP 8.5, decisões de produto). Este documento separa o que foi verificado do que permanece pendente.

## 1. Baseline
- PR #90 integrada à `main`: merge `dd5c8a4`, cabeça da PR `d0fe8ff`. Conferido com `git merge-base --is-ancestor`.
- Branch de auditoria: `audit/aculta-portal-p10-r-final`, criada a partir de `origin/main`, sem upstream para evitar push acidental na `main`.
- Ambiente: Homelab Debian; Drupal 11.4.8; PHP 8.4.26 (único instalado); Runtime SQLite; Apache + PHP-FPM.

## 2. Commits desta auditoria
| SHA | Assunto |
| --- | --- |
| `a946880` | `chore(composer)`: piso de PHP, `composer/semver` declarado, scaffolding não sobrescreve `.gitattributes` |
| `4bf6b89` | `test(portal)`: suíte PHPUnit (33 testes) e `drupal/core-dev` (dev) |
| `e1516df` | `fix(portal)`: contas bloqueadas indistinguíveis no login |
| `1ecf643` | `docs(portal)`: estado de fases corrigido (roadmap, handoff, AGENTS) |
| (este documento) | `docs(portal)`: auditoria P10-R |

## 3. Pendências auditadas (RELEASE-P10)
| # | Pendência original | Situação verificada | Classificação |
| --- | --- | --- | --- |
| 1 | ACL `bdtgn` em `web/sites/default/files` | Removida (sem entrada). Pool `bdtgn` atende só `dbtng.toca.net.br`. | **Resolvida** |
| 2 | Drift de Configuration Sync | Só `smtp.settings` e `system.mail`, intencionais e específicos do ambiente. Ver A.1. | **Resolvida no Homelab**; importação em produção pendente |
| 3 | PHPUnit inexistente | 33 testes unitários PASS; Kernel pendente | **Parcial** |
| 4 | PHP 8.5 não testado | Não instalado; análise estática sem bloqueios | **DEFERRED** |
| 5 | Enumeração no login | Contas bloqueadas corrigidas; cadastro revela endereços (Core) | **Corrigida** (login); **decisão pendente** (cadastro) |
| 6 | Mercado Pago com segredo real | Não testado (deliberado) | **DEFERRED** (sem credencial real em teste) |
| 7 | Segredo exposto no chat | Nenhum literal no repositório nem no histórico; rotação não verificável daqui | **Pendente** (responsável) |
| 8 | Política de PR por fase | Entregue em PR única por instrução | **Decisão do responsável** |

## 4. A.1 Configuração
- Diagnóstico somente leitura: `config:status` lista apenas `smtp.settings` e `system.mail`. Não há objeto apenas no sync nem apenas no banco. As 40 criações e 12 alterações da exportação anterior estão refletidas no sync.
- Sem valores literais de segredo em `config/sync` (verificação por nomes de chave).
- **Itens que exigem atenção antes da importação em produção:**
  - `smtp.settings` e `system.mail`: habilitam SMTP e o mailer padrão. Credenciais vêm do ambiente via Drupal Key.
  - `user.settings`: cadastro público aberto (`register: visitors`) com confirmação por e-mail.
  - Domain aliases `homelab` e `local` estão versionados (15 arquivos). Só são ativos quando o ambiente corresponde; **verificar o nome do ambiente de produção antes da importação**.
  - Módulos de administração ativos (`views_ui`, `field_ui`, `help`, `update`, `dblog`). Avaliar a desativação em produção.
- **Reconciliação proposta (não executada):** importar apenas os objetos com diferença (`smtp.settings`, `system.mail`) em produção, após verificar que `config:status` de produção não mostra "Only in DB". Não usar `cim` completo sem essa verificação. Rollback: restaurar os valores anteriores de `smtp_on`, `smtp_allowhtml` e `system.mail` com `config:set`.

## 5. A.2 PHP 8.5
- Ambiente: apenas PHP 8.4.26 (`/usr/bin/php8.4`). PHP 8.5 não instalado; instalação exige pacotes do sistema, permissões administrativas e avaliação de impacto. Não realizada.
- Análise estática: nenhum cast removido/deprecado (`(boolean)`, `(integer)`, `(double)`, `(real)`, `(binary)`, `(unset)`), nenhuma função removida ou deprecada (`strftime`, `filter_sanitize_string`, `mhash`, etc.) e nenhum recurso exclusivo do 8.4 no módulo. Os únicos resultados nas varreduras eram comentários.
- Pacotes travados: `doctrine/collections` exige `^8.4`; `ezyang/htmlpurifier` declara 8.5.
- **Declaração:** `require.php: >=8.3`, piso compartilhado pelo Core 11.4 e pelos pacotes travados. A versão testada é 8.4.26. Não há declaração de compatibilidade com 8.5 porque não houve execução nessa versão.
- **Condição para homologar PHP 8.5:** instalar PHP 8.5 no Homelab (ação administrativa), executar lint, PHPUnit e o smoke HTTP nessa versão.

## 6. A.3 Dependências Composer
- `composer/semver: ^3.4` declarado (uso direto em `PortalRequirementsController`); travado em 3.4.4; nenhuma mudança de versão.
- `require.php: >=8.3` (ver A.2).
- `scaffold.file-mapping` exclui `[project-root]/.gitattributes`: o scaffolding do Core sobrescrevia o arquivo com regras que removiam entradas do projeto durante `composer update`.
- `drupal/core-dev: 11.4.8` (dev), necessário para PHPUnit 11 com o bootstrap do Core. Impacto medido com `--with-all-dependencies --dry-run`: 85 pacotes de desenvolvimento novos, 0 atualizações e 1 downgrade (`sebastian/diff` 7.0.1 → 6.0.2, dentro de `^4 || ^5 || ^6 || ^7` do Core). Verificado que nenhum código de runtime usa `sebastian/diff`.
- `composer validate`: válido com avisos pré-existentes sobre pins exatos em bibliotecas de frontend; não alterado (política de dependências, fora do escopo).
- `composer audit`: nenhum advisory. `composer check-platform-reqs`: PHP 8.4.26 e extensões OK.

## 7. A.4 PHPUnit
- Executar: `vendor/bin/phpunit -c web/core/phpunit.xml.dist web/modules/custom/aculta_portal/tests/src/Unit`
- Resultado: **OK (33 testes, 52 asserções)**.
- Cobertura:
  - rotas por purpose (`DomainRoutePolicy`);
  - exceção de reset de senha: dono e token de sessão (`AccountRouteSubscriber`);
  - Mercado Pago: fail-closed sem segredo (503), sem método POST (405), sem assinatura (401), assinatura forjada (401), notificação legada (401/400);
  - acesso cruzado entre contas no formulário genérico de usuário (`EntityHooks`);
  - contrato de apresentação (`DomainPresentation`);
  - bloqueio de conta no login (`PortalFormCallbacks::validateLoginAuthentication`).
- Mutação: remoção de cada proteção (uid no reset, segredo ausente no webhook, regra de acesso cruzado, pulo de conta bloqueada) faz o teste correspondente falhar; código restaurado e reverificado.
- Limites: as classes de Kernel dos serviços P5–P7 (`AccountCoursesManager`, `SupportController`) e o fluxo de Domain com banco exigem `SIMPLETEST_DB` e não foram executados. Não há PASS declarado para eles.

## 8. B Segurança
- **B.1 enumeração (login):** o Core informava "não foi ativado ou está bloqueado" antes da verificação de senha para contas existentes. Corrigido com `validateLoginAuthentication`. Verificado pela cadeia real de validação, em transação revertida, com três estados (inexistente, bloqueada, ativa com senha errada): as três respostas são idênticas.
- **B.1 enumeração (reset):** coberta pelo módulo contrib `username_enumeration_prevention`, habilitado e verificado.
- **B.1 enumeração (cadastro):** o Core responde "The email address … is already taken." Com cadastro aberto, isso permite descobrir se um endereço tem conta. **Risco residual aceito até decisão do responsável.** Opções: (a) manter a mensagem e confiar no CAPTCHA contra automação; (b) resposta genérica com e-mail ao titular, que exige novo fluxo. Não implementado.
- **B.2 credenciais:** varredura do HEAD: nenhum literal de credencial em código ou configuração. Histórico verificado em etapa anterior: nenhum padrão de credencial. Cópias do banco do Runtime em `~/` contêm hashes de senha de contas de teste; remover quando não forem mais necessárias.
- **B.2 rotação:** a senha de root exposta no chat **deve ser rotacionada** pelo responsável; não foi possível verificar a rotação daqui e não há credencial real neste relatório.
- **B.3 ACL:** conferido: sem entrada `bdtgn` em `web/sites/default/files`. Remoção segura: o vhost do Aculta usa o pool `179115646510665` (usuário `aculta`), que é dono do diretório.

## 9. C Verificação funcional
| Verificação | Resultado |
| --- | --- |
| Lint PHP (59 arquivos do módulo e alterados) | PASS |
| Gate Drupal 11+ (366 checks) | PASS |
| PHPUnit Unit (33 testes) | PASS |
| `composer validate` / `audit` / `check-platform-reqs` | PASS / PASS / PASS (PHP 8.4.26) |
| `drush cr` / `updatedb:status` | PASS / sem atualizações |
| `config:status` | Apenas `smtp.settings` e `system.mail` (esperado) |
| Validadores de Domain, pagamento, shell, fundação e design do tema | PASS |
| Smoke HTTP: home, Wiki, cursos, apoio, Conta, administração, 404/403 esperados | PASS (todas as linhas) |
| Mercado Pago sem segredo | 503 (fail-closed) |
| Mercado Pago GET | 405 |
| Social Auth (início) | 302 para Google com escopos `openid email profile` |
| Conta logada (5 rotas) | 200 |
| Isolamento entre duas contas (`/dados`) | PASS: cada página mostra apenas o próprio e-mail |
| Entrada de cadastro `/criar-conta` (Conta) | 200 com formulário e Turnstile; 404 no host principal |
| Tema ACULTA420 | Sem alterações desde `main`; validadores PASS |
| Login completo com CAPTCHA, envio real de e-mail, Mercado Pago com credencial real | **DEFERRED** |
| Testes de Kernel (serviços P5–P7) | **DEFERRED** |
| PHP 8.5 | **DEFERRED** |

## 10. D Riscos residuais
1. Produção: importação de `smtp.settings`/`system.mail` e verificação de entrega de e-mail. Sem isso, o cadastro público aberto cria contas que não recebem confirmação.
2. Enumeração de endereços pelo cadastro (ver B.1).
3. PHP 8.5 não testado; a declaração `>=8.3` não substitui a verificação.
4. Cobertura automatizada incompleta: serviços P5–P7 sem teste de Kernel.
5. Mensagem do webform via SMTP sem o modelo HTML do webform (ver CHANGELOG).
6. Credencial root exposta: rotação pendente.
7. Módulos de administração ativos em produção (avaliar).

## 11. Recomendações operacionais
1. Antes de importar em produção: `config:status` de produção, ausência de "Only in DB", nome do ambiente para os Domain aliases, variáveis `SMTP2GO_USERNAME`/`SMTP2GO_PASSWORD` presentes, envio de teste a um endereço controlado.
2. Rotacionar a senha root exposta e revisar quem tem acesso ao servidor.
3. Decidir a política de enumeração no cadastro.
4. Instalar PHP 8.5 no Homelab e executar lint, PHPUnit e smoke nessa versão antes de declarar compatibilidade.
5. Criar testes de Kernel para os serviços P5–P7 quando houver banco de teste disponível.

## 12. Revisão independente
REVIEW_PLACEHOLDER

## 13. Prontidão para encerramento
- **Auditoria P10-R:** concluída para o que é verificável no Homelab.
- **Homologação total:** **não declarada**. Bloqueadores: importação e teste de e-mail em produção; PHP 8.5; decisão sobre enumeração no cadastro; rotação da credencial exposta.
- **Recomendação:** aprovar esta PR de encerramento para revisão. O merge não deve ser feito antes de as decisões 1–4 estarem registradas pelo responsável.
