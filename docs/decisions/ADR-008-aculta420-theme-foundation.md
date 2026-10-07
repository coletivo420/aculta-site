# ADR-008: ACULTA420 como nova fundação do tema

Status: Accepted  
Data: 2026-10-07

## Contexto

O tema histórico `aculta` passou por uma refatoração estrutural ampla e tornou-se
a base de um Component Design System. A continuidade sob o mesmo provider
misturaria identidade histórica, documentação transitória e uma API que ainda
não havia sido versionada formalmente.

No Drupal, o machine name de um tema é parte da API: nomeia pasta/arquivos,
hooks, libraries, settings e o namespace SDC `provider:component`.

## Decisão

A nova fundação nasce como:

- nome humano: **ACULTA420**;
- machine name: `aculta420`;
- versão inicial: `0.1.0`;
- diretório: `web/themes/custom/aculta420`;
- libraries: `aculta420/*`;
- SDC namespace: `aculta420:*`;
- settings: `aculta420.settings`;
- hooks: `aculta420_preprocess_*`.

Bootstrap5 permanece base estrutural/comportamental. `aculta_portal` permanece
camada de integração.

## Compatibilidade de deploy

0.1.0 mantém temporariamente um shim oculto em
`web/themes/custom/aculta/`, contendo apenas metadata mínima.

Motivo: ambientes existentes podem ainda listar `aculta` em `core.extension`.
Drupal trata extensão instalada ausente do filesystem como estado inválido.

O shim:

- não possui Twig, CSS, JS, libraries ou SDC;
- não é o tema default sincronizado;
- não recebe novas features;
- será removido em 0.1.1 após todos os ambientes migrarem.

A migração deve ocorrer em maintenance mode.

## IDs preservados

Não renomeamos automaticamente:

- `.aculta-*`;
- `--aculta-*`;
- `aculta_portal`;
- Domain/content/view/menu/block IDs `aculta_*`.

Esses identificadores representam marca, conteúdo/configuração ou outros
subsistemas e não o provider do tema.

## Consequências

### Positivas

- API do design system começa limpa e versionada;
- namespace SDC e libraries ficam coerentes;
- documentação passa a refletir estado atual;
- futuras releases têm SemVer próprio.

### Custos

- config sync precisa migrar theme dependencies/settings;
- deploy exige sequência controlada;
- consumidores do namespace antigo precisam migrar;
- shim temporário precisa ser removido após a migração.

## Referências

- documentação ACULTA420: `web/themes/custom/aculta420/docs/`;
- migração: `web/themes/custom/aculta420/docs/migration-0.1.0.md`;
- SDC API: https://www.drupal.org/docs/develop/theming-drupal/using-single-directory-components/api-for-single-directory-components
- troubleshooting de extensão ausente: https://www.drupal.org/docs/updating-drupal/troubleshooting-database-updates
