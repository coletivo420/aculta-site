# Minha Conta — contratos de componentes

Data: 2026-10-06

Status: **contratos de integração; implementação visual pertence ao tema**

## Objetivo

Definir contratos que o `aculta_portal` pode alimentar sem transferir regras
de negócio para o ACULTA Bootstrap Component Design System.

Os componentes abaixo são candidatos do tema. Este documento não cria SDCs no
módulo e não autoriza alteração concorrente de `web/themes/custom/aculta/**`.

## Regra

```text
fonte funcional -> manager/adapter -> presenter -> contrato -> SDC do tema
```

O presenter pode conhecer:

- estado funcional autorizado;
- label traduzida;
- CTA permitida;
- URL por Domain purpose;
- cache metadata.

O SDC conhece:

- slots/props visuais;
- Bootstrap;
- tokens ACULTA;
- estados de apresentação.

## status-badge

### Uso

- curso;
- pagamento/apoio;
- segurança;
- integração externa;
- fórum;
- revisão Wiki.

### Contrato proposto

Prop simples:

- `tone`: `neutral | info | success | warning | danger`.

Slot:

- `label`.

O SDC **não conhece** valores como `needs_evaluation`,
`payment_completed` ou `forum_closed`.

O presenter converte o estado funcional para uma semântica visual genérica.

## empty-state

### Uso

- nenhum curso;
- nenhum apoio;
- nenhuma contribuição;
- nenhum tópico;
- nenhuma integração conectada.

### Slots

- `title` opcional;
- `message`;
- `action` opcional.

Não consultar Views/entidades no componente.

## summary-card

### Uso

Dashboard da Conta:

- cursos;
- apoio;
- participação;
- segurança;
- integrações.

### Slots

- `title`;
- `summary`;
- `status` opcional;
- `action` opcional.

O card não calcula contagem.

## action-list

### Uso

- atalhos da Conta;
- ações de segurança;
- conexões;
- links de participação.

### Slots

- `items`.

Access deve ser resolvido antes de montar o slot.

## course-card

### Fonte

Drupal LMS + Group.

### Presenter

`AccountCoursePresenter`.

### Contrato inicial proposto

Slots:

- `title`;
- `description` opcional;
- `status`;
- `score` opcional;
- `cta` opcional.

O wrapper/presenter Drupal preserva:

- cache tags;
- contexts;
- atributos necessários.

O componente não recebe:

- Group;
- CourseStatus;
- User;
- membership;
- TrainingManager.

### Estado no Portal

O view-model atual contém:

- `id`;
- `title`;
- descrição renderable;
- status `code + label`;
- score + label;
- finished;
- CTA `label + URL`;
- cache tags.

Nem todos esses valores precisam virar props do SDC. Valores internos podem
permanecer no presenter.

## account-shell

Contrato futuro, não parte do piloto S3.2A.

Deve receber:

- título da seção;
- menu renderable;
- conteúdo renderable.

Não deve conhecer routes nem calcular logout.

A navegação assíncrona continua fora do componente.

## data-section

Contrato futuro.

Deve aceitar:

- heading;
- tabs/actions;
- Form API em slot.

O SDC não reconstruirá Profile/Address forms.

## security-card

Contrato futuro.

Pode apresentar:

- heading;
- description;
- status/pending;
- Form API/action.

Não decide se SMTP/OAuth/e-mail está homologado.

## integration-card

Contrato futuro.

Pode apresentar:

- provider;
- estado;
- descrição;
- ação.

OAuth redirect/callback continua fora do SDC.

## support/order-card

Contrato futuro.

Commerce continua fonte.

Access ao pedido deve ser validado antes do presenter.

## participation-card

Contrato futuro.

Pode representar itens de:

- Fórum;
- Wiki;
- cursos.

A origem funcional deve permanecer explícita no presenter, mas o componente não
consulta storage.

## AJAX

Nenhum contrato desta página implica transporte AJAX.

Um componente pode ser renderizado:

- na carga normal;
- por Views AJAX;
- por Drupal Ajax;
- por HTMX Core;
- dentro de Form API.

O comportamento assíncrono é uma camada separada.

Ver [ACCOUNT-SDC-AJAX.md](ACCOUNT-SDC-AJAX.md).

## Maturidade

Os contratos desta página devem ser considerados **propostos/experimental** até
que o tema implemente e valide o primeiro consumidor real.

A promoção para `stable` segue os critérios do design system do tema.
