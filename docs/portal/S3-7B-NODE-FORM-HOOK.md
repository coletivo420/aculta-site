# S3.7B — Hook de formulário editorial

RUNTIME STATUS: **DEFERRED**

Migra `hook_form_node_form_alter` para `EditorialFormHooks`.

O callback `aculta_portal_validate_activity` permanece procedural porque é
referenciado nominalmente pelo Form API.

Gates Runtime:

- PHP lint;
- drush cr;
- article/activity/project forms;
- details groups;
- activity validation;
- editorial author/publication labels.

Próxima subfase: S3.7C — Entity access/presave.
