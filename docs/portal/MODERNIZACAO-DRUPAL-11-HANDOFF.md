# Modernização Drupal 11+ Aculta Portal — passagem para outro agente

## Identificação
Repositório: coletivo420/aculta-site.
Branch: refactor/aculta-portal-p1-drupal11-standards.
PR: https://github.com/coletivo420/aculta-site/pull/90.
HEAD anterior à atualização documental: 75362a35c14156be9274ed1a29181e71a7bcf6e6.
Verificar HEAD e main novamente ao retomar.

## Leia primeiro
- [Roadmap completo P0–P10 e P5.x](ROADMAP.md)
- [Normas obrigatórias Drupal 11+](DRUPAL-11-STANDARDS.md)
- [Arquitetura](ARCHITECTURE.md)
- [Fontes de verdade](SOURCE-OF-TRUTH.md)
- [Changelog](../../web/modules/custom/aculta_portal/CHANGELOG.md)

## Estado das fases
P0–P4 com revisões estáticas concluídas. Hooks runtime procedurais foram substituídos por OOP e o arquivo .module vazio foi removido. P5.1 concluída: SupportForm com EntityTypeManager e BlockManager injetados, mantendo fail-closed Commerce. P5.2-A é a próxima etapa. P5.2-B, demais P5, P6–P10 ainda pendentes.

## Próxima execução P5.2-A
Auditar CoursesController.php, views_embed_view('courses_catalog', 'block_1') e definição real da View. Confirmar API Core instalada e, se equivalente, preferir render element Views nativo. Preservar display, argumentos, access, cache, pager, filtros, attachments e empty state. Atualizar gate, docs, changelog e PR; parar após esta subfase.

## P5-extra-1 — separação DBTNG-2

Portabilidade, migração e conversão SQLite/MariaDB pertencem exclusivamente ao projeto separado **DBTNG-2**, e não ao `aculta_portal`. Não adicionar essa responsabilidade a P5, P9, P10, gates ou testes do Portal. Menções aos SGBDs para descrever ambientes são permitidas.

## P5-extra-2 — economia de tokens para agentes

Antes de continuar, consultar [AGENT-TOKEN-ECONOMY.md](AGENT-TOKEN-ECONOMY.md). Preferir modelos econômicos em pesquisa e tarefas pequenas, reservar modelos de maior capacidade para revisão final ou segurança/Domain/Auth/Commerce. `python3 scripts/portal-agent-budget.py route research` recomenda o tier manualmente e `context P5.2-A 3000` prepara contexto reduzido. Nenhuma ferramenta muda modelos automaticamente; economizar tokens não elimina gates nem documentação.

## Política de execução
Mesma PR #90 e branch. Não mergear, rebasiar ou usar force-push. Commits pequenos, padrão Drupal Core 11.3+ e preferir APIs modernas. Diferenciar deprecações oficiais de dívida normativa ACULTA. Nunca modificar Core/contrib, armazenar credenciais, criar estado paralelo ou enfraquecer segurança. MAIN mantém administração, carrinho, checkout e pagamentos.

## Validação
Revisões P0–P4 são estáticas, não homologação runtime. Lint integral, gate executável, Drush, Composer e smoke Homelab estão pendentes para P10/Codex. Nunca declarar PASS sem executar. Não executar updb/cim/cex automaticamente.
