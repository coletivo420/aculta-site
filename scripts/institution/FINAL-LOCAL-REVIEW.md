# Revisão final local — aculta.org

Data: 3 de outubro de 2026. Ambiente: Windows, Drupal 11.4.8, PHP 8.5.10, MariaDB. Produção não acessada. Nenhum commit, push ou deployment executado.

1. **Editorial.** Home atualizada com a apresentação curta de organização da sociedade civil sem fins lucrativos, quatro frentes aprovadas, resumo institucional do PodPlant420 e os três novos textos do carrossel. Hero e missão preservados. Adicionada a chamada “Construir em parceria”, com destino real `/contato`. Rodapé usa o slogan oficial. A página Institucional contextualiza o antiproibicionismo e distingue a Assembleia Geral de Fundação em **22/08/2025** da abertura cadastral em **22/10/2025**. A referência à Fiocruz permanece como experiência objeto de trabalho, sem atribuir parceria formal. Não foram inventados indicadores, dirigentes, parceiros ou documentos.

2. **Visual.** Identidade, fontes, base theme e composição preservados. Navegação de 16px recebeu uma superfície derivada dos dois verdes para atingir contraste AA; o fundo principal do header continua `#689427`. A regra mobile do carrossel foi alinhada ao limite nativo de 768px. Barras continuam compactas: padding de 10px × 20px, título de 25–29px, altura medida de 47,5–51,7px. Espaçamento de grandes seções continua em 36–68px. Não foi feita refatoração visual ampla.

3. **Arquivos.** Alterados nesta rodada: `web/themes/custom/aculta/css/style.css`, `web/themes/custom/aculta/js/aculta.js`, `web/themes/custom/aculta/templates/block--block-content--type--aculta-institution.html.twig`, `scripts/institution/home-editorial.json` e `scripts/validate-home-carousel.php`. Criados os scripts `audit-final-local.php`, `finalize-institution-local.php`, `review-final-config.php`, `sync-reviewed-final-config.php`, `validate-final-drupal.php`, `validate-final-contact.php`, `validate-final-sitemap.php`, `validate-final-local.mjs`, `inspect-final-browser.mjs`, `capture-final-home.mjs`, `audit-package-integrity.py` e `audit-public-worktree.py`, além deste relatório e dos inventários JSON relacionados abaixo. A lista exata das configurações reconciliadas está em `FINAL-CONFIG-CHANGES.json`. Evidências e capturas ficam em `tmp/`, ignorado pelo Git.

4. **Composer.** Nenhum pacote instalado ou atualizado nesta rodada. Dependências acumuladas no trabalho local:

   | Pacote | Versão instalada | Constraint |
   |---|---|---|
   | drupal/bootstrap5 | 4.0.8 | 4.0.8 |
   | drupal/metatag | 2.2.0 | ^2.2 |
   | drupal/pathauto | 1.15.0 | 1.15 |
   | drupal/redirect | 1.13.0 | ^1.13 |
   | drupal/simple_sitemap | 4.2.3 | ^4.2 |
   | drupal/vvjb | 2.0.0 | ^2.0 |
   | drupal/webform | 6.3.1 | ^6.3 |

   Dependências transitivas relevantes: VVJ Core 2.0.0, Token 1.17.0 e CTools 4.1.1. Composer JSON e lock já estavam modificados ao iniciar esta rodada.

5. **VVJB.** VVJB 2.0.0 e VVJ Core 2.0.0 preservados, sem edição de contrib. View `home_editorial_highlights`, formato `views_vvjb`, conteúdo `editorial_highlight`; campos administráveis de categoria, resumo, complemento, link e ordem, além de título e publicação nativos. Somente publicados, ordenação por peso e desempate por identificador. Nenhuma dependência de exatamente três nodes.

