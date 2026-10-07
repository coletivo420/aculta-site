# R1 — Dependency and Config Integration Queue

Status: **pronto para execução futura no Homelab**

## Objetivo

Aplicar e validar as branches preparadas durante o modo GitHub-first sem
transformar a janela Runtime em um mega-merge.

Regra principal:

**um PR por vez -> testes afetados -> correção -> merge -> novo baseline.**

## Antes de começar

R0 precisa estar PASS.

Sempre:

```sh
git fetch --prune origin
git switch main
git pull --ff-only origin main
```

Antes de cada draft, atualizar a branch contra o `main` vigente.

Como as branches já foram publicadas, evitar force-push por conveniência.
Preferir merge do `origin/main` na branch quando a atualização exigir
reescrever histórico compartilhado.

## Fila de drafts existente

Snapshot documental em 2026-10-06/07.

### 1. PR #23 — S3.1 Hooks + DI

**Runtime PASS e merge concluídos.**

Ver [R1-1-PR23-PASS.md](R1-1-PR23-PASS.md).

Branch:

`refactor/portal-s3.1-hooks-di`

Primeiro porque prepara DI usada por refactors posteriores.

Gates principais:

- PHP lint;
- container compilation;
- hooks;
- breadcrumb;
- Domain isolation;
- ACCOUNT.

### 2. PR #30 — S3.5 Domain policy

**Runtime bloqueado por finding Wiki/Media Library.**

Ver [R1-2-WIKI-MEDIA-BLOCKER.md](R1-2-WIKI-MEDIA-BLOCKER.md).

Antes de concluir #30, executar R1.2A em PR próprio e repetir os gates Wiki
add/edit.

Branch:

`refactor/portal-s3.5-domain-policy`

Hoje está empilhada sobre #23.

Processo:

1. integrar #23;
2. atualizar #30 contra o novo main;
3. validar Domain matrix;
4. só então merge.

Não tentar validar #30 isoladamente contra um main sem #23.

### 3. PR #24 — S3.2A Course presenter

Branch:

`refactor/portal-s3.2-account-course-presenter`

Independente da stack de Domain.

Gates:

- LMS/Group;
- todos os estados de CourseStatus;
- access;
- cache tags;
- URLs COURSES;
- AJAX/fallback.

### 4. PR #26 — S3.2C Segurança + Conexões

Branch:

`refactor/portal-s3.2c-security-connections`

Gates:

- container;
- Social Auth;
- Google configured/unconfigured;
- entity delete access;
- mail readiness;
- pending e-mail;
- senha local;
- User A/B.

### 5. PR #27 — S3.2D Identidade + Dados

Branch:

`refactor/portal-s3.2d-identity-data`

Hoje está empilhada sobre #26.

Processo:

1. integrar #26;
2. atualizar #27 para o novo main;
3. validar identidade/Profile/Address/photo/CEP;
4. merge.

### 6. PR #28 — S3.3 Support access first

Branch:

`refactor/portal-s3.3-support-access`

Prioridade de segurança.

Validar:

- order access;
- payments;
- User A/B;
- SUPPORT/ACCOUNT;
- Commerce regressions.

### 7. PR #29 — S3.4 Wiki boundary

Branch:

`refactor/portal-s3.4-wiki-boundary`

Validar:

- published/unpublished;
- revisions;
- Diff;
- Domain;
- Views;
- search atual;
- cache/access.

### 8. S3.7 — procedural hooks

PRs existentes:

- #32 — Editorial/SEO;
- #33 — formulário editorial;
- #34 — entity security;
- #35 — forms/library.

Essas branches alteram áreas relacionadas do módulo.

Aplicar **uma por vez**, nessa ordem inicial, atualizando cada branch depois do
merge anterior.

Depois de cada lote:

- PHP lint;
- `drush cr`;
- hook behavior;
- config;
- regressão específica.

Não juntar #32–#35 em um único merge não revisado.

## Drafts futuros

Qualquer novo draft criado antes da janela Runtime entra na fila com:

- dependências;
- risco;
- arquivos sobrepostos;
- gates próprios.

Não inserir silenciosamente no meio da fila.

## Dependencies/features S4

Depois de estabilizar os refactors estruturais, iniciar features na ordem:

1. Portal 0.11 Forum Foundation;
2. 0.12 Forum Participation;
3. 0.13 Participation Hub;
4. 0.14 Admin Hub;
5. 0.15 AJAX Consolidation;
6. 0.16 Search;
7. 0.17 Engagement;
8. 0.18 Deduplication;
9. 0.19 Hardening.

## Composer

Mudança de dependency acontece somente na versão correspondente.

Exemplos planejados:

- Forum em 0.11;
- Search API em 0.16;
- Flag/Comment Notify em 0.17.

Para cada dependency:

1. confirmar versão atual;
2. `composer require`;
3. revisar lockfile;
4. `composer validate`;
5. audit quando rede;
6. enable;
7. config;
8. export;
9. testes;
10. commit.

Não acumular dependencies de várias versões em uma única instalação.

## Configuration Sync

Para config funcional:

1. aplicar via Drupal/Drush/admin quando apropriado;
2. validar no Runtime;
3. `drush cex -y`;
4. revisar diff;
5. testar import;
6. terminar CLEAN.

Não fabricar config final manualmente a partir da especificação.

## State/snapshot

Criar novo Estado somente em milestones em que facilite rollback/validação.

Não criar snapshot a cada PR.

Estado não substitui commit/config export.

## Stop conditions

Parar a fila se:

- baseline quebra;
- config fica inexplicavelmente dirty;
- updatedb pendente inesperado;
- Domain regression;
- User A/B leak;
- security finding;
- data migration não entendida;
- dependency conflict;
- rollback falha.

Resolver em PR próprio antes de continuar.

## Evidência por PR

Registrar:

- PR;
- commit testado;
- main antes/depois;
- PHP lint;
- Composer;
- Drush;
- config;
- updatedb;
- testes específicos;
- HTTP/Domain quando aplicável;
- findings;
- rollback.

## Próxima fase

R2 — Functional Validation Matrix.
