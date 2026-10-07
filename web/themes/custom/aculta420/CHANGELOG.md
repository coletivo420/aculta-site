# Changelog — tema ACULTA

Este changelog versiona somente o tema `aculta` / ACULTA Bootstrap Component
Design System.

## 0.1.0 — 2026-10-07

Baseline versionada do design system.

### Arquitetura

- encerra a refatoração estrutural defensiva A–G4;
- formaliza o ACULTA Bootstrap Component Design System;
- mantém Bootstrap5 como infraestrutura e ACULTA como linguagem visual;
- documenta a fronteira Drupal/contrib -> aculta_portal -> tema.

### Foundations

- tokens ACULTA centralizados;
- integração com custom properties Bootstrap;
- CSS separado por responsabilidade;
- correções de tokens/RGB auditadas.

### Components

- `aculta:editorial-card` é o primeiro SDC `stable`;
- CSS exclusivo do editorial card co-localizado;
- presenter Drupal preserva attributes/title_prefix/title_suffix;
- auditoria de primitives registrada.

### Assets

- navigation JS permanece global;
- editorial-carousel JS é contextual;
- VVJB permanece responsável pela engine do carrossel editorial.

### Twig

- overrides redundantes removidos;
- herança Bootstrap5/Core priorizada.

### Próximo ciclo

0.2.0 inicia Foundations 2.0, schemas obrigatórios, motion/semantic tokens e
primeiro primitive SDC experimental.
