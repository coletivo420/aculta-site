# R3 — Hardening Execution Runbook

Status: **pronto para execução futura no Homelab**

## Objetivo

Transformar a especificação do Portal 0.19 em uma execução ordenada,
reproduzível e com critérios de severidade.

Documento normativo de escopo:

[S4-0.19-HARDENING.md](S4-0.19-HARDENING.md)

## Pré-condições

- R0 PASS;
- drafts estruturais relevantes integrados;
- R2 funcional sem regressão crítica aberta;
- config status conhecido;
- rollback disponível.

## Ordem de execução

### H1 — Access

Executar primeiro porque vazamento de dados invalida as demais medições.

Cobrir:

- route access;
- entity access;
- revision access;
- User A/B;
- admin partial/full;
- wrong-host.

Finding alto/crítico:

- metadata privada visível;
- operação mutável sem autorização;
- rota especializada acessível no purpose errado quando deveria falhar closed.

### H2 — Cache

Depois de access:

- contexts;
- tags;
- max-age;
- Views;
- BigPipe;
- grants;
- Domain;
- user-specific state.

Testar conteúdo com User A e B alternando requests.

### H3 — Mutações

Revisar forms/callbacks/toggles/actions:

- proteção Core/contrib;
- método HTTP;
- access;
- confirmação;
- failure.

### H4 — Session / Domain

- login/logout;
- reset;
- OAuth;
- shared production trust boundary;
- aliases Homelab;
- cookies;
- wrong-host.

### H5 — Integrações externas

Separadamente:

- transactional mail;
- Social Auth;
- CEP;
- Commerce/payment/webhook;
- Search;
- Google platform features realmente habilitadas.

Simular indisponibilidade.

### H6 — Performance

Somente após correção de access/cache.

Medir:

- request time;
- DB queries;
- N+1;
- Views;
- Search;
- Participation aggregation;
- Admin counts;
- AJAX volume.

Registrar baseline antes/depois.

### H7 — Cron / queues

- execução;
- repetição;
- failure;
- recovery;
- observability.

### H8 — Logs / headers

- canais/níveis;
- minimização de dados;
- headers Apache/Drupal;
- policies de browser compatíveis com integrações ativas.

### H9 — Dependency/security status

```sh
composer validate
composer audit
vendor/bin/drush status
vendor/bin/drush pm:security
```

Se um comando não existir na versão instalada, usar o mecanismo equivalente e
registrar.

Falha de rede = INCONCLUSIVE.

### H10 — Portabilidade

Validar pontos custom relevantes em:

- SQLite Runtime;
- MariaDB compatibility environment quando disponível.

Não alterar o banco do Homelab só para executar o gate.

### H11 — Failure/rollback

Simular falhas externas e executar rollback de pelo menos uma mudança
representativa de dependency/config.

## Severidade

### Critical

- exposição grave de dados;
- bypass de autorização;
- corrupção/perda de dados;
- execução não autorizada;
- falha de isolamento de alto impacto.

Bloqueia imediatamente.

### High

- acesso incorreto relevante;
- CSRF/mutação insegura;
- segredo/material sensível exposto;
- payment/webhook insecurity;
- regressão de sessão/domain com risco.

Bloqueia 1.0.

### Medium

- cache incorreto sem exposição grave;
- failure handling ruim;
- problema de a11y importante;
- performance relevante;
- observability insuficiente.

Corrigir antes do 1.0 ou aceitar formalmente com owner/prazo apenas se o risco
for compatível.

### Low

- melhoria não bloqueante;
- manutenção;
- documentação menor.

Pode virar backlog pós-1.0.

## Registro de finding

Cada finding deve ter:

- ID;
- severidade;
- área;
- commit;
- passos;
- impacto;
- evidência;
- correção proposta;
- PR;
- retest;
- status.

Não corrigir diretamente no servidor sem PR quando a mudança pertence ao repo.

## Regra de correção

Para finding:

1. criar branch;
2. corrigir mínimo;
3. testar;
4. atualizar docs;
5. PR;
6. review;
7. merge;
8. sincronizar Homelab;
9. retest;
10. fechar finding.

## Evidências

Evitar dumps indiscriminados.

Guardar apenas material necessário para provar:

- status;
- comando;
- resposta HTTP;
- cache/header;
- logs minimizados;
- query/performance;
- retest.

## Critério de saída R3

- zero Critical;
- zero High;
- Mediums resolvidos ou formalmente aceitos;
- dependency/security status conhecido;
- access/cache/session PASS;
- integrations failure-safe;
- portabilidade avaliada;
- rollback PASS;
- relatório final produzido.

## Próxima fase

R4 — Release and Tagging.
