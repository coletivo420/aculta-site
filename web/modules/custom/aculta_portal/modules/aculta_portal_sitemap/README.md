# ACULTA Portal Sitemap

Submódulo do `aculta_portal` que integra o sitemap multidomínio da plataforma. Linha de
desenvolvimento **0.1.x**, planejada em `docs/decisions/ADR-009-sitemap-multidominio.md`.

## Estado (microfase 0.1.0-B)

- Estrutura mínima: metadados, dependências declaradas e documentação.
- **Opt-in:** habilitar o módulo não modifica a geração do sitemap. Não há rotas, serviços
  ou hooks nesta microfase.
- Dependências: `aculta_portal`, `simple_sitemap` (4.x) e `domain`. O Portal principal
  não depende deste submódulo.
- Não está habilitado no ambiente de desenvolvimento.

## Próximas microfases

- 0.1.0-C: avaliação do `domain_simple_sitemap` 3.0.0-rc3 (superfície e issues).
- 0.1.0-D: política de indexação por purpose, consumindo o `DomainPurposeManager`.
- 0.1.0-E: sitemaps piloto para MAIN e SUPPORT.

Documentação de referência: `docs/decisions/ADR-009-sitemap-multidominio.md`.
