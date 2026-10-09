# QA Design B — ACULTA420 0.2-F

Status: **parcial. A linha 0.2.0 NÃO está fechada.** Parte dos critérios da 0.2-F
depende de ambiente ou de estados que não foram exercitados (ver “Não verificado”).

## Ambiente

- Homelab `aculta.toca.net.br` (Apache + PHP-FPM, Drupal 11.4.8, tema `aculta420`).
- Firefox 153 ESR em modo headless, com WebDriver BiDi, para medições e capturas.
  Os números abaixo vêm de `getBoundingClientRect`/`getComputedStyle` medidos no
  navegador, não de inspeção visual.
- Apenas o MAIN foi exercitado. Os demais hosts (`apoio`, `coletivo420`, `conta`,
  `cursos`, `loja`, `wiki420`) não têm TLS válido neste vhost; não foram validados.

## Verificado (PASS)

| Critério | Medição |
| --- | --- |
| Sticky desktop 1280×900 | rolagem de 1184px: header `top: 0`; faixa institucional `top: -1184` (saiu) |
| Sticky desktop dark | rolagem de 1200px: header `top: 0` com `data-bs-theme="dark"` |
| Sticky mobile 390×844 | rolagem de 855px: header `top: 0` |
| Alvo de toque — toggle | 91×53px |
| Alvo de toque — links do menu | `min-height: 44px`, altura real 47px |
| Menu mobile aberto | `aria-expanded="true"`; painel 471px; `max-height: 764px`; `overflow-y: auto` |
| Rolagem horizontal | `scrollWidth` ≤ largura da viewport nos dois tamanhos |
| Contraste dos tokens de shell | pelo gate de design (ver `validate-aculta420-design-foundations.php`); texto de domínio sobre superfície de domínio: 14.50:1 no escuro |
| Ordem de tab | Skip link → Entrar → logo → menu principal, na ordem do DOM |

Gates de Runtime e de fixtures: PASS (Foundation 276, shell-contract 9,
domain-presentation 127, fixtures design-foundations 156, fixtures shell-contract 19).

## Regressões encontradas e corrigidas nesta fase

1. **Logo invisível no header claro** (regressão da 0.2-D). A logo é branca e o
   header passou a ser claro. Correção: token semântico `--aculta-shell-logo-filter`
   (`brightness(0)` no claro, `none` no escuro). Tamanho, caixa e DOM não mudam.
2. **Anel de foco do header em branco** (regressão da 0.2-D). `.aculta-header
   :focus-visible` ainda usava `--aculta-text-inverse`. Correção: `--aculta-shell-domain-text`.
3. **Sticky quebrado** (regressão da 0.2-E). O Bootstrap5 base aplica `h-100` ao
   wrapper do off-canvas; a altura de 100vh prendia o header à primeira tela
   (medido: `top: -438` após rolar 1199px). Correção: override do template
   `content/off-canvas-page-wrapper.html.twig` com `min-vh-100 flex-shrink-0`, mantendo
   `data-off-canvas-main-canvas`. O contrib não foi alterado.

## Não verificado (DEFERRED)

- **Foco por teclado visível.** A janela headless não recebe foco: Tab não move
  `document.activeElement` e `:focus-visible` não casa, mesmo com
  `focus({ focusVisible: true })`. A regra existe e usa tokens, mas o contorno não foi
  observado. Verificar em navegador com foco real.
- **Reduced motion.** As regras `prefers-reduced-motion` existem; não foram exercitadas.
- **Estados longos e vazios.** Não testados com títulos longos nem com conta autenticada
  (menu de conta com itens).
- **Demais purposes e modos.** Não validados fora do MAIN (ver Ambiente).
- **Botões do hero.** “Nossos projetos” tem largura diferente de “Conheça a associação”
  no mobile. Vem do CSS de botões do conteúdo, não do shell; registrado como pendência.
- **Modo escuro real.** O tema ainda não gera `data-bs-theme` (0.5). Para o QA, o
  atributo foi aplicado pela API do navegador; a seleção real do modo é da 0.5.

## Decisão

A 0.2-F não fecha a linha 0.2.0. Para fechar, falta exercitar foco por teclado,
reduced motion, estados longos/vazios e os demais purposes em um ambiente com TLS válido.
