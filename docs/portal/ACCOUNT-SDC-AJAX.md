# Minha Conta — matriz SDC, integrações e AJAX

Data: 2026-10-06

## Objetivo

Definir explicitamente como a experiência **Minha Conta** evolui para o
ACULTA Bootstrap Component Design System sem confundir:

- **fonte de verdade**;
- **integração/presenter do Portal**;
- **componente visual SDC**;
- **comportamento AJAX**.

Virar SDC não significa deixar de ser AJAX.

O SDC define apresentação e estados visuais. AJAX/HTMX/Form API continua sendo
a camada de interação quando a atualização parcial melhora a experiência.

## Regra de camadas

```text
fonte de verdade
      ↓
aculta_portal
access + cache + presenter + URL/purpose
      ↓
SDC do tema aculta
      ↓
Bootstrap 5 + tokens ACULTA
      ↓
Drupal AJAX / Views AJAX / Form API / HTMX
quando houver comportamento assíncrono
```

## Matriz da Conta

| Área | Fonte de verdade | Integração Portal | SDC candidato | AJAX / comportamento |
| --- | --- | --- | --- | --- |
| Shell da Conta | rotas/menu Drupal | AccountShellBuilder | `account-shell` | navegação entre seções continua progressivamente assíncrona; link normal é fallback |
| Identidade | Drupal User + Profile | presenter de identidade | `account-identity`, `avatar`, `summary-card` | conteúdo pode ser trocado junto do painel; nenhuma API própria |
| Foto | User picture + Crop/Image Widget Crop | form integration | `photo-editor` | diálogo JS permanece; upload/crop continua Form API/widget contrib; fallback sem JS obrigatório |
| Resumo de cursos | LMS + Group | AccountCoursesManager | `summary-card`, `status-badge` | carregado com a seção; não precisa de AJAX próprio |
| Meus cursos | LMS + Group | course presenter | `course-card`, `progress`, `empty-state` | lista pode usar Views AJAX no futuro; entrar/continuar curso continua link normal |
| Meus dados | Profile participante | data-section presenter | `data-section`, `action-list` | troca Básicos/Endereço continua assíncrona; submit permanece Form API |
| Endereço | Commerce customer Profile + Address | Profile/Address integration | `data-section`, `form-feedback` | CEP continua AJAX/JS específico; submit do endereço continua Form API |
| CEP | CEP Autocomplete/ViaCEP | adaptação acessível ACULTA | feedback/status visual reutilizável | **permanece AJAX** via endpoint contrib; stale-response, aria-live e focus permanecem |
| Conexões | Social Auth + Drupal User | `AccountConnectionsPresenter` | `integration-card`, `status-badge`, `action-list` | painel pode carregar assíncrono; OAuth redirect/callback não deve ser convertido em AJAX genérico |
| Google Login | Social Auth Google | integração configurável | `integration-card` | OAuth continua navegação/redirect completa; não interceptar handshake |
| Segurança | Drupal User | `AccountSecurityPresenter` | `security-card`, `alert`, `status-badge` | painel pode carregar assíncrono; alteração de senha continua Form API |
| E-mail | Change Mail + Email Confirmer | status/pending presenter | `security-card`, `pending-state` | solicitação é Form API; confirmação por e-mail continua fluxo externo |
| Meu Apoio | Commerce Order/Payment | support presenter | `support-summary`, `order-card`, `status-badge`, `empty-state` | painel pode carregar assíncrono; paginação/filtro futuro pode usar Views AJAX |
| Apoiar | Commerce Donation Flow | entrada/links | `action-card` | checkout/pagamento segue Commerce; não envolver o fluxo financeiro em fetch próprio |
| Minha participação | Forum + Comment + Wiki + LMS | participation aggregator | `participation-card`, `activity-list`, `empty-state` | **Views AJAX preferido** para filtros/listas |
| Meus tópicos | Forum/Node | presenter/View | `forum-topic-card` | Views AJAX para paginação/filtros |
| Minhas respostas | Comment | presenter/View | `participation-item` | Views AJAX para paginação/filtros |
| Contribuições Wiki | Node/Revisions | presenter/View | `wiki-contribution-card` | Views AJAX para listagem/filtros |
| Favoritos/seguir | Flag | adapter mínimo | `follow-action`, `status-badge` | usar comportamento AJAX do Flag quando adotado; não criar toggle próprio |
| Notificações | Comment Notify | integração/preferences | `notification-preference` | usar forms/APIs do módulo; não criar transporte próprio |

