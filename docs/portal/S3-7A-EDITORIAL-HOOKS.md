# S3.7A — Hooks Editorial/SEO

RUNTIME STATUS: **DEFERRED**

## Escopo

Migra para `EditorialHooks`:

- metatag_tags_alter;
- token_info;
- tokens;
- node_presave;
- metatags_alter.

## Preservado

- Metatag continua renderer;
- Schema Metatag continua serializador;
- Domain Source continua canônico;
- tokens institucionais continuam lendo o bloco oficial;
- published_at continua preenchido no primeiro publish;
- event locations mantêm regras presenciais/online/híbridas;
- nenhum callback de Form API foi movido.

## DI

EditorialHooks recebe:

- ConfigFactory;
- EntityTypeManager;
- DomainPurposeManager;
- DomainNegotiator;
- RequestStack;
- FileUrlGenerator;
- Translation.

## Runtime gates

- PHP lint;
- drush cr;
- token rebuild;
- Metatag/Schema de article/activity/project/wiki/course;
- canonical local/produção;
- imagem publicada/não publicada;
- institution tokens;
- node presave first publication.

## Próxima subfase

S3.7B — Forms/Conta hooks.
