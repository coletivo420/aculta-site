# S3.7D — Form alters + CEP library hook

RUNTIME STATUS: **DEFERRED**

## Hooks migrados

- form_alter -> AccountFormHooks + CommerceFormHooks;
- library_info_alter -> LibraryHooks.

## Callbacks mantidos procedurais

Continuam no `aculta_portal.module` por serem registrados nominalmente pelo
Form API:

- change_mail confirmation message;
- password redirect;
- password after_build;
- photo redirect;
- address redirect;
- customer address name sync;
- activity validation;
- donation amount validation.

## Preservado

- Change Mail UX;
- password form own-account;
- donation amount validation;
- redaction de credentials Mercado Pago;
- CEP contrib endpoint/cache + behavior ACULTA.

## Gates Runtime

- PHP lint;
- drush cr;
- Change Mail;
- password;
- Donation Flow;
- Mercado Pago admin form;
- CEP library dependency e behavior;
- todos os callbacks procedurais.

## Resultado da série S3.7

Após integração sequencial de S3.7A–D, hooks Drupal passam a classes OOP.
O arquivo `.module` permanece apenas com callbacks procedurais que ainda têm
referências nominais.

Próxima fase: S3.8 — lifecycle/install audit.
