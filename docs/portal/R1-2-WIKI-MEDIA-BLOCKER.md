# R1.2 — PR #30 bloqueado por Media Library

Data: 2026-10-07

Status: **RESOLVIDO pela R1.2A; PR #30 ainda depende de reteste S3.5**

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


## R1.2A — resolução do finding

**R1.2A ROOT CAUSE: CONFIRMED**
**R1.2A FIX: `filter.format.full_html` salvo pela Config Entity API com IDs como chaves**
**RUNTIME STATUS: PASS**

Main usado como baseline: `b175955b8ed7d7cea8275f17e9d2b9e6bde830b2`.

Antes da correção, os media types ativos eram `image`, `document` e
`remote_video`. `full_html` usava CKEditor 5 com `drupalMedia`; seu filtro
`media_embed` continha `allowed_media_types` como lista numérica (`0 => image`,
`1 => document`, `2 => remote_video`). A interseção Core por chave resultou em
array vazio. `basic_html` e o formato `wiki` não foram alterados.

A stack trace HTTP confirmou o fluxo:
`MediaLibraryState::validateRequiredParameters()` →
`MediaLibraryState::create()` →
`CKEditor5Plugin\MediaLibrary::getDynamicPluginConfig()` →
`CKEditor5PluginManager::getCKEditor5PluginConfig()` →
`CKEditor5::getJSSettings()` → renderização do formulário de edição Wiki.
O formato envolvido foi `full_html`.

A alteração foi aplicada com `FilterFormat` Config Entity API, seguida de
cache rebuild e export. O array ativo/exportado agora é associativo:

```yaml
allowed_media_types:
  image: image
  document: document
  remote_video: remote_video
```

A interseção Core após a mudança contém os três IDs: `image`, `document` e
`remote_video`.

Validação HTTP autenticada no Wiki host: `/node/add/wiki_entry` e `/node/43/edit`
retornaram 200 antes e depois do config import; anônimo recebeu 403 em
`/node/add/wiki_entry`. O formulário carregou CKEditor 5 e gerou a URL de
Media Library com exatamente os três tipos. A URL foi requisitada e retornou
200; a UI contém a ação de inserção. O grid administrativo de mídia no host
MAIN também retornou 200. Nenhum upload, mídia ou conteúdo foi criado. Não foi
executado clique de seleção/cancelamento em navegador automatizado; o ambiente
não possui Selenium, Playwright ou geckodriver.

`drush cex` alterou somente `config/sync/filter.format.full_html.yml`; o diff
consiste exclusivamente na serialização associativa dos três IDs. `drush cim`
reportou nenhuma alteração pendente; cache rebuild, config status e updatedb
passaram. O verificador Homelab, Composer validate/audit/platform requirements
e os smoke tests de host passaram. Não foram alterados Domain policy, código
PHP, tema, dependências Composer, tipos de mídia, Runtime SQLite ou produção.

O PR #30 não foi alterado nem retestado nesta unidade. Após o merge desta
correção, R1.2B deve atualizar #30 contra o novo `main` e repetir os gates S3.5.
