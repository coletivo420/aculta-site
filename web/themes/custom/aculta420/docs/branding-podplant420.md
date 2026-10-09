# Branding Podplant420

Este documento define o uso dos assets visuais do **Podplant420** no ACULTA420. O escopo é identidade estática do projeto; capas de episódios, convidados, thumbnails, fotografias e demais imagens editoriais continuam pertencendo ao conteúdo gerenciado pelo Drupal.

## 1. Identidade e variantes

Quatro identidades são preservadas, sem redesenho ou mudança de cores:

| Variante | Superfície recomendada |
| --- | --- |
| `stacked-on-dark` | marca clara em superfície escura |
| `stacked-on-light` | marca escura em superfície clara |
| `horizontal-on-dark` | composição horizontal clara em superfície escura |
| `horizontal-on-light` | composição horizontal escura em superfície clara |

Não aplicar filtros CSS para inverter, recolorir ou alterar a identidade.

## 2. Origem

A preparação partiu de quatro PNGs RGBA de 1024 x 1024 fornecidos para esta tarefa: duas composições empilhadas e duas horizontais, cada uma com variante para superfície clara ou escura. O briefing menciona um `podplant420-logomarcas.zip`, mas o arquivo ZIP de origem não foi recebido separadamente nesta execução; os quatro PNGs anexados foram inspecionados diretamente.

Os arquivos foram fornecidos como material aprovado pelo responsável do projeto. A cadeia jurídica de autoria/licenciamento não foi auditada de forma independente. Novos assets devem registrar origem, autorização/licença e finalidade.

## 3. Nomenclatura

O padrão é:

`podplant420-<layout>-on-<surface>-<width>w.<ext>`

Exemplos:

- `podplant420-stacked-on-dark-256w.webp`;
- `podplant420-horizontal-on-light-480w.webp`.

`on-dark` e `on-light` descrevem a superfície de destino, não um modo de cor global.

## 4. Estrutura

```text
assets/branding/podplant420/
├── README.md
├── source/
│   └── README.md
└── web/
    ├── stacked/
    └── horizontal/
```

O diretório `source/` documenta os originais e o tratamento, mas não duplica masters grandes sem consumidor. O pacote de handoff preserva os originais/derivados completos fora do runtime do tema.

Não existe `icons/` nesta entrega: nenhum ícone isolado foi fornecido e recortar um símbolo da composição seria criar uma variante não aprovada.

## 5. Formatos

- distribuição no tema: WebP com transparência;
- fonte fornecida: PNG RGBA;
- SVG: não produzido, porque a origem é raster;
- filtros de cor: proibidos para a marca.

## 6. Dimensões

Variantes canônicas versionadas no tema:

| Layout | Largura | Dimensão |
| --- | ---: | --- |
| stacked | 128 | 128 x 128 |
| stacked | 256 | 256 x 256 |
| stacked | 512 | 512 x 512 |
| horizontal-on-dark | 240 | 240 x 99 |
| horizontal-on-dark | 480 | 480 x 198 |
| horizontal-on-light | 240 | 240 x 99 |
| horizontal-on-light | 480 | 480 x 199 |

O pacote de handoff também contém derivados horizontais de 720 e 960 px e masters raster normalizados. Eles não são duplicados no runtime enquanto não houver consumidor que precise dessas resoluções.

Nas horizontais, somente o excesso de canvas transparente foi removido, com margem de segurança; a proporção do desenho foi mantida.

## 7. Aplicações recomendadas

Uso previsto:

- cards de projetos;
- vitrines de podcast;
- seções editoriais;
- listagens e grids;
- página do projeto;
- rodapé;
- peças promocionais;
- links externos e integrações visuais.

O Podplant420 **não** é identidade do cabeçalho principal nesta tarefa. Não modificar Institution Bar ou Domain Header, e não criar Domain purpose específico sem decisão arquitetural posterior.

## 8. Superfícies claras e escuras

Selecionar sempre a arte correspondente à superfície onde a marca será renderizada. A escolha deve ser feita pelo consumidor/contexto de apresentação, não por filtro CSS e não por lógica Podplant420 dentro de SDC genérico.

Uma futura integração com color mode deve trocar o asset explicitamente no presenter/Render API, preservando o mesmo espaço e a mesma semântica do componente.

## 9. Acessibilidade

- imagem informativa: usar texto alternativo contextual e conciso;
- marca dentro de link: o link precisa de nome acessível inequívoco, sem repetir texto desnecessariamente;
- marca puramente decorativa ao lado de texto equivalente: `alt=""`;
- não codificar informação apenas pela diferença claro/escuro;
- não reduzir a marca até perder legibilidade.

Créditos, licença e origem pertencem aos metadados do conteúdo quando a imagem vier da Media API.

## 10. Responsividade

SDCs e presenters genéricos recebem a imagem por props/slots ou render arrays. Não criar branches Podplant420 dentro de componentes genéricos.

Quando houver consumidor real, preferir `srcset`/`sizes` ou Responsive Image conforme o contexto. Os assets 128/256/512 e 240/480 cobrem os usos estáticos atuais previstos; ampliar o conjunto somente com requisito medido.

## 11. Cache

Assets estáticos do tema são versionados pelo deploy e podem usar cache HTTP/CDN de longa duração conforme a política global do site. Mudanças de conteúdo da marca exigem novo arquivo/commit e invalidação normal do deploy.

Imagens editoriais dinâmicas devem usar Media/File API, Image Styles, Responsive Image e Render API, preservando Cacheability Metadata. Não servir catálogo editorial por JSON paralelo e não contornar os caches do Drupal.

## 12. Política para futuras imagens

Capas de episódios, convidados, fotografias, vídeos e thumbnails devem ser entidades Media/arquivos gerenciados pelo Drupal. Preservar, quando aplicável:

- texto alternativo;
- crédito;
- licença;
- origem;
- dimensões;
- proporção;
- descrição.

Não armazenar centenas de capas no tema. Não criar módulo custom apenas para servir imagens.

## 13. Regras anti-regressão

1. Não redesenhar, esticar, recolorir, filtrar ou auto-vetorizar a marca.
2. Não usar Podplant420 no Institution Bar ou Domain Header sem decisão específica.
3. Não criar Domain purpose Podplant420 por conveniência visual.
4. Não adicionar condição por hostname/purpose no tema para escolher esta marca.
5. Não introduzir lógica Podplant420 em SDC genérico; dados entram por contrato de apresentação.
6. Não transformar assets editoriais em catálogo estático do tema ou JSON paralelo.
7. Não gerar dezenas de derivados físicos de conteúdo; usar Image Styles/Responsive Image.
8. Não criar ícone recortado sem fonte/aprovação própria.
9. Não adicionar caminhos absolutos.
10. Não declarar QA visual ou Drupal runtime como aprovado sem execução real.

## Estado da integração

Nesta entrega não foi identificado consumidor runtime concreto que justificasse alterar Twig, SDC, Institution Bar, Domain Header ou Portal. Portanto, a mudança fica deliberadamente restrita a assets, documentação e gate determinístico. A integração deve ocorrer apenas quando um componente/presenter real consumir a marca.
