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
| 0.4.0 | linha aberta, marcada em `0.4.0-dev.5` | marcada no código | Patterns v1; T2 e T3 concluídas; falta fechar T1 (decisões de estruturas internas) |

Regras de versão em [`docs/versioning.md`](versioning.md). A versão atual fica marcada em
`aculta420.info.yml`. Tags Git só são criadas sob pedido do responsável.

## Saneamento

### T0 — Higiene documental (DT-T01, DT-T02, DT-T14) — concluída em 0.4.0-dev.2

- Remover "0.1.0" como versão atual em `README.md` e `docs/README.md` (`docs/features.md` já foi removido),
  `docs/design-system.md` e `docs/development.md`. Manter menção histórica quando for histórica.
- Descrever o estado atual do shell (0.2.0 em diante), e não a Foundation.
- Critério: nenhum documento afirma "versão atual 0.1.0"; o gate de documentação passa.

### T1 — Fechar a linha 0.4.0 (DT-T03) — decisão registrada em 0.4.0-dev.2

Estruturas internas permanecem como rich text (ver `components.md`). A linha 0.4.0 fecha quando T2 e T3 forem concluídas.

- Decidir, para cada estrutura interna, entre migrar para SDC ou manter como conteúdo rico:
  `aculta-areas`, `aculta-callout`, `aculta-page-intro`, CTAs (`aculta-editorial-link`) e
  `aculta-institutional-note`.
- Critério: decisão registrada por estrutura; a linha 0.4.0 fecha com `0.4.0` e tag final.

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

### T4 — Validação automatizada (DT-T08, DT-T09) — schemas concluídos em 0.4.0-dev.2

- Validar schemas dos SDCs contra os props e slots usados nos templates.
- Parametrizar a porta DevTools dos validadores `.mjs` (hoje fixa em 9223).
- Critério: schema inválido quebra o gate; nenhuma porta fixa nos scripts.

### T5 — Portabilidade do conteúdo (DT-T10, DT-O03)

- Versionar a migração de conteúdo das seções, do hero e do cabeçalho de projetos, sem
  credenciais, em `scripts/migrations/` ou equivalente, com dry-run e documentação.
- Critério: um ambiente novo reproduz o conteúdo esperado a partir do Git e dos scripts.

### T6 — Dependência do LMS (DT-T11)

- A skin do `lms:course_card` depende de variáveis internas do módulo `lms`. Definir teste de
  regressão visual e revisar a skin em cada upgrade do LMS.
- Critério: captura de referência do catálogo e checagem no upgrade.

## Features de produto (depois do saneamento)

Só começam quando T0–T4 estiverem fechadas, ou quando o responsável priorizar explicitamente.
Nenhuma destas fases tem versão alvo de 1.0.

### F1 — Modo de cor e controle de aparência (DT-T12)

- Seleção claro, escuro e automático com `data-bs-theme`; preferência persistente para usuário
  autenticado na camada de conta, e local e sem quebra de cache para anônimo.
- Pré-requisito: T2 (validação do modo escuro em navegador real).

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
