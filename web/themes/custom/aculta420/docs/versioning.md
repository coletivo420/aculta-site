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

## Decisão de versão (0.3.1)

A fase de padrões SDC (seções, hero, grade, carrossel e cabeçalho por purpose) foi
registrada como **0.3.1** por decisão do responsável. A regra acima recomendaria MINOR,
porque introduz componentes novos. A exceção vale somente porque:

- os componentes novos são `experimental`;
- nenhum componente `stable` teve API alterada;
- o escopo é a conclusão de uma migração iniciada na 0.3.x.

Qualquer fase futura com componente novo deve seguir a regra geral e registrar a
classificação antes de codar. Não reutilizar esta exceção como precedente sem decisão
explícita.

## Subversões e registro de mudanças (obrigatório)

Toda mudança do tema e do Portal passa por este procedimento, antes de começar o código.

### 1. Classificar antes de codar

| Tipo de mudança | Versão alvo | Exemplo |
| --- | --- | --- |
| correção, docs, acessibilidade, performance sem API nova | PATCH | corrigir sticky, reduced motion |
| componente novo `experimental`, ou feature compatível | MINOR (ou PATCH com decisão registrada) | project-card |
| quebra de componente `stable`, ou de contrato Portal → tema | MINOR pré-1.0, com nota de migração | troca de props stable |
| breaking em 1.0+ | MAJOR | — |

Antes de codar, anotar: a versão alvo, a lista das mudanças principais e a subversão
prevista para cada uma (`X.Y.Z-dev.N`).

### 2. Subversões `-dev.N` por mudança principal validada

- Cada mudança principal, depois de validada (gates de tema e Runtime, e HTTP ou
  navegador no escopo), recebe o próximo `-dev.N` e uma tag anotada:
  `aculta420-theme-vX.Y.Z-dev.N`.
- A tag vai no commit que está na `main` após o merge, nunca em commit intermediário
  não validado.
- Estados quebrados ou não validados não recebem tag. Um commit intermediário pode
  existir na branch sem tag.

### 3. Entrada no CHANGELOG

Para cada `-dev.N`, uma linha com a mudança principal, o SHA da `main` e o status de
validação. Mudanças que não foram validadas entram como "não validado".

### 4. Release final

Quando a fase fecha:

1. `aculta420.info.yml` recebe a versão final;
2. `CHANGELOG.md` recebe a seção com data;
3. `docs/roadmap.md` marca a fase como concluída;
4. a tag `aculta420-theme-vX.Y.Z` é criada na `main` depois do merge;
5. o gate de Foundation passa a esperar a versão final.

Os quatro documentos acima precisam estar coerentes (ver "Fonte de verdade").

### 5. Portal e configuração

- Mudança em `aculta_portal` entra também no `CHANGELOG.md` do módulo, com a versão
  do tema em que foi entregue. O Portal ainda não tem tag própria; a primeira release
  rastreada deve usar `portal-vX.Y.Z` (ver `docs/operations/RELEASES.md`).
- Mudança de configuração ou de conteúdo do Runtime registra a alteração no CHANGELOG
  do tema, com a dependência de conteúdo que outro ambiente precisa.

### 6. PRs

- Uma PR por fase ou subversão. Ao fechar uma PR obsoleta, registrar o motivo no
  próprio PR e arquivar o que for único fora do repositório antes de excluir a branch.
- Antes de mesclar, a PR não pode deixar commit intermediário em conflito com a
  versão declarada.
