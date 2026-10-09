# Changelog — ACULTA Deployer

## 0.1.10 — verify por ambiente; build somente de produção (fase 10) — 2026-10-09

- `verify` respeita o ambiente (`--env` ou o arquivo): no teste, as sondas e os valores esperados do registro são
  convertidos para os hosts de teste (`Verify::toTestEnvironment`). Host parecido com produção não é alterado.
- `build --env=test` é recusado: o build gera somente a saída de produção.
- Testes: conversão de hosts (canonical e host parecido) e recusa do build de teste.

## 0.1.9 — remoção da seção de credenciais do relatório — 2026-10-09

- `report` deixa de incluir a situação das credenciais obrigatórias por ambiente, e a opção `--file` sai com ela.
- A página de status do Portal deixa de mostrar a tabela de credenciais. O cadastro continua na página "Credenciais do ambiente".
- Removido `src/Secrets.php` (sem uso). A checagem de nomes do contrato contra `docs/operations/SECRETS.md` lê o JSON diretamente.

## 0.1.8 — ambiente do site (teste/produção) — 2026-10-09

- Comando `environment [show] | environment set --to=production|test`: grava `var/deployer/environment.json` com o
  ambiente e o endereço do site (`config/deploy.json`, bloco `environments`). Valor desconhecido é recusado.
- `robots`, `sitemap` e `report` usam o mesmo ambiente de trabalho: `--env`, senão o arquivo, senão `test`.
- Relatório do painel inclui ambiente e endereço. O Portal lê o mesmo arquivo para escolher o conjunto de credenciais.
- Testes: set de produção e teste, recusa e restauração do arquivo real.

## 0.1.7 — remoção da importação de credenciais; cadastro pelo painel do Portal — 2026-10-09

- Removidos os comandos `secrets check` e `secrets export` e as funções que liam ou gravavam valores de credenciais.
  O cadastro passa a ser feito no painel "Credenciais do ambiente" do `aculta_portal` (0.2.0-dev.11).
- `Secrets.php` mantém só o formato `NAME=value` e o contrato usados pelo relatório (`report`).
- Em produção, a camada criptografada no banco será provisionada pela ferramenta após o deploy (fase 9 do roadmap). Ainda não implementada.
- Testes: removidos os casos dos comandos removidos; mantidos relatório sem valores, publicação atômica, fronteira e contrato.

## 0.1.6 — complemento do Portal e relatório para o painel — 2026-10-09

- Comando `report [--out] [--file]`: grava `var/deployer/status.json` (esquema 1) com fronteiras, correções abertas
  e credenciais obrigatórias por ambiente (só nomes e estados). Sem rede e sem valores. Publicação atômica, 0640.
- Declarado como complemento opcional do `aculta_portal` (`info.yml`). O Portal não depende do módulo.
- Política de fronteira: a pasta de leitura do painel (`aculta_portal/src/Deployer`) pode citar a ferramenta, mas não
  pode executá-la nem usar seu código (`consumer_tool_reference_allowed` e `consumer_allowed_forbidden`).
- Testes: relatório sem valores, sobrescrita atômica e a exceção de fronteira.

## 0.1.5 — regra de permissão do arquivo de credenciais — 2026-10-09

- `Secrets::modeProblem()` passa a recusar só escrita de grupo (0020) e acesso de outros (0007). Leitura de grupo
  (ACL de leitura do processo web, modo 0640) é aceita, alinhada ao loader do Drupal. Aprovada pelo responsável.
- Testes atualizados: 0640 aceito; 0620, 0664, 0604, 0644 e 0666 recusados.

## 0.1.4 — arquivo local de credenciais e exportação pós-deploy — 2026-10-09

- `src/Secrets.php` e `config/secrets-contract.json` (nomes e ambientes; sem valores).
- `secrets check [--env] [--file]`: valida o arquivo (permissões 0600, fora de `web/`, ignorado pelo Git
  quando está no repositório, sintaxe, nomes do contrato e obrigatórios do ambiente). Só imprime nomes.
- `secrets export --env --out`: grava as variáveis do contrato em arquivo NOVO, 0600, fora do repositório,
  para importação manual pós-deploy. Não sobrescreve.
- Testes: parse, permissões, contenção, coerência do contrato com `docs/operations/SECRETS.md`, e a CLI
  (recusa dentro do repositório, recusa de obrigatórios ausentes, export positivo, sem valores na saída).

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
