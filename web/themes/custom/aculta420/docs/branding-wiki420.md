# Wiki420 — logomarcas e assets

Status: **PR de inclusão — aguardando upload dos assets binários**.

## Fontes fornecidas pelo projeto
- `Logo WIKI420 com Globo de Quebra-Cabeça.png`: composição horizontal 2048×699 RGBA, preferida no Domain Header.
- `wiki420.png`: composição empilhada 1024×1024 RGBA, para cartões, peças quadradas e redes.

Derivados realizados sem alteração da identidade: PNG transparente e WebP redimensionados com LANCZOS, crop das margens transparentes e ícones do globo em 32/48/96/180/192/512 px.

## Arquivos esperados no repositório

Colocar os derivados em `web/themes/custom/aculta420/assets/branding/wiki420/web/`.

O **arquivo crítico para ativar o cabeçalho** deve se chamar:
`wiki420-horizontal-960w.webp`.

A origem é a imagem horizontal do usuário, com transparência e proporção preservadas. O Portal verifica que esse arquivo realmente existe antes de produzir `regions.brand_media`; sem ele, o branding anterior continua funcionando.

O tema usa `domain_presentation.regions.brand_media` sem inspecionar o purpose ou hostname. O Portal seleciona a identidade Wiki; a Render API propaga cache metadata de Domain existente.

## Verificação de aceitação (Homelab)

1. Importar todos os arquivos do kit (link no resumo de entrega deste chat) para `assets/branding/wiki420/web`.
2. Verificar que o arquivo de cabeçalho 960w foi publicado e que o diretório não contém assets não autorizados.
3. Executar gate de tema e Portal, PHP lint e `drush cr`.
4. Testar Wiki anônima/logada e MAIN anônima/logada, verificando: somente Wiki tem imagem própria; MAIN mantém ACULTA.
5. Checar comportamento responsivo, modo claro/escuro, alt, teclado, cache entre hosts e fallback sem arquivo.
6. Atualizar esta documentação e fechar PR apenas após os assets estarem versionados e testes passarem.

Não modificar configuração global `system.site` ou branding de outros purposes.
