# Relatório da implementação institucional local

Data: 3 de outubro de 2026. Ambiente de revisão: http://localhost:8080/.

Implementação restrita ao ambiente local. Não foram feitos commit, push ou alterações em produção. A identidade pública utilizada é Associação Cultural Antiproibicionista; os identificadores técnicos existentes foram preservados.

## 1. Arquivos criados

- `scripts/apply-official-institution.php`: aplicação dos dados oficiais.
- `scripts/refine-institution.php`: ajustes de entidades, apresentação e navegação.
- `scripts/configure-institution-modules.php`: configuração dos módulos e integrações editoriais.
- `scripts/revise-home-editorial.php`: aplicação da revisão de densidade e dos resumos.
- `scripts/institution/home-editorial.json`: conteúdo curto da Home.
- `scripts/validate-institution.php`: validação da arquitetura e configuração.
- `scripts/validate-institution-browser.mjs`: verificação das páginas no navegador.
- `scripts/diagnose-institution.php`: diagnóstico local de leitura.
- `scripts/institution/IMPLEMENTATION-REPORT.md`: este relatório.
- `config/sync/`: exportação de 468 arquivos YAML da configuração ativa.
- `web/themes/custom/aculta/templates/block--block-content--type--aculta-institution.html.twig`: substitui o nome anterior do template para corresponder ao hook de blocos do Drupal.

Capturas e resultados de verificação estão em `tmp/institution-review/`, diretório ignorado pelo Git.

## 2. Arquivos modificados

- `composer.json` e `composer.lock`: dependências gerenciadas pelo Composer.
- `scripts/install-institution.php`: instalação sustentável, criação do campo body e recuperação de entidades existentes.
- `scripts/institution/content.json`: base editorial completa e dados oficiais.
- `web/themes/custom/aculta/aculta.theme`: integração da apresentação, títulos e JSON-LD.
- `web/themes/custom/aculta/templates/page.html.twig`: composição das regiões e utilitário de conta.
- `web/themes/custom/aculta/templates/node--project--teaser.html.twig`: resumo editorial dos projetos.
- `web/themes/custom/aculta/css/style.css`: escala tipográfica, largura de leitura, espaçamento e apresentação dos dados.
- `web/themes/custom/aculta/config/schema/aculta.schema.yml`: esquema das configurações do tema.

O repositório já continha alterações e arquivos não rastreados; eles foram preservados. Nenhum arquivo de Core, módulo contrib ou tema contrib foi editado diretamente.

## 3. Estrutura Drupal utilizada

Nodes para páginas e projetos; blocos de conteúdo para as seções da Home; campos estruturados para identificação institucional; Views para projetos, atividades, notícias e documentos; menus nativos para navegação; Webform para contato. O tema técnico `aculta` e seu base theme foram mantidos. Não foi criado módulo customizado nem instalado page builder.

Módulos contrib presentes e ativados nesta implementação:

- Webform 6.3.1 e Webform UI: formulário administrável.
- Simple XML sitemap 4.2.3: sitemap com destinos públicos reais.
- Pathauto 1.15.0, Token e CTools: padrões de aliases para conteúdo futuro.
- Metatag 2.2.0 e Metatag Open Graph: títulos e descrições.
- Redirect 1.13.0: gestão de redirecionamentos de aliases.

Media e Media Library do Core foram habilitados e integrados ao editor. Metatag e Redirect foram adicionados pelo Composer nesta execução; as demais dependências solicitadas já estavam disponíveis nos arquivos Composer ao retomar o trabalho. Todas continuam gerenciadas pelo Composer.

## 4. Conteúdos administráveis pelo painel

Páginas, textos institucionais, projetos, resumos de projetos, notícias, atividades, documentos e metadados são editáveis como conteúdo Drupal. Os blocos da Home e os dados institucionais são editáveis em Blocos de conteúdo. Menus e Views permanecem administráveis. O formulário e sua notificação são administráveis pelo Webform.

Os projetos possuem estrutura para categoria, imagem, histórico, objetivos, atividades e registros. Notícias e atividades podem referenciar projetos. Documentos possuem campo para PDF. Nenhuma imagem ou publicação fictícia foi criada.

## 5. Conteúdo em Twig e motivo

Twig contém composição visual, wrappers semânticos, apresentação dos campos, rótulos de interface e condições de navegação. Os textos editoriais da Home não ficaram permanentemente presos a `page.html.twig`: estão em nodes e blocos. O menu é renderizado pelo Drupal. O JSON-LD é construído a partir dos campos oficiais e recebe metadados de cache.

## 6. Páginas criadas ou preparadas

Publicadas e verificadas:

- `/`
- `/institucional`
- `/projetos`
- `/atividades`
- `/noticias`
- `/transparencia`
- `/contato` — página do Webform.
- `/politica-de-privacidade`
- `/projetos/carnareggae-bloco-sativa`
- `/projetos/baque-sativa`
- `/projetos/podplant420`
- `/projetos/batalha-do-riddim`

A Home usa o hero revisado, o slogan oficial, dois parágrafos curtos em Quem somos e um resumo por projeto. Os textos completos foram preservados nas páginas internas e na base editorial. A história distingue a trajetória cultural da abertura jurídica em 22 de outubro de 2025. A referência à pesquisa na Fiocruz não afirma parceria formal.

Atividades e Notícias possuem introduções e Views preparadas, mas ainda não têm registros individuais publicados. O comprovante do CNPJ foi preparado como documento não publicado, sem arquivo ou URL inventados.

## 7. Menu

