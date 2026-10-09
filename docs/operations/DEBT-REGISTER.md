# Registro de dívidas e pendências

Atualizado em 2026-10-09 pela revisão documental do `aculta_portal` e do tema `aculta420`.

Este é o registro único de dívidas históricas e pendências de desenvolvimento. Os roadmaps
(`docs/portal/ROADMAP.md` e `web/themes/custom/aculta420/docs/roadmap.md`) planejam as
fases de saneamento a partir daqui. Não há item de lançamento de versão 1.0 neste registro.

## Como ler

- **Evidência:** comando, arquivo ou gate que comprova o estado. Item sem evidência não entra.
- **Severidade:** Alta (risco de segurança, dado ou regressão em produção), Média (fronteira
  de arquitetura, cobertura ou operação), Baixa (higiene).
- **Decisão:** Sim quando depende de decisão do responsável antes de executar.
- **Estado:** Aberta, Decisão registrada, ou Resolvida (com commit ou PR).

## Portal (`aculta_portal`)

| ID | Dívida | Evidência | Sev. | Decisão | Fase | Estado |
| --- | --- | --- | --- | --- | --- | --- |
| DT-P01 | Classes de tema (`aculta-*`) em templates do módulo | `templates/aculta-portal-shell.html.twig` e `aculta-portal-photo-editor.html.twig` (14 ocorrências); viola a fronteira do `AGENTS.md` | Média | Não | S1 | Aberta |
| DT-P02 | Arquivo sem `declare(strict_types=1)` | `src/Hook/PortalHooks.php`; teto registrado no gate | Baixa | Não | S2 | Aberta |
| DT-P03 | Sem testes de Kernel para os serviços P5–P7 | `tests/` tem 7 arquivos unitários e nenhum Kernel; P10-R item "DEFERRED" | Média | Não | S2 | Aberta |
| DT-P04 | PHP 8.5 não instalado nem testado | `P10-R-FINAL-AUDIT.md` item 4; `composer.json` declara `>=8.3` | Média | Não | S3 | Aberta |
| DT-P05 | Entrega de e-mail em produção não validada; `smtp.settings` e `system.mail` não importados | `P10-R` seção 10, risco 1 e seção 14 | Alta | Não (depende de deploy) | S3 | Aberta |
| DT-P06 | Enumeração de contas pelo cadastro | `P10-R` seção 14: risco aceito pelo responsável | Média | Decisão registrada | S4 | Decisão registrada (reabrir em revisão) |
| DT-P07 | Mercado Pago não testado com credencial real | `P10-R` item 6, DEFERRED | Média | Sim (credencial de sandbox) | S3 | Aberta |
| DT-P08 | Login completo com CAPTCHA não exercitado por HTTP | `P10-R` seção 9, DEFERRED | Média | Não | S3 | Aberta |
| DT-P09 | Mensagem de webform via SMTP sem o modelo HTML | `P10-R` risco 5; `CHANGELOG.md` do Portal | Baixa | Não | S4 | Aberta |
| DT-P10 | Módulos de administração ativos em produção | `P10-R` risco 7 ("avaliar") | Média | Sim | S4 | Aberta |
| DT-P11 | `validate-final-contact` não executa fora do ambiente local | saída `Local only.`, código 1 | Baixa | Não | S3 | Aberta |
| DT-P12 | Conta: segurança/conexões e AJAX herdados da lista antiga de prioridades | `ROADMAP.md` antigo (prioridades 2 e 3); `js/account-navigation.js` com 183 linhas | Média | Sim (escopo) | S5 | Aberta |
| DT-P13 | Busca, engajamento e fórum sem decisão de adoção | `ROADMAP.md` antigo (prioridades 5 e 6) | Baixa | Sim | S5 | Aberta (condicional) |
| DT-P14 | Portal sem tag própria antes de 2026-10-09 | `RELEASES.md` | Baixa | Não | — | Resolvida: `portal-v0.1.0` |
| DT-P15 | Composer sem `composer/semver` declarado (P8) | `composer.json` linha 213 (commit `a946880`) | Média | Não | — | Resolvida: `a946880` |
| DT-P16 | PR #63 citada como integração em andamento | `docs/modules/OPERATIONS.md` e `docs/modules/README.md` | Baixa | Não | S0 | Resolvida nesta revisão |
| DT-P17 | Roadmap do Portal cita prioridades e versões do tema já superadas | `docs/portal/ROADMAP.md` antes desta revisão | Média | Não | S0 | Resolvida nesta revisão |
| DT-P18 | `AGENTS.md` aponta "auditoria P10-R" como próxima etapa, já mesclada | `AGENTS.md` linha 73 | Baixa | Não | S0 | Resolvida nesta revisão |
| DT-P19 | Credencial root exposta no chat | `P10-R` item 7 e seção 14: rotacionada pelo responsável em 2026-10-09 | Alta | Não | — | Resolvida (informada pelo responsável) |

## Tema (`aculta420`)

