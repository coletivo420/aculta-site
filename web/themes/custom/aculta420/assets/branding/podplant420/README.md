# Podplant420 — arquivos estáticos de branding

Este diretório contém **4 originais recebidos** em `source/originais/`, **4 masters raster derivados** em `source/masters/` e **14 arquivos WebP** de distribuição em `web/`.

- `on-dark`: letras claras, para superfícies escuras.
- `on-light`: letras escuras, para superfícies claras.
- `stacked`: composição quadrada com áreas transparentes internas intencionais.
- `horizontal`: composição recortada, preservando proporção e sem remodelar a identidade.

`asset-inventory.json` registra hashes SHA-256, dimensões, alfa e peso por arquivo. Os PNGs em `source/originais/` foram copiados sem edição; os PNGs em `source/masters/` são **derivados de trabalho, não os originais**.

Use `web/` apenas onde existir consumidor real. Não introduzir assets em Domain Header, Institution Bar, nem filtrar/recolorir as marcas. Não usar estas logomarcas como favicon global. A escolha claro/escuro pertence à camada de apresentação consumidora, mantendo mesmo tamanho/layout.

Leia `../../../docs/branding-podplant420.md` (a partir deste diretório, consultar documentação no diretório `aculta420/docs`).
