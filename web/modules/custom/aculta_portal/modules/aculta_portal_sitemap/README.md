# ACULTA Portal Sitemap

Submódulo do `aculta_portal` que integra o sitemap multidomínio da plataforma (linha 0.1.x,
ADR-009 em `docs/decisions/`).

## Estado (microfase 0.1.0-F: expansão para WIKI, MAGAZINE e COURSES)

- Plugin `aculta_purpose_node` (`PurposeNodeUrlGenerator`): gera URLs canônicas de nós por
  purpose de origem (`field_domain_source`), com regras R1 a R5a e R7 da política de indexação.
- Raiz de cada purpose indexável (SUPPORT, MAGAZINE, WIKI, COURSES) incluída como URL própria,
  porque a página inicial é uma rota, não um nó (`ROOT_PURPOSES`).
- Página inicial de domínio definida em `system.site:page.front` da coleção do domínio é
  excluída do conjunto de nós, para não gerar URL que redireciona para a raiz.
- Verbetes da WIKI não aparecem enquanto o acesso anônimo a eles for negado (R4).
- SHOP fica fora (sem conteúdo público e sem página inicial).
- Tipo de sitemap `aculta_purpose`; variantes `main` e `support`, com o purpose nas
  configurações de terceiros de cada variante.
- Endpoints pilotos: `/sitemaps/{main,support,magazine,wiki,courses}/sitemap.xml`.
- O sitemap institucional `default` (`/sitemap.xml`) não foi alterado.
- **Não promovido:** o índice central e a troca de `/sitemap.xml` pertencem à fase G.
- **Habilitação:** o módulo está habilitado no runtime de desenvolvimento para o piloto. A
  habilitação e as variantes não foram exportadas para `config/sync` (ato de deploy, a decidir).

## Dependências

`aculta_portal` (serviço `aculta_portal.domain_purpose`), `simple_sitemap` 4.x e `domain`.

## Próximas microfases

- G: índice central e promoção controlada de `/sitemap.xml`.
