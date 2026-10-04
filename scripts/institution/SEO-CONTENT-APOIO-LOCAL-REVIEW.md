# Revisão local: SEO, conteúdo editorial e apoio financeiro

Data: 3 de outubro de 2026. Execução interrompida conforme a regra de parada 49 do comando desta rodada.

## Resumo executivo

A auditoria inicial e a instalação Composer foram realizadas. A implementação das quatro frentes **não foi concluída**. Não houve habilitação dos novos módulos, alteração da configuração ativa, migração de conteúdo ou integração financeira.

O Real-time SEO 2.2.0 declara compatibilidade com Drupal 11, mas apresenta uma limitação funcional na integração com o CKEditor 5 utilizado pelo projeto. Seu JavaScript continua escutando exclusivamente eventos do CKEditor 4. Além disso, o preview, quando configurado para permitir edição do título, falha ao chamar `.attr()` em um elemento DOM nativo. A falha foi reproduzida no Chrome local executando a implementação instalada, sem modificar o contrib ou o DOM público.

Evidência: `node scripts/verify-yoast-compatibility.mjs` retornou `TypeError: snippetTitle.attr is not a function`, integração CKEditor 4 presente e integração CKEditor 5 ausente no arquivo inspecionado. O teste é da função JavaScript em isolamento, não de um formulário Drupal já configurado. Não demonstra incompatibilidade de toda a instalação do módulo com Drupal 11; demonstra problemas na experiência editorial exigida nesta rodada.

