# Registro de dívidas e pendências

Atualizado em 2026-10-09 pela revisão documental do `aculta_portal` e do tema `aculta420`.

Este é o registro único de dívidas históricas e pendências de desenvolvimento. Os roadmaps
(`docs/portal/ROADMAP.md` e `web/themes/custom/aculta420/docs/roadmap.md`) planejam as
fases de saneamento a partir daqui. Não há item de lançamento de versão 1.0 neste registro.

## Documentos removidos (limpeza de 2026-10-09)

Evidências citadas abaixo como "P10-R", "RELEASE-P10", "HANDOFF", "HARDENING-P9",
"DEPRECATION-MATRIX-P8" e os documentos de produto (Fórum, Revista, Loja, Wiki, Conta) foram
removidos do working tree. O último commit em que existem é `9c95420`. Para ler um deles:

```sh
git show 9c95420:docs/portal/P10-R-FINAL-AUDIT.md
```

As regras que sobreviveram estão em `docs/portal/GUARDRAILS.md`.

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
| DT-P02 | Arquivo sem `declare(strict_types=1)` | `src/Hook/PortalHooks.php` com `declare(strict_types=1)`; gate do Portal PASS (470 checagens) | Baixa | Não | S2 | Resolvida |
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
| DT-P20 | 20 rotas públicas de contrib com termo em inglês ou ID numérico (após DT-P21) (LMS, Group, Social Auth, Profile, Change Mail, Webform, Diff, Email Confirmer), por exemplo `/user/{user}/...` e `/group/{group}/...` | `scripts/validate-public-slugs.php`, linha de base `$contribBaseline`; só pode diminuir | Média | Não | P | Aberta |
| DT-P21 | Lições e atividades do LMS usavam posições numéricas na URL (`/course/1/0/1`) | rota contrib `course/{group}/{lesson_delta}/{activity_delta}` substituída pelo Portal (`LmsFriendlyRouteSubscriber`): `/curso/{curso}/{lição}/{atividade}`, com slugs derivados dos títulos (`src/Lms/`); conversão de parâmetros e processador de saída; sem campos novos | Média | Sim (desenho: slugs derivados do curso e do título da lição) | P | Resolvida em 0.2.0-dev.1 (branch `feat/portal-lms-friendly-routes`) |
| DT-P22 | URLs numéricas antigas `/course/{id}/{lição}/{atividade}` respondem 404; não há redirecionamento 301 para as novas URLs | `LmsFriendlyRouteSubscriber`: rota antiga removida sem alias | Baixa | Não | P | Resolvida: decisão do responsável, sem redirecionamento até a versão estável (404) |
| DT-P23 | Sitemap sem o host de apoio: o simple_sitemap gera um sitemap único e não aceita URL absoluta de outro host em custom links | `simple_sitemap` 4.2.3 valida custom links como rota interna; o host SUPPORT (`apoio.aculta.org`) não aparece no sitemap. Alternativas pesquisadas: Domain Simple XML Sitemap 3.0.0-rc3 (release candidate, manutenção mínima) e rota técnica própria no Portal. Registro de correção: DEP-0002 em `modules/aculta_deployer/registry`. Resolvida no servidor de testes pela microfase 0.1.0-G (índice `/sitemap.xml` com os cinco filhos; decisão: `aculta_portal_sitemap`, não Domain Simple XML Sitemap). Produção pendente (DEP-0001/0002/0003) | Média | Sim (escolher entre módulo por domínio e rota técnica própria) | P | Parcial: teste resolvido, produção aberta |
| DT-P19 | Credencial root exposta no chat | `P10-R` item 7 e seção 14: rotacionada pelo responsável em 2026-10-09 | Alta | Não | — | Resolvida (informada pelo responsável) |

## Tema (`aculta420`)

