# Planejamento de construção do ACULTA Deployer

Este documento define as fases de construção do submódulo `aculta_deployer`
(versão atual: 0.1.0). Regra geral: cada fase é curta, termina com testes
determinísticos e entra em PR própria. Fases longas terminam com uma **verificação
de segurança** obrigatória (seção "Gates de segurança"). Nenhuma fase começa com a
anterior com pendência de segurança aberta.

## Premissas

- A ferramenta é standalone: sem Drupal, Drush ou vendor (ver `ARQUITETURA.md`).
- O build de produção nunca grava dentro do repositório.
- Correções que o código não resolve são registradas em `registry/deploy-registry.json`.
- Segredos não entram no build nem no registro (ver `GUARDRAILS.md`).

## Estado atual (fase 0, concluída)

- CLI com `check`, `boundaries`, `list`, `register`, `build`, `version`.
- Substituição de host com fronteira de rótulo; remoção de aliases de teste.
- Registro com validação de campos e ids sequenciais.
- Fronteiras verificadas para tema, Portal (fora do submódulo) e a própria ferramenta.
- Testes em `tests/run.php`; documentação completa da versão 0.1.0.
- Pendência herdada: o favicon do validador de navegador ainda espera `aculta_favicon.ico`,
  e o site serve `aculta420-favicon.ico`.

## Fase 1 — Endurecimento da CLI e do registro (curta)

Objetivo: tornar as operações de escrita seguras e os erros previsíveis.

- Escrita atômica do registro (arquivo temporário e renomeação), com bloqueio contra
  gravações concorrentes.
- Códigos de saída documentados para todos os comandos; mensagens sem caminhos absolutos.
- Testes de erro: registro corrompido, JSON inválido, campos vazios, id duplicado.
- Corrigir o favicon no validador de navegador (`aculta420-favicon.ico`), pendência herdada.

Critério de saída: `tests/run.php` com novos casos de erro passando; `check` e
`boundaries` passando; PR própria.

## Fase 2 — Cobertura do escopo do build (longa)

**Status: concluída (0.1.x em desenvolvimento).** Implementado: saída nunca sobrescrita; saída via link ou dentro do repositório recusada; binários preservados e reprovados se tiverem host de teste; limite de tamanho por arquivo (`max_bytes`); hashes SHA-256 no relatório; autoverificação que remove a saída se restar host de teste; idempotência testada.

Objetivo: garantir que nenhum host de teste escape para produção.

- Varredura completa do escopo com relatório por arquivo (substituições, remoções).
- Idempotência: rodar `build` duas vezes produz a mesma árvore.
- Tratamento de arquivos binários e de tamanho grande (sem carregar tudo em memória).
- Escopo configurável por arquivo `config/deploy.json` validado (padrões inválidos são erro).
- Testes com árvore de fixtures: aliases, prefixos, rótulos extras, arquivos binários.

Gate de segurança ao fim da fase 2: ver seção "Gate S2".

## Fase 3 — Integração com os validadores (curta)

Objetivo: transformar achados do validador em entradas do registro, sem escrita direta.

- `register --from-json=ARQUIVO`: importa achados de um JSON gerado pelo validador de
  navegador (canonical, sitemap, favicon), com validação completa antes de gravar.
- O validador continua sem escrever no registro; a importação é um passo explícito.
- Testes com achados de exemplo, inclusive achados inválidos.

Critério de saída: importação recusa achados sem `page`, `current` ou `expected_production`.

## Fase 4 — Sitemap por host de apoio (longa, depende de decisão)

Objetivo: resolver DT-P23 (sitemap sem o host de apoio). A decisão entre módulo por
domínio e rota técnica própria é do responsável. Esta fase não começa sem ela.

- Se módulo: avaliação de compatibilidade com Drupal 11.3, teste em branch, justificativa
  de dependência Composer (exigência do `AGENTS.md`).
- Se rota própria: controlador no Portal com purpose SUPPORT, sem conflito com a rota
  `/sitemap.xml` do simple_sitemap, com teste de precedência de rotas.
- Atualizar DEP-0002 para `resolved` após verificação no servidor de testes.

Gate de segurança ao fim da fase 4: ver seção "Gate S4".

## Fase 5 — Pipeline de deploy e verificação pós-deploy (longa)

Objetivo: fechar o ciclo entre build, deploy e verificação.

- Comando `verify --url=PRODUÇÃO`: somente leitura, confere canonical e sitemap das
  páginas registradas; atualiza nada automaticamente.
- Relatório de deploy com hashes dos arquivos gerados.
- Procedimento de rollback documentado (o build anterior é preservado, não sobrescrito).

Gate de segurança ao fim da fase 5: ver seção "Gate S5".

## Fase 6 — Release 0.2.0 (curta)

- Atualizar CHANGELOG, VERSION e `info.yml`; a tag só é criada sob pedido do responsável.
- Revisão final da documentação e das fronteiras.

## Gates de segurança

Os gates são obrigatórios e devem ser registrados no CHANGELOG da versão.

### Gate S2 (cobertura do build)

- [x] `build` recusa saída dentro do repositório, inclusive por link simbólico (testado).
- [x] Nenhum arquivo gerado contém `toca.net.br` (autoverificação do build e teste).
- [x] Aliases de teste não aparecem no build de produção (testado).
- [x] Padrões de escopo inválidos são rejeitados antes de qualquer escrita (validação antes de `mkdir`).
- [x] Idempotência confirmada por comparação de hashes entre duas execuções (testado).

### Gate S4 (sitemap)

- [ ] Nenhuma rota nova sobrepõe `/sitemap.xml` sem teste de precedência.
- [ ] O host de apoio é listado somente com URL do próprio host, em produção.
- [ ] Dependência nova (se houver) justificada e aprovada pelo responsável.
- [ ] Nenhum vazamento de dados pessoais no sitemap (somente URLs públicas).

### Gate S5 (deploy)

- [ ] `verify` é somente leitura: nenhuma requisição altera estado do servidor.
- [ ] Relatório de deploy não contém segredos, tokens nem caminhos de servidor.
- [ ] Build anterior preservado para rollback.
- [ ] Verificação pós-deploy compara canonical e sitemap com o registro.

## Riscos abertos

- Sem fase 1, escritas concorrentes podem corromper o registro.
- Sem fase 2, um host de teste pode escapar para produção sem aviso.
- A fase 4 depende de decisão de arquitetura; sem ela, DEP-0002 permanece bloqueante.
