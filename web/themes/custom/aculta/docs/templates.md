# Inventário de templates Twig

Overrides só devem existir quando alteram apresentação necessária. Remoção exige comparação com Core/base theme/contrib e teste de regressão.

## `page.html.twig`

**Motivo:** define o shell semântico público: header, menu principal, utility/account, breadcrumb, highlighted/help, título, content/sidebar e footer.

**Dados:** regions Drupal e variáveis preparadas por preprocess/Portal.

**Preservar:** landmarks, `main-content`, foco, classes Bootstrap, regiões, sidebar condicional, notice de edição de Conta e render arrays completos.

## `block--system-branding-block.html.twig`

**Motivo:** apresenta logo e slogan aprovados.

**Dados:** System Branding e preprocess do tema.

**Preservar:** link para front, alt com nome institucional, dimensões do logo e fallback sem imagem.

## `block--block-content--type--aculta-institution.html.twig`

**Motivo:** apresenta o bloco institucional em view modes distintos, incluindo footer, home, institutional e registration.

**Dados:** fields do custom block e variáveis de preprocess.

**Preservar:** fields configuráveis, view modes, link de transparência e ausência de storage paralelo.

## `node--editorial-highlight.html.twig`

**Motivo:** presenter Drupal do card de destaque editorial usado na home/VVJB.

**Dados:** category, summary, complement, link e label do node.

**Implementação:** preserva o wrapper `<article>` e os attributes do node e delega o markup interno ao SDC `aculta:editorial-card` via `include(..., with_context = false)`.

**Preservar:** attributes/contextual do node, conteúdo editorial editável e o contrato de slots do SDC.

## `node--project--teaser.html.twig`

**Motivo:** teaser editorial de projetos.

**Dados:** imagem, categoria, display title, body, label/link do node.

**Preservar:** title attributes, bookmark URL, fields editáveis e contexto `.aculta-prose`.

## `navigation/breadcrumb.html.twig`

**Motivo:** markup público do breadcrumb e página atual.

**Dados:** links e política de visibilidade/hierarquia vêm de `AcultaBreadcrumbBuilder`; o preprocess do tema apenas expõe `currentTitle()` como `aculta_current_breadcrumb` para apresentação.

**Preservar:** `nav`, label acessível, lista ordenada e `aria-current="page"`.

**Fronteira arquitetural:** purpose, rotas ocultas, raiz, hierarquia, dependências de cache e resolução segura do título pertencem ao Portal. O tema mantém somente markup e semântica visual/acessível.

## `form/input.html.twig`

**Motivo atual:** mantém um override mínimo de input preservando attributes e children.

**Dados:** Form API/preprocess herdado do Bootstrap5.

**Preservar:** todos os attributes e children.

**Revisão futura:** só remover se comparação com a versão efetivamente instalada do base theme/Core provar redundância.

## `views-view-vvjb.html.twig`

**Motivo:** fornece o label referenciado pelo `aria-labelledby` do VVJB 2.0 na View de destaques da home, anexa a library contextual `aculta/editorial-carousel` e inclui o template original do módulo.

**Dados:** View e options do VVJB.

**Preservar:** IDs compatíveis, label visualmente oculto, attachment da library apenas para `home_editorial_highlights` e delegação dos controles ao VVJB.

## `feed-icon.html.twig`

**Motivo:** preserva o link de feed sem herdar a iconografia/cores padrão do Drupal.

**Dados:** URL e título do feed.

**Preservar:** texto RSS e accessible name contextual.

## Regras Twig

Twig não deve:

- consultar banco;
- avaliar pagamentos;
- decidir matrícula/progresso;
- decidir Domain access;
- autenticar;
- persistir estado.

Quando a preparação de contexto crescer, preferir preprocess ou serviço na camada responsável, sem mover regra de negócio para o tema.