## Semântica compartilhada

Status, ações, empty states, summaries e action lists seguem o contrato de
[ACCOUNT-PRESENTATION-MODEL.md](ACCOUNT-PRESENTATION-MODEL.md).

Esses contratos não autorizam automaticamente novos SDCs. A maturidade visual
continua sob o roadmap H3/H4 do tema.

## O que vira SDC primeiro

Ordem preferencial:

1. validar semanticamente status/empty/summary/action no Portal;
2. `course-card` quando o tema H4 fornecer o contrato visual;
3. promover status/empty/summary/action a SDC somente se a reutilização real justificar;
6. `account-shell`;
7. `account-identity`;
8. `data-section`;
9. `security-card`;
10. `integration-card`;
11. `support-summary/order-card`;
12. componentes de participação;
13. `photo-editor` por último entre os elementos atuais, por ser mais sensível.

A ordem prioriza componentes simples e reaproveitáveis antes de componentes
com Form API, dialogs ou JS específico.

## O que permanece AJAX

### Mantém comportamento assíncrono

- navegação entre seções da Conta;
- troca Básicos / Endereço;
- CEP;
- listagens futuras de participação;
- paginação/filtros de Views;
- toggles fornecidos por Flag;
- feedback parcial do Form API quando houver `#ajax` justificado.

### Não deve virar AJAX genérico

- OAuth redirect/callback;
- checkout/pagamento Commerce;
- confirmação externa por e-mail;
- acesso/continuação de curso;
- criação/edição essencial de tópico ou conteúdo quando Form API normal resolve;
- alterações críticas de senha quando não houver benefício claro.

## account-navigation.js

O comportamento de navegação parcial deve permanecer como requisito de UX, mas
a implementação atual não é o destino final.

Hoje o arquivo faz:

- `fetch()`;
- `DOMParser`;
- merge manual de `drupalSettings`;
- detach/attach de behaviors;
- `innerHTML`;
- History API.

Destino:

```text
mesma UX assíncrona
       ↓
menos infraestrutura custom
       ↓
Drupal AJAX / HTMX / Views AJAX / Form API
```

A substituição será feita por fluxo, nunca com remoção total antecipada.

## SDC e reattachment

Todo SDC usado dentro de uma região atualizada por AJAX deve:

- funcionar após `Drupal.attachBehaviors()` ou mecanismo Core equivalente;
- não depender de inicialização global única;
- preservar IDs/aria relationships;
- tolerar re-render;
- ter estados loading/empty/error definidos quando necessário.

## Forms

SDC pode apresentar a moldura visual de uma seção, mas não deve recriar Form
API.

Exemplo:

```text
data-section SDC
    └── slot form
          └── Profile Form API render array
```

O mesmo vale para:

- endereço;
- senha;
- mudança de e-mail;
- foto;
- preferências futuras.

## Access e cache

Nenhum SDC decide se um dado pode ser exibido.

Antes de montar props/slots:

- validar entity access;
- carregar somente metadata autorizada;
- aplicar cache contexts;
- aplicar cache tags;
- respeitar Domain purpose.

## Resultado esperado

Minha Conta deve evoluir para uma interface visualmente consistente, composta
por SDCs, sem perder:

- progressive enhancement;
- AJAX onde agrega valor;
- Form API;
- APIs dos módulos contrib;
- fontes de verdade;
- access/cache;
- isolamento multidomínio.
