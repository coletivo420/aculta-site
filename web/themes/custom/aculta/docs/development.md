# Desenvolvimento do tema

## Princípio

Evoluir o **ACULTA Bootstrap Component Design System** sem alterar regras de negócio, fontes de verdade Drupal/contrib/Portal ou identidade visual aprovada.

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

## Workflow

Mudanças devem ser pequenas, testáveis e reversíveis. A refatoração A–G4 está encerrada; a macrofase ativa é a **Fase H — ACULTA Bootstrap Component Design System**.

A arquitetura, camadas, critérios de maturidade e roadmap H1–H10 estão em [component-design-system.md](component-design-system.md).

Não misturar reorganização estrutural, redesign e otimização agressiva de carregamento no mesmo passo.

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

### Overrides Twig

Herdar Bootstrap5/Core/contrib por padrão. Não copiar templates apenas para mantê-los idênticos ao upstream.

Antes de adicionar ou manter override:

- comparar com a versão efetivamente instalada do base theme/contrib;
- documentar o delta ACULTA;
- preservar attributes, cache/access e hooks esperados;
- remover overrides que apenas mascaram upstream sem benefício.

No G4, `form/input.html.twig` foi removido para voltar a herdar o template Bootstrap5 4.0.8.


### Breadcrumb

Após o Commit E:

- `aculta_portal` decide purpose, visibilidade, raiz, hierarquia, cache metadata e título atual;
- o preprocess do tema apenas adapta `currentTitle()` para a variável Twig;
- o Twig renderiza somente markup e semântica acessível.

Não voltar a duplicar arrays de purpose/rotas ou resolução de título em `aculta.theme`.

## Single Directory Components

O projeto usa SDC apenas quando houver fronteira visual e benefício concreto. Drupal 11 já fornece SDC estável no Core; não adicionar módulo contrib para essa capacidade.

Componente-modelo estável após H2:

```text
components/
└── editorial-card/
    ├── editorial-card.component.yml
    ├── editorial-card.twig
    ├── editorial-card.css
    └── README.md
```

Regras:

- manter integração Drupal específica no presenter e preservar `attributes`, `title_prefix`, `title_suffix`, access/cache metadata quando aplicável;
- usar slots para renderables/markup e props apenas para dados estruturados;
- preferir `include(..., with_context = false)` para evitar dependência implícita de contexto;
- não mover CSS/JS para o diretório do SDC no mesmo commit que cria o contrato, salvo quando a mudança de attachment for objetivo explícito e testado;
- não converter templates em massa;
- SDC não consulta serviços, storage, banco, Node/Commerce/LMS diretamente;
- CSS/JS exclusivo pode ser co-localizado como `<component>.css`/`<component>.js` para carregamento automático do SDC.

### Ownership de CSS no H2

O H2 move somente regras exclusivas de `aculta:editorial-card` para o SDC. Seletores compartilhados continuam globais; regras de VVJB permanecem no CSS do pattern de carousel.

Não duplicar de volta regras do card em `content.css`, `breadcrumb.css` ou `editorial-carousel.css`.

### Regra H3 para primitives

Primitive não implica SDC. Se o contrato precisa estilizar markup produzido por Core/Bootstrap/Form API, CSS global pode ser a implementação correta.

O primeiro primitive SDC aprovado é `category-label`. Button permanece CSS/Bootstrap; section-heading e media aguardam H5/H4 respectivamente.

Antes de adicionar novos SDCs, considerar ativar `enforce_prop_schemas: true` em commit próprio e validar todos os componentes existentes.

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