Referências oficiais:
- [Problema de atualização com CKEditor 5](https://www.drupal.org/project/yoast_seo/issues/3351523).
- [Problema do preview DOM nativo](https://www.drupal.org/project/yoast_seo/issues/3606061).

Não foi aplicado patch, criado workaround, trocado editor ou alterado contrib. A decisão sobre contornar essas limitações fica para revisão antes de continuar, conforme a regra explícita de parada.

## Ambiente e alterações

- Drupal: 11.4.8; PHP: 8.5.10; Composer: 2.10.3.
- Tema público `aculta` e administrativo Claro preservados.
- Modificados nesta retomada: `composer.json`, `composer.lock`.
- Criados: `scripts/verify-yoast-compatibility.mjs` e este relatório.
- Atualizado inventário somente de leitura em `tmp/final-local-audit.json`, ignorado pelo Git.
- Evidência do teste em `tmp/seo-content-apoio/yoast-compatibility.json`, ignorada pelo Git.
- Composer adicionou `drupal/yoast_seo` 2.2.0, constraint `^2.2`; `drupal/schema_metatag` 3.0.4, constraint `^3.0`; dependência transitiva `goalgorilla/rtseo.js` 2.1.0.
- Nenhum pacote preexistente atualizado ou removido. Drupal Core preservado.
- Novos módulos habilitados: nenhum. Submódulos Schema habilitados: nenhum.
- Metatag 2.2.0, Open Graph, Pathauto, Redirect, Simple XML Sitemap e VVJB anteriores preservados.
- Credenciais Mercado Pago não configuradas, reproduzidas ou enviadas. Nenhum pagamento, assinatura ou cobrança criado.

## Estrutura auditada e frentes pendentes

| Item | Situação observada / trabalho ainda necessário |
| --- | --- |
| Tipos de conteúdo | Existem `article` (Notícia), `activity` (Atividade), `project` (Projeto), `page`, `document`, `editorial_highlight`. Nenhum novo tipo criado. Reutilização deve preceder criação de tipos equivalentes. |
| Campos compartilhados | Existem `field_summary`, `field_project`, `field_meta_tags`; auditar tipos/cardinalidade antes de reutilizar. `field_image` de Projeto é Image/file, não Media. |
| Notícia | Estrutura existente ainda precisa dos campos editoriais, taxonomias, autoria estruturada, publicação efetiva e defaults SEO solicitados. |
| Evento | `activity` não contém nodes; seu campo `field_activity_date` é apenas data, insuficiente para início/fim com horário. Modelagem de Evento não implementada. |
| Projeto | Quatro nodes existentes preservados, sem duplicação ou migração nesta rodada. Campos atuais de história, objetivos, atividades e registros preservados. |
| Taxonomias | Categoria, Tags, Tipo de Evento, Área de Atuação e autoria editorial ainda não configurados nesta rodada. Não foram inventados termos ou pessoas. |
| Views | `aculta_projects`, `aculta_news`, `aculta_activities` e `home_editorial_highlights` existentes. Próximos/eventos realizados e relacionamentos ainda não implementados. |
| Aliases | Quatro projetos permanecem em `/projetos/carnareggae-bloco-sativa`, `/projetos/baque-sativa`, `/projetos/podplant420`, `/projetos/batalha-do-riddim`. Nenhuma URL alterada. |
| Metatag defaults | Configuração anterior preservada; novos defaults de Notícia/Evento/Projeto/Apoie ainda pendentes. |
| Real-time SEO | Pacote instalado, não habilitado. Limitações encontradas descritas acima. Score não foi tornado condição de publicação. |
| Organization | JSON-LD customizado continua no `aculta.theme`, com dados institucionais. Migração para Schema.org Metatag e eliminação da duplicação potencial ainda pendentes. |
| WebSite | Configuração Schema pendente. |
| NewsArticle | Mapeamento de autoria, imagem, resumo, datas e canonical pendente. |
| Event | Mapeamento de modalidades, locais, datas e estados Schema pendente. |
| Projeto WebPage | Configuração Schema pendente. |
| Simple XML Sitemap | Preservado; regras novas de Eventos/Notícias/Apoie e exclusões financeiras ainda não implementadas. |
| Apoie | `/apoie` ainda não criado. Home, menu e footer não alterados. |
| Módulo `aculta_apoio` | Ainda não criado. |
| Checkout Pro | Documentação oficial confirma criação por `POST https://api.mercadopago.com/v1/orders`, retorno `checkout_url` e header `X-Idempotency-Key`. **Orders API não foi implementada ou chamada nesta rodada.** Preferences API não utilizada. |
| Assinaturas | Integração e estados ainda não implementados. Nenhum cartão recebido ou armazenado. |
| Entidade Contribuição | Ainda não criada; não há transações financeiras em nodes. |
| Webhooks | Endpoint, validação de assinatura, fila, idempotência e reconfirmação server-to-server ainda pendentes. |
| Pix | Nenhum dado inventado. Modal/configuração ainda não criados. |
| Administração | Configuração não sensível, permissões e listagem de contribuições ainda pendentes. |
| Secrets | Integração futura deve usar ambiente/settings privado; não inserir credenciais em config/sync. Nenhum segredo novo introduzido nesta retomada. |
| LGPD | Política existente preservada. Atualização referente a tratamento financeiro só deve ocorrer após implementação efetiva. |
| LMS / Commerce | Não instalados. Documento arquitetural futuro ainda pendente. |

Referência da Orders API consultada: [Criar Order Checkout Pro](https://www.mercadopago.com.br/developers/pt/reference/online-payments/checkout-pro/create-order/post).

## Conteúdo e configuração local

Inventário: 16 nodes, 12 entidades de bloco de conteúdo, 16 links de menu, 20 aliases e 7 redirects. Os quatro projetos são os nodes 2–5 e os destaques editoriais são os nodes 14–16; esses IDs descrevem o inventário, não devem ser hardcoded em uma nova implementação.

Home, projetos, páginas institucionais, destaques e blocos editoriais continuam sendo conteúdo do banco local. Não são transportados por config export ou pelo Git. Os manifests da rodada anterior continuam válidos: `FINAL-CONTENT-MANIFEST.json` e `FINAL-CONFIG-CHANGES.json`.

Antes da instalação Composer, o inventário confirmou ausência de diferenças entre config ativa e `config/sync`, inclusive coleções. Nenhuma configuração Drupal foi alterada depois disso; portanto não foi feito export nem import. Instalar o código de um módulo não o habilita nem exige adicionar uma entrada ao `core.extension` antes de sua habilitação.

Foram inventariados 329 arquivos em `sites/default/files`, incluindo arquivos gerados/runtime. Essa contagem não representa 329 assets editoriais. Não havia imagens/documentos gerenciados de conteúdo no inventário anterior; nenhum novo asset público foi criado nesta retomada. Reutilizar a classificação de arquivos da revisão anterior no plano de migração.

## Testes e verificações

| Verificação | Resultado |
| --- | --- |
| Composer require dry-run | Três instalações, zero atualizações/remoções. Houve HTTP 502 de repositório na simulação; instalação efetiva subsequente terminou com sucesso. |
| Composer require efetivo | OK, três pacotes instalados e lock gerado pelo Composer. |
| Composer validate --no-check-publish | OK; avisos preexistentes sobre constraints exatas Bootstrap5 4.0.8 e Pathauto 1.15. |
| Composer audit --locked --format=json | OK: advisories, abandoned e filter vazios. |
| Drush status | OK: bootstrap e conexão com banco bem-sucedidos, Drupal 11.4.8, PHP 8.5.10, tema aculta, admin Claro. |
| Inventário Drupal | OK; configurações sincronizadas antes das mudanças Composer. |
| Reprodução JavaScript | Falha esperada confirmada: `snippetTitle.attr is not a function`, quando edição do título está habilitada no preview. |
| node --check scripts/verify-yoast-compatibility.mjs | OK. |
| git diff --check | OK. |
| Cache rebuild | Não executado nesta retomada: nenhum módulo habilitado, conteúdo, tema ou configuração Drupal alterado. |
| PHP lint / YAML parse | Nenhum PHP/YAML criado ou alterado nesta retomada. Testes gerais da nova arquitetura não executados. |
| SEO completo | NÃO EXECUTADO: módulos novos não habilitados/configurados. Não afirmar JSON-LD novo validado. |
| Financeiro | NÃO EXECUTADO: integração não implementada. Nenhuma API financeira de criação chamada. |
| Responsividade / acessibilidade | Não repetidas nesta retomada, pois não houve mudança da interface. Resultados anteriores não substituem os testes das futuras funcionalidades. |
| Integridade Core/contrib | Não repetida por hash nesta retomada. Dois módulos novos instalados por Composer, nenhum contrib editado manualmente. |
| Git / secrets | Alterações Composer revisadas e scripts novos sem credenciais ou PII. Auditoria abrangente de todos os candidatos preexistentes não repetida nesta retomada. |

## Git status

```text
 M composer.json
 M composer.lock
?? config/
?? scripts/
?? web/themes/custom/
```

Os diretórios não rastreados já existiam antes desta retomada. Nenhum `git add`, commit ou push executado. Nenhum acesso à produção, deployment, alteração de Core/contrib ou troca de temas.

## Pendências e continuidade

1. Decidir como tratar as limitações do Real-time SEO com CKEditor 5 e o preview editável. Não aplicar correção em contrib ou trocar de versão/editor silenciosamente.
2. Concluir modelagem editorial preservando conteúdos e aliases existentes, Schema/Metatag, Views e sitemap.
3. Implementar e testar `aculta_apoio`, Orders API, Assinaturas, armazenamento, webhooks, Pix e permissões.
4. As credenciais mencionadas pelo responsável não estão acessíveis no contexto desta execução. Preparar suporte a variáveis de ambiente; disponibilizar credenciais de teste e segredo de assinatura webhook fora do Git para homologação real. Dados Pix oficiais continuam não fornecidos e o recurso deve permanecer desativado até configuração completa.
5. Testar formulários, SEO, callbacks e estados com mocks/fixtures reversíveis, sem cobranças reais. Repetir testes responsivos e de acessibilidade depois das mudanças.
6. Reconciliar somente configurações revisadas, gerar novos manifests e documentar migração de conteúdo e arquivos. Nada desta rodada está autorizado para deployment antes de revisão.

O site anteriormente aprovado não foi reconstruído. A classificação abaixo se refere **ao escopo novo desta rodada**, ainda não concluído, e não revoga por si só a avaliação técnica anterior.

## Conclusão

**NÃO PRONTO PARA DEPLOY**

Bloqueadores concretos: incompatibilidade funcional da experiência Real-time SEO requerida ainda sem decisão; modelagem editorial e configuração Schema solicitadas não implementadas; página Apoie e integração financeira segura ainda não implementadas/testadas. Execução parada pela regra 49 antes de prosseguir.