Menu principal com sete destinos reais: Início, Institucional, Projetos, Atividades, Notícias, Transparência e Contato. Menus do footer organizam links institucionais, conteúdo e participação, incluindo Política de Privacidade. A conta é discreta e aparece somente para usuários autenticados.

## 8. Blocos

Oito blocos editoriais da Home foram preparados, além do bloco estruturado de dados institucionais. Este último possui apresentações compacta, institucional, cadastral, contato e footer. As colocações utilizam a mesma entidade oficial para evitar divergências. Um bloco antigo não utilizado foi preservado, identificado administrativamente.

## 9. Elementos padrão reorganizados

A página inicial vazia foi substituída pela Home institucional configurada no Drupal. A View padrão da front page foi desabilitada, removendo seus destinos públicos padrão e RSS sem finalidade. O bloco de crédito Drupal foi desabilitado. A faixa utilitária anônima vazia não é renderizada. O menu duplicado de Início foi desabilitado. Esses ajustes não dependem de esconder conteúdo padrão com CSS.

## 10. Links pendentes

Downloads do Estatuto, comprovante do CNPJ, atas e relatórios dependem dos arquivos reais. Não há botões ou URLs de download fictícios. Perfis externos para `sameAs` e logo definitivo não foram adicionados. As quatro páginas de projetos já existem localmente e seus links funcionam.

## 11. Dados institucionais pendentes

Permanecem pendentes a composição oficial da governança, mandatos e conselhos quando aplicáveis, documentos públicos e relatórios, perfis oficiais e a revisão definitiva da Política de Privacidade. Não há placeholders públicos desses dados.

## 12. CNPJ

O CNPJ real `68.238.467/0001-08` está aplicado na Home, Institucional, Transparência, footer e JSON-LD. A Transparência apresenta o nome fantasia registrado Coletivo 420, situação Ativa, natureza jurídica, abertura e CNAEs conforme os dados fornecidos. O PDF comprobatório continua pendente.

## 13. Endereço

O endereço oficial completo está em Institucional, Transparência, Contato e JSON-LD: Avenida Cristóvão Colombo, 736, Quadra 205, Lote 27, Sala 3, Jardim Novo Mundo, Goiânia - GO, CEP 74705-130, Brasil. Home e footer usam a localização compacta conforme solicitado.

## 14. Contato

E-mail `4e20coletivo@gmail.com` e telefone `(62) 9282-0666` aplicados. O Webform em `/contato` possui Nome, E-mail, Assunto e Mensagem obrigatórios; guarda submissões com acesso administrativo e configura uma notificação ao e-mail oficial. A coleta de IP foi desativada. Nenhuma mensagem de teste foi enviada: a entrega de e-mail pelo transporte do ambiente ainda precisa ser confirmada antes da publicação.

## 15. Transparência

Identificação cadastral completa em HTML, com estrutura de documentos e relatórios. Governança sem dados confirmados não é apresentada como informação oficial. Não foram inventados documentos ou integrantes. A página não depende exclusivamente de PDFs.

## 16. Política de Privacidade

Página publicada com descrição técnica compatível com o formulário instalado: submissões, notificação, autenticação e carregamento externo das fontes. Não afirma instalação de Analytics, pixels ou cookies de marketing. O texto ainda precisa de revisão definitiva pelo responsável conforme a operação real e as ferramentas que forem utilizadas em produção.

## 17. Validação

- PHP: lint do tema e de oito scripts aprovado.
- Twig: cinco templates compilados sem erros.
- YAML: 468 arquivos exportados e quatro arquivos do tema validados.
- JavaScript: checagem de sintaxe do tema e do verificador aprovada.
- CSS: verificação no navegador em larguras 1440, 1280, 768 e 390 px; sem overflow horizontal. Body de 16 px e entrelinha 1,65; H1 entre 40 e 54 px. Paleta e fontes aprovadas preservadas.
- Menu mobile: abertura, fechamento por Escape e retorno de foco verificados.
- Doze páginas públicas: HTTP 200, um H1, metadescrição, ausência de placeholders e imagens quebradas.
- JSON-LD: identidade, CNPJ, contato e endereço conferidos; sem `sameAs` ou logo inventados.
- Sitemap: doze destinos públicos em `https://aculta.org/`, sem usuários, rascunhos ou página antiga de contato.
- Composer validate: aprovado com avisos sobre versões exatas já existentes de Bootstrap5 e Pathauto.
- Composer audit: sem avisos de segurança ou dependências abandonadas.

Não houve alteração de HTTPS ou servidor em produção. O redirecionamento HTTP para HTTPS e a ausência de mixed content no domínio real devem ser confirmados na futura etapa de publicação.

## 18. Problemas encontrados e cuidados de continuidade

Foram corrigidos a ausência inicial do campo body, o escopo de estado nos scripts executados pelo Drush, o nome incorreto do template de bloco e aliases concorrentes do formulário. Entidades preexistentes foram preservadas, e as colocações públicas foram unificadas nos dados oficiais. Não restaram erros nas verificações finais.

A exportação de configuração não transporta nodes, blocos de conteúdo, arquivos e demais entidades de conteúdo. Uma futura publicação exige planejar também a transferência dessas entidades; não se deve tratar o diretório `config/sync/` como uma cópia completa do site nem importá-lo cegamente em outra base. Os scripts desta etapa são destinados à preparação local e não devem ser reaplicados indiscriminadamente sobre conteúdo posteriormente editado.

O ambiente local está pronto para revisão visual. A conclusão da documentação institucional, da governança, da política e da entrega de e-mail permanece necessária para considerar o site pronto para produção.
