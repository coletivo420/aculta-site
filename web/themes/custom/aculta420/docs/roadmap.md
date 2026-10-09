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
| 0.4.0 | linha aberta, marcada em `0.4.0-dev.1` | marcada no código | Patterns v1; falta fechar estruturas internas |

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

### T2 — Validação em navegador (DT-T04, DT-T05, DT-T06) — carrossel validado; rail com cursos reais pendente

- Ativar a view do carrossel no Runtime (ou criar um bloco de teste) e validar em navegador:
  rolagem, teclado, reduced motion.
- Validar o rail com cursos reais (hoje só há um curso publicado).
- Foco por teclado em janela com foco real, para o anel de foco de tokens.
- Carrossel: validado em navegador e reativado na home em 0.4.0-dev.3.
- Rótulos em inglês corrigidos (DT-T15).
- Rail: validado com o curso real; falta validar a rolagem com vários cursos (DT-T05).
- Critério: cada item com captura e medição, sem clones no DOM.

### T3 — CSS residual dos padrões (DT-T07)

- Mover a visual de `.aculta-project`, `.aculta-hero` e `.aculta-section-title` para os SDCs,
  ou registrar por que ficam globais.
- Critério: nenhum seletor de padrão migrado fica sem dono; visual idêntico medido em captura.

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
