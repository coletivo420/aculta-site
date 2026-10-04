# Refinamento local da Home

Revisão em 3 de outubro de 2026. Site: http://localhost:8080/. Não houve commit, push ou alteração em produção.

1. **Drupal:** 11.4.8, detectado no ambiente ativo.
2. **PHP:** 8.5.10. O VVJB instalado exige PHP >= 8.3 e Drupal ^11.3 ou ^12.
3. **VVJB:** 2.0.0 estável, com VVJ Core 2.0.0 como dependência. Release coberta pela política de segurança, conforme https://www.drupal.org/project/vvjb.
4. **Composer:** constraint `drupal/vvjb: ^2.0`; `composer.json` e `composer.lock` alterados pelo Composer. Nenhum pacote existente foi atualizado na instalação do VVJB; foram instalados somente VVJB e VVJ Core.
5. **Extensões:** habilitados `vvjb` e `vvj_core`. Olivero desinstalado conforme solicitação posterior. Seus arquivos distribuídos com o Core permanecem intactos. O tema `aculta`, base Bootstrap5 e tema administrativo foram preservados.
6. **Código:** alterados `web/themes/custom/aculta/css/style.css`, `js/aculta.js`, `aculta.theme` e `templates/page.html.twig`. Criados `templates/node--editorial-highlight.html.twig` e `templates/views-view-vvjb.html.twig`; scripts `refine-home-carousel.php`, `finalize-home-carousel.php`, `describe-institution-admin.php`, `inspect-carousel-runtime.php`, `validate-home-carousel.php` e `validate-home-carousel-browser.mjs`. O template VVJB apenas fornece o rótulo referenciado pelo `aria-labelledby` e inclui o template original; não substitui controles do módulo.
7. **Configuração:** exportação seletiva, com nomes completos listados abaixo. Não houve exportação geral nem importação em produção. Foram retirados do snapshot os blocos Olivero realmente removidos pela desinstalação.
8. **Conteúdo:** novo tipo `editorial_highlight`, nome administrativo “Destaque editorial”. Os tipos existentes não representavam esse conteúdo de forma limpa. A composição da Home continua por blocos, Views e conteúdo Drupal.
9. **Campos:** reutilizado `field_category` (string). Criados `field_summary` e `field_complement` (texto simples longo), `field_link` (link interno com título obrigatório quando preenchido), `field_weight` (inteiro). Título e Publicado são nativos. O campo de ordem é oculto na apresentação pública.
10. **View:** “Destaques editoriais da Home”, `home_editorial_highlights`, display `block_1`. Filtra tipo e publicação; ordena por peso crescente e ID crescente como desempate. Não filtra IDs dos três nodes.
11. **VVJB:** plugin real `views_vvjb`; orientação horizontal; `items_small: 1`, `items_big: 2`, `gap: 24`, `item_width: 0`, `breakpoints: '768'`, `looping: true`, `navigation: both`. Teclado, touch, hover e reduced motion habilitados. Play/pause habilitado; contador e progresso circular desabilitados. Deep links desabilitados.
12. **Itens visíveis:** medidos dois em 1440, 1200, 1100, 1099 e 1024 px; um em 768, 480 e 360 px. O módulo adapta a quantidade à largura disponível, respeitando os limites configurados. No desktop, cards com 370 px; no celular, largura disponível. A altura cresce com o texto e não há corte ou ellipsis.
13. **Autoplay:** 3000 ms, fornecido pelo VVJB, com looping habilitado. Dois itens por grupo no desktop garantem rotação com os três destaques atuais; no mobile, um item por grupo. A faixa dos slides foi centralizada com largura máxima de 764 px no desktop e 390 px no mobile.
14. **Pausa:** nativa ao passar o mouse na área dos slides e conforme o comportamento do módulo para visibilidade. Botão manual de play/pause. Complemento mínimo no tema pausa pela API pública ao entrar foco; mantém a pausa até o visitante escolher Reproduzir, evitando retomada inesperada. Nenhum timer de carrossel foi criado no tema.
15. **Reduced motion:** pausa nativa habilitada; teste confirmou estado pausado. CSS contextual do tema elimina transições e animações remanescentes; duração medida de 0 s.
16. **Menu:** breakpoint final 1100 px. Testado aberto em 1100 e colapsável em 1099. A navegação tem espaço flexível, sem limite antigo de 60%, sem quebra de linha e com links indivisíveis. Desktop e notebook mantêm os sete itens.
17. **Fonte do menu:** Oswald, 16 px, peso 600; gap de 20 px. Branding com `clamp(1.55rem, 2.1vw, 2rem)` e entrelinha 1,05. Faixa de conta com fonte de 14 px e padding vertical de 8 px; ausente para anônimos.
18. **Barras:** padding de 10 px vertical e 20 px horizontal, fonte entre 25 e 29 px e entrelinha 1,1. Linhas laterais de 1 px mais curtas. Altura medida de aproximadamente 48–52 px, conforme a largura.
19. **Espaçamento:** intervalo reutilizável `clamp(2.25rem, 4.5vw, 4.25rem)`: 36 px mínimo, até 68 px em desktop. Hero, missão, parágrafos, CTAs, grid e introdução de projetos foram compactados nas regras existentes. Escala de headings e body de 16 px preservados. Quem somos não foi reescrito; missão, quatro áreas, projetos e transparência permanecem fora do carrossel.
20. **Administração:** em Conteúdo, filtrar “Destaque editorial” e editar ou adicionar um item (`/node/add/editorial_highlight`). Campos possuem orientações administrativas. Ordem menor aparece antes; despublicar retira o item da View. View disponível em `/admin/structure/views/view/home_editorial_highlights`; colocação em Layout de blocos. Descrições e nomenclatura administrativa padronizadas em seis tipos de conteúdo, dois tipos de bloco, seis menus, cinco Views e campos do destaque. Os títulos públicos aprovados foram preservados.
21. **Conteúdo inicial:** três nodes criados na base local, IDs 14, 15 e 16. Esses IDs são apenas registro da execução, não dependência da View/Twig. Nodes não são exportados como configuração. O script de preparação cria os conteúdos uma vez e registra o estado; não deve ser reaplicado para sobrescrever futuras edições. Traduções de interface também são dados locais, documentadas no script de finalização.
22. **CTAs:** comunicação → `/noticias`; cuidado → `/institucional`; conhecimento/história → `/institucional`. Todos os destinos existem. Nenhum CTA pendente. Os três blocos editoriais anteriores foram desabilitados por configuração; seus conteúdos não foram apagados e não há duplicação nem ocultação via CSS.
23. **Testes:** View e renderização com 0, 1, 3 e 4 itens aprovadas. Alteração dos campos, criação de quarto item, publicação, despublicação e ordenação testadas em transação revertida; os sete widgets administrativos foram conferidos. Com zero itens não há carrossel; o bloco oculta resultado vazio. Com um, sem controles e `slide_time: 0`. Navegador: inicialização, autoplay, setas, indicadores, teclado, foco, hover, gesto touch sintetizado, reduced motion e menu com Escape/retorno de foco aprovados. Sem overflow nas oito larguras testadas; sem blocos antigos duplicados. Não foi realizado teste manual em aparelho físico ou leitor de tela. PHP, JavaScript, YAML seletivo e renderização Twig verificados. Artefatos em `tmp/home-carousel-review/` são ignorados pelo Git.
24. **Problemas e avisos:** erro inicial de autoload no runtime HTTP foi resolvido reconstruindo seu container local, distinto do CLI, e atualizando caches. O tratamento de item único precisou de preprocess no tema porque o módulo não o fornece automaticamente. A pausa por foco também exigiu complemento mínimo. Controles traduzidos via Locale; sem edição do contrib. Composer validate aprovado, com avisos preexistentes de versões exatas de Bootstrap5 e Pathauto. Composer audit sem advisories ou dependências abandonadas. Nenhum patch, instalação forçada ou dependência de carrossel via CDN.
25. **Git status resumido:** estado abaixo; o repositório já continha alterações e diretórios não rastreados antes desta rodada. Nenhum staging, commit ou push.