| ID | Dívida | Evidência | Sev. | Decisão | Fase | Estado |
| --- | --- | --- | --- | --- | --- | --- |
| DT-T01 | Documentação dizendo "0.1.0" como versão atual | `README.md`, `docs/README.md` e `docs/features.md` corrigidos; restam trechos de `design-system.md` e `development.md` | Média | Não | T0 | Parcial |
| DT-T02 | Regra de versão de `info.yml` vs subversões `-dev` não escrita | `aculta420.info.yml` = 0.3.1 com tag `0.4.0-dev.1` | Baixa | Não | T0 | Resolvida nesta revisão (ver `versioning.md`) |
| DT-T03 | Linha 0.4.0 aberta: estruturas internas em rich text (`aculta-areas`, `aculta-callout`, `aculta-page-intro`, CTAs, `aculta-institutional-note`) | `components.md` seção "Padrões 0.4" | Média | Sim (migrar ou manter como conteúdo) | T1 | Aberta |
| DT-T04 | Carrossel da home não validado em navegador | view `home_editorial_highlights` desativada no Runtime | Média | Sim (ativar no Runtime para teste) | T2 | Aberta |
| DT-T05 | Rail de cursos validado só com clones no DOM | `CHANGELOG.md`, entrada `0.4.0-dev.1` | Baixa | Não | T2 | Aberta |
| DT-T06 | Foco por teclado visível não observado em janela com foco real | `docs/design-b-qa.md` (limite conhecido) | Média | Não | T2 | Aberta |
| DT-T07 | CSS residual de cards e hero fora dos SDCs | `institutional.css` e `content.css` com `.aculta-project`, `.aculta-hero` e `.aculta-section-title` | Média | Não | T3 | Aberta |
| DT-T08 | Validação automatizada de schemas SDC ausente (só `enforce_prop_schemas`) | gates listam SDC; nenhum valida o schema contra os props usados | Média | Não | T4 | Aberta |
| DT-T09 | Validadores de navegador acoplados à porta DevTools 9223 | `scripts/*.mjs` (ex.: `validate-institution-browser.mjs`) | Baixa | Não | T4 | Aberta |
| DT-T10 | Conteúdo das seções, hero e cabeçalho existe só no Runtime | `docs/operations/TEST-DATA.md`; migração em `~/.config/aculta-homelab/migration-0.4/` (fora do Git) | Alta | Sim (versionar migração) | T5 | Aberta |
| DT-T11 | Skin do card de curso depende de variáveis internas do LMS | `css/components/course-card.css` mapeia `--color-*` do módulo `lms` | Média | Não | T6 | Aberta |
| DT-T12 | Modo de cor e troca light/dark/auto ausentes | `docs/roadmap.md` antigo, item 0.5 | Baixa | Sim | T7 | Aberta (backlog) |
| DT-T13 | Ícones, busca, feedback, UI Patterns e biblioteca de componentes sem adoção | `docs/roadmap.md` antigo, itens 0.5–0.8 | Baixa | Sim | T7 | Aberta (backlog) |
| DT-T14 | Foundation de 0.1.0 descrita como "preserva o shell existente" em documentos atuais | `architecture.md`, `development.md` | Baixa | Não | T0 | Aberta |

## Operação e Runtime

| ID | Dívida | Evidência | Sev. | Decisão | Fase | Estado |
| --- | --- | --- | --- | --- | --- | --- |
| DT-O01 | 100 branches remotas; 64 já mescladas na `main` | `git branch -r --merged origin/main` | Baixa | Sim (apagar exige autorização) | S6 | Aberta |
| DT-O02 | Snapshot de `estados/` é de 2026-10-04 e o Runtime já divergiu dele | `estados/manifesto.yml`; `TEST-DATA.md` | Média | Sim | S6 | Aberta |
| DT-O03 | Scripts de migração e QA fora do repositório | `~/.config/aculta-homelab/migration-0.4/` e scratchpad de QA | Alta | Sim | T5 | Aberta |
| DT-O04 | Diretório de agregados do Drupal não é gravável pelo usuário de desenvolvimento | `ls -ld web/sites/default/files/css` (dono `aculta:www-data`) | Média | Não (ambiente) | S6 | Aberta |
| DT-O05 | Credenciais de teste no Runtime local, sem rotação programada | `~/.config/aculta-homelab/test-credentials.env`; `TEST-DATA.md` | Baixa | Não | S6 | Aberta |
| DT-O06 | Teste de login negado para conta bloqueada descrito como esperado, não executado | `TEST-DATA.md` | Baixa | Não | S3 | Aberta |
| DT-O07 | Config ainda com drift (`smtp.settings` e `system.mail`) | `drush config:status` | Média | Não | S3 | Aberta (já documentada) |
| DT-O08 | PRs obsoletas acumuladas | PR #63 fechada em 2026-10-09 com arquivo em `archive/pr63` | Baixa | Não | — | Resolvida |

## Governança

| ID | Dívida | Evidência | Sev. | Decisão | Fase | Estado |
| --- | --- | --- | --- | --- | --- | --- |
| DT-G01 | Política "PR por fase" versus a entrega única da P10 | `P10-R` item 8 | Baixa | Decisão registrada pelo responsável | S4 | Decisão registrada |
| DT-G02 | Regra de versionamento não cobre mudança apenas documental | `versioning.md`, seção "Mudanças apenas documentais" | Baixa | Não | T0 | Resolvida nesta revisão |