6. **Módulos.** Nenhum habilitado ou desabilitado nesta rodada. Estão habilitados VVJB/VVJ Core, Webform/Webform UI, Simple XML sitemap, Metatag/Open Graph, Pathauto, Token, Redirect e os recursos Core de Views, menus, conteúdo, idiomas, Media Library e CKEditor 5, entre outros. A relação completa e autoritativa está em `config/sync/core.extension.yml`. CTools está instalado por Composer, mas não está habilitado. Tema público `aculta`, administrativo Claro; Olivero permanece desinstalado.

7. **Configurações.** Criadas `block.block.aculta_home_partnership` e `block.block.aculta_transparency_sections`. Alterados os pesos de `block.block.aculta_data_registration` e `block.block.aculta_documents`, `system.site:mail`, metadescrição/OG em `metatag.metatag_defaults.front` e rótulo/descrição de `field.field.block_content.aculta_institution.field_org_description`. Foram reconciliadas diferenças preexistentes de idioma, rótulos e textos administrativos: **160 configurações existentes**, **duas novas** e **duas exclusões** na coleção principal, além de **uma exclusão** em `language.pt-br`. Removidos do sync os remanescentes `olivero.settings` e `core.date_format.olivero_medium`, inclusive sua tradução. Não foi executado export geral cego. Os nomes exatos estão em `FINAL-CONFIG-CHANGES.json`.

8. **Conteúdo do banco local.** Há 16 nodes: 14 publicados, o documento CNPJ sem arquivo despublicado e a antiga página Contato despublicada. Os publicados incluem Home, quatro projetos, seis páginas institucionais/listagens e três destaques. Há 12 blocos de conteúdo: oito usados e quatro versões anteriores sem posicionamento, preservadas para não apagar conteúdo. Os dois novos blocos de parceria e seções de Transparência foram criados nesta rodada. Existem ainda 16 links de menu armazenados, 20 aliases e sete redirects. Não existem notícias ou atividades individuais publicadas; as respectivas páginas possuem apresentação institucional real. Inventário por ID/UUID em `FINAL-CONTENT-MANIFEST.json`.

   **Esses conteúdos não seguem pelo Git nem pelo config export.** A etapa de deployment deve prever migração segura das entidades necessárias. As referências da front page ao node 1, da configuração do tema ao node 10 e dos posicionamentos aos UUIDs de blocos devem ser preservadas ou remapeadas. Não importar somente a configuração sobre um banco sem esses conteúdos. Os blocos antigos sem posicionamento e a página Contato antiga não são necessários à apresentação pública.

9. **Arquivos públicos.** Não há entidades de arquivo/media nem uploads institucionais de imagens, logos ou PDFs. `sites/default/files` contém caches CSS/JS/PHP, traduções, ícones de media e arquivos de proteção/configuração gerados. Caches não precisam ser transportados; são regenerados. Preservar a proteção `.htaccess` e as permissões apropriadas do diretório. Nenhum Estatuto ou comprovante CNPJ foi inventado ou apresentado como download disponível. As fontes continuam sendo carregadas por Google Fonts em HTTPS, conforme a Política de Privacidade.

10. **Páginas existentes.** Home, Institucional, Projetos, Atividades, Notícias, Transparência, Contato e Política de Privacidade existem. As quatro páginas próprias dos projetos também existem. `/contato` é o Webform real, com dados oficiais e campos Nome, E-mail, Assunto e Mensagem.

11. **Páginas pendentes.** Nenhuma das oito páginas obrigatórias está ausente ou vazia. Listagens de notícias e atividades aguardam registros futuros; isso não elimina o conteúdo institucional existente nas páginas.

12. **Links.** Doze destinos internos distintos encontrados na navegação e nos CTAs retornaram HTTP 200. Nenhum `href="#"`, CTA sem destino ou link para 404 encontrado. Referências externas ao domínio oficial foram conferidas por conteúdo e equivalentes locais, sem acessar produção. Downloads de documentos, perfis `sameAs`, logos de parceiros e outras URLs ainda não fornecidas continuam ausentes, sem links fictícios.

