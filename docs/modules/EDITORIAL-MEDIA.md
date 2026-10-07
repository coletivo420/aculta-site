# Módulos — Editorial, mídia e formulários

Data da revisão: 2026-10-07.

## Editorial

| Módulo | Papel |
| --- | --- |
| node | conteúdo |
| taxonomy | classificação |
| views | listas/consultas |
| ckeditor5 | editor |
| filter | formatos de texto |
| workflows / content_moderation | fluxo editorial |
| content_lock | lock de edição |
| diff | comparação de revisões |
| freelinking | links editoriais, especialmente Wiki |
| token | tokens de integrações |

A Wiki permanece Node/Taxonomy/Views. Freelinking não vira storage de conteúdo.

## Mídia

| Módulo | Papel |
| --- | --- |
| media / media_library | ativos e seleção de mídia |
| image | imagens/estilos |
| crop | configuração/entidades de crop |
| image_widget_crop | integração de crop no widget |

O tema não cria pipeline de imagem paralelo.

## Formulários

`webform` e `webform_ui` atendem formulários estruturados do projeto. Conta pertence a User/Profile/Change Mail; checkout pertence ao Commerce.

## Views interativas

`vvjb` fornece carousel de Views e usa `vvj_core`. VVJT pode ser avaliado para Views tabuladas específicas, mas não é engine da Minha Conta. Ver [ACCOUNT-UI.md](ACCOUNT-UI.md).
