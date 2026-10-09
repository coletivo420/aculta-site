# Branding Baque Sativa — kit de imagens do grupo

## Escopo e posição no site

Este kit reúne as logomarcas do **Grupo de Percussão Baque Sativa** para uso em cards de projetos, listagens, grids, páginas institucionais do grupo, conteúdo editorial, perfis, galerias e materiais de divulgação.

**As imagens não fazem parte do cabeçalho principal do ACULTA.** Não são usadas no Domain Header, na Institution Bar nem na identidade global do ACULTA420. Não alterar esses componentes para receber este kit sem decisão de design aprovada.

## Origem e identidade visual

- Artes oficiais recebidas pelo responsável do projeto: texto **amarelo** e texto **vermelho**.
- Ambas são verticais, com fundo transparente. O desenho original (percussionista, tambor, correntes e lettering) foi preservado.
- Os originais têm 1024 × 1024 px, em PNG RGBA. Estão em `source/` e não devem ser alterados.
- Licença e direitos de uso não foram auditados neste repositório. Novos usos externos exigem confirmação do responsável.

## Variantes

| Variante | Cor do lettering | Uso |
| --- | --- | --- |
| `yellow` | amarelo | versão amarela da marca |
| `red` | vermelho | versão vermelha da marca |

As duas variantes são **versões da marca**. **Não são modos claro/escuro.** A escolha entre amarelo e vermelho deve ser feita pelo conteúdo ou pelo design aprovado, nunca por domínio, hostname ou modo de cor do site.

Observação visual: o amarelo tem pouco contraste sobre superfície clara. Em uma prancha de revisão, o lettering amarelo sobre creme ficou visivelmente mais fraco que o vermelho. Não foi medida uma razão de contraste numérica. Para superfícies claras, avaliar o vermelho ou um fundo escuro antes de adotar o amarelo.

## Dimensões

Todas as proporções são preservadas. Nenhuma variante vertical é esticada.

**Verticais (altura fixa, largura proporcional):**

| Arquivo base | Altura | Largura amarela | Largura vermelha |
| --- | ---: | ---: | ---: |
| `h64` | 64 | 37 | 37 |
| `h96` | 96 | 56 | 56 |
| `h128` | 128 | 74 | 74 |
| `h192` | 192 | 111 | 112 |
| `h256` | 256 | 148 | 149 |
| `h384` | 384 | 223 | 223 |
| `h512` | 512 | 297 | 298 |
| `h768` | 768 | 445 | 447 |
| `master` | 899 | 521 | 557 (altura 957) |

**Quadrados (composição inteira centralizada em canvas N × N):** `square-128`, `square-192`, `square-256` e `square-512`. Não há corte destrutivo: o desenho inteiro fica dentro do canvas.

O master amarelo mede 521 × 899 px e o vermelho mede 557 × 957 px. Ambos mantêm margem de segurança de cerca de 2,5% (21–23 px na medição).

## Recorte e transparência

- Todos os arquivos têm canal alfa com transparência real. Não há fundo opaco.
- `master` é o original recortado com margem de segurança. Os demais derivados são redimensionados a partir dele.
- Foi verificado visualmente, em fundo claro e escuro, que tambor, correntes e lettering estão íntegros, sem cortes e sem distorção. Não houve halos visíveis na inspeção em 520 px. Em tamanhos pequenos (64–128 px) a inspeção foi somente determinística (dimensões e transparência), sem zoom visual.

## Formatos: PNG e WebP

- Cada derivado existe em **PNG** e em **WebP**, com o mesmo nome base.
- **PNG** preserva transparência e serve de referência e fallback.
- **WebP** é a alternativa otimizada, com transparência. No kit, reduz o peso em cerca de 13% (miniaturas) a 30% (masters e quadrados grandes) em relação ao PNG equivalente.
- Use WebP quando o consumidor aceitar. Use PNG onde for necessário fallback direto.
- Os originais são PNG. Não converter novamente sem erro comprovado.

## Seleção de variantes

- **Cards quadrados:** `square-256` ou `square-512`.
- **Cards verticais, listas e páginas do grupo:** `h384`, `h512`, `h768` ou `master`, conforme o espaço.
- **Miniaturas e listas compactas:** `h64`, `h96` ou `h128`.
- **Não esticar** a marca vertical para ocupar formato horizontal.

## Tamanhos responsivos

- Dimensione o elemento com `max-width: 100%` e `height: auto`.
- Informe `width` e `height` (ou `aspect-ratio`) no HTML ou no render array, para evitar CLS.
- Quando houver um consumidor real com `srcset`, use os tamanhos reais de cada derivado como descritor `w`, por exemplo `…h256.webp 149w`. Nenhum `srcset` é criado sem consumidor.
- Imagens fora da área crítica usam `loading="lazy"`.

## Acessibilidade e texto alternativo

- Use alt contextual quando a imagem identifica o grupo, por exemplo `Logomarca do Grupo de Percussão Baque Sativa`.
- Use `alt=""` apenas quando a imagem for estritamente decorativa e houver texto equivalente ao lado, sem repetir o nome.
- Não duplique o nome do grupo em alt e em texto adjacente para leitor de tela.
- A marca não deve ser recolorida, invertida ou filtrada por CSS.

## Uso no tema e no Drupal

- Componentes genéricos continuam recebendo a imagem por props, slots ou render arrays. Não há SDC específico para o Baque Sativa nesta fase.
- Imagens dinâmicas, como fotografias, capas e imagens editoriais, pertencem a Drupal Media com Image Styles e Responsive Image. Não copiar esse conteúdo para o tema.
- O tema não resolve domínio, hostname nem purpose para escolher a variante. Essa decisão, quando existir, pertence à camada de apresentação ou ao conteúdo.
- Não há caminho de produção nem lógica de Domain neste documento ou no kit.

## Localização dos arquivos

- Kit: `web/themes/custom/aculta420/assets/branding/baque-sativa/`
  - `source/`: dois originais (`source/baque-sativa-yellow-source.png`, `source/baque-sativa-red-source.png`).
  - `web/`: 52 derivados PNG/WebP.
  - `manifest.json`: bytes, dimensões e SHA-256 dos 54 arquivos raster.
  - `README.md`: resumo do pacote entregue.
- Verificador: `scripts/verify-baque-sativa-assets.py`, a partir da raiz do repositório.

## Manutenção e atualizações

- **Não recomponha nem reconverta** os derivados sem defeito reproduzível. Cada arquivo é a referência aprovada.
- **Mantenha os nomes** `baque-sativa-<cor>-<tipo>.<ext>`. Nomes são estáveis; uma mudança de nome exige redirecionamento ou atualização de todos os consumidores.
- **Ao adicionar ou trocar um arquivo**, atualize `manifest.json` (bytes, dimensões, SHA-256) e rode `python3 scripts/verify-baque-sativa-assets.py`.
- **Ao trocar a arte** (nova versão da marca), crie uma nova versão do kit. Não sobrescreva silenciosamente os arquivos atuais, e registre a troca no CHANGELOG.
- **Ao adotar uma variante em um consumidor**, registre o uso neste documento.

## Limites desta entrega

- Nenhum template, SDC, CSS ou library consome o kit nesta fase. Os arquivos estão prontos para uso, sem integração visual.
- Não foram feitas medições de contraste numérico.
- QA visual em navegador e no runtime Drupal estão **DEFERRED** para o homelab, quando houver consumidor.
