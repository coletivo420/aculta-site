# R1.1 — PR #23 S3.1 Hooks + DI: PASS

Data: 2026-10-06/07

Status: **PASS — integrado no main**

## Baseline

- main antes: `e7ecc05e9e9ff401eaacb728f3c122d987d503b8`;
- PR head antes: `9b264bf1e2aa3414b5eb9a38614e2a0b86b6a355`;
- merge de sincronização com main: `253aef5`;
- main após integração: `6ec1abba58d60a252f31c53547b4cd85d722107c`.

## Resultado

- sync com main: PASS;
- sem rebase/force-push;
- PHP lint: PASS;
- bash syntax: PASS;
- diff check: PASS;
- `PortalHooks`: zero `\Drupal::`;
- `AcultaBreadcrumbBuilder`: zero `\Drupal::`;
- `DomainPurposeRequestSubscriber`: zero `\Drupal::`;
- `drush cr`: PASS;
- config status: CLEAN;
- updatedb: NONE;
- Composer validate/audit: PASS;
- Homelab verify: PASS;
- theme diff: vazio.

## Runtime smoke

- MAIN: 200;
- ACCOUNT: root anônimo 403; login/recovery 200;
- SUPPORT: 200;
- MAGAZINE: 200;
- WIKI: 200;
- SHOP: 404 esperado;
- COURSES: 200.

## Domain isolation

- `/wiki`, `/cursos`, `/meus-cursos` em host incorreto: 404;
- alias de verbete em host incorreto: 403 sem exposição do conteúdo.

## Sessão/autenticação

- sessão Drupal temporária reconhecida em ACCOUNT e COURSES;
- logout/token do Portal encerrou sessão nos dois hosts;
- login por senha via navegador não foi repetido nesta rodada;
- Social Auth page carregou;
- autenticação Google real não testada.

## Breadcrumb

- MAIN e MAGAZINE: labels/links preservados;
- SUPPORT home: breadcrumb suprimido como esperado;
- ACCOUNT testado não renderizou breadcrumb.

## Limitações registradas

User A/B ficou:

`DEFERRED_FIXTURE`

Isso não bloqueou a S3.1 porque a mudança validada é estrutural/DI e os smokes de
isolamento/hosts permaneceram corretos.

## Gate

`R1.1: PASS`

Próximo item:

**PR #30 — S3.5 Domain policy**
