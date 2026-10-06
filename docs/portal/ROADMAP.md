# Roadmap do ACULTA Portal

Este roadmap organiza a evolução do `aculta_portal` em versões revisáveis.

O tema `aculta` possui linha de trabalho própria e não faz parte deste roadmap.

## 0.10.0 — Foundation

Objetivo: organizar antes de ampliar.

Entregas:

- arquitetura;
- fontes de verdade;
- política upstream;
- política AJAX;
- especificação Fórum;
- testes;
- versionamento;
- roadmap;
- CHANGELOG.

Nenhuma feature pública nova é requisito desta versão.

## 0.11.0 — Forum Foundation

Objetivo: ativar o Fórum como oitavo purpose.

Entregas planejadas:

- `drupal/forum`;
- Domain `forum.aculta.org`;
- alias `forum.aculta.toca.net.br`;
- purpose `forum`;
- landing nativa;
- tópicos/respostas;
- isolamento;
- sessão compartilhada;
- config exportada;
- testes.

## 0.12.0 — Forum Participation

Objetivo: integrar a participação do Fórum na Conta.

Entregas:

- página/seção "Minha participação";
- meus tópicos;
- minhas respostas;
- links FORUM;
- Views filtradas pelo usuário;
- AJAX via Views/Core;
- cache/access.

Ainda sem storage Portal.

## 0.13.0 — Participation Hub

Objetivo: central única da participação do usuário.

Integrar:

- Fórum;
- Wiki;
- Cursos;
- comentários relevantes aprovados.

A Conta passa a apresentar atividade cruzada sem copiar dados.

## 0.14.0 — Admin Hub

Objetivo: transformar `/admin/config/aculta/portal` em hub real.

Integrar status/links para:

- usuários/perfis;
- Domains;
- Wiki;
- Fórum/moderação;
- cursos/Group/LMS;
- Commerce/apoio/pagamentos;
- Webforms;
- requisitos;
- segurança.

Não recriar CRUDs.

## 0.15.0 — AJAX Consolidation

Objetivo: reduzir infraestrutura JavaScript própria.

Alvo principal:

`account-navigation.js`.

Migrar progressivamente para:

- Views AJAX;
- Form API AJAX;
- Drupal Ajax API;
- Core HTMX quando apropriado.

Preservar UX, accessibility e progressive enhancement.

A adaptação necessária do CEP não é removida automaticamente.

## 0.16.0 — Search

Objetivo: adotar Search API.

Primeiros índices:

- Wiki;
- Fórum.

Substituir gradualmente a busca `LIKE` custom da Wiki.

Backend inicial pode usar Database Search no Homelab, se compatível com o
desenho aprovado na implementação.

## 0.17.0 — Engagement

Objetivo: acompanhamento e notificações.

Candidatos:

- Flag;
- Comment Notify.

Entregas dependem de revisão de SMTP, privacidade e UX.

## 0.18.0 — Deduplication

Objetivo: remover somente duplicações comprovadas.

Auditar:

- breadcrumb custom;
- plugins Schema Metatag;
- preprocess de menus;
- busca Wiki antiga;
- transport AJAX antigo;
- Support table manual;
- CEP override excessivo;
- usos estáticos de `\Drupal::`.

Cada remoção precisa de substituto upstream + teste de paridade.

## 0.19.0 — Hardening

Foco:

- permissions;
- access;
- cache;
- CSRF;
- AJAX;
- Domain isolation;
- Security Review;
- performance;
- logs;
- cron;
- SQLite/MariaDB portability.

## 1.0.0 — Portal Stable

Gates:

- Conta estável;
- dados/CEP estáveis;
- apoio integrado;
- cursos integrados;
- Wiki integrada;
- Fórum integrado;
- participação integrada;
- admin hub;
- Search API;
- AJAX consolidado;
- Domain isolation;
- Apache Homelab;
- SQLite Runtime;
- compatibilidade MariaDB;
- security review;
- runbook;
- snapshot/restore;
- documentação completa.

## Disciplina

Somente uma versão funcional deve estar em implementação principal por vez.

Não antecipar dependências de versões futuras apenas porque aparecem neste
roadmap.
