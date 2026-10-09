# Changelog — ACULTA Deployer

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
