# Macrofase S — Fechamento do trabalho sem Runtime

Data: 2026-10-06/07

Status: **todo trabalho seguro/útil identificado sem Homelab foi preparado**

## Objetivo

Registrar o ponto exato em que o projeto deixa de ganhar qualidade significativa
com mudanças não executadas e passa a precisar do Runtime.

Isso não significa que todos os drafts estão aprovados.

Significa:

- arquitetura/documentação possível foi consolidada;
- refactors seguros foram preparados em PRs draft;
- feature specs 0.11–0.19 foram escritas;
- gates 1.0 foram definidos;
- runbooks R0–R4 estão prontos;
- próximos passos funcionais exigem validação Drupal real.

## Baseline do main

No fechamento desta fase:

`7db70da9884f103697b3d703633b52e3404f0ea9`

O valor histórico acima documenta o fechamento; durante R0 deve-se sempre usar
o `origin/main` real mais recente.

## Concluído e integrado no main

### Fundação/arquitetura

- Portal Foundation;
- Source of Truth;
- Bootstrap Component Design System;
- GitHub-first / Runtime-last;
- Static Portal Audit;
- matriz Minha Conta SDC/AJAX;
- semântica compartilhada de apresentação.

### Auditorias S3 integradas documentalmente

- S3.3A AJAX boundary;
- S3.6 CSS ownership;
- S3.8 lifecycle/install;
- S3.9 avatar assets.

### Feature specifications

- 0.11 Forum Foundation;
- 0.12 Forum Participation;
- 0.13 Participation Hub;
- 0.14 Admin Hub;
- 0.15 AJAX Consolidation;
- 0.16 Search;
- 0.17 Engagement;
- 0.18 Deduplication;
- 0.19 Hardening.

### Release/runtime documentation

- Portal 1.0 Release Gates;
- R0 Clean Baseline;
- R1 Integration Queue;
- R2 Functional Validation;
- R3 Hardening Execution;
- R4 Release and Tagging.

## Código preparado em drafts

Os seguintes PRs permanecem propositalmente abertos porque contêm código
executável ainda não comprovado no Homelab.

| PR | Fase | Dependência/observação |
| --- | --- | --- |
| #23 | S3.1 Hooks + DI | primeiro draft estrutural |
| #24 | S3.2A Course presenter | independente; LMS/Group Runtime |
| #26 | S3.2C Segurança + Conexões | Social Auth/mail/container |
| #27 | S3.2D Identidade + Dados | empilhado sobre #26 |
| #28 | S3.3 Support access first | Commerce/access |
| #29 | S3.4 Wiki boundary | Wiki/Views/access |
| #30 | S3.5 Domain policy | empilhado sobre #23 |
| #32 | S3.7A Editorial/SEO hooks | aplicar sequencialmente |
| #33 | S3.7B editorial form hook | aplicar sequencialmente |
| #34 | S3.7C entity security hooks | inclui responsabilidades Commerce/security |
| #35 | S3.7D forms/library hooks | inclui CommerceFormHooks |

Todos devem continuar com:

`RUNTIME STATUS: DEFERRED`

até os gates próprios passarem.

## S3.7 e Commerce

Não existe necessidade de criar artificialmente um S3.7E apenas para eliminar a
função `aculta_portal_validate_donation_amount()`.

Ela é callback de validação Form API, não implementação de hook.

O objetivo S3.7 é migrar **hooks procedurais** para classes OOP. O callback pode
permanecer procedural até existir motivo técnico comprovado para uma API
diferente.

O código Commerce dentro de `hook_form_alter` já está preparado no PR #35, e
entity access/presave no PR #34.

## Tema

O tema continua linha paralela.

PR #19 pertence à Fase H do tema e não deve ser misturado aos drafts Portal.

Portal não deve implementar SDCs concorrentes para contornar o roadmap do tema.

## O que deliberadamente não foi feito

### Composer/config de features futuras

Não instalar/configurar antecipadamente:

- Forum;
- Search API;
- Flag;
- Comment Notify.

Isso exige Runtime e Configuration Sync real.

### Domains/config FORUM

Não fabricar YAML final.

Criar/exportar no Runtime da 0.11.

### SDCs futuros

Não criar no módulo.

A Fase H do tema decide maturidade/ownership.

### Testes

Nenhum draft executável é chamado de PASS sem Homelab.

### Produção

Nenhuma mudança em Hostinger/produção.

## Fronteira Runtime

A partir deste ponto, novas refatorações amplas sem executar Drupal aumentam o
risco mais rápido do que aumentam a qualidade.

A próxima ação correta é R0 quando houver janela do Codex/Homelab.

## Ordem operacional

```text
R0 clean baseline
        ↓
R1 drafts um por vez
        ↓
0.11 -> 0.19
        ↓
R2 funcional durante toda a fila
        ↓
R3 hardening
        ↓
1.0 gates
        ↓
R4 release/tag
```

R2 não precisa esperar todas as features: ele acompanha cada integração.

## Regra para trabalho novo antes do Runtime

Somente abrir nova PR se surgir:

- finding documental real;
- conflito arquitetural novo;
- atualização upstream material;
- bug claramente demonstrável por revisão estática;
- necessidade explícita do usuário.

Não criar refactor especulativo apenas porque a janela Runtime ainda não abriu.

## Resultado

**Macrofase S encerrada no limite seguro do trabalho sem ambiente.**

O backlog restante é majoritariamente execução, validação e correção baseada em
evidência Runtime.
