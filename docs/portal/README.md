# ACULTA Portal

Documentação canônica da camada de integração `aculta_portal`.

O Portal integra Drupal Core/contrib, Domain, Conta, Commerce, LMS e conteúdo.
Ele não substitui as fontes de verdade desses subsistemas.

## Ler primeiro

- [Arquitetura](ARCHITECTURE.md)
- [Fontes de verdade](SOURCE-OF-TRUTH.md)
- [Camadas anti-regressão](../ANTI-REGRESSION.md)
- [Módulos Drupal](../modules/README.md)
- [Roadmap atual](ROADMAP.md)

## Conta

- [Política AJAX](AJAX.md)
- [Semântica de apresentação](ACCOUNT-PRESENTATION-MODEL.md)
- [Matriz SDC/AJAX](ACCOUNT-SDC-AJAX.md)
- [Conta, AJAX e Views interativas](../modules/ACCOUNT-UI.md)
- [Autenticação e identidade](../modules/AUTHENTICATION.md)

## Produto/domínios

- [Fórum](FORUM.md)
- [Wiki420](WIKI.md)
- [Revista](MAGAZINE.md)
- [Loja](SHOP.md)
- [Slugs públicos em português](FRIENDLY-PORTUGUESE-SLUGS.md)

## Tema

- [Integração com o Component Design System](COMPONENT-DESIGN-SYSTEM.md)
- [Documentação ACULTA420](../../web/themes/custom/aculta420/README.md)

## Domínios e requests

- [Política do domínio administrativo](ADMIN-DOMAIN-POLICY.md)
- [Requests e redirects cross-domain](CROSS-DOMAIN-REQUEST-POLICY.md)
- [Domain Presentation Contract](DOMAIN-PRESENTATION-CONTRACT.md)

## Operação

- [Testes](../operations/TESTING.md)
- [Hardening](../operations/HARDENING.md)
- [Releases](../operations/RELEASES.md)
- [Homelab](../../scripts/homelab/README.md)

## Integrações

- [Autenticação](../integrations/AUTHENTICATION.md)
- [CAPTCHA / Turnstile](../integrations/CAPTCHA.md)
- [Google](../integrations/GOOGLE.md)

## Regra principal

Antes de escrever código novo:

1. identificar a fonte de verdade;
2. usar a API pública de Core/contrib;
3. decidir o Domain purpose;
4. aplicar access/cache antes da apresentação;
5. preservar progressive enhancement;
6. atualizar a documentação canônica;
7. validar no Runtime quando houver mudança funcional.

Histórico de fases/PRs não é fonte de verdade. Consulte o Git/GitHub quando
precisar de evidência histórica.
