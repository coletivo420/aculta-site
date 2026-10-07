# R1.2 — PR #30 bloqueado por Media Library

Data: 2026-10-07

Status: **FAIL — blocker fora do escopo da S3.5**

## Contexto

A S3.5 Domain policy passou nos gates centrais de:

- PHP lint;
- service locator;
- container/Drush;
- config/updatedb;
- Homelab verify;
- Domain matrix;
- wrong-host policy;
- URL generation;
- non-mutation de Domain;
- password reset;
- logout.

Porém Wiki add/edit autenticados retornam HTTP 500.

PR #30 permanece:

`RUNTIME STATUS: DEFERRED`

e não deve ser mergeado até o blocker ser resolvido e os gates Wiki add/edit
serem repetidos.

## Finding R1.2-WIKI-MEDIA-001

Erro:

`InvalidArgumentException` em `MediaLibraryState` porque o parâmetro
`allowed_types` chega vazio/ausente.

O finding não foi introduzido pela S3.5 e ocorre durante a construção da
experiência editorial autenticada.

## Evidência estática

O projeto possui media types versionados e ativos:

- `image`;
- `document`;
- `remote_video`.

O formato `full_html` possui CKEditor5 com `drupalMedia`.

O filtro `media_embed` declara os mesmos três media types, mas a configuração
atual está serializada como lista numérica:

```yaml
allowed_media_types:
  - image
  - document
  - remote_video
```

A implementação Core CKEditor5 Media Library filtra os media types com
`array_intersect_key()`. Configurações produzidas pelo próprio Form API de
checkboxes normalmente preservam os IDs também como chaves.

Hipótese a comprovar no Runtime:

a forma numérica da configuração faz a interseção por chave ficar vazia e leva
`MediaLibraryState::create()` a receber uma lista vazia.

## Regra de correção

Não editar o YAML final manualmente como solução.

Primeiro:

1. inspecionar config ativa;
2. inspecionar media types ativos;
3. reproduzir e capturar stack trace;
4. confirmar a lista resultante do CKEditor5 Media Library;
5. corrigir via Drupal/Drush/config entity;
6. exportar com `drush cex`;
7. revisar o diff exportado;
8. testar import;
9. repetir Wiki add/edit;
10. repetir gates da S3.5 afetados.

## Não fazer

- não patchar Core;
- não remover validação do `MediaLibraryState`;
- não criar media type duplicado;
- não desabilitar Media Library apenas para eliminar o erro;
- não remover `drupalMedia` sem decisão funcional;
- não editar o PR #30 para esconder o blocker;
- não iniciar PR #24 enquanto R1.2 estiver bloqueado.

## Próxima unidade

**R1.2A — corrigir configuração Media Library / CKEditor5 em PR próprio.**

Depois do merge dessa correção:

1. atualizar PR #30 contra o main;
2. repetir Wiki add/edit;
3. repetir gates S3.5 afetados;
4. somente então decidir merge do #30.
