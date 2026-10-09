## 0.4.0-dev.7 — T4 concluída: validadores de navegador sem endpoint fixo — 2026-10-09

Classificação: PATCH da linha 0.4.0 (ferramentas de validação; sem mudança de tema visível); sem componente stable alterado.

- Os oito validadores `scripts/*.mjs` deixaram de fixar a porta DevTools (9223) e a origem (`localhost:8080`). Lêem `ACULTA_DEVTOOLS_PORT` e `ACULTA_SITE_ORIGIN` por `scripts/lib/browser-env.mjs` e param antes de conectar quando elas não existem (DT-T09).
- Novo gate `scripts/validate-browser-validators.php`: 19 checagens; reprova endpoint literal e validador que fala com o navegador sem o helper. Caso negativo verificado.
- Execução real: `validate-institution-browser.mjs` conectou ao Chromium (porta 9333) e leu o site pela origem informada. O validador reprovou por expectativas de conteúdo antigas (`/apoie`, `aculta_favicon.ico`), registradas como DT-T18; não é falha da porta.
- Versão marcada em `aculta420.info.yml` e na asserção do gate de Foundation; sem tag.

## 0.4.0-dev.6 — Logomarca Wiki420 no Domain Header (PR #112) — 2026-10-09

Classificação: MINOR da linha 0.4.0 (novo asset de identidade e apresentação por purpose); sem componente stable alterado.

- Assets versionados em `assets/branding/wiki420/web/` (horizontal 480/720/960 WebP, empilhada 256/512 WebP, globo 32/48/96/192/512 PNG) e `source/` (masters 960 PNG e 512 PNG).
- Cabeçalho da Wiki420 com `srcset` (480/720/960 px) e `sizes="18rem"`; carregamento eager e `fetchpriority=high`. Integração via `brand_media` do Portal; o tema não inspeciona purpose nem hostname.
- Correção: com `brand_media`, o fallback textual não é montado (decisão no pré-processamento de `ThemeHooks`, template inalterado). Antes havia dois links para a home no cabeçalho da Wiki.
- Verificação: no desktop (1280 px, modo claro) o cabeçalho mede 159 px contra 139 px na principal, ou seja, +20 px de altura; em 390 px, 141 px. Sem overflow horizontal em 1280 e 390 px. Respostas alternadas Wiki/principal (duas rodadas) sem vazamento de marca.
- Limites: masters são derivados (originais não vieram no pacote); razão de contraste da marca no modo escuro não medida; zoom real não testado; o Portal conhece o caminho dos assets (ver `docs/branding-wiki420.md`).

## 0.4.0-dev.5 — T3 concluída: CSS residual dos padrões nos SDCs — 2026-10-09

Classificação: PATCH da linha 0.4.0 (movimentação de CSS sem mudança visual); sem componente stable alterado.

- Hero: `.aculta-hero`, `.aculta-eyebrow`, `h1`, `.aculta-hero-lead` e `.aculta-hero-slogan` em `components/patterns/hero/hero.css`.
- Título de seção: `.aculta-section-title` em `components/patterns/content-section/content-section.css`, com a regra de 575 px.
- Card de projeto: moldura `.aculta-project` (presenter) e filhos em `components/content/project-card/project-card.css`, incluindo a redução de movimento.
- Medição antes/depois: estilo computado idêntico por propriedade em 17 seletores; 14 capturas de tela idênticas byte a byte (1280 e 390 px, páginas `/inicio`, `/institucional`, `/projetos`, `/atividades`, `/noticias`, `/transparencia` e um projeto); foco e movimento reduzido do card idênticos.
- Os seletores de `.card` continuam em `content.css`; não há SDC de card para eles.
- Versão marcada em `aculta420.info.yml` e na asserção do gate de Foundation; sem tag.

## 0.4.0-dev.4 — T2 concluída: rail com cursos reais e foco visível no card — 2026-10-09

Classificação: PATCH da linha 0.4.0 (correção de foco no card do LMS e fechamento da validação em navegador); sem componente stable alterado.

