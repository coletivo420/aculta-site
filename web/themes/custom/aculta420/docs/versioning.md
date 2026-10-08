# Versionamento

ACULTA420 possui versão própria, independente do site Drupal e do
`aculta_portal`.

Versão inicial: **0.1.0**.

## SemVer

Formato:

`MAJOR.MINOR.PATCH`

Antes de 1.0:

- MINOR = entrega coerente de arquitetura/features;
- PATCH = correção compatível, docs, acessibilidade ou performance;
- prerelease = `-dev.N`, `-beta.N`, `-rc.N` quando necessário.

Exemplos:

```text
0.2.0-dev.1
0.2.0-rc.1
0.2.0
0.2.1
```

## Proteção de componentes stable

Mesmo em 0.x:

- PATCH não quebra props/slots/semântica stable;
- quebra intencional exige MINOR e migration note;
- remoção deve documentar substituição quando houver consumidor.

Experimental pode mudar em MINOR, mas a quebra deve ser explícita.

## 1.0+

- MAJOR = breaking API;
- MINOR = feature compatível;
- PATCH = fix compatível.

## Tags

O repositório contém site, módulos e tema. Tags do tema são namespaced:

`aculta420-theme-v0.1.0`

Não usar `v0.1.0` genérico.

## Fonte de verdade

Release deve manter coerentes:

1. `aculta420.info.yml`;
2. `CHANGELOG.md`;
3. `docs/roadmap.md`;
4. tag Git quando publicada.

## Release checklist

- version metadata;
- CHANGELOG;
- roadmap;
- schema validation;
- PHP/Twig/YAML sanity;
- `drush cr`;
- config status/import;
- smoke matrix de domains;
- desktop/mobile;
- keyboard/focus;
- reduced motion;
- component states;
- asset attachment;
- nenhuma regra de negócio nova no tema.