13. **Composer validate.** Passou. Avisos não bloqueantes sobre constraints exatas de Bootstrap5 e Pathauto, preservadas deliberadamente. `composer audit --locked --format=json`: nenhuma advisory e nenhum pacote abandonado. Não foi executado `composer update` global.

14. **Drush status.** Drupal 11.4.8 / PHP 8.5.10, bootstrap bem-sucedido e banco conectado; tema público `aculta`, administrativo Claro. Drush foi invocado pelo arquivo PHP, conforme o ambiente Windows.

15. **Cache rebuild.** Concluído com sucesso após as alterações. PHP do tema e todos os scripts PHP passam no lint; sete templates customizados compilam; os 504 YAML do sync foram analisados; JavaScript passa em `node --check`. CSS foi conferido no navegador pelas regras efetivamente aplicadas, medidas de layout e contraste.

16. **Testes de páginas.**

   | Página | Resultado |
   |---|---|
   | / | OK — 200 |
   | /institucional | OK — 200 |
   | /projetos | OK — 200 |
   | /atividades | OK — 200 |
   | /noticias | OK — 200 |
   | /transparencia | OK — 200 |
   | /contato | OK — 200 |
   | /politica-de-privacidade | OK — 200 |
   | Quatro páginas de projetos | OK — 200 |

   Um H1 por página, sem imagens quebradas e com títulos institucionais coerentes. JSON-LD usa somente os dados oficiais informados, sem logo ou `sameAs` inventados.

17. **Menu.** Breakpoint preservado em **1100px**: desktop em uma linha a partir desse limite, colapsável abaixo dele. Fonte Oswald 16px/600, sem retirar ou abreviar itens. Abertura, `aria-expanded`, Escape e retorno do foco ao botão passaram. Usuário anônimo não recebe faixa utilitária vazia.

18. **Carrossel.** Centralizado, com dois itens acima de 768px e um item até 768px. Autoplay nativo de **3000ms**, looping habilitado; ciclo observado **1 → 2 → 3 → 1 → 2**. Anterior/próximo, indicadores, teclado, touch, pause on hover, pausa por foco e reduced motion passaram. Testes de zero/um/três/quatro conteúdos, edição, ordem e despublicação passaram com rollback. Zero itens não renderiza carrossel vazio; um item desativa rotação e controles inúteis. Não há timer customizado ou reprodução duplicada.

   A integração do tema aguarda a hidratação lazy nativa antes de chamar `pause()`. Também observa a promessa `ready` da View Transition e trata somente o cancelamento esperado `AbortError` quando uma navegação substitui a anterior; outras falhas continuam sendo propagadas. Isso corrige o aviso encontrado nos testes sem editar contrib ou recriar animação/carrossel.

19. **Responsividade.** Home em 1440, 1200, 1100, 1099, 1024, 768, 480 e 360px; sete páginas internas em 1440, 1200, 1024, 768, 480 e 360px. Nenhum overflow horizontal encontrado. Carrossel centralizado nas medidas estáveis. Capturas em `tmp/final-local-review/` para revisão visual.

20. **Acessibilidade.** Contraste automatizado dos textos visíveis da Home sem falhas após o ajuste do menu; nomes acessíveis e controles nativos preservados, foco visível e labels dos quatro campos verificados. Teclado, fechamento de menu, indicadores e reduced motion passaram. A navegação do carrossel fica pausada durante interação por foco, inclusive quando o primeiro foco precede a hidratação. Esta revisão não equivale a uma certificação completa de conformidade nem a teste com todos os leitores de tela.

21. **Placeholders.** Nenhum placeholder ou conteúdo padrão indesejado encontrado nas páginas públicas testadas: sem Welcome, Home vazia do Drupal, Lorem ipsum, Coming soon, “Em construção”, `admin@example.com`, botões falsos ou downloads fictícios. O conteúdo antigo não foi ocultado com CSS; os três blocos antigos do carrossel não estão posicionados. O documento sem PDF permanece despublicado.