- Rail (DT-T05) validado com quatro cursos reais publicados, temporários e removidos após a medição. Em 1280 px a rolagem por seta moveu 151 px; em 390 px, 310 px; a página não rolou horizontalmente; Tab alcança cada card.
- Foco (DT-T06) medido com foco real (Chromium headless com `Emulation.setFocusEmulationEnabled`). Shell e rail com contorno sólido de 3px. O card do LMS (`lms:course_card`) tinha só a troca de borda para amarelo, cerca de 1,6:1 sobre superfície clara, abaixo dos 3:1 da WCAG 1.4.11. A correção está em `css/components/course-card.css`: anel de 3px com `--aculta-focus-ring` (claro: verde escuro; escuro: amarelo). O seletor espelha o do LMS com maior especificidade, sem editar o contrib.
- Ordem de Tab medida a partir do topo: cabeçalho, depois o rail como parada própria, depois os cards.
- Versão marcada em `aculta420.info.yml` e na asserção do gate de Foundation; sem tag.

## 0.4.0-dev.3 — T2 concluída, rótulos em pt-BR e curso de introdução — 2026-10-09

Classificação: MINOR da linha 0.4.0 (JavaScript do carrossel, catálogo de tradução do tema e conteúdo do LMS); sem componente stable alterado.

- Carrossel da home reativado (`block.block.aculta_home_editorial_highlights`, `status: true`). Validado em navegador: autoplay com movimento normal, parada com movimento reduzido, Enter avança com movimento reduzido, foco visível no botão Próximo.
- Rótulos em inglês corrigidos: catálogo `translations/aculta420.pt-br.po` importado com `--override=none` (12 strings novas). Região do carrossel traduzida no `js/editorial-carousel.js`. Medido no DOM: região "Carrossel", controles "Controles do carrossel", "Slide anterior", "Próximo slide", "Navegação dos slides". Só restam textos mistos em português com a palavra "Login", que são nomes próprios de ação.
- Curso "Introdução ao Antiproibicionismo" (id 1) publicado com 7 lições e 14 atividades. Conteúdo versionado em `scripts/content/courses/introducao-antiproibicionismo.json` e carregado por `scripts/content/load-course-introducao-antiproibicionismo.php` (idempotente, sem apagar nada). Fontes: UNAIDS, Harm Reduction International, MPPR, IPEA, Lei 11.343/2006, nota técnica do MPPR sobre o RE 635.659 e ConJur.
- Rail validado com o curso real: rótulo, card, foco por teclado e largura em 1280 e 390 px. A rolagem com vários cards segue pendente.
- Diretiva de slugs públicos em português em `AGENTS.md` e gate `scripts/validate-public-slugs.php` (linha de base DT-P20, caso do curso em DT-P21).
- LMS em português: 331 strings do módulo `lms` (contrib) que estavam em inglês foram traduzidas em `translations/lms.pt-br.po` e importadas sem sobrescrever as 125 traduções existentes. Verificado pelo serviço de tradução do Drupal em pt-BR (verificar resposta, aguardando correção, retomar treinamento, progresso etc.). Contrib não foi alterado.
- Gates: SDC schemas, design, fixtures, Foundation, shell-contract, domain-presentation e Portal Drupal 11+ — ver o registro de dívidas.

## Validação T2 (sem nova versão), 0.4.0-dev.2 — 2026-10-09

Validação em navegador, sem alteração de código do tema. Versão vigente permanece 0.4.0-dev.2.

- Carrossel da home validado com teclas reais: avanço automático com movimento normal; sem avanço automático com movimento reduzido; Enter avança mesmo com movimento reduzido; contorno de foco no botão Próximo. Space não conclusivo.
- Placement `aculta_home_editorial_highlights` ativada no Runtime só para o teste e restaurada para desativada. Config inalterada.
- Novo achado: rótulos em inglês nos controles e na região do carrossel (DT-T15).
- Rail: ainda validado só com clones; falta curso publicado (DT-T05).

## 0.4.0-dev.2 — Saneamento T0, T1 e T4 — 2026-10-09

