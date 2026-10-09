# Guardrails

1. **Canonical com problema exige registro.** Qualquer página cujo canonical ou
   sitemap não aponte para produção, no servidor de testes, deve ter uma entrada no
   registro com `kind` `canonical` ou `sitemap`, o host atual e o host esperado em
   produção. A correção no código não é suficiente quando o canonical é gerado a
   partir do host da requisição.
2. **Entrada bloqueante impede o build de produção.** `build` recusa a execução com
   entradas `blocking: true` abertas. Só `--allow-open-blocking` contorna, e só para ensaio.
3. **Build fora do repositório.** O diretório de saída não pode ficar dentro do repositório.
4. **Nenhum host de teste em produção.** Aliases `*_toca_net_br` e `*_test_8080` são
   removidos; o restante do escopo tem o sufixo substituído.
5. **Fronteiras.** Tema e Portal (fora do submódulo) não referenciam a ferramenta.
   A ferramenta não depende de Drupal, Drush, vendor, tema ou Portal. Verifique com
   `boundaries` antes de cada alteração.
6. **Standalone.** A CLI roda sem Drupal. Qualquer dependência nova precisa de
   justificativa no `docs/` e de revisão do responsável.
7. **Sem credenciais.** A ferramenta não lê nem grava segredos. O registro não deve
   conter chaves, tokens ou senhas.
8. **Registro é a fonte única.** Correções de deploy não ficam espalhadas em
   comentários, planilhas ou dívidas soltas: ficam em `registry/deploy-registry.json`.
   A dívida técnica de código correspondente é registrada em
   `docs/operations/DEBT-REGISTER.md`.
