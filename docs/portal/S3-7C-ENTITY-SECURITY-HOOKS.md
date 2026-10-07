# S3.7C — Entity access/presave hooks

RUNTIME STATUS: **DEFERRED**

Migra para `EntitySecurityHooks`:

- entity_access;
- entity_presave.

Preservado:

- Wiki entity fora de WIKI -> forbidden;
- generic user edit bloqueado para usuário comum;
- password reset Core continua exceção;
- Mercado Pago admin update continua proibido;
- gateway não pode ser habilitado sem credentials runtime.

Gates Runtime:

- PHP lint;
- drush cr;
- Wiki host isolation;
- password reset;
- user edit;
- Mercado Pago form/access;
- gateway enable sem/com env.

Próxima subfase: S3.7D — form_alter + CEP library hook.
