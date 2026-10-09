# Changelog — ACULTA Portal Sitemap

## 0.1.0-F — expansão para WIKI, MAGAZINE e COURSES — 2026-10-09

- `INDEXABLE_PURPOSES` passa a incluir `magazine`, `wiki` e `courses`; SHOP continua fora.
- `ROOT_PURPOSES` passa a incluir a raiz de cada purpose indexável (URL própria, rota).
- `isPurposeFrontNode()`: a página inicial do domínio (`system.site:page.front` da coleção
  `domain.<id>`) é excluída do conjunto de nós. Corrige `/noticias` do MAGAZINE, que redirecionava
  para a raiz.
- Dependência `domain_config` declarada em `aculta_portal_sitemap.info.yml`.
- Variantes de piloto no runtime: `wiki`, `magazine` e `courses`.
- Verificado no servidor de testes: MAIN 11 URLs; SUPPORT 1; WIKI 1 (`https://wiki420.aculta.org/`);
  MAGAZINE 1 (`https://coletivo420.aculta.org/`); COURSES 1 (`https://cursos.aculta.org/`).
  Total de 15 URLs canônicas de produção, sem duplicatas entre variantes.
- Pendência: verbetes da WIKI (nós 42–45) não entram porque o acesso anônimo é negado
  (tabela `node_access` sem concessões). Reconstruir concessões é operação de banco, não autorizada.
- Não promovido, não exportado para `config/sync`. Pendência: fase G (índice).

## 0.1.0-E — sitemaps piloto MAIN e SUPPORT — 2026-10-09

- Plugin `aculta_purpose_node` com injeção de dependências e regras R1 a R5a e R7.
- Tipo `aculta_purpose`, variantes `main` e `support`, e raiz do SUPPORT como URL própria.
- Verificado no servidor de testes: MAIN com 11 URLs canônicas de produção; SUPPORT com
  `https://apoio.aculta.org/`; sitemap institucional `default` inalterado (13 URLs).
- `DomainPurposeManager::canonicalPathUrl()` adicionado ao Portal (0.2.0-dev.7).
- Não promovido, não exportado para `config/sync`. Pendências: fase F (expansão) e G (índice).
