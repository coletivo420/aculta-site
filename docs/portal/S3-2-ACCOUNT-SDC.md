# S3.2 — Minha Conta -> SDC

## S3.2A — Course presentation boundary

Data de atualização: 2026-10-07

RUNTIME STATUS: **PARCIAL — core/LMS PASS; HTTP autenticado pendente**

STATIC STATUS: **PASS — diff audit + PHP 8.4 syntax**

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
status + tone + score label + action + empty state
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

O retorno do manager é contrato interno de **dados autorizados**, não um card de
apresentação. Labels, tones, action semântica e empty state pertencem ao presenter;
essa separação deve ser preservada em refatorações futuras.

A política de URL já incorpora S3.5: `routeUrl('courses', ...)` deve continuar
resolvendo para COURSES tanto em produção quanto nos aliases Homelab.

## AccountCoursePresenter

Service:

`aculta_portal.presentation.account_course`

Responsável por:

- status `code + label + tone`;
- score label;
- action `label + url + kind`;
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

Para manter a direção arquitetural da S3.1, o controller também deixa de herdar
`ControllerBase` apenas para tradução. Ele implementa
`ContainerInjectionInterface` e recebe explicitamente o presenter, o usuário
atual e `string_translation`. O método estático `create()` é somente a factory
de DI; a lógica da requisição não consulta o container nem usa helper lazy de
`ControllerBase`.

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

- action de curso usa purpose COURSES;
- `DomainPurposeManager` permanece o dono das URLs cross-domain;
- nenhum service locator volta a ser introduzido;
- nenhum Domain entity é mutado pelo presenter;
- curso aguardando avaliação continua sem action enganosa.

## Revisão estática feita no chat

Confirmado por inspeção do diff contra a `main` e lint sintático local:

- `php -l` PASS em `AccountCoursesManager.php`, `AccountCoursePresenter.php` e `AccountCoursesController.php` com PHP 8.4.23 CLI;

- manager continua sendo a fronteira LMS/Group/access;
- presenter não carrega Group/User/CourseStatus por storage;
- o view-model usa a chave normativa `action` da S3.2B e não repassa `score`/`finished` brutos para a camada de apresentação;
- controller não referencia SDC e não depende de helpers lazy de `ControllerBase`;
- presenter, current user e tradução entram por DI explícita;
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
- aguardando avaliação sem action;
- score presente e ausente;
- Group sem access sem vazamento de metadata;
- action no hostname COURSES;
- cache tags;
- navegação AJAX da Conta;
- fallback full-page;
- regressão dos sete hosts após `drush cr`.

O servidor de desenvolvimento usa **Apache + PHP-FPM**. Não usar gates Nginx.


## Validação Runtime — 2026-10-07

**Resultado: PARCIAL.**

Passaram:

- `php -l` nos três PHPs da S3.2A;
- `composer validate`, `composer audit` e `composer check-platform-reqs`;
- Drupal bootstrap e `drush cr`;
- Configuration Sync limpo;
- `updatedb:status` sem atualizações;
- smoke HTTP dos sete purposes dentro do baseline S3.5;
- `/meus-cursos` anônimo: 403 em ACCOUNT e 404 em MAIN/MAGAZINE;
- Entity API: zero, um e múltiplos cursos;
- estados não iniciado, em andamento, aprovado, reprovado e aguardando avaliação;
- score presente e ausente;
- description pelo Field API;
- cache tags;
- actions no purpose COURSES;
- Group sem `view` access não expôs metadata;
- `needs_evaluation` permaneceu sem action.

Fixtures temporárias foram removidas ao final. O Homelab voltou para
`main@bf5faa0` com worktree limpo.

### Cobertura HTTP autenticada pendente

O teste tentou usar o login normal com JavaScript desabilitado para então validar
o fallback full-page da Conta. O submit não produziu token Cloudflare Turnstile e
foi rejeitado pelo CAPTCHA.

Isso **não é regressão S3.2A**. Turnstile depende de JavaScript para produzir o
token de validação e não oferece fallback no-JS automático seguro.

A mensagem observada ainda estava em inglês:

`The answer you entered for the CAPTCHA was not correct.`

A internacionalização de login/CAPTCHA está sendo tratada separadamente no PR
#63. Esse PR não deve introduzir bypass de CAPTCHA.

Para concluir S3.2A, preparar sessão autenticada de fixture server-side e testar,
sem depender do login UI:

- `/meus-cursos` autenticado;
- navegação parcial/AJAX com JavaScript normal;
- fallback full-page da navegação da Conta com o JavaScript da Conta ausente;
- action para COURSES e sessão compartilhada;
- isolamento User A/User B se a fixture estiver disponível.

O requisito de fallback full-page é da **navegação da Conta**, não uma exigência
de que Cloudflare Turnstile autentique usuários sem JavaScript.

## Merge policy

Manter o PR em draft até Runtime PASS no Homelab.

Nenhuma mudança em SQLite, conteúdo, produção ou Hostinger pertence a S3.2A.

## Próxima subfase

Após S3.2A Runtime PASS, seguir o roadmap atual. S3.2B já estabeleceu a
semântica compartilhada; os próximos presenters funcionais são Segurança e
Conexões (S3.2C), sem antecipar novos SDCs.