Classificação: PATCH de qualidade (gate novo e documentação), dentro da linha 0.4.0. Validado: gates de tema e Portal (ver abaixo).

- Novo gate `scripts/validate-aculta420-sdc-schemas.php`: valida metadados e schema de cada `component.yml`, tipos, enums e defaults, e confirma que cada `include('aculta420:…')` passa só variáveis declaradas. Testado com caso negativo (variável inexistente reprovada).
- Decisão T1: estruturas internas (`aculta-areas`, `aculta-callout`, `aculta-page-intro`, CTAs, notas) permanecem como rich text, registrada em `components.md`.
- T0: `architecture.md`, `design-system.md` e `development.md` descrevem o estado atual, e não a Foundation ou o planejamento.
- Registro de dívidas: DT-T01, DT-T03, DT-T08 e DT-T14 atualizados.
- Gates: SDC schemas PASS (98 checagens); design-foundations PASS; Foundation PASS (309); shell-contract PASS (9); domain-presentation PASS (127); Portal Drupal 11+ PASS (366).
- Não feito nesta fase: T2 (exige view do carrossel ativa no Runtime), T3 (CSS residual), T5 (scripts de migração ainda dependem de IDs do Runtime) e T6 (regressão após upgrade do LMS).

## Versionamento por marcação no código (sem tag), 0.4.0-dev.1 — 2026-10-09

- Política: a versão é assinalada no código (`aculta420.info.yml`, este changelog, roadmap e gate). Tags Git não são criadas sem pedido explícito do responsável. `docs/versioning.md`, `AGENTS.md` e `docs/operations/RELEASES.md` atualizados.
- `aculta420.info.yml` passa a marcar a versão atual, `0.4.0-dev.1`. Asserção do gate de Foundation acompanha.

## Documentação (sem tag), versão vigente 0.3.1 — 2026-10-09
- Limpeza documental: removidos `design-b-qa.md` e `features.md` (histórico no Git, último commit `9c95420`); índices reescritos; referências corrigidas. Regras que sobreviveram ficam em `docs/portal/GUARDRAILS.md` e nos guardrails do tema.

- Revisão documental: registro único de dívidas (`docs/operations/DEBT-REGISTER.md`), roadmap reescrito em saneamento antes de features, sem plano de 1.0.
- Versões obsoletas corrigidas em `README.md`, `docs/README.md` e `docs/features.md`. Regra de mudança apenas documental em `docs/versioning.md`.

## 0.4.0-dev.1 — Rail de cursos — 2026-10-09

Classificação (antes de codar, conforme `docs/versioning.md`): componente `experimental` novo e mudança de configuração da view `courses_catalog` → MINOR, linha 0.4.0, subversão `-dev.1`.

- Novo padrão `aculta420:rail` (experimental): região rolável com `scroll-snap`, sem JavaScript, rotulada pelo título da view, foco por teclado com contorno de token (`--aculta-focus-ring`).
- `views-view-unformatted--courses-catalog.html.twig` envolve as linhas (cards do LMS, sem alteração) no rail.
- Configuração: `views.view.courses_catalog` passa de `grid_responsive` (3 colunas) para estilo de linhas (`default`). Efeito visível: o catálogo vira uma faixa horizontal.
- Validado em Chromium com teclas reais: região e rótulo corretos, rolagem com quatro itens (dois clones de teste, só no DOM), foco por teclado com contorno visível, seta direita rola, sem rolagem horizontal da página em 1280 e 390 px.
- Gates: design-foundations PASS; fixtures 156 e 19 PASS; Foundation PASS (309); shell-contract PASS (9); domain-presentation PASS (127); Portal Drupal 11+ PASS (366); `config:status` só com as exceções já documentadas.
- Não validado: a rolagem com cursos reais, pois o Runtime tem apenas um curso publicado.

## 0.3.1 — Patterns SDC (fase parcial da linha 0.4) — 2026-10-09

Versão decidida pelo responsável para esta fase. Pela regra de `docs/versioning.md`, uma entrega com componentes novos seria MINOR; a exceção está registrada na seção "Decisão de versão" do próprio `docs/versioning.md`. Nenhum componente `stable` teve API alterada.

