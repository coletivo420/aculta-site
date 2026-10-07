# ACULTA Portal

Esta pasta é a fonte de verdade documental do `aculta_portal`.

O módulo existe para integrar capacidades nativas do Drupal e módulos contrib em
uma experiência única para usuários e administradores. Ele não substitui as
fontes de verdade funcionais dessas ferramentas.

## Documentos

- [Arquitetura](ARCHITECTURE.md)
- [Fontes de verdade](SOURCE-OF-TRUTH.md)
- [Módulos upstream](UPSTREAM-MODULES.md)
- [Política AJAX](AJAX.md)
- [Integração com o Bootstrap Component Design System](COMPONENT-DESIGN-SYSTEM.md)
- [Minha Conta — matriz SDC, integrações e AJAX](ACCOUNT-SDC-AJAX.md)
- [Minha Conta — semântica compartilhada de apresentação](ACCOUNT-PRESENTATION-MODEL.md)
- [Fórum](FORUM.md)
- [Wiki420](WIKI.md)
- [Revista / Observatório Coletivo 420](MAGAZINE.md)
- [Loja](SHOP.md)
- [Modo de entrega GitHub-first / Runtime-last](DELIVERY-MODE.md)
- [S2 — Static Portal Audit](STATIC-AUDIT.md)
- [Mapa de refatoração](REFACTOR-MAP.md)
- [S3.8 — Lifecycle/install audit](S3-8-LIFECYCLE-INSTALL.md)
- [S3.6 — Public CSS ownership](S3-6-CSS-OWNERSHIP.md)
- [Testes](TESTING.md)
- [Versionamento](VERSIONING.md)
- [Roadmap](ROADMAP.md)
- [Integrações Google](../integrations/GOOGLE.md)

## Estado atual

- baseline web do Homelab: Apache + PHP-FPM;
- Runtime de desenvolvimento: SQLite;
- produção: Apache + MariaDB;
- sete purposes ativos: MAIN, ACCOUNT, SUPPORT, MAGAZINE, WIKI, SHOP e COURSES;
- oitavo purpose planejado: FORUM;
- `aculta_portal` continua sendo a camada de integração;
- o tema `aculta` já está em fase avançada do ACULTA Bootstrap Component Design System;
- o Portal opera temporariamente em modo GitHub-first / Runtime-last;
- trabalho local antigo não publicado não é baseline; `origin/main` é autoritativo.

## Regra principal

Antes de escrever código para uma capacidade nova, identificar:

1. qual módulo já é fonte de verdade;
2. qual API pública esse módulo oferece;
3. qual Domain purpose recebe a experiência;
4. qual parte realmente precisa de adaptação pelo Portal;
5. como acesso, cache e privacidade serão preservados;
6. como a feature será testada no Homelab.

Código custom deve existir para **orquestrar, adaptar e integrar**, não para criar
uma segunda implementação da mesma capacidade.
