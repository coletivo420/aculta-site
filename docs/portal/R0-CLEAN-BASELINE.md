# R0 — Clean Baseline Runbook

Status: **pronto para execução futura no Homelab**

## Objetivo

Começar a janela Runtime a partir do `origin/main` real, descartando trabalho
local antigo não publicado conforme decisão do projeto.

R0 não aplica nenhuma feature.

## Fonte autoritativa

`origin/main`

Não recuperar a antiga linha local 9.3B.

## Procedimento inicial

```sh
git fetch --prune origin
git switch main
git reset --hard origin/main
git clean -fd
```

Não usar `git clean -fdx`.

Motivo:

- arquivos ignorados podem conter settings locais;
- Runtime local;
- material de ambiente;
- arquivos que não pertencem ao Git.

Se a branch local antiga ainda existir e não possuir trabalho remoto a
preservar:

```sh
git branch -D chore/fase-9.3b-estado-integral 2>/dev/null || true
```

Não criar stash do worktree descartado.

## Gate Git

Registrar:

```sh
git status --short
git branch --show-current
git rev-parse HEAD
git rev-parse origin/main
git log --oneline -10
```

Esperado:

- branch = main;
- HEAD = origin/main;
- worktree clean.

## Baseline de dependências

Sem alterar nada:

```sh
composer validate
vendor/bin/drush status
vendor/bin/drush config:status
vendor/bin/drush updatedb:status
```

Registrar `composer audit` quando a rede estiver disponível.

Falha de rede = INCONCLUSIVE, nunca PASS.

## Apache/Homelab

Executar:

```sh
bash scripts/homelab/verify-aculta-homelab.sh
```

Esperado:

- Apache baseline PASS;
- PHP-FPM operacional;
- SQLite Runtime correto;
- nenhum processo nginx ativo segundo o baseline;
- Drupal bootstrap;
- config/updatedb coerentes.

## HTTP baseline

Validar antes de aplicar drafts:

- MAIN;
- ACCOUNT;
- SUPPORT;
- MAGAZINE;
- WIKI;
- SHOP;
- COURSES.

FORUM ainda não é exigido antes da 0.11.

Registrar expectativa atual de SHOP sem inventar storefront concluído.

## Regra stop

Se o baseline puro do `main` falhar:

**não aplicar nenhum PR draft.**

Corrigir primeiro o baseline em PR próprio ou registrar infraestrutura externa.

## Saída R0

Relatório mínimo:

- data;
- main SHA;
- PHP/Drupal;
- DB runtime;
- Apache;
- Composer;
- Drush;
- config;
- updatedb;
- HTTP matrix;
- worktree;
- blockers.

## Próxima fase

R1 — Dependency and Config Integration.
