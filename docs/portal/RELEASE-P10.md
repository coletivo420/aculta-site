# P10 — homologação Homelab e auditoria final (aculta_portal)

Ambiente: Homelab Debian, Apache + PHP-FPM, Drupal 11.4.8, PHP 8.4.26 (alvo do projeto: 8.5, não testado), Runtime SQLite. Branch `refactor/aculta-portal-p1-drupal11-standards`. Nenhum merge, nenhuma atualização de banco (`updb`), importação (`cim`) ou exportação (`cex`) executada.

## Resultados

| # | Verificação | Resultado |
| --- | --- | --- |
| 1 | Lint PHP de `aculta_portal` e dos arquivos alterados desde `main` (71 arquivos) | PASS |
| 2 | Gate `validate-aculta-portal-drupal11.php` | PASS (361 checks) |
| 3 | Validadores do tema e do Portal, comparados com `main` | 9 PASS; 7 FAIL, todos também em `main` (ver abaixo) |
| 4 | `composer validate` | Válido, com avisos pré-existentes: pins exatos em bibliotecas frontend (não alterados) |
| 5 | `composer audit` | PASS: nenhum advisory de segurança |
| 6 | `composer check-platform-reqs` | PASS para PHP 8.4.26 e extensões. `require.php` não declarado (pendência) |
| 7 | `drush cr` | PASS |
| 8 | `drush updatedb:status` | PASS: nenhuma atualização pendente |
| 9 | `drush config:status` | **FAIL pré-existente**: 12 itens diferentes e 40 apenas no banco (drift Runtime × `config/sync`). Este branch não altera `config/`. Não resolvido: exige decisão de sincronização |
| 10 | Testes do repositório (`scripts/tests/*`) | PASS (fixtures de design e shell) |
| 11 | Testes PHPUnit do módulo | **Inexistentes**: `aculta_portal` não tem suíte PHPUnit |
| 12 | Smoke HTTP por purpose (matriz final) | PASS em todos os casos esperados (ver matriz abaixo) |
| 13 | Autenticação: `/entrar` com CAPTCHA (Turnstile) | Formulário presente; envio por script rejeitado pela verificação anti-bot (esperado). Enumeração de contas **não verificada**: não contornei o CAPTCHA |
| 14 | Contas e permissões (P5.6) | PASS: matriz de rotas; isolamento entre usuários; administração negada a não-admin |
| 15 | Views Wiki e Cursos | PASS: `/wiki/busca`, verbete, `/cursos` renderizam |
| 16 | Commerce e Mercado Pago | Sem cobrança nem credencial real. Webhook falha fechada: 503 sem segredo, 401 sem/assinatura inválida (verificado in-process com segredo fictício) |
| 17 | Cacheability, Domain e isolamento | PASS (P6/P7): intercalado entre hosts e usuários, 0 divergências em 3 rodadas |
| 18 | Testes de regressão e correções | Correções desta fase: gate (P5.0), serviços e cache, tags de subscriber, contextos de token |

### Falhas dos validadores (idênticas a `main`)
- `portal-commerce-security`: 5 checks de deriva de configuração (item 9).
- `cross-domain-request-policy`: host do Runtime fora do cookie compartilhado (ambiente).
- `admin-cleanup`: Views retidas (dívida de limpeza do admin, fora do Portal).
- `final-drupal`, `final-contact`, `final-sitemap`, `home-carousel`: exigem ambiente "local only".

### Matriz de smoke final
| Host | Caminho | Esperado | Obtido |
| --- | --- | --- | --- |
| aculta | `/` | 200 | 200 |
| aculta | `/wiki`, `/cursos` | 404 (purpose errado) | 404 |
| aculta | `/painel-administrativo` | 403 anônimo | 403 |
| cursos | `/` | 200 | 200 |
| cursos | `/cursos` | 301 para `/` (normalizador de rota do módulo contrib `redirect`: `/cursos` não é a URL canônica do catálogo neste host) | 301 |
| wiki420 | `/wiki/busca?q=maconha`, verbete | 200 | 200 |
| apoio | `/` | 200 | 200 |
| conta | `/entrar` | 200 | 200 |
| conta | `/dados` anônimo | 403 | 403 |
| conta | `/painel-administrativo` | 302 para o principal | 302 |
| conta | `/user/1/edit` | 404 | 404 |
| aculta | webhook Mercado Pago (GET) | 405 | 405 |

## Pendências que bloqueiam a conclusão

1. **Remover a ACL `bdtgn`** em `web/sites/default/files` (`setfacl -R -x` e `-d -x`). Requer `sudo`; aguardando o responsável.
2. **Drift de Configuration Sync** (item 9): decisão de sincronização do responsável; `cim`/`cex` não executados.
3. **Suíte PHPUnit do módulo inexistente**: a cobertura atual é gate estático, fixtures e smoke manual.
4. **PHP 8.5 não testado**: Homelab tem 8.4.26; `composer.json` não declara `require.php`.
5. **Enumeração de contas no login** não verificada (CAPTCHA).
6. **Mercado Pago com segredo real** não testado (deliberado).
7. **Segredo de root exposto no chat**: rotação pelo responsável.
8. **Política de PR por fase** (`AGENTS.md`): cada fase deveria ter PR próprio; este trabalho foi entregue como commits na PR #90, conforme instrução de branch única. Confirmar se as fases P5–P9 serão separadas em PRs antes do merge.

## Recomendação

**Não mesclar ainda.** O código está verificado no Homelab nas superfícies tocadas e o gate passa. Bloqueiam a recomendação de merge: o drift de configuração (decisão de sincronização), a ausência de testes automatizados do módulo, PHP 8.5 não testado e a política de PR por fase. A remoção da ACL `bdtgn` é higiene operacional e não bloqueia o merge.
