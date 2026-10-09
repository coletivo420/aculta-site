# Arquitetura

## Componentes

| Arquivo | Função |
| --- | --- |
| `bin/aculta-deployer` | Ponto de entrada da CLI. Carrega as classes de `src/`. |
| `src/Cli.php` | Comandos: `check`, `boundaries`, `list`, `register`, `build`, `version`. |
| `src/Transform.php` | Escopo, remoção de aliases de teste e substituição de host. |
| `src/Registry.php` | Leitura, validação e gravação de `registry/deploy-registry.json`. |
| `src/Secrets.php` | Lógica do arquivo local de credenciais: parse NAME=value, permissões, contenção e contrato. |
| `config/secrets-contract.json` | Nomes de variáveis e ambientes obrigatórios (sem valores). Espelha `docs/operations/SECRETS.md`. |
| `src/Boundary.php` | Verificação de fronteiras, com regras em `config/boundary.json`. |
| `config/deploy.json` | Escopo, aliases removidos e regras de host. |
| `registry/deploy-registry.json` | Correções de deploy abertas e resolvidas. |
| `tests/run.php` | Testes determinísticos da transformação, do registro e das fronteiras. |

## Escopo do build

- Entra: `config/sync/**/*.yml` e `scripts/content/**/*.json`.
- Sai do build: aliases de homologação e de porta 8080 (`domain_alias.alias.*_toca_net_br.yml`
  e `*_test_8080.yml`). Em produção eles duplicariam o host real do domínio, então são
  removidos, e não trocados.
- Fora do escopo: documentação (`docs/`), código PHP e tema. A documentação descreve o
  ambiente de teste e não é publicada como configuração.

## Barreiras de separação

- **Tema (`web/themes/custom/aculta420`)**: não pode referenciar a ferramenta. Qualquer
  termo como `aculta-deployer` ou `aculta_deployer` no tema reprova `boundaries`.
- **Portal (`aculta_portal`, fora do submódulo)**: não pode referenciar a ferramenta. O
  submódulo é opcional: o Portal funciona sem ele. A exclusão do submódulo é explícita
  em `config/boundary.json` (`consumer_exclude`).
- **Core e contribs**: nunca são alterados nem referenciados.
- **A própria ferramenta**: não pode depender de Drupal (`\Drupal\`, `Drupal::`),
  de Drush, de vendor, do tema ou de módulos do Portal. Os termos proibidos ficam em
  `config/boundary.json`, para que o código não os contenha.

## Por que submódulo

A ferramenta é versionada junto do Portal e segue a mesma revisão de código. Ela não
é habilitada no Drupal: fica disponível no código e roda pela CLI no pipeline de
deploy. A decisão de ser submódulo é do responsável do projeto (ver REGISTRO e
o CHANGELOG do `aculta_portal`).
