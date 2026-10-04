# Limpeza da estrutura administrativa local

Data: 3 de outubro de 2026. Escopo exclusivamente local; sem commit, push, deployment ou edição de Core/contrib.

Foram removidas 47 configurações sem uso confirmado e atualizadas 19 configurações mantidas, além das respectivas traduções administrativas. A exportação foi seletiva, comparando configuração ativa antes e depois de cada operação.

## Removido

- Oito Views públicas padrão/de exemplo: arquivo cronológico, conteúdo recente, front page padrão, glossário, termos de taxonomia, usuários novos/online e exemplo do VVJB. Nenhuma possuía dependência de configuração.
- Doze colocações de blocos herdadas do Bootstrap5. O Bootstrap5 permanece instalado como base do tema customizado.
- Três colocações antigas dos destaques substituídos pelo carrossel e o bloco desabilitado de crédito Drupal.
- Formulário de exemplo `contact` do Webform, sem submissões. O formulário institucional `aculta_contact` foi preservado.
- Módulo Contact do Core desinstalado localmente, com remoção dos dois formulários antigos e configurações associadas. Não foi removido código do Core. O contato público continua em `/contato`, usando Webform.
- Menus `footer` e `tools`, sem links editoriais e sem colocações restantes. Os três menus institucionais do footer continuam ativos.
- Vocabulário vazio `tags`, sem termos nem campos que o utilizassem. Permissões do papel editorial relativas a esse vocabulário foram retiradas. O módulo Taxonomy permanece disponível no Core.
- Tipos vazios de mídia `audio` e `video`, seus campos e displays. Não eram permitidos pelo editor nem possuíam entidades. Imagem, Documento e Vídeo remoto foram mantidos por sua integração real com o editor institucional.

## Mantido e contextualizado em português

As Views administrativas necessárias para conteúdo, blocos, arquivos, mídia, biblioteca de mídia, usuários, eventos, redirecionamentos e submissões receberam nomes, descrições e títulos administrativos em português. Os displays de seleção de mídia e de submissões receberam nomes distintos para suas funções.

Os menus Administração, Gestão de conteúdo, Links do usuário e Conta do usuário receberam referências de uso. O menu Gestão de conteúdo e os links do usuário são usados pelo módulo Navigation: não foram tratados como vazios apenas por não terem links criados manualmente.

O tipo Bloco editorial e os três tipos de mídia mantidos receberam descrições em português. Traduções herdadas que mascaravam os novos nomes ou descrições foram corrigidas pelo sistema de configuração de idioma do Drupal. Identificadores técnicos foram preservados.

Tipos institucionais, notícias, atividades, projetos, documentos, destaques, campos, menus públicos e Views institucionais foram mantidos. Ausência de registros em uma estrutura prevista e utilizada pela arquitetura não foi confundida com estrutura abandonada.

## Conteúdo preservado

Não foram apagados nodes, blocos de conteúdo, mídias, arquivos ou submissões existentes. A contagem de nodes, blocos de conteúdo e links editoriais foi comparada com o inventário inicial e permaneceu igual.

Os textos de blocos antigos e os dados institucionais anteriores continuam armazenados como conteúdo para referência; suas colocações públicas obsoletas foram removidas. Esta etapa limpou a estrutura administrativa e não descartou o histórico editorial.

## Validação

- Quatorze Views necessárias e oito menus mantidos conferidos.
- Dez rotas administrativas essenciais presentes.
- Dependências de configuração verificadas, sem referências a configurações removidas.
- Quinhentos e cinco arquivos YAML do snapshot parseados sem erro.
- Doze páginas públicas com HTTP 200, incluindo Home, Contato e páginas dos projetos.
- Carrossel verificado em 1440, 1200, 1024, 768, 480 e 360 px: centralizado e sem overflow. Autoplay de 3000 ms com sequência observada `1 → 2 → 3 → 1 → 2`.
- Cache local reconstruído após as remoções. Nenhuma dependência Composer nova e nenhuma alteração visual nesta rodada.

As verificações administrativas usaram as APIs e a base local; não foi feita uma revisão manual de cada tela com uma sessão autenticada de navegador.

## Arquivos desta rodada

Criados:

- `scripts/audit-admin-structure.php`
- `scripts/inspect-admin-cleanup-dependencies.php`
- `scripts/clean-admin-structure.php`
- `scripts/refine-admin-retained-structure.php`
- `scripts/finalize-admin-references.php`
- `scripts/validate-admin-cleanup.php`
- Este relatório.

Configurações e traduções afetadas foram sincronizadas seletivamente em `config/sync/`. O inventário prévio e os resultados detalhados estão em `tmp/admin-structure-audit.json`, `tmp/admin-cleanup-before.json` e `tmp/admin-cleanup-final-diff.json`, ignorados pelo Git. O inventário serve como referência local; não substitui um backup completo do banco.

## Configurações atualizadas

```text
block_content.type.basic
core.extension
media.type.document
media.type.image
media.type.remote_video
system.menu.account
system.menu.admin
system.menu.content
system.menu.navigation-user-links
user.role.content_editor
views.view.block_content
views.view.content
views.view.files
views.view.media
views.view.media_library
views.view.redirect
views.view.user_admin_people
views.view.watchdog
views.view.webform_submissions
```

## Configurações removidas

```text
block.block.aculta_home_care
block.block.aculta_home_knowledge
block.block.aculta_home_research
block.block.aculta_powered
block.block.bootstrap5_account_menu
block.block.bootstrap5_branding
block.block.bootstrap5_breadcrumbs
block.block.bootstrap5_content
block.block.bootstrap5_footer
block.block.bootstrap5_help
block.block.bootstrap5_local_actions
block.block.bootstrap5_local_tasks
block.block.bootstrap5_main_navigation
block.block.bootstrap5_messages
block.block.bootstrap5_page_title
block.block.bootstrap5_powered_by_drupal
contact.form.aculta_contact
contact.form.personal
contact.settings
core.entity_form_display.media.audio.default
core.entity_form_display.media.audio.media_library
core.entity_form_display.media.video.default
core.entity_form_display.media.video.media_library
core.entity_view_display.media.audio.default
core.entity_view_display.media.audio.media_library
core.entity_view_display.media.video.default
core.entity_view_display.media.video.media_library
core.entity_view_mode.contact_message.token
field.field.media.audio.field_media_audio_file
field.field.media.video.field_media_video_file
field.storage.media.field_media_audio_file
field.storage.media.field_media_video_file
language.content_settings.taxonomy_term.tags
media.type.audio
media.type.video
system.menu.footer
system.menu.tools
taxonomy.vocabulary.tags
views.view.archive
views.view.content_recent
views.view.frontpage
views.view.glossary
views.view.taxonomy_term
views.view.vvjb_example
views.view.who_s_new
views.view.who_s_online
webform.webform.contact
```

## Git

Estado resumido, sem staging:

```text
 M composer.json
 M composer.lock
?? config/
?? scripts/
?? web/themes/custom/
```

As modificações de Composer e os diretórios não rastreados já existiam antes desta rodada. Não houve commit, push ou atuação em produção. Ambiente pronto para revisão administrativa local.