```text
 M composer.json
 M composer.lock
?? config/
?? scripts/
?? web/themes/custom/
```

## Configurações criadas ou alteradas nesta rodada

```text
core.extension
node.type.editorial_highlight
node.type.page
node.type.project
node.type.activity
node.type.article
node.type.document
block_content.type.basic
block_content.type.aculta_institution
field.storage.node.field_summary
field.storage.node.field_complement
field.storage.node.field_link
field.storage.node.field_weight
field.field.node.editorial_highlight.field_category
field.field.node.editorial_highlight.field_summary
field.field.node.editorial_highlight.field_complement
field.field.node.editorial_highlight.field_link
field.field.node.editorial_highlight.field_weight
core.entity_form_display.node.editorial_highlight.default
core.entity_view_display.node.editorial_highlight.default
views.view.home_editorial_highlights
views.view.aculta_projects
views.view.aculta_activities
views.view.aculta_news
views.view.aculta_documents
block.block.aculta_home_editorial_highlights
block.block.aculta_home_knowledge
block.block.aculta_home_care
block.block.aculta_home_research
system.menu.main
system.menu.account
system.menu.footer
system.menu.aculta-footer-institution
system.menu.aculta-footer-content
system.menu.aculta-footer-participation
```

Configurações de blocos removidas pela desinstalação do Olivero, incluindo suas traduções no snapshot quando existentes:

```text
block.block.olivero_account_menu
block.block.olivero_breadcrumbs
block.block.olivero_content
block.block.olivero_help
block.block.olivero_main_menu
block.block.olivero_messages
block.block.olivero_page_title
block.block.olivero_powered
block.block.olivero_primary_admin_actions
block.block.olivero_primary_local_tasks
block.block.olivero_secondary_local_tasks
block.block.olivero_site_branding
```

A instalação pode registrar traduções e configuração opcional do próprio módulo no banco local. Esses efeitos nativos não foram incluídos por uma exportação geral. O snapshot contém apenas o conjunto explicitamente revisado desta funcionalidade e das referências administrativas solicitadas.

Cache rebuild local já executado pelo PHP + `vendor/drush/drush/drush.php`. A próxima ação é a revisão visual pelo responsável; não foi executado deployment.

## Ajuste posterior: alinhamento e autoplay de 3 segundos

Criados `scripts/adjust-carousel-autoplay.php` e `scripts/validate-carousel-loop.mjs`. Alterados apenas o CSS contextual, as opções da View e sua exportação, além deste relatório. O teste mediu centralização nos grupos inicial e seguinte em 1440, 1200, 1024, 768, 480 e 360 px: diferença de centro entre 0 e 0,54 px, sem overflow. Autoplay nativo observado no mobile na sequência `1 → 2 → 3 → 1 → 2`, com intervalo configurado de 3000 ms. Em desktop há dois grupos: os dois primeiros destaques e o terceiro centralizado. Permanecem as pausas acessíveis por hover, foco e reduced motion.
