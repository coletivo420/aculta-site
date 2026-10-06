# S3.2 — Minha Conta -> SDC

## S3.2A — Course presentation boundary

Data de preparação: 2026-10-06

RUNTIME STATUS: **DEFERRED**

## Objetivo

Criar a primeira fronteira presenter -> futuro SDC dentro da Minha Conta sem
alterar o tema e sem depender de um componente ainda inexistente.

O piloto escolhido é Cursos porque:

- LMS/Group já são fontes de verdade claras;
- `AccountCoursesManager` já verifica access antes de metadata;
- não existe storage paralelo;
- o controller possui markup de card simples;
- `course-card` já é candidato oficial no design system do tema.

## Mudanças preparadas

### AccountCoursesManager

Antes misturava:

- integração LMS/Group;
- access;
- URLs;
- label de status em português.

Agora mantém:

- membership;
- group access;
- CourseStatus;
- description renderable;
- score;
- finished;
- URL COURSES;
- cache tags.

A label de apresentação sai desta camada.

### AccountCoursePresenter

Novo service:

`aculta_portal.presentation.account_course`

Responsável por:

- status label;
- score label;
- CTA label;
- empty-state;
- view-model estável para apresentação.

Não persiste estado e não consulta storage diretamente.

### AccountCoursesController

Continua usando render arrays atuais como fallback.

O controller passa a consumir o presenter, mas **não referencia
`aculta:course-card`** porque esse SDC ainda não existe no tema.

Isso permite validar a separação antes de trocar a apresentação.

## Tema

Nenhum arquivo em `web/themes/custom/aculta/**` foi alterado.

Quando os agentes do tema implementarem `course-card`, poderão consumir o
contrato documentado em
[ACCOUNT-COMPONENT-CONTRACTS.md](ACCOUNT-COMPONENT-CONTRACTS.md).

## AJAX

Nenhuma alteração.

A rota de Meus Cursos continua participando da navegação assíncrona da Conta
pela infraestrutura atual.

O card não ganha transporte AJAX próprio.

## Revisão estática

Confirmado:

- presenter não carrega Group/User/Status entity por storage;
- manager continua usando LMS/Group como fontes;
- access do Group permanece antes de metadata;
- cache tags permanecem no view-model;
- controller mantém fallback atual;
- nenhuma dependência Composer;
- nenhum arquivo do tema;
- nenhuma configuração Drupal nova.

A tentativa de clonar a branch para `php -l` falhou porque o ambiente auxiliar
não resolveu `github.com`. Não registrar como PASS.

## Gates Runtime

Antes do merge funcional:

```sh
php -l web/modules/custom/aculta_portal/src/AccountCoursesManager.php
php -l web/modules/custom/aculta_portal/src/Presentation/AccountCoursePresenter.php
php -l web/modules/custom/aculta_portal/src/Controller/AccountCoursesController.php

composer validate

vendor/bin/drush status
vendor/bin/drush cr
vendor/bin/drush config:status
vendor/bin/drush updatedb:status
```

Funcional:

- User sem cursos;
- User com 1 curso;
- User com múltiplos cursos;
- curso não iniciado;
- em andamento;
- aprovado;
- reprovado;
- aguardando avaliação sem CTA enganosa;
- score visível;
- curso sem score;
- Group sem access não vaza metadata;
- CTA aponta para COURSES Domain;
- cache tags;
- navegação AJAX da Conta;
- fallback full-page.

## Merge policy

Manter PR em draft até Runtime PASS.

## Próxima subfase

S3.2B — primitives/view-models compartilhados da Conta:

- status badge semantics;
- empty state;
- summary card;
- action list.

A implementação visual desses componentes continua pertencendo ao tema.
