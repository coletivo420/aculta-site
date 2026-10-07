# S3.3A — Minha Conta AJAX boundary

Data: 2026-10-06

Status: **concluída documentalmente**

## Objetivo

Definir o destino de cada comportamento hoje concentrado em
`account-navigation.js`, preservando a experiência assíncrona sem manter
indefinidamente um mini-SPA próprio.

## Estado atual

O behavior atual faz manualmente:

- `fetch()` de HTML completo;
- `DOMParser`;
- extração de fragmento;
- `innerHTML`;
- merge recursivo de `drupalSettings`;
- `detachBehaviors()` / `attachBehaviors()`;
- History API;
- loading/status;
- focus pós-navegação;
- fallback full-page;
- navegação aninhada Básicos/Endereço;
- dialog do editor de foto em behavior separado no mesmo arquivo.

## Decisão

A UX assíncrona continua.

A infraestrutura custom deve ser reduzida progressivamente usando recursos do
Drupal 11.4.

Drupal Core 11.3+ possui integração HTMX nativa, incluindo renderização de main
content para rotas marcadas com `_htmx_route: TRUE`.

Não instalar módulo contrib HTMX apenas para obter capacidades que já existem
no Core.

## Matriz

| Fluxo | Estado atual | Destino preferido |
| --- | --- | --- |
| navegação top-level da Conta | fetch documento | Core HTMX/fragment route após prova de conceito |
| Básicos -> Endereço | fetch subfragmento | Core HTMX ou Form/route fragment |
| back/forward | popstate custom | história nativa HTMX quando adotado |
| title | parse document.title | resposta/headers/pattern Core |
| focus | JS manual | preservar explicitamente após swap |
| aria-busy/status | JS manual | preservar como contrato de a11y |
| drupalSettings | merge manual | deixar Core cuidar quando possível |
| Drupal behaviors | detach/attach manual | integração Core |
| CEP | JS específico | **permanece** integração CEP contrib |
| photo dialog | JS behavior | permanece progressive enhancement |
| Forms | HTML dentro do fragmento | Form API continua fonte |
| OAuth | link/redirect | **fora do HTMX genérico** |
| checkout/payment | Commerce | **fora do HTMX genérico** |

## Estratégia de migração

### A0 — baseline

Não alterar o JS até Runtime.

### A1 — prova de conceito isolada

Escolher uma seção de leitura simples, preferencialmente Cursos ou Dashboard,
sem Form API sensível.

Criar/ajustar rota fragmentável e testar Core HTMX.

### A2 — top-level navigation

Substituir somente a navegação entre seções.

Preservar:

- URL;
- history;
- page title;
- aria-current;
- aria-busy;
- loading;
- status;
- focus;
- full-page fallback.

### A3 — Básicos/Endereço

Migrar a troca aninhada só depois de A2.

Validar:

- Profile form;
- Address;
- CEP;
- form errors;
- submit;
- behavior reattachment.

### A4 — remover infraestrutura custom

Somente quando não houver consumidor:

- requestDocument;
- mergeSettings;
- DOMParser;
- innerHTML swap;
- popstate custom.

### A5 — separar photo editor

O dialog não depende do transporte da Conta.

Quando o arquivo de navegação puder ser removido, mover o behavior do photo
editor para asset próprio do componente/pattern responsável.

## Core HTMX

No Drupal 11.3+, rotas destinadas a HTMX podem usar:

`_htmx_route: TRUE`

para responder somente com main content.

Antes de usar, validar no Apache Homelab que request/response headers HTMX não
são filtrados.

## O que não fazer

- instalar uma segunda camada SPA;
- React/Vue para a Conta;
- interceptar OAuth;
- interceptar Commerce checkout;
- transformar todos os forms em requests custom;
- remover fallback normal;
- fazer big-bang replacement de `account-navigation.js`.

## Gates Runtime

- Apache preserva HX headers;
- URL/history/back/forward;
- title;
- keyboard/focus;
- screen reader status;
- Drupal behaviors;
- drupalSettings necessários;
- Profile/Address/CEP;
- security forms;
- Connections/OAuth;
- Courses;
- Support;
- photo editor;
- full-page fallback;
- BigPipe.

## Resultado

A fase S3.3A fecha a decisão de arquitetura.

A implementação pertence ao Portal 0.15 — AJAX Consolidation e deve ocorrer
depois dos presenters/SDCs essenciais e com Runtime disponível.

## Próxima macrofase sem Runtime

S4 — especificações das versões 0.11–0.18.
