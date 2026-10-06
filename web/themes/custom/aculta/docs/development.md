# Desenvolvimento do tema

## Princípio

Refatorar apresentação sem alterar comportamento da aplicação ou identidade visual.

```text
Drupal/contrib = funcionalidade
aculta_portal  = integração/orquestração
aculta         = apresentação
```

## Sem build tooling por padrão

A preferência do projeto é:

- CSS nativo;
- Drupal Libraries;
- JavaScript nativo;
- Drupal behaviors;
- `once()`.

Não introduzir Node, npm, Webpack, Vite, Sass ou PostCSS sem benefício técnico concreto, demonstrável e aprovado.

Simplicidade é uma decisão arquitetural.

## Ambientes

O servidor de desenvolvimento/homelab usa atualmente:

- Debian;
- Apache;
- PHP-FPM;
- SQLite;
- aliases `*.aculta.toca.net.br`;
- noindex.

Produção Hostinger usa:

- Apache;
- PHP;
- MariaDB;
- hosts `*.aculta.org`.

Apache em ambos os ambientes não significa configuração idêntica. O tema não deve depender de detalhe exclusivo de VirtualHost, módulo ou configuração local.

## Workflow de refatoração

Mudanças devem ser pequenas, testáveis e reversíveis.

Sequência planejada:

1. documentação e inventário;
2. tokens/base/layout;
3. componentização CSS;
4. separação JavaScript/libraries;
5. cleanup Twig/preprocess e fronteiras Portal/tema;
6. SDC piloto apenas se justificado.

Não misturar, no mesmo passo, reorganização de arquivos com otimização agressiva de carregamento.

## CSS

Estrutura CSS atual após o G2:

```text
css/
├── tokens.css
├── base.css
├── layout.css
├── drupal-bootstrap.css
├── responsive.css
├── components/
│   ├── header.css
│   ├── navigation.css
│   ├── breadcrumb.css
│   ├── content.css
│   ├── buttons.css
│   ├── footer.css
│   ├── institutional.css
│   ├── editorial-carousel.css
│   └── auth.css
└── style.css
```

A library global deve preservar a ordem documentada em `aculta.libraries.yml`. Após o G2, `style.css` contém somente o trecho ainda misto de footer-layout, formulários, Conta/segurança e participação; ele só deve ser reduzido novamente quando houver fronteiras contíguas que não reordenem a cascade.

Antes de remover ou mover regra:

- verificar Twig;
- Views/configuração Drupal;
- classes geradas por módulos;
- estados Bootstrap;
- páginas especializadas.

Classes podem existir apenas em configuração e não aparecer em PHP/Twig.

Não minificar fontes no Git; agregação de produção pertence ao Drupal/infra.

## JavaScript

Estrutura atual após o Commit D:

```text
js/
├── navigation.js
└── editorial-carousel.js
```

`navigation.js` permanece em `aculta/global`. Após o G3, `editorial-carousel.js` é carregado apenas pela library `aculta/editorial-carousel`, anexada no template VVJB da View `home_editorial_highlights`.

Separar responsabilidade não autoriza alterar selectors, IDs de `once()`, eventos ou APIs públicas de Bootstrap/VVJB. Libraries contextuais devem ser anexadas pelo componente/theme hook real, não por comparação de URL em JavaScript.

## PHP/Twig

Não mover código apenas para eliminar `\Drupal::service()` cosmeticamente.

Primeiro classificar a lógica:

- apresentação legítima do tema;
- integração que pertence ao `aculta_portal`;
- funcionalidade que pertence a Core/contrib.

Twig não recebe regra de negócio.

### Breadcrumb

Após o Commit E:

- `aculta_portal` decide purpose, visibilidade, raiz, hierarquia, cache metadata e título atual;
- o preprocess do tema apenas adapta `currentTitle()` para a variável Twig;
- o Twig renderiza somente markup e semântica acessível.

Não voltar a duplicar arrays de purpose/rotas ou resolução de título em `aculta.theme`.

## Single Directory Components

O projeto usa SDC apenas quando houver benefício concreto. Drupal 11 já fornece SDC no Core; não adicionar módulo contrib para essa capacidade.

Piloto atual:

```text
components/
└── editorial-card/
    ├── editorial-card.component.yml
    ├── editorial-card.twig
    └── README.md
```

Regras:

- manter integração Drupal específica no presenter quando isso preserva attributes/contexto;
- usar slots para renderables/markup e props apenas para dados estruturados;
- preferir `include(..., with_context = false)` para evitar dependência implícita de contexto;
- não mover CSS/JS para o diretório do SDC no mesmo commit que cria o contrato, salvo quando a mudança de attachment for objetivo explícito e testado;
- não converter templates em massa.

## Testes mínimos por etapa

Quando houver mudança funcional/visual, validar:

- PHP lint;
- Twig sanity;
- cache rebuild no runtime;
- hosts MAIN, ACCOUNT, SUPPORT, COLETIVO420, WIKI420, SHOP e COURSES;
- menu desktop/mobile;
- teclado/foco;
- home institucional;
- página editorial;
- Wiki420;
- curso;
- login;
- Minha Conta;
- apoio.

Quando possível, comparar visualmente before/after.

Commits apenas documentais não exigem rebuild de Drupal, mas devem validar links, paths e consistência com o código atual.

## Documentação

Qualquer mudança estrutural no tema deve atualizar `README.md` e o documento correspondente em `docs/`.

Se a mudança altera a fronteira tema/Portal, atualizar também `web/modules/custom/aculta_portal/README.md` e o ADR correspondente.

Documentação deve descrever o estado atual, não um “futuro” já implementado.