| ID | Dívida | Evidência | Sev. | Decisão | Fase | Estado |
| --- | --- | --- | --- | --- | --- | --- |
| DT-T01 | Documentação dizendo "0.1.0" como versão atual | `README.md`, `docs/README.md`, `docs/features.md`, `design-system.md`, `development.md` e `architecture.md` corrigidos (0.4.0-dev.2) | Média | Não | T0 | Resolvida |
| DT-T02 | Regra de versão de `info.yml` vs subversões `-dev` não escrita | `aculta420.info.yml` = 0.3.1 com tag `0.4.0-dev.1` | Baixa | Não | T0 | Resolvida nesta revisão (ver `versioning.md`) |
| DT-T03 | Estruturas internas em rich text (`aculta-areas`, `aculta-callout`, `aculta-page-intro`, CTAs, `aculta-institutional-note`) | decisão em `components.md`: permanecem como conteúdo rico | Média | Decisão registrada | T1 | Decisão registrada |
| DT-T04 | Carrossel da home não validado em navegador | validado e reativado em 0.4.0-dev.3 (placement `status: true` versionada) | Média | Não | T2 | Resolvida |
| DT-T05 | Rail de cursos validado só com clones no DOM | validado com quatro cursos reais publicados (temporários, removidos após a medição) em 0.4.0-dev.4: rolagem por teclado (151 px em 1280, 310 px em 390), sem rolagem horizontal da página, todos os cards alcançáveis por Tab | Baixa | Não | T2 | Resolvida |
| DT-T06 | Foco por teclado visível não observado em janela com foco real | medido com foco real (Chromium headless, `Emulation.setFocusEmulationEnabled`): shell e rail com contorno sólido de 3px; o card do LMS tinha só a borda amarela (~1,6:1, abaixo de 3:1 da WCAG 1.4.11) e recebeu anel com `--aculta-focus-ring` em 0.4.0-dev.4 (claro: 3px verde escuro; escuro: 3px amarelo) | Média | Não | T2 | Resolvida |
| DT-T07 | CSS residual de cards e hero fora dos SDCs | movido para `components/patterns/hero/hero.css`, `components/patterns/content-section/content-section.css` e `components/content/project-card/project-card.css` em 0.4.0-dev.5; estilo computado por propriedade e capturas de tela idênticos antes e depois | Média | Não | T3 | Resolvida |
| DT-T08 | Validação automatizada de schemas SDC ausente | `scripts/validate-aculta420-sdc-schemas.php`: 7 componentes, 6 chamadas, 98 checagens; reprova variável não declarada (testado com caso negativo) | Média | Não | T4 | Resolvida |
| DT-T09 | Validadores de navegador acoplados à porta DevTools 9223 e à origem `localhost:8080` | `scripts/*.mjs`; corrigido com `scripts/lib/browser-env.mjs` (`ACULTA_DEVTOOLS_PORT`, `ACULTA_SITE_ORIGIN`) e gate `validate-browser-validators.php` (19 checagens, caso negativo reprovado) em 0.4.1 | Baixa | Não | T4 | Resolvida |
| DT-T18 | `validate-institution-browser.mjs` espera `/apoie` com canonical `https://aculta.org/apoie` e favicon `aculta_favicon.ico`. Informado pelo responsável: a página de apoio foi movida para o host SUPPORT (homelab `apoio.aculta.toca.net.br`; produção `apoio.aculta.org`), por isso `/apoie` no host principal não é mais a referência. O runtime responde 404 em `/apoie` e serve `aculta420-favicon.ico` | `scripts/validate-institution-browser.mjs` (linhas de `supportLayout` e `faviconUrl`); execução em 0.4.1. Pendente: (1) reescrever `supportLayout` para o host SUPPORT, sem `fetch` cross-origin a partir de `aculta.org`, com canonical comparado por host; (2) rota da página de apoio confirmada em `/apoio` (front do host SUPPORT e rota do Portal; `/apoie` redireciona 301), aplicada no Portal 0.2.0-dev.3 (execução em runtime DEFERRED); (3) informar o favicon oficial. Em 0.4.5-dev.5 o sitemap passa a ser verificado pelo `aculta_deployer sitemap` (PASS no servidor de testes; `DEP-0002` no registro do deployer). Permanecem: botão de apoio com gateway ativa (servidor de testes sem gateway) e favicon oficial. Pendente independente do tema, até concluir o sistema de apoios | Média | Sim (favicon oficial; ativação do gateway) | Apoios (independente do tema) | Pendente |
| DT-T10 | Conteúdo das seções, hero e cabeçalho existia só no Runtime | conteúdo declarado em `scripts/content/institution/home-content.json` (UUID), loader com dry-run e gate `validate-institution-content.php` (0.4.1); dry-run no Runtime sem diferença. Pendente: reconstrução em ambiente novo (snapshot em `estados/` anterior aos campos) | Alta | Sim (versionar migração) | T5 | Parcial (DEFERRED: ambiente novo) |
| DT-T11 | Skin do card de curso depende de variáveis internas do LMS | `course-card.css` não define nem consome mais `--color-*`; propriedades com tokens ACULTA; medição sem diferença no catálogo; gate `validate-lms-skin.php` fixa LMS 1.2.3 e reprova upgrade até revisão (0.4.1). Cores de status do LMS ficam como fallback do módulo (decisão). Estados de status não medidos (visitante anônimo) | Média | Não | T6 | Resolvida |
| DT-T12 | Modo de cor e troca light/dark/auto ausentes | concluída em 0.5.0 (F1): Portal 0.2.0-dev.17/18 e tema 0.5.0; validada no Runtime oficial (2026-10-09) | Baixa | Não | F1 | Resolvida |
| DT-T13 | Ícones, busca, feedback, UI Patterns e biblioteca de componentes sem adoção | Ícones resolvidos em 0.5.1 (F2, Bootstrap Icons sem dependência nova). Busca (F3): backend de banco e página `/busca` entregues no Portal 0.2.0-dev.19; pendências de `content_access` e testes. UI Patterns e biblioteca (F4): UI Patterns 2.0.22 com biblioteca e 9 stories em 0.6.0-dev.1; consumidores em Views e Manage Display pendentes de uso real | Baixa | Sim (módulo da F4) | F3, F4 | Aberta (parcial) |
| DT-T15 | Rótulos em inglês no carrossel e na Wiki ("Next Slide", "Carousel", "Read more", "Skip to main content") | corrigidos em 0.4.0-dev.3: catálogo `translations/aculta420.pt-br.po` e JS do carrossel; medido no DOM | Média | Não | T2 | Resolvida |
| DT-T16 | Catálogos de tradução do tema (`aculta420.pt-br.po`) e do LMS (`lms.pt-br.po`) precisam ser importados em cada ambiente | Verificado no runtime local em 0.4.4: `drush locale:import --type=customized --override=none pt-br <arquivo>` é idempotente (13.013 traduções pt-br antes e depois; "Next Slide" e "Skip to main content" aplicadas). Em cada ambiente novo, executar o mesmo comando para os dois catálogos e conferir as strings | `drush locale:import` (ver `translations/`) | Média | Não | T5 | Resolvida (runtime local); ambientes novos seguem o procedimento |
| DT-T17 | 331 strings do LMS (contrib) estavam em inglês | corrigido em 0.4.0-dev.3 via `translations/lms.pt-br.po`; a extração é por expressão regular, então pode haver strings em Twig/JS não capturadas | Média | Não | T2 | Resolvida (verificar em nova release do LMS) |
| DT-T14 | Foundation de 0.1.0 descrita como "preserva o shell existente" em documentos atuais | `architecture.md` e `development.md` reescritos (0.4.0-dev.2) | Baixa | Não | T0 | Resolvida |