Mudanças principais, em ordem (SHA na `main`):

- **Padrões SDC experimentais** `content-section`, `hero`, `content-grid` e `carousel` (`d865502`).
- **Campos e placement de seção e hero**: `field_section_title`, `field_section_heading`, `field_section_variant` em `basic`; `field_hero_*` em `page`; placement `aculta_home_mission` separado de "Quem somos" (`db0ac93`).
- **Hook de tema `aculta_section`** declarada pelo `aculta_portal` (`PortalHooks::theme()`) e entregue por `EditorialHooks::entityViewAlter()`; template `aculta-section.html.twig` do tema (`1645359`, `ae94114`).
- **Cabeçalho de projetos por purpose**: dois placements (`aculta_projects_header_home` e `aculta_projects_header_page`) com `aculta_domain_purpose = main`; removido da view (`939eab9`, `7618eba`).
- Títulos de seção usam a barra de título em todas as seções (mudança visual aprovada). Cabeçalho único do Domain Header inalterado.

Validação do estado final (`a286db2`): gates de design, fixtures, Foundation (309 checks), shell-contract, domain-presentation e Portal Drupal 11+ (366) passam; HTTP da home, de `/projetos` e de `cursos` conferido.

Não validado: carrossel em navegador (view da home desativada no Runtime); rail de cursos (não criado).

Pendente para a linha 0.4.0: rail de cursos e estruturas internas em rich text.

## 0.3.0 — Card System v1 — 2026-10-09

- 0.3.0 — Card System v1: novo SDC experimental `aculta420:project-card`, extraído do teaser de projeto com a mesma marcação (`node--project--teaser.html.twig` passa a ser só o presenter). Skin de `lms:course_card` pela `css/components/course-card.css`, com variáveis LMS e botão mapeados para tokens; reduced motion e modo escuro medidos. `editorial-card` sem alteração. Gate de Foundation: discovery e compilação do `project-card` (compilação de SDC agora usa o id do componente) e status `experimental`. Sem product-card, sem variantes e sem abstração de mídia, por falta de consumidor ou duplicação comprovada.

## 0.2.0 — Multidomain Shell Foundations — 2026-10-09

