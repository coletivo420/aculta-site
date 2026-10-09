# Roadmap — ACULTA420

Atualizado em 2026-10-09 pela revisão documental. Dívidas e pendências, com evidência,
estão em [`docs/operations/DEBT-REGISTER.md`](../../../../../docs/operations/DEBT-REGISTER.md).
Este roadmap planeja o saneamento primeiro e as features de produto depois. Não planeja 1.0.

## Releases e marcações

| Versão | Estado | Marcação | Conteúdo |
| --- | --- | --- | --- |
| 0.1.0 | concluída (2026-10-07) | marcada no código | Foundation: provider, namespace, tokens, fronteiras |
| 0.2.0 | concluída (2026-10-09) | marcada no código | Shell multidomínio: Domain Presentation, Institution Bar, Domain Header, sticky, QA |
| 0.3.0 | concluída (2026-10-09) | marcada no código | Card System v1: `editorial-card` stable, `project-card` experimental, skin do curso |
| 0.3.1 | concluída (2026-10-09) | marcada no código | Padrões SDC: seções, hero, grade, carrossel e cabeçalho por purpose |
| 0.4.0 | linha 0.4.x fechada em 0.4.5 (2026-10-09), sem versão 0.4.0 retroativa (decisão do responsável) | sem tag | Patterns v1; T1 a T6 com decisão ou conclusão registradas (T5 com reconstrução em ambiente novo DEFERRED) |
| 0.4.6-dev.1 | validador de navegador: regras de barra e sigla ajustadas ao shell; estado indisponível do apoio aceito; PASS no servidor de testes | marcada no código |
| 0.4.5 | fechamento da linha 0.4.x (2026-10-09): 0.4.4 (PR #122), 0.4.5-dev.1 a dev.5 (PRs #124, #129, #130 e o dev.5); DT-T18 pendente, independente do tema | marcada no código (release) |
| 0.4.5-dev.5 | sitemap do validador de navegador delegado ao `aculta_deployer sitemap`; DT-T18 pendente (independente do tema) | marcada no código |
| 0.4.5-dev.4 | validador de navegador: página de apoio no host SUPPORT (DT-T18) | marcada no código |
| 0.4.5-dev.3 | cards de projeto: imagem 256 px com 2x e conteúdo centralizado | marcada no código |
| 0.4.5-dev.2 | imagem dos cards de projeto e grade responsiva (CSS entregue pela biblioteca global) | marcada no código |
| 0.4.5-dev.1 | kit de artes Bloco Sativa 420 (sem consumidor de template; autoria e licença pendentes) | marcada no código | Kit validado offline; QA em navegador DEFERRED |
| 0.4.4-dev.1 | gates executados no runtime e correção do gate institucional | marcada no código |
| 0.4.3 | PR #115 mesclada (kit Baque Sativa; sem consumidor de template) | marcada no código | Kit validado offline; QA visual e runtime DEFERRED |
| 0.4.2 | PR #114 mesclada (assets Podplant420; handoff fora do tema, sem consumidor de template) | marcada no código | Assets de identidade Podplant420 validados offline; QA visual e runtime DEFERRED |

Regras de versão em [`docs/versioning.md`](versioning.md). A versão atual fica marcada em
`aculta420.info.yml`. Tags Git só são criadas sob pedido do responsável.

## Saneamento

### T0 — Higiene documental (DT-T01, DT-T02, DT-T14) — concluída em 0.4.0-dev.2

- Remover "0.1.0" como versão atual em `README.md` e `docs/README.md` (`docs/features.md` já foi removido),
  `docs/design-system.md` e `docs/development.md`. Manter menção histórica quando for histórica.
- Descrever o estado atual do shell (0.2.0 em diante), e não a Foundation.
- Critério: nenhum documento afirma "versão atual 0.1.0"; o gate de documentação passa.

### T1 — Fechar a linha 0.4.0 (DT-T03) — decisão registrada em 0.4.0-dev.2

Estruturas internas permanecem como rich text (ver `components.md`, decisão T1). T2 e T3 foram concluídas em 0.4.0-dev.4 e 0.4.0-dev.5, então a condição de fechamento da linha está cumprida. Como o tema já está em 0.4.3, fechar a linha com a versão `0.4.0` seria um retrocesso de versão; o fechamento sem versão retroativa depende de decisão do responsável.

- Decidir, para cada estrutura interna, entre migrar para SDC ou manter como conteúdo rico:
  `aculta-areas`, `aculta-callout`, `aculta-page-intro`, CTAs (`aculta-editorial-link`) e
  `aculta-institutional-note`.
- Critério (decisão do responsável, 2026-10-09): decisão registrada por estrutura; a linha 0.4.x fecha em `0.4.5` sem versão `0.4.0` retroativa e sem tag. Cumprido.

### T2 — Validação em navegador (DT-T04, DT-T05, DT-T06) — concluída em 0.4.0-dev.4

- Carrossel validado em navegador e reativado na home em 0.4.0-dev.3 (DT-T04).
- Rail validado com quatro cursos reais publicados em 0.4.0-dev.4 (DT-T05): rolagem por teclado,
  sem rolagem horizontal da página e todos os cards alcançáveis por Tab. Os cursos de teste foram
  removidos após a medição.
- Foco por teclado medido com foco real (DT-T06): o card do LMS tinha só a borda amarela, com cerca
  de 1,6:1 sobre superfície clara; recebeu anel com `--aculta-focus-ring`.
- Rótulos em inglês corrigidos (DT-T15) em 0.4.0-dev.3.
- Critério cumprido: cada item com medição registrada no changelog, sem clones no DOM.

### T3 — CSS residual dos padrões (DT-T07) — concluída em 0.4.0-dev.5

- `.aculta-hero` e `.aculta-eyebrow` saíram de `institutional.css` para `components/patterns/hero/hero.css`.
- `.aculta-section-title` saiu de `content.css` para `components/patterns/content-section/content-section.css`.
- `.aculta-project` (moldura do presenter) e seus filhos saíram de `institutional.css`, `content.css` e `drupal-bootstrap.css` para `components/content/project-card/project-card.css`.
- Medição antes/depois: estilo computado idêntico por propriedade em 17 seletores de controle e padrão; 14 capturas de tela idênticas byte a byte (1280 e 390 px, 7 páginas); foco e movimento reduzido do card idênticos.
- Os seletores compartilhados por `.card` continuam globais em `content.css`, porque ainda não têm consumidor de SDC.

### T4 — Validação automatizada (DT-T08, DT-T09) — concluída em 0.4.1

- Schemas dos SDCs validados contra props e slots usados nos templates (`validate-aculta420-sdc-schemas.php`, DT-T08).
- Validadores `.mjs` sem porta nem origem fixas: leem `ACULTA_DEVTOOLS_PORT` e `ACULTA_SITE_ORIGIN` por `scripts/lib/browser-env.mjs`. Sem as variáveis, o validador para antes de conectar (DT-T09).
- Gate `validate-browser-validators.php` reprova endpoint literal nos scripts.
- Critério: schema inválido quebra o gate; nenhuma porta fixa nos scripts. Cumprido.
- Dívida nova: `validate-institution-browser.mjs` espera `/apoie` no host principal e `aculta_favicon.ico`. A página de apoio fica no host SUPPORT (homelab `apoio.aculta.toca.net.br`; produção `apoio.aculta.org`), na rota `/apoio` (Portal 0.2.0-dev.3). Falta a reescrita de `supportLayout` e o favicon oficial (DT-T18, pendente e independente do tema).

### T5 — Portabilidade do conteúdo (DT-T10, DT-O03) — concluída em 0.4.1, reconstrução em ambiente novo DEFERRED

- Conteúdo de seções, missão, cabeçalho de projetos e hero declarado em `scripts/content/institution/home-content.json`, com exportador e loader por UUID, dry-run por padrão (`ACULTA_APPLY=1` para gravar).
- Gate `validate-institution-content.php`: o JSON cobre todo UUID referenciado pelas colocações da home e do cabeçalho.
- Dry-run no Runtime atual: 14 entidades sem diferença. Teste negativo: alteração do slogan detectada.
- DEFERRED: reconstrução completa num ambiente novo. O único snapshot em `estados/` é de 2026-10-04 e não tem os campos de seção nem do hero; a verificação exige importar a configuração num ambiente de teste, o que fica para uma tarefa autorizada.
- Critério parcial: versionado e verificável; reconstrução em ambiente novo pendente.

### T6 — Dependência do LMS (DT-T11) — concluída na PR #113 (merge 0.4.1)

- A skin do `lms:course_card` não define nem consome mais as variáveis `--color-*` do módulo. Propriedades com tokens ACULTA, mesmos seletores e cascata.
- Medição: sem diferença de estilo computado no catálogo (padrão e foco, 1280 e 390 px); capturas idênticas.
- Gate `validate-lms-skin.php`: fixa a versão do LMS revisada (1.2.3). Upgrade reprova até revisão da skin e da captura de referência.
- Decisão: cores de status do LMS ficam como fallback do módulo.
- Critério cumprido para o que é visível a visitante; estados de status pendentes (ver CHANGELOG 0.4.1).

## Features de produto (depois do saneamento)

Só começam quando T0–T4 estiverem fechadas, ou quando o responsável priorizar explicitamente.
Nenhuma destas fases tem versão alvo de 1.0.

### F1 — Modo de cor e controle de aparência (DT-T12)

- Seleção claro, escuro e automático com `data-bs-theme`; preferência persistente para usuário
  autenticado na camada de conta, e local e sem quebra de cache para anônimo.
- Pré-requisito: T2 (validação do modo escuro em navegador real).

**Estado (2026-10-09): em avaliação; sem código.** A linha 0.4.x está fechada; a F1 começa depois, em fase própria. Achados:

- Já existe: `css/tokens.css` com os blocos `:root, [data-bs-theme="light"]` e `[data-bs-theme="dark"]`, e o contrato de superfície dark validado pelo gate de design. Falta o controle.
- Não existe: seletor de modo, JavaScript de troca, persistência nem leitura de `prefers-color-scheme`. O gate de design bloqueia esses itens até a fase prevista, então a F1 precisa atualizar o gate junto.
- Pré-requisito não atendido: T2 validou carrossel, rail e foco no modo claro. Nenhuma medição de modo escuro em navegador foi feita. É o primeiro passo da F1.
- Persistência para conta: a preferência do usuário autenticado pertence à camada de conta (`aculta_portal`, linha separada). Não há campo nem storage de preferência de aparência. Decisão necessária antes de implementar: onde guardar (campo de usuário ou dados do usuário via API do Core) e como o Portal entrega o modo ao tema pelo contrato neutro, sem o tema ler usuário ou configuração.
- Anônimo (proposta, não decidida): `localStorage` com try/catch, sem alterar o HTML em cache; o modo seria aplicado por `data-bs-theme` no `<html>`.
- Próximo passo: autorização explícita do responsável para tocar `web/themes/custom/aculta420/**` e decisão sobre a persistência do usuário autenticado.

### F2 — Ícones (DT-T13)

- Ícones pela API oficial (Core Icon API, UI Icons ou Bootstrap Icons), sem SVG espalhado.
- Pré-requisito: decisão de dependência, com justificativa.

### F3 — Busca e feedback (DT-T13)

- Apresentação de busca com Search API e Autocomplete; mensagens por toast e alert com
  Messenger. Backend e índices ficam no Portal e no Core.
- Pré-requisito: F2 e decisão de backend (ver Portal F2).

### F4 — Padrões de Drupal UI e biblioteca de componentes (DT-T13)

- UI Patterns para Views e Manage Display onde houver consumidor real.
- Biblioteca navegável dos componentes (UI Patterns Library ou UI Examples), com estados
  documentados.
- Pré-requisito: T4 (schemas validados).

### F5 — Mega menu

- Só depois de a navegação base estar estável em produção.

## Critério para encerrar o saneamento do tema

Todos os itens de DEBT-REGISTER do tema estão em Resolvida, Decisão registrada ou Aberta com
justificativa aceita pelo responsável. Os gates passam. Não há dívida nova sem registro.
