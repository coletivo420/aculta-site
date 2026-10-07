# R4 — Release and Tagging Runbook

Status: **pronto para execução futura após R3 PASS**

## Objetivo

Publicar uma versão Portal somente quando código, configuração, Runtime,
hardening e documentação apontarem para o mesmo baseline.

## Pré-condições

Antes de qualquer tag:

- R0 PASS;
- R1 fila integrada;
- R2 PASS;
- R3 PASS;
- zero finding Critical/High;
- config CLEAN;
- updatedb NONE;
- main sem PR funcional obrigatório pendente;
- CHANGELOG atualizado;
- documentação do release atualizada;
- rollback conhecido.

## Preparação do main

```sh
git fetch --prune origin
git switch main
git pull --ff-only origin main
git status --short
git rev-parse HEAD
```

Esperado:

- worktree clean;
- HEAD igual ao main aprovado.

## Gate técnico final

```sh
composer validate
vendor/bin/drush status
vendor/bin/drush cr
vendor/bin/drush config:status
vendor/bin/drush updatedb:status
git diff --check
```

Executar audit de dependencies quando a rede estiver disponível.

## Evidence pack

Registrar:

- release candidate SHA;
- Drupal/PHP;
- DB Runtime;
- Composer status;
- Drush status;
- config;
- updatedb;
- Domain matrix;
- User A/B;
- Search;
- AJAX;
- integrations;
- hardening;
- rollback;
- known limitations.

O evidence pack aponta para evidências existentes; não precisa duplicar logs
inteiros dentro do Git.

## CHANGELOG

Mover itens relevantes de `Unreleased` para a versão.

Registrar:

- features;
- refactors;
- migrations;
- dependencies;
- deprecations/removals;
- known limitations.

Não declarar como entregue o que permaneceu apenas especificado.

## Versionamento

Portal usa tag namespaced.

Para 1.0:

`portal-v1.0.0`

Para versões anteriores Runtime aprovadas:

`portal-v0.x.y`

Não criar tag genérica `v1.0.0` que possa conflitar com o versionamento do
repositório/plataforma.

## Tag

Somente no SHA aprovado.

Exemplo operacional:

```sh
git tag -a portal-v1.0.0 -m "ACULTA Portal 1.0.0"
git push origin portal-v1.0.0
```

Antes do push:

- confirmar SHA;
- confirmar que não existe tag divergente;
- confirmar release gates.

## GitHub Release

Criar release a partir da tag com:

- resumo;
- principais mudanças;
- upgrade notes;
- dependencies relevantes;
- known limitations;
- rollback/recovery pointer;
- documentação.

Não anexar banco, settings ou material de ambiente.

## Deploy

O deploy usa o runbook operacional do ambiente.

Ordem geral:

1. backup/restore point;
2. atualizar código;
3. dependencies;
4. DB update quando necessário;
5. config import;
6. cache rebuild;
7. cron/queue status;
8. HTTP smoke;
9. Domain smoke;
10. logs.

A ordem exata pode variar conforme o runbook de produção aprovado.

## Pós-deploy

Validar rapidamente:

- MAIN;
- ACCOUNT login;
- SUPPORT;
- MAGAZINE;
- WIKI;
- COURSES;
- FORUM se ativo;
- Search;
- AJAX principal;
- mail/integration status sem gerar ações destrutivas;
- logs.

SHOP segue o escopo realmente implementado no release.

## Rollback

Definir antes do deploy:

- código;
- dependency lock;
- config;
- DB restore/migration reversibility;
- cache.

Se DB migration não for reversível, isso precisa estar explícito antes da
janela.

## Hotfix

Hotfix pós-release:

- branch a partir do baseline apropriado;
- correção mínima;
- testes;
- PR;
- patch version;
- nova tag.

Não mover/regravar tag publicada.

## Critério de conclusão R4

- tag criada no SHA correto;
- release notes;
- deploy concluído;
- smoke pós-deploy;
- nenhum rollback acionado, ou rollback concluído e release marcado
  apropriadamente;
- documentação aponta para versão publicada.

## Próximo ciclo

Após 1.0, novas features voltam ao fluxo:

```text
spec -> branch -> tests -> PR -> release
```

O roadmap pós-1.0 deve ser criado a partir de necessidades reais, não por
continuação automática de numeração.