- 0.2-F — Design B QA concluída; linha 0.2.0 fechada. QA em Chromium com teclas reais e medições. Correções: logo branca invisível no header claro (novo token `--aculta-shell-logo-filter`, sem novo asset); anel de foco do header que usava texto invertido (agora `--aculta-shell-domain-text`); sticky do header quebrado pelo `h-100` do wrapper do off-canvas do Bootstrap5 base (override de `content/off-canvas-page-wrapper.html.twig` com `min-vh-100 flex-shrink-0`, incluído na allowlist revisada do gate de Foundation); reduced motion ignorado nos links do shell por especificidade (regra repetida em `navigation.css`). Todos os purposes com shell e tablet verificados; limites em `docs/design-b-qa.md`. Versão do tema: `0.2.0`.
- 0.2-E — Mobile/Sticky Shell: `.aculta-header` passa a `position: sticky` com `top` vindo de `--drupal-displace-offset-top` do Core (respeita o Toolbar), sem compensação no `body` e sem `fixed`; `scroll-padding-top` evita que âncoras e foco fiquem sob o header; controles de shell ganham alvo mínimo de 44px; no mobile o menu expandido rola dentro da viewport. Reaproveita o Collapse do Bootstrap e o `navigation.js` existente. Account dropdown e search trigger não foram criados (não existem no shell; busca é da 0.6.0). Visual QA em navegador fica DEFERRED para 0.2-F.
- 0.2-D — Domain Header: o `<header class="aculta-header">` deixa a superfície verde sólida e passa a usar `--aculta-shell-domain-bg` / `--aculta-shell-domain-text`, com linha inferior `--aculta-border-accent`; navegação, título e slogan herdam esses tokens e o toggle mobile mantém o verde escuro. Mudança visual intencional, prevista em `docs/shell.md` (Domain Header dominante e superfície elevada). Nenhum ramo por purpose ou hostname foi adicionado; visual QA em navegador fica DEFERRED para 0.2-F.
- 0.2-C — Institution Bar: faixa institucional global, discreta e superior ao Domain Header, com marca textual ACULTA (“Associação Cultural Antiproibicionista”, oculta no mobile estreito) e o menu de conta migrado da linha abaixo do header para a faixa (classe `.aculta-utility` mantida). Usa exclusivamente os semantic tokens `--aculta-shell-institution-*` e `--aculta-shell-border`; ordem do DOM: Institution Bar → `<header class="aculta-header">`. A marca não é link: a URL institucional de MAIN depende de dado a ser preparado pelo Portal. `regions.*` permanece `NULL` (sem consumidor no Portal); nenhuma regra de Domain, hostname ou purpose foi adicionada ao tema. Visual QA em navegador fica DEFERRED para 0.2-F. Versão `info.yml` mantida em 0.1.0 até o fechamento da linha 0.2.0.
- 0.2-B.3 conecta `domain_presentation.identity` ao shell sem redesign e deriva somente o fallback realmente consumido (`label` + `home_url`). `page.header` é sempre preservado e o fallback só entra sem `system_branding_block`; `purpose` não é emitido no DOM e `logo_alt` não é repurposed em links textuais. Nenhuma regra Domain/hostname/Portal service foi movida para o tema.
- Adiciona o gate Runtime read-only `validate-aculta420-shell-contract.php` para contrato completo/parcial/ausente, ausência de object leakage e independência funcional do tema.
- 0.2-B.4 extrai `Aculta420ShellContractAnalyzer` e adiciona fixtures independentes do Drupal para branches por purpose em Twig/PHP/JS, qualquer namespace/service Portal, APIs Domain, hosts hardcoded, hostname/service locator, `regions.*` prematuras e regressões de fallback; comentários PHP não contam como dependência real e o gate Runtime reutiliza o mesmo analyzer.
- Corrige o gate B.3 para carregar explicitamente a classe de hook ao testar seu comportamento no Drush e distinguir um fallback explicitamente `NULL` de uma chave ausente; ajusta o docblock para não disparar o verificador estático da Foundation.

