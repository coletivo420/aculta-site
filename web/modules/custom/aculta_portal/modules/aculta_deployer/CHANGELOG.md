# Changelog — ACULTA Deployer

## 0.1.3 — descoberta e sitemaps por ambiente (0.1.0-H) — 2026-10-09

- Comando `sitemap --env=production|test` (GET somente leitura): confere o índice central,
  a base de cada filho (problema 1: base de teste em produção ou o inverso) e os hosts das URLs
  de conteúdo, que devem pertencer à política de produção e responder (problema 2: cross-host,
  por exemplo `apoio.aculta.org`).
- `robots --env=production` passa a conferir o `robots.txt` de cada host: diretiva `Sitemap:`
  apontando para o índice de produção e ausência de `Disallow: /`.
- `web/robots.txt` anuncia `Sitemap: https://aculta.org/sitemap.xml`.
- `config/deploy.json` ganha o bloco `sitemap` por ambiente (`index_url` e `index_base`).
- Helpers em `Verify`: `xmlLocs()` (sem entidades externas), `sitemapDirectives()`,
  `disallowsRoot()` e `hostOf()`.
- Testes: 15 asserções novas (XML, XXE, diretivas, host e coerência com `web/robots.txt`).

## 0.1.2 — verificação de caminhos privados e meta robots — 2026-10-09

- `robots --env=production` passa a conferir os caminhos privados (`private_probes` em
  `config/deploy.json`): conta, login, painel, carrinho e checkout.
- Caminho privado passa quando tem noindex no cabeçalho ou em `<meta name="robots">`, ou
  responde 401, 403, 404 ou 410. Um 200 sem noindex falha.
- `Verify::metaNoindex()`, `Verify::statusCode()` e `Verify::isRefusedStatus()` adicionados.
- Testes: asserções de meta robots, status e lista de caminhos privados de produção.
- `sitemap --env=test` verifica os hosts de conteúdo pelo equivalente de teste (`apoio.aculta.toca.net.br`), sem depender de produção: PASS no servidor de testes.
- Documentação em `docs/USO.md` e `docs/GUARDRAILS.md` (motivo da checagem de meta e de 404/410).
- Pendência fora deste submódulo: o Portal não emite `noindex` em `/entrar` (rota
  `user.login`); a regra em `PortalHooks` cobre só rotas `aculta_portal.*`.

## 0.1.1 — política de indexação por ambiente — 2026-10-09

- Comando `robots --env=production|test` (GET somente leitura) por host.
- `build` grava `deploy-policy.json` e recusa política de produção com noindex.
- Política: produção indexável nos sete domínios; teste com noindex.
- Testes da leitura de cabeçalho, da detecção de noindex e da cobertura dos domínios.

## 0.1.0 — primeira versão — 2026-10-09

- Submódulo do `aculta_portal` em `modules/aculta_deployer/`, descoberto e não habilitado.
- CLI standalone `bin/aculta-deployer` com `check`, `boundaries`, `list`, `register`, `build` e `version`.
- Build de produção: substitui `*.aculta.toca.net.br` por `*.aculta.org` (preserva o prefixo) e remove aliases de teste.
- Registro de correções de deploy com validação de campos e ids sequenciais.
- Barreiras de separação verificadas por `boundaries`: tema e Portal (fora do submódulo) não referenciam a ferramenta; a ferramenta não depende de Drupal, Drush, vendor, tema ou Portal.
- Testes determinísticos em `tests/run.php`.
- Entradas iniciais: DEP-0001 (canonical da página de apoio) e DEP-0002 (sitemap sem o host de apoio), ambas bloqueantes e abertas.
