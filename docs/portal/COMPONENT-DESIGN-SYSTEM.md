# ACULTA Portal + ACULTA420 Component Design System

## Objetivo

Este documento define como o `aculta_portal` consome o design system já
estabelecido pelo tema `aculta420`.

A arquitetura visual canônica do tema está em:

`web/themes/custom/aculta420/docs/architecture.md`.

O Portal não mantém um design system concorrente.

## Camadas

```text
módulo funcional
  User / Profile / Commerce / LMS / Wiki / Forum
                    |
                    v
              aculta_portal
    access + cache + presenter + purpose
                    |
                    v
         contrato visual reutilizável
                    |
                    v
             tema ACULTA420 SDC
                    |
                    v
               Bootstrap 5
```

## Regra principal

O Portal conhece **significado e estado**.

O componente conhece **apresentação**.

Exemplo de course card:

Portal conhece:

- título autorizado;
- status LMS;
- score quando permitido;
- URL correta no COURSES purpose;
- cache tags;
- CTA disponível ou ausente.

SDC conhece:

- slot de título;
- badge de status;
- região de progresso;
- CTA;
- spacing;
- layout responsivo.

O SDC não carrega Group, CourseStatus ou User.

## Presenter/View-model

Preferir services/presenters que retornem estruturas simples e renderables
seguros.

Requisitos:

- access antes de metadata;
- cache tags/contexts preservados;
- URLs construídas por purpose;
- formatted text continua render array;
- não converter markup filtrado em string crua;
- não incluir entidades inteiras como prop de SDC;
- não usar SDC como service locator.

## Shell multidomínio

O shell público segue a mesma fronteira Portal → apresentação.

```text
Domain
  ↓
DomainPurposeManager
  ↓
aculta_portal
  ↓
domain_presentation/render array
  ↓
ACULTA420
```

O Portal pode preparar `purpose`, títulos, URL inicial, branding, navegação e
accent. O tema não resolve hostname, Domain access ou storage. Nenhuma entidade
`Domain` deve ser passada diretamente para SDC/Twig.

Branding específico é opcional. O contrato deve permitir fallback para ACULTA e
depois para título textual, sem impedir a criação de um novo purpose.

A arquitetura visual planejada está em
`web/themes/custom/aculta420/docs/shell.md`.

## Bootstrap primeiro

Antes de criar markup custom, verificar se Bootstrap já fornece o primitive ou
comportamento necessário:

- card;
- badge;
- alert;
- list group;
- progress;
- pagination;
- collapse;
- offcanvas;
- modal;
- nav;
- dropdown;
- spinner/placeholders;
- grid/utilities.

A identidade ACULTA420 vem dos tokens e contratos do tema, não da reinvenção do componente estrutural.

## SDC primeiro para composição reutilizável

Criar/usar SDC quando a apresentação:

- se repete;
- tem contrato de slots/props estável;
- possui estados visuais conhecidos;
- tem ownership de assets claro;
- melhora consistência entre Portal, Views e conteúdo.

Não criar SDC apenas para embrulhar uma `<div>` usada uma vez.

## Famílias Portal

### Conta

A matriz detalhada está em
[ACCOUNT-SDC-AJAX.md](ACCOUNT-SDC-AJAX.md).

Candidatos atuais:

- account shell;
- account identity;
- summary card;
- course card;
- action list;
- status badge;
- empty state;
- data section;
- security card;
- integration card;
- support/order summary;
- participation card;
- photo editor.

A migração é incremental. Um SDC pode conter slots com Form API ou conteúdo
atualizado por AJAX; ele não substitui essas APIs.

### Cursos

Candidatos:

- course card;
- progress/status;
- course action.

A fonte continua Drupal LMS/Group.

### Wiki

Candidatos:

- contribution card;
- revision/status badge;
- content list/empty state.

A fonte continua Node/Taxonomy/Revisions.

### Fórum

Candidatos:

- forum topic card;
- participation item;
- reply metadata;
- follow/status action quando Flag existir.

A fonte continua Forum/Node/Comment/Taxonomy.

### Loja/Apoio

Candidatos:

- product card;
- order summary/status;
- payment/support status;
- empty state.

Commerce continua fonte de verdade.

## AJAX

Componentes devem sobreviver a reattachment por Drupal behaviors.

Preferência:

- Views AJAX;
- Form API AJAX;
- Drupal Ajax API;
- Core HTMX quando apropriado.

Um SDC não deve exigir transporte AJAX próprio.

## Acessibilidade

Cada componente Portal deve cobrir, conforme o caso:

- keyboard;
- focus-visible;
- accessible name;
- heading hierarchy;
- aria-current/status;
- reduced motion;
- empty/loading/error state;
- mobile;
- títulos longos;
- contraste.

Não substituir acessibilidade funcional já fornecida por Core/contrib/Bootstrap.

## Cache e access

A camada visual não corrige falhas de cache ou access.

Presenter/Portal deve fornecer o resultado correto antes da renderização.

Para páginas privadas, revisar pelo menos:

- user;
- user.permissions;
- domain/purpose;
- entity cache tags;
- max-age.

## Anti-regressão

Não:

- copiar HTML Bootstrap repetidamente em controllers;
- criar CSS exclusivo do Portal para reproduzir componente já existente;
- mover regra LMS/Commerce/Wiki/Forum para Twig/SDC;
- adotar outro framework de componentes por conveniência;
- criar dependência em `drupal/bootstrap_components` sem decisão explícita;
- converter em massa código do tema já refatorado.

Sempre preservar a fronteira:

```text
dados/regras -> Portal -> apresentação -> SDC/Bootstrap
```
