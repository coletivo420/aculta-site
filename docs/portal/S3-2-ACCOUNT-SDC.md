# S3.2 — Minha Conta -> SDC

## S3.2A — Course presentation boundary

Data de atualização: 2026-10-07

RUNTIME STATUS: **DEFERRED**

STATIC STATUS: **PASS — diff audit + PHP 8.4 syntax; Drupal Runtime deferred**

## Objetivo

Criar a primeira fronteira executável entre dados autorizados de Cursos e a
apresentação da Minha Conta, sem alterar o tema e sem depender de um SDC ainda
inexistente.

O piloto é Cursos porque:

- LMS/Group já são as fontes de verdade;
- `AccountCoursesManager` já verifica access antes de metadata;
- não existe storage paralelo;
- o controller possui fallback de render simples;
- `course-card` continua candidato do design system, não dependência do Portal.

## Relação com S3.2B

A `main` já contém
[ACCOUNT-PRESENTATION-MODEL.md](ACCOUNT-PRESENTATION-MODEL.md), que é a fonte
normativa para status, ações, empty states, summary e cache/access.

S3.2A **consome** esse contrato. Não mantém uma segunda especificação paralela.

Fluxo:

```text
Drupal LMS + Group
        ↓
AccountCoursesManager
access + source data + URLs por purpose + cache tags
        ↓
AccountCoursePresenter
status + tone + score label + CTA + empty state
        ↓
render atual da Conta
        ↓
futuro course-card SDC, somente quando aprovado no tema
```

## AccountCoursesManager

Permanece responsável por:

- membership Group;
- access do Group antes de metadata;
- CourseStatus;
- description renderable;
- score bruto;
- estado finished;
- URL no purpose COURSES;
- cache tags.

Deixa de possuir label de status voltada ao usuário.

A política de URL já incorpora S3.5: `routeUrl('courses', ...)` deve continuar
resolvendo para COURSES tanto em produção quanto nos aliases Homelab.

## AccountCoursePresenter

Service:

`aculta_portal.presentation.account_course`

Responsável por:

- status `code + label + tone`;
- score label;
- CTA `label + url + kind`;
- empty state;
- view-model estável para apresentação.

Não:

- persiste estado;
- consulta storage diretamente;
- decide access;
- cria membership/progresso paralelo;
- conhece Domain entity;
- implementa AJAX;
- depende de SDC.

## AccountCoursesController

Passa a consumir o presenter.

O markup atual continua sendo o fallback funcional. O controller não referencia
`aculta:course-card` nem qualquer outro SDC inexistente.

## Cache e access

A ordem permanece:

```text
membership
  ↓
Group access
  ↓
metadata + LMS progress
  ↓
view-model
  ↓
render
```

Cache tags de Group/CourseStatus continuam propagadas para cada card. Os
contexts existentes da página permanecem inalterados.

## Tema e AJAX

Nenhum arquivo em `web/themes/custom/aculta/**` faz parte desta subfase.

Nenhuma alteração de transporte AJAX faz parte desta subfase. A navegação da
Conta continua com o comportamento atual e fallback full-page.

## Compatibilidade com S3.5

Este PR foi sincronizado semanticamente com a `main` após S3.5.

Regras que não podem regredir:

- CTA de curso usa purpose COURSES;
- `DomainPurposeManager` permanece o dono das URLs cross-domain;
- nenhum service locator volta a ser introduzido;
- nenhum Domain entity é mutado pelo presenter;
- curso aguardando avaliação continua sem CTA enganosa.

## Revisão estática feita no chat

Confirmado por inspeção do diff contra a `main` e lint sintático local:

- `php -l` PASS em `AccountCoursesManager.php`, `AccountCoursePresenter.php` e `AccountCoursesController.php` com PHP 8.4.23 CLI;

- manager continua sendo a fronteira LMS/Group/access;
- presenter não carrega Group/User/CourseStatus por storage;
- controller não referencia SDC;
- tema não é alterado;
- Composer/config sync não são alterados;
- documentação aponta para uma única fonte semântica compartilhada;
- mudanças S3.5 em services/Domain policy são preservadas no merge.

Essa revisão não substitui bootstrap Drupal nem testes de runtime.

## Gates delegados ao Homelab

Executar antes de marcar Runtime PASS:

```sh
php -l web/modules/custom/aculta_portal/src/AccountCoursesManager.php
php -l web/modules/custom/aculta_portal/src/Presentation/AccountCoursePresenter.php
php -l web/modules/custom/aculta_portal/src/Controller/AccountCoursesController.php

composer validate
composer audit
composer check-platform-reqs

vendor/bin/drush status
vendor/bin/drush cr
vendor/bin/drush config:status
vendor/bin/drush updatedb:status
```

Validar funcionalmente:

- usuário sem cursos;
- usuário com um curso;
- usuário com múltiplos cursos;
- não iniciado;
- em andamento;
- concluído/aprovado;
- não aprovado;
- aguardando avaliação sem CTA;
- score presente e ausente;
- Group sem access sem vazamento de metadata;
- CTA no hostname COURSES;
- cache tags;
- navegação AJAX da Conta;
- fallback full-page;
- regressão dos sete hosts após `drush cr`.

O servidor de desenvolvimento usa **Apache + PHP-FPM**. Não usar gates Nginx.

## Merge policy

Manter o PR em draft até Runtime PASS no Homelab.

Nenhuma mudança em SQLite, conteúdo, produção ou Hostinger pertence a S3.2A.

## Próxima subfase

Após S3.2A Runtime PASS, seguir o roadmap atual. S3.2B já estabeleceu a
semântica compartilhada; os próximos presenters funcionais são Segurança e
Conexões (S3.2C), sem antecipar novos SDCs.