## Operação e Runtime

| ID | Dívida | Evidência | Sev. | Decisão | Fase | Estado |
| --- | --- | --- | --- | --- | --- | --- |
| DT-O01 | 100 branches remotas; 64 já mescladas na `main` | `git branch -r --merged origin/main` | Baixa | Sim (apagar exige autorização) | S6 | Aberta |
| DT-O02 | Snapshot de `estados/` é de 2026-10-04 e o Runtime já divergiu dele | `estados/manifesto.yml`; `TEST-DATA.md` | Média | Sim | S6 | Aberta |
| DT-O03 | Scripts de migração e QA fora do repositório | migração de conteúdo versionada (ver DT-T10); scripts pontuais de migração continuam fora do Git por já terem sido aplicados; scratchpad de QA segue fora | Alta | Sim | T5 | Parcial |
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

## Rodada de saneamento (2026-10-09)

Estado após a rodada. Só o que foi verificado está como resolvido.

- **Resolvida:** DT-P02 (`strict_types` em `PortalHooks.php`); DT-T12 (F1, 0.5.0); DT-T13 parcial (ícones em 0.5.1; busca pelo banco e biblioteca pendentes).
- **Bloqueio concreto, decisão do responsável:** DT-T10 / DT-T05 / DT-O03. Ambiente novo a partir de `config/sync`: o `site:install --existing-config` falha, e o `config:import` após instalar com o perfil `standard` também. Causa: `cep_autocomplete` (contrib, sem canal de log próprio) referencia `logger.channel.cep_autocomplete`, que só existe quando `aculta_portal` está ativo, e o módulo é ativado no mesmo lote. O Portal declara `cep_autocomplete` como dependência, e nenhuma configuração versionada usa o módulo, mas removê-lo muda o comportamento de CEP em produção. Opções: (a) definir o canal em um arquivo de serviços do site carregado sempre; (b) remover a dependência do Portal, após confirmar que o preenchimento de CEP não é usado; (c) manter e instalar em duas etapas. Recomendação: (a), por não mexer em contrib nem no comportamento.
- **Pendente de insumo do responsável:** DT-T18 (favicon oficial e credencial de sandbox da gateway). A credencial do Mercado Pago fica adiada a pedido do responsável (DT-P07).
- **Pendente de decisão de escopo:** DT-P10 (módulos de administração em produção), DT-P12 (escopo da conta), DT-P13 (adoção de busca, engajamento e fórum; a busca já tem decisão pelo banco), DT-P23 (sitemap de apoio: módulo por domínio ou rota técnica), DT-T13 (módulo da biblioteca na F4).
- **Pendente de autorização explícita:** DT-O01 (apagar 64 branches remotas já mescladas), DT-O02 (regerar o snapshot versionado em `estados/`).
- **Pendente de ambiente com privilégio:** DT-O04 (diretório de agregados do Drupal com dono `aculta:www-data`), DT-P04 (PHP 8.5 não instalado neste host).
- **Pendente de operação:** DT-O05 (rotação de credenciais de teste), DT-P05 e DT-O07 (e-mail em produção e drift de `smtp.settings` e `system.mail`, dependem de credenciais SMTP e de deploy).
- **Pendente de implementação maior:** DT-P01 (classes `aculta-*` no módulo, com ajuste visual), DT-P03 (testes de Kernel para P5–P7), DT-P09 (modelo HTML do webform), DT-P08 (login com CAPTCHA por HTTP), DT-P11, DT-P20 (slugs de rotas de contrib), DT-O06 (teste de conta bloqueada).