- Revisão Codex da PR #83: a análise de literais CSS agora ignora comentários, reconhece funções modernas de cor e nomes de cores em propriedades color-bearing. Fixtures cobrem comentário, `oklch()`, `lab()` e `red`; nenhum CSS visual foi alterado.
- Revisão Codex da PR #83: aplica a checagem de color-mode persistence também a `<script>` inline Twig, detecta mutações de classe com getters de modo, preserva URLs em regex literals ao remover comentários, rejeita statements CSS como `@import` em `tokens.css`, ignora texto comum contendo “dark theme” nas condições e inspeciona `<style>` inline Twig. Fixtures positivas e negativas adicionadas.
- Revisão Codex da PR #83: preserva o predicado Twig em qualquer posição ao redor de ternários, ignora regex literals em corpos de `switch`, resolve `VAR()` sem distinção de caixa, valida o RGB da superfície ACULTA, examina scripts inline Twig e rejeita regras arbitrárias em `tokens.css`; fixtures adicionadas sem alterações visuais.
- Revisão Codex da PR #83: preserva ternários JavaScript com arms objeto, mantém predicados Twig externos, trata case PHP encerrado por `;`, ignora `?.`/`??` como níveis ternários, reconhece getter `getColorScheme()`, writes compostos de `className`, todas as classes de modo em `classList`, regex literals em condições e seletores de atributo parciais; exige a união dos tokens ACULTA/Bootstrap nos dois modos. Fixtures cobrem cada regressão sem alterar CSS visual.
- Revisão adicional da PR #83: valida posição do slash-alpha em `rgb()`, percorre predicados ternários Twig/JavaScript aninhados sem analisar resultados e detecta atribuições diretas de classes de modo por `className`.
- Revisão suplementar da PR #83: permite formas RGB/hex equivalentes para superfícies aprovadas, isola labels em switches mistos/aninhados, cobre chamadas opcionais de `setAttribute`, ternários multiline e valida sintaxe RGB contra mistura inválida de separadores.
- Revisão final adicional da PR #83: aplica comparação numérica também às superfícies dark, trata switches PHP aninhados nos dois estilos, reconhece ternários JavaScript multiline e chamadas opcionais `setAttribute?.()`, e rejeita mistura de separadores legacy/modernos em cores CSS.
- Revisão adicional da PR #83: cobre ternários Twig parenthesizados, `endswitch` aninhado, `if` JavaScript com parênteses balanceados, `setAttribute()` dinâmico e operadores de atributo CSS; valida a borda Bootstrap translúcida e aceita representações numericamente equivalentes de cores.
- Revisão final da PR #83: restringe análise de ternários Twig ao predicado, de PHP `match` às condições dos arms, reconhece flags de atributo CSS `i`/`s` e valida fontes semânticas dos tokens base Bootstrap em cada modo; fixtures cobrem falsos positivos e alteração coordenada de cor/RGB.
- Fortalece o gate dark token-only: detecta seletores/branches alternativos, resolve aliases e cascata de tokens, protege superfícies carvão/grafite e rejeita duplicatas, ciclos e valores sem contraste; adiciona fixtures negativas isoladas.
- Hardening ACULTA420 0.2-A.2: cobre regras de modo dentro de `tokens.css`, arms `switch/case`, ternários Twig em statements, `dataset`, resolução de todos os tokens e mappings Bootstrap por modo, além de raízes Windows; adiciona fixtures positivas/negativas sem alterar valores visuais.
- Revisão automática da PR #83: compõe alpha nos cálculos de contraste, valida o contrato completo de cores/RGB, detecta writes dinâmicos de `dataset`, switches alternativos PHP e switches JavaScript com chamadas, seletores de modo em `:where()`/`:is()`, e ignora comentários PHP nas condições; fixtures agora cobrem esses casos.
- Segunda revisão automática da PR #83: rejeita alpha malformado, cobre atribuições compostas de `dataset`, analisa somente labels de `switch`, remove comentários JavaScript inline com scanner sensível a strings, valida os pares RGB Bootstrap e reconhece `colorScheme`; suíte de fixtures ampliada.
- 0.2-A.1 consolida o contrato dark com superfícies carvão/grafite quentes, texto creme, acentos de marca e verificação WCAG; light permanece inalterado.
- Color mode é token-only: estrutura, layout, tipografia e comportamento são compartilhados; o gate impede branches por modo fora de `tokens.css`.
- Documenta variante de asset de logo apenas como exceção futura de legibilidade, sem mudança de DOM ou dimensões.
- Inicia 0.2-A Semantic Foundations: semantic surfaces/text/borders/interactions, shell tokens, light/dark-ready values e superfície de página Design B; não implementa o shell, Institution Bar ou Domain Header.
- Bootstrap body mapping e componentes globais passam a consumir semantic tokens onde a função visual é clara; primitivas da paleta permanecem em `tokens.css`.
- Adiciona validator estático para valores por color mode, fronteiras do tema, CSS literals e ausência prematura de UI/script de color mode.
- Formaliza autenticação/anti-bot como fronteira de apresentação: ACULTA420 estiliza formulários e markup neutro do Portal sem depender de Social Auth, OAuth, CAPTCHA ou Turnstile.
- CSS de botões usa o contrato semântico `.aculta-auth-provider`, fornecido pelo Portal, em vez de classes específicas de provider.
- Gate da Foundation impede referências técnicas de autenticação e anti-bot no runtime do tema e dependências correspondentes em info/libraries.

## 0.1.0 — Foundation — 2026-10-07

### Identidade técnica

- novo nome: **ACULTA420**;
- novo machine name: `aculta420`;
- novo diretório funcional: `web/themes/custom/aculta420`;
- provider legado de compatibilidade removido antes do fechamento da Foundation; o único tema custom público é `aculta420`;
- provider SDC: `aculta420:*`;
- libraries: `aculta420/*`;
- settings: `aculta420.settings`;
- hooks PHP: `aculta420_preprocess_*`.

