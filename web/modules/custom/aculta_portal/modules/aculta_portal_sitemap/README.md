# ACULTA Portal Sitemap

Submódulo do `aculta_portal` que integra o sitemap multidomínio da plataforma (linha 0.1.x,
ADR-009 em `docs/decisions/`).

## Estado (microfase 0.1.0-E: pilotos MAIN e SUPPORT)

- Plugin `aculta_purpose_node` (`PurposeNodeUrlGenerator`): gera URLs canônicas de nós por
  purpose de origem (`field_domain_source`), com regras R1 a R5a e R7 da política de indexação.
- Raiz do SUPPORT (`https://apoio.aculta.org/`) incluída como URL própria, porque a página de
  apoio é uma rota, não um nó (`ROOT_PURPOSES`).
- Tipo de sitemap `aculta_purpose`; variantes `main` e `support`, com o purpose nas
  configurações de terceiros de cada variante.
- Endpoints pilotos: `/sitemaps/main/sitemap.xml` e `/sitemaps/support/sitemap.xml`.
- O sitemap institucional `default` (`/sitemap.xml`) não foi alterado.
- **Não promovido:** o índice central e a troca de `/sitemap.xml` pertencem à fase G.
- **Habilitação:** o módulo está habilitado no runtime de desenvolvimento para o piloto. A
  habilitação e as variantes não foram exportadas para `config/sync` (ato de deploy, a decidir).

## Dependências

`aculta_portal` (serviço `aculta_portal.domain_purpose`), `simple_sitemap` 4.x e `domain`.

## Próximas microfases

- F: expansão para WIKI, COURSES, SHOP e MAGAZINE.
- G: índice central e promoção controlada de `/sitemap.xml`.
