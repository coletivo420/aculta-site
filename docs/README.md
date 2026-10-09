# Documentação do ACULTA

Documentação normativa do projeto. Só guardrails e referências vigentes: regras que bloqueiam
regressão, políticas e procedimentos de operação. Histórico de fases, relatórios e planos
concluídos não ficam aqui; estão no Git (ver [DOCUMENTATION.md](DOCUMENTATION.md)).

## Começar

- [AGENTS.md](../AGENTS.md) — instruções para agentes e humanos (raiz do repositório).
- [DOCUMENTATION.md](DOCUMENTATION.md) — política desta documentação: o que é guardrail e o que sai.
- [operations/DEBT-REGISTER.md](operations/DEBT-REGISTER.md) — dívidas e pendências, com evidência.

## Arquitetura e decisões

- [architecture/overview.md](architecture/overview.md) — camadas Core → Portal → tema → Bootstrap.
- [architecture/multidomain.md](architecture/multidomain.md) — purposes, hosts e cookie compartilhado.
- [architecture/environments.md](architecture/environments.md) — Homelab e produção; Apache como baseline.
- [architecture/data-ownership.md](architecture/data-ownership.md) — quem é dono de cada dado.
- [decisions/](decisions/) — ADRs vigentes.

## Módulos e integrações

- [modules/README.md](modules/README.md) — referência canônica dos módulos e das regras anti-regressão.
- [ANTI-REGRESSION.md](ANTI-REGRESSION.md) — camadas de anti-regressão.
- [integrations/README.md](integrations/README.md) — integrações externas: autenticação, CAPTCHA e Google.

## Portal

- [portal/README.md](portal/README.md) — índice do Portal.
- [portal/DRUPAL-11-STANDARDS.md](portal/DRUPAL-11-STANDARDS.md) — padrão obrigatório Drupal 11+ para código.
- [portal/GUARDRAILS.md](portal/GUARDRAILS.md) — regras de Fórum, Revista, Loja, Wiki e Conta.
- [portal/ROADMAP.md](portal/ROADMAP.md) — saneamento e, depois, features de produto.

## Tema

- [../web/themes/custom/aculta420/docs/README.md](../web/themes/custom/aculta420/docs/README.md) — índice do ACULTA420.

## Operação

- [operations/README.md](operations/README.md) — testes, releases, segredos, deploy e dados de teste.
