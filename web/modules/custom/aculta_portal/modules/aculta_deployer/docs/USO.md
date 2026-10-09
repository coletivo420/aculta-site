# Uso

Todos os comandos rodam a partir da raiz do repositório, com a CLI do submódulo.

```
CLI=web/modules/custom/aculta_portal/modules/aculta_deployer/bin/aculta-deployer
```

## Comandos

- `$CLI check [--strict]`: valida o registro, as fronteiras e o escopo, e lista as
  entradas abertas. Com `--strict`, sai com código 2 se houver entrada bloqueante aberta.
- `$CLI boundaries`: só a verificação de fronteiras.
- `$CLI list`: lista as entradas do registro.
- `$CLI register --kind=K --page=P --current=C --expected=E --reason=R --owner=O [--blocking]`:
  acrescenta uma entrada. Os campos são validados antes de gravar.
- `$CLI build --out=DIR [--allow-open-blocking]`: gera a árvore de produção em `DIR`.
  - `DIR` não pode ficar dentro do repositório.
  - Com entradas bloqueantes abertas, o build é recusado. `--allow-open-blocking` serve
    só para ensaio, e não para publicar.
  - Grava `DIR/deploy-report.json` com os arquivos, as remoções e as substituições.
- `$CLI verify`: GET somente leitura nas entradas com `probe` e `expect`. Só aceita HTTPS,
  sem credenciais na URL, sem seguir redirecionamentos. Não altera nenhum arquivo nem o registro.
- `$CLI version`.

## Procedimento de deploy

1. `$CLI check --strict`. Precisa passar; se não, resolva ou registre as entradas.
2. `$CLI build --out=/caminho/de/producao`.
3. Confira `deploy-report.json`: nenhum `toca.net.br` deve restar nos arquivos gerados.
4. Aplique a árvore gerada no ambiente de produção, pelo processo do responsável.
5. Após o deploy, rode `$CLI verify`. Entradas que passam podem ser marcadas `status: resolved`
   no registro, com a decisão registrada.
6. Rollback: cada build fica em seu próprio diretório. Para voltar, aponte o ambiente de
   produção para o diretório do build anterior, pelo processo do responsável. Builds nunca
   são sobrescritos.

## Testes

```
php web/modules/custom/aculta_portal/modules/aculta_deployer/tests/run.php
```

## Indexação (política por ambiente)

- `$CLI robots --env=production`: GET em todos os hosts de produção; espera **ausência** de
  `X-Robots-Tag` com noindex.
- `$CLI robots --env=test`: espera noindex nos hosts de teste.
- `$CLI robots --env=production` também confere os **caminhos privados** (`private_probes`):
  conta, login (`/entrar`), painel, carrinho e checkout. Cada um deve ter noindex no
  cabeçalho ou `<meta name="robots">` no HTML, ou status 401, 403, 404 ou 410 (não indexável).
  Resposta 200 sem noindex falha.
- A verificação é GET somente leitura, sem seguir redirecionamentos, em HTTPS.
- A lista de hosts, a política e os caminhos privados ficam em `config/deploy.json`
  (`robots_policy`).

### Por que o deployer confere o meta robots

O noindex das páginas privadas é emitido pelo Portal por rota (`meta robots`), não pelo
Apache. Uma checagem só de `X-Robots-Tag` deixaria de ver essas páginas. Por isso a
verificação lê o HTML e aceita o noindex em qualquer um dos dois.

### Por que 404/410 contam como não indexável

Um caminho que responde 404 para o anônimo não tem conteúdo indexável. A checagem aceita
esses status para que a lista de caminhos não precise espelhar rotas que não existem em
produção. Um 200 sem noindex é sempre falha.

## Ambiente do site (0.1.8)

- `$CLI environment` mostra o ambiente atual. `$CLI environment set --to=production` ou `--to=test` grava o ambiente e o
  endereço do site em `var/deployer/environment.json`. Sem o arquivo, as funções usam `test`.
- O Portal lê esse arquivo para escolher o conjunto de credenciais obrigatórias.

### Comportamento por função e ambiente

| Função | Teste | Produção | Observação |
| --- | --- | --- | --- |
| `environment` | define `test` | define `production` | grava o arquivo neutro |
| `robots` | confere noindex nos hosts de teste | confere ausência de noindex e `Sitemap:` do índice | `--env` ou arquivo |
| `sitemap` | confere o índice na base de teste | confere o índice na base de produção | `--env` ou arquivo |
| `report` | ambiente e endereço de teste | ambiente e endereço de produção | sem rede, sem valores |
| `build` | não aplicável (gera a saída de produção) | gera a saída de produção | recusa saída dentro do repositório |
| `verify` | probes do registro (produção) | probes do registro (produção) | somente leitura |
| `check` | valida o registro e o escopo | valida o registro e o escopo | não depende do ambiente |
| `boundaries` | independe do ambiente | independe do ambiente | |

Pendente: `build` e `verify` ainda não recebem `--env`; a fase 10 do roadmap trata disso.

## Painel do Portal (0.1.6)

- `$CLI report` grava `var/deployer/status.json` (ignorado pelo Git). A página `/admin/config/aculta/deployer`
  (permissão `administer aculta deployer`) lê esse arquivo e mostra fronteiras, correções abertas e credenciais
  por nome e estado, com o mesmo sistema de status do diagnóstico (✔, ⚠, ✖).
- Gere o relatório antes de consultar o painel. Sem o arquivo, a página informa que o relatório está indisponível.
- O Portal não executa a ferramenta. Verificações de rede (sitemap e robots) continuam como comandos próprios.

## Credenciais (0.1.7)

- Não há comandos de importação ou exportação de credenciais na ferramenta. O cadastro é feito no painel
  "Credenciais do ambiente" do Portal (ver `docs/operations/SECRETS.md`).
- `$CLI report` inclui, por ambiente, só os nomes presentes e ausentes das credenciais obrigatórias.
- Em produção, a camada criptografada no banco é provisionada pela ferramenta após o deploy (fase 9, pendente).

## Descoberta e sitemaps (0.1.3)

- `$CLI sitemap --env=production`: GET no índice (`sitemap.production.index_url`), nos filhos
  e nos hosts de conteúdo. Confere (1) que cada filho do índice está na base do ambiente
  (`index_base`) e (2) que as URLs de conteúdo pertencem aos hosts da política de produção e
  que esses hosts respondem (cross-host).
- `$CLI sitemap --env=test`: o mesmo, com a base do servidor de testes. Os filhos continuam
  com URLs canônicas de produção, por desenho do Portal; só o índice segue a base do ambiente.
- `$CLI robots --env=production` também confere o `robots.txt` de cada host: diretiva
  `Sitemap:` com o índice de produção e nenhum `Disallow: /`.

### Provisionamento por ambiente (problema 1)

- Produção: `simple_sitemap.settings:base_url` é `https://aculta.org` em `config/sync`.
- Servidor de testes: o runtime define `base_url` como `https://aculta.toca.net.br`, por
  `drush config:set` no runtime (não versionado, operação de runtime). Sem isso,
  `sitemap --env=test` falha com "índice aponta para aculta.org".
- Qualquer nova divergência de host, base, `robots.txt` ou sitemap entre ambientes deve ser
  implementada e verificada aqui (regra nas instruções de IA do projeto).

