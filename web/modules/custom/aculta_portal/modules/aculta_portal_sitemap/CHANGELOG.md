# Changelog — ACULTA Portal Sitemap

## 0.1.0-E — sitemaps piloto MAIN e SUPPORT — 2026-10-09

- Plugin `aculta_purpose_node` com injeção de dependências e regras R1 a R5a e R7.
- Tipo `aculta_purpose`, variantes `main` e `support`, e raiz do SUPPORT como URL própria.
- Verificado no servidor de testes: MAIN com 11 URLs canônicas de produção; SUPPORT com
  `https://apoio.aculta.org/`; sitemap institucional `default` inalterado (13 URLs).
- `DomainPurposeManager::canonicalPathUrl()` adicionado ao Portal (0.2.0-dev.7).
- Não promovido, não exportado para `config/sync`. Pendências: fase F (expansão) e G (índice).
