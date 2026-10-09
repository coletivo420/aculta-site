# QA Design B — ACULTA420 0.2-F

Status: **concluída. Fecha a linha 0.2.0.** Os critérios do roadmap foram medidos
em navegador real. Os limites conhecidos estão listados no fim.

## Ambiente

- Homelab `aculta.toca.net.br` e os purposes `*.aculta.toca.net.br` (Apache,
  PHP-FPM, Drupal 11.4.8, tema `aculta420`). Os hosts são liberados pelo
  `trusted_host_patterns` do `settings.local.php`, fora do Git.
- **Chromium 154** (headless, protocolo DevTools). Teclas são enviadas pelo
  protocolo (`Input.dispatchKeyEvent`), então o foco por teclado é real.
  `prefers-reduced-motion` e `prefers-color-scheme` são emulados pelo protocolo.
- Números vêm de `getBoundingClientRect` / `getComputedStyle` medidos no navegador,
  não de inspeção visual. Capturas ficam fora do repositório.

## Purposes (light, desktop 1280×900 e mobile 390×844)

| Purpose | Host | HTTP | Shell presente | Observação |
| --- | --- | --- | --- | --- |
| MAIN | aculta | 200 | sim | — |
| Apoio | apoio | 200 | sim | — |
| Coletivo420 | coletivo420 | 200 | sim | — |
| Cursos | cursos | 200 | sim | — |
| Wiki420 | wiki420 | 200 | sim | — |
| Conta | conta | 403 | sim | “Access denied” na raiz, esperado para visitante |
| Loja (SHOP) | loja | 404 | não | 404 da raiz, sem shell; já documentado em 0.2-B.2 |

Em todos os purposes com shell: sem rolagem horizontal no desktop e no mobile.

## Verificado (PASS)

| Critério | Medição |
| --- | --- |
| Sticky desktop | rolagem de 1184px: header `top: 0`; faixa institucional fora da tela |
| Sticky desktop dark | rolagem de 1151px: header `top: 0`, `data-bs-theme="dark"` |
| Sticky mobile | rolagem de 855px: header `top: 0` |
| Foco por teclado | Tab real percorre skip link → Entrar → logo → menu; cada elemento tem `:focus-visible` e contorno sólido de 3px na cor `--aculta-shell-domain-text` |
| Menu mobile por teclado | clique abre (`aria-expanded=true`); Tab entra no menu; **Escape fecha e devolve o foco ao botão** |
| Alvos de toque | botão do menu 53px de altura; links do menu com altura mínima de 44px (real: 47px) |
| Painel do menu mobile | 471px, `max-height: 764px`, `overflow-y: auto` |
| Reduced motion | com `prefers-reduced-motion: reduce`, transição dos links `0s` (antes da correção: `0.18s`) |
| Títulos/rótulos longos | um rótulo de 70 caracteres quebra a navegação em duas linhas (header 156px) sem rolagem horizontal, em 1280 e 1100px |
| Menu de conta com muitos itens | 8 itens extras: faixa cresce e quebra linha (163px no mobile, 119px em 900px), sem rolagem horizontal |
| Estado vazio (sem menu de conta) | faixa com 37px, sem quebra de layout |
| Tablet | 900 e 1024px: botão do menu visível, sem rolagem horizontal |
| Contraste | gate de design: texto e superfície de shell em light e dark; texto de domínio no escuro 14.50:1 |
| Ordem de tab | igual à ordem do DOM |

Gates de Runtime e de fixtures: PASS (Foundation 276, shell-contract 9,
domain-presentation 127, fixtures design-foundations 156, fixtures shell-contract 19).

## Regressões encontradas e corrigidas (0.2-F)

1. **Logo invisível no header claro** (0.2-D). Logo branca sobre header claro.
   Correção: token `--aculta-shell-logo-filter` (`brightness(0)` no claro, `none`
   no escuro). Tamanho, caixa e DOM iguais.
2. **Anel de foco do header branco** (0.2-D). `.aculta-header :focus-visible` usava
   `--aculta-text-inverse`. Correção: `--aculta-shell-domain-text`.
3. **Sticky quebrado** (0.2-E). O `h-100` do wrapper do off-canvas (Bootstrap5 base)
   prendia o header à primeira tela (medido: `top: -438` após 1199px). Correção:
   override de `content/off-canvas-page-wrapper.html.twig` com `min-vh-100
   flex-shrink-0`, mantendo `data-off-canvas-main-canvas`. Contrib não alterado.
4. **Reduced motion ignorado nos links do shell** (0.2-E/0.2-D). A regra de reset em
   `drupal-bootstrap.css` tem especificidade menor que a transição da navegação.
   Correção: regra repetida em `navigation.css` com a especificidade adequada.

## Limites conhecidos (fora do escopo da 0.2.0)

- **Modo escuro selecionado pelo usuário:** o tema ainda não gera `data-bs-theme`;
  a seleção light/dark/auto é da 0.5. Aqui o atributo foi aplicado pelo protocolo para
  validar os tokens.
- **Conta autenticada real:** não foi feito login com credenciais. O caso de muitos
  itens no menu de conta foi simulado no DOM.
- **Botões do hero:** “Nossos projetos” tem largura diferente de “Conheça a associação”
  no mobile. Vem do CSS de botões do conteúdo, não do shell. Pendência para a 0.8 ou
  para uma correção de conteúdo.
- **Dropdowns de conta e busca:** não existem no shell. A busca é da 0.6.0.

## Decisão

Linha 0.2.0 fechada: 0.2-A a 0.2-F implementadas em branches empilhadas, gates e
medições acima aprovados. A tag `aculta420-theme-v0.2.0` deve ser criada depois que a
cadeia de PRs (#92 → #93 → #94 → #95) for integrada na `main`.
