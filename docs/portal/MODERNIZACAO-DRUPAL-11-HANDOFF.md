# Modernização Drupal 11+ Aculta Portal — passagem para outro agente

## Identificação
Repositório: coletivo420/aculta-site.
Branch de origem: refactor/aculta-portal-p1-drupal11-standards (integrada).
PR: https://github.com/coletivo420/aculta-site/pull/90 — **integrada à `main`** pelo merge `dd5c8a4`.
Auditoria pós-merge: branch `audit/aculta-portal-p10-r-final` (ver `P10-R-FINAL-AUDIT.md`).
Verificar HEAD e `origin/main` antes de retomar.

## Leia primeiro
- [Roadmap completo P0–P10 e P10-R](ROADMAP.md)
- [Normas obrigatórias Drupal 11+](DRUPAL-11-STANDARDS.md)
- [Auditoria final P10-R](P10-R-FINAL-AUDIT.md)
- [Homologação P10](RELEASE-P10.md) e [Hardening P9](HARDENING-P9.md)
- [Changelog](../../web/modules/custom/aculta_portal/CHANGELOG.md)

## Estado das fases
P0–P10 concluídas e integradas à `main`. P5.2-A concluída (`76439ca`). A próxima etapa não é uma fase de modernização: é a auditoria P10-R e as decisões pendentes listadas em `P10-R-FINAL-AUDIT.md` (PHP 8.5, sincronização de configuração em produção, cadastro e entrega de e-mail em produção, política de enumeração no cadastro, rotação de credencial exposta).

## Estado de validação
- Gate `validate-aculta-portal-drupal11.php`: PASS. Suíte PHPUnit do módulo: PASS (Unit).
- Testes de Kernel (serviços P5–P7 com banco): pendentes.
- PHP 8.5: não executado (Homelab tem 8.4.26).
- Produção: configuração, SMTP e cadastro dependem de importação e teste de e-mail pelo responsável.

## P5-extra-1 — separação DBTNG-2

Portabilidade, migração e conversão SQLite/MariaDB pertencem exclusivamente ao projeto separado **DBTNG-2**, e não ao `aculta_portal`. Não adicionar essa responsabilidade a P5, P9, P10, gates ou testes do Portal. Menções aos SGBDs para descrever ambientes são permitidas.

## P5-extra-2 — economia de tokens para agentes

Antes de continuar, consultar [AGENT-TOKEN-ECONOMY.md](AGENT-TOKEN-ECONOMY.md). Preferir modelos econômicos em pesquisa e tarefas pequenas, reservar modelos de maior capacidade para revisão final ou segurança/Domain/Auth/Commerce. `python3 scripts/portal-agent-budget.py route research` recomenda o tier manualmente e `context P5.2-A 3000` prepara contexto reduzido. Nenhuma ferramenta muda modelos automaticamente; economizar tokens não elimina gates nem documentação.

## Política de execução
Mesma PR #90 e branch. Não mergear, rebasiar ou usar force-push. Commits pequenos, padrão Drupal Core 11.3+ e preferir APIs modernas. Diferenciar deprecações oficiais de dívida normativa ACULTA. Nunca modificar Core/contrib, armazenar credenciais, criar estado paralelo ou enfraquecer segurança. MAIN mantém administração, carrinho, checkout e pagamentos.

## Validação
Revisões P0–P4 são estáticas, não homologação runtime. Lint integral, gate executável, Drush, Composer e smoke Homelab estão pendentes para P10/Codex. Nunca declarar PASS sem executar. Não executar updb/cim/cex automaticamente.