### Configuração

- a configuração sincronizada aponta exclusivamente para `aculta420`; compatibilidade com provider histórico não permanece no runtime final;
- `core.extension` passa a instalar `aculta420`;
- `system.theme` passa a usar `aculta420` como default;
- block placements passam a depender do novo tema;
- settings/favicons apontam para o novo diretório;
- IDs de conteúdo/config `aculta_*` são preservados quando não representam o
  provider do tema.

### Design system

- Bootstrap5 permanece infraestrutura;
- Core SDC permanece base de componentização;
- `enforce_prop_schemas: true` ativado;
- `editorial-card` organizado em `components/content/`;
- primeiro SDC stable passa a ser `aculta420:editorial-card`;
- CSS do card continua auto-carregado pelo SDC;
- VVJB permanece engine do carousel editorial.

### Documentação

A documentação histórica de refatoração foi consolidada e substituída por um
conjunto normativo:

- architecture;
- features;
- design-system;
- components;
- development;
- accessibility;
- decisions;
- roadmap;
- versioning;

### Validação Runtime R0.1

- O gate compila o Twig do SDC pelo ID canônico `aculta420:editorial-card`, resolvido pelo loader de componentes do Drupal.
- shell multidomínio planejado, com Institution Bar global e Domain Header por purpose;
- D-012 e regras anti-regressão para impedir hostname/Domain entity no tema;
- instruções de agentes consolidam `aculta420` como único provider e proíbem aliases/shims legados.

### Saneamento pós-rename

- relatório de requisitos do Portal passa a reconhecer `aculta420` como provider público;
- dados institucionais saem do tema e passam para `aculta_portal.settings`;
- validadores ativos passam a inspecionar o contrato atual do provider `aculta420`;
- instruções de agentes deixam de apontar para documentação Portal removida;
- gate de autenticação reflete o baseline atual sem `user_registrationpassword`.
### Limpeza da Foundation

- remove o diretório/provider de compatibilidade temporária;
- remove o runbook transitório de migração da documentação normativa;
- estabelece como gate da 0.1.0 a ausência de provider legado no código e na configuração;
- histórico da transição permanece no Git/ADR, sem virar contrato de runtime.

### Ownership de CSS

- remove o catch-all `css/style.css`;
- footer, institucional/participação, formulários e Conta passam a ter ownership explícito;
- o validador percorre a árvore CSS real em vez de depender de um arquivo agregado obsoleto;
- regra anti-regressão impede recriação de CSS residual genérico.

### Runtime mínimo

- remove override de ícone RSS sem display feed público consumidor;
- remove `css/responsive.css` e devolve media queries aos componentes donos;
- centraliza duração rápida/easing e sombra de hover em tokens;
- remove hook de login duplicado no Portal;
- remove exports web sem consumidor; preserva originais de branding e os assets efetivamente usados;
- alinha defaults de fresh install de logo/favicon ao config sync;
- hooks do tema usam OOP/DI em `src/Hook/ThemeHooks.php`; o arquivo procedural `.theme` deixa de existir.

- remove caminho morto de `system_powered_by_block`/`aculta_site_credit`, sem placement configurado.

- move CSS do carrossel editorial para a library contextual já anexada pela View VVJB;
- registra allowlist dos sete overrides Twig com delta comprovado.

### Gate da Foundation

- cria `scripts/validate-aculta420-foundation.php` como gate runtime exclusivo do tema;
- separa validação ACULTA420 de Portal/Commerce/security;
- valida OOP hooks, provider/config, libraries/assets, Twig/YAML, SDC e invariantes de CSS;
- valida consistência de plugin, settings e dependências por UUID nos block placements ativos e sincronizados;
- corrige documentação normativa do Portal para o provider `aculta420`.

### Base herdada

0.1.0 reaproveita a base madura do antigo tema `aculta`:

- tokens;
- CSS/JS organizados por responsabilidade;
- contextual asset loading;
- cleanup Twig;
- boundary Portal/theme;
- acessibilidade/progressive enhancement.

O histórico detalhado anterior permanece no Git; não é especificação corrente.