22. **E-mail do site e contato.** `system.site:mail` foi corrigido de `admin@example.com` para `4e20coletivo@gmail.com`. O formulário valida e grava os quatro campos e produz uma notificação destinada ao e-mail institucional. O teste com coletor não entrega e-mail externo e removeu seus registros e alterações temporárias por rollback. Na primeira tentativa, o transporte padrão do Webform ainda prevaleceu sobre o coletor default e falhou no Windows; o teste foi corrigido para selecionar e verificar explicitamente o coletor também para Webform antes da submissão. Nenhum registro de teste permanece. **Entrega real pela hospedagem não foi homologada nesta rodada local**; deverá ser verificada na etapa operacional autorizada de deployment. A configuração de transporte real foi preservada.

23. **Config/sync.** Configuração ativa e sync correspondem integralmente, incluindo coleções de tradução. O diretório padrão apontado pelo ambiente local ainda é o diretório gerado pelo Drupal em `sites/default/files`; o repositório usa `config/sync`. Para deployment, definir o caminho de sync no settings da hospedagem ou usar `config:import --source=<caminho absoluto para config/sync>`, após planejar a transferência do conteúdo. `settings.php` não foi versionado nem alterado nesta rodada.

24. **Webroot Hostinger.** Workaround da `.htaccess` da raiz preservado: `DRUPAL_WEBROOT=/web`, encaminhamento para `/web` e bloqueio de Composer, vendor, config, Git, AGENTS e demais caminhos protegidos. Não houve alteração de Core, contrib ou do base theme. Comparação de **33.047 arquivos em 11 pacotes Drupal** com os ZIPs de release do cache Composer não encontrou diferenças. A aplicação local usa o router do PHP; comportamento do Apache, HTTPS/redirecionamento HTTP e permissões na hospedagem serão conferidos somente na etapa autorizada, sem antecipar acesso à produção.

25. **Secrets e proteção.** Inspeção do diff e varredura dos candidatos ao Git sem assinaturas de credenciais, tokens, chaves privadas ou dumps. Nenhum valor sensível foi divulgado. `.gitignore` preserva vendor, Core/contrib, settings/services locais, uploads, `.env`, dumps SQL, logs, temporários, backups e runtime. Nenhuma regra foi enfraquecida. `git diff --check` passou.

26. **Git.** Saída resumida ao concluir:

   ```text
    M composer.json
    M composer.lock
   ?? config/
   ?? scripts/
   ?? web/themes/custom/
   ```

   Não há staging, commit ou push. Como as três árvores ainda não são rastreadas, o `git diff` convencional mostra somente os arquivos Composer; os novos arquivos devem entrar na revisão de conteúdo antes do futuro commit.

27. **Bloqueadores reais.** Nenhum bloqueador técnico da implementação local identificado após as correções e a repetição dos testes. Geração de sitemap pelo comando batch do Drush encontrou a limitação `sh` no Windows; a API backend nativa do Simple XML sitemap gerou e validou **12 URLs oficiais HTTPS**, sem modificar o módulo. Os scripts de conteúdo usam guardas de execução local e não devem ser executados automaticamente em produção. Transferência de conteúdo/configuração e homologação de e-mail/HTTPS são tarefas da etapa posterior de deployment, não ações realizadas ou autorizadas por este relatório.

28. **Melhorias futuras não bloqueantes.** Disponibilizar Estatuto e comprovante CNPJ em arquivo público, atas, relatórios, prestação de contas, instrumentos de parceria e composição nominal atual da governança quando oficialmente fornecidos. Publicar notícias/atividades, consolidar indicadores, confirmar perfis oficiais e logos autorizados. Completar a Política de Privacidade conforme ferramentas e procedimentos reais; a versão atual descreve Webform, sessões e Google Fonts sem atribuir Analytics, pixels ou cookies publicitários inexistentes. Os objetivos apresentados refletem as informações estatutárias fornecidas pelo responsável; nenhum documento jurídico original foi alterado.

PRONTO PARA DEPLOY
