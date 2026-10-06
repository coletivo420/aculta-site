# ACULTA Bootstrap Component Design System

## Status

A refatoração estrutural A–G4 está encerrada. A partir da Fase H, o tema `aculta` evolui explicitamente como o **ACULTA Bootstrap Component Design System**.

Baseline desta decisão:

- Drupal Core 11.4.8;
- base theme `bootstrap5` 4.0.8;
- SDC estável no Drupal Core;
- Bootstrap permanece infraestrutura estrutural/comportamental;
- ACULTA é a linguagem visual e de composição.

## Arquitetura

```text
Drupal Core + contrib
  dados, entidades, Views, Form API,
  Commerce, LMS/Group, menus, routing
                |
                v
          aculta_portal
  regras de negócio, domínio, presenters
                |
                v
 ACULTA Component Design System
  Foundations -> Primitives -> Components
      -> Patterns -> Shell
                |
                v
          Bootstrap 5
 estrutura, utilities e behaviors
```

A direção de dependência é sempre de cima para baixo. Um SDC nunca passa a ser dono de Node, Commerce, LMS, Domain ou persistência apenas porque apresenta esses dados.

## Camadas

### Foundations

Fonte de verdade global para:

- tokens;
- cor;
- tipografia;
- spacing;
- radius/borders;
- motion;
- foco e acessibilidade;
- integração de tokens com `--bs-*`.

Arquivos globais como `tokens.css`, `base.css` e `layout.css` permanecem apropriados aqui.

### Primitives

Peças visuais pequenas e reutilizáveis com contrato claro, por exemplo:

- button;
- badge/category;
- icon;
- heading;
- media.

Não transformar toda classe CSS em SDC. Um primitive só deve existir quando reduz duplicação ou cria um contrato visual realmente reutilizável.

### Components

Unidades visuais compostas e independentes, por exemplo:

- card;
- editorial-card;
- project-card;
- course-card;
- product-card;
- breadcrumb;
- pagination.

Componentes recebem dados preparados; não descobrem entidades nem regras de negócio.

### Patterns

Composição de componentes, sem criar uma nova fonte de dados:

- hero;
- carousel/rail;
- content-grid;
- content-section;
- search-panel.

Um pattern pode coordenar layout e acessibilidade, mas engines existentes continuam pertencendo a Bootstrap/contrib. VVJB não deve ser reimplementado apenas para uniformizar tecnologia.

### Shell

Estrutura transversal da aplicação:

- header;
- navigation;
- account utility;
- footer.

`page.html.twig` continua compositor do shell Drupal. Não criar um SDC monolítico da página inteira.

## Contrato presenter -> SDC

O presenter Drupal conhece a entidade e preserva o contrato do Theme API. O SDC conhece apenas sua API visual.

```text
Node -----------------+
Commerce Product -----+
Drupal LMS/Group -----+--> presenter --> SDC --> Bootstrap + ACULTA tokens
View -----------------+
Block ----------------+
```

Regras obrigatórias:

- presenter preserva `attributes`, `title_prefix`, `title_suffix`, acesso e cache metadata quando aplicável;
- presenter extrai/encaminha dados do Drupal;
- SDC não acessa `node.field_*`, serviços, storage, banco ou regras de domínio;
- usar slots para renderables/markup;
- usar props somente para dados estruturados simples;
- preferir `include('aculta:componente', {...}, with_context = false)`;
- não passar contexto Twig implícito para o componente.

## Bootstrap como infraestrutura

Bootstrap5 é a única implementação Bootstrap do tema.

Bootstrap pode fornecer:

- grid;
- utilities;
- estados;
- componentes estruturais;
- Collapse e outros behaviors;
- custom properties e APIs públicas.

ACULTA fornece:

- identidade;
- tokens;
- decisões visuais;
- contratos de componentes;
- composição;
- acessibilidade complementar.

Não duplicar Bootstrap, não reimplementar behaviors funcionais e não adicionar uma segunda suíte de design system disputando responsabilidade.

## Assets e carregamento

Política alvo:

- foundations e integrações realmente transversais ficam em `aculta/global`;
- CSS/JS exclusivo de SDC fica no diretório do componente;
- arquivos `<component>.css` e `<component>.js` usam o carregamento automático do SDC;
- assets de patterns que envolvem engine contrib podem usar library contextual;
- nenhum componente deve exigir JavaScript global apenas para existir;
- progressive enhancement continua obrigatório.

A existência de um SDC não exige que todo seu styling seja imediatamente co-localizado. A migração só ocorre quando a propriedade das regras estiver comprovada.

## Acessibilidade

Todo componente novo deve prever, conforme aplicável:

- teclado;
- `:focus-visible`;
- landmarks/headings;
- labels e accessible names;
- `aria-current`/estado;
- reduced motion;
- títulos longos;
- conteúdo vazio;
- mobile;
- contraste WCAG AA.

Acessibilidade funcional do Core/contrib não deve ser substituída pelo tema.

## Maturidade de componentes

`experimental` é usado enquanto contrato, markup ou ownership de assets ainda estão sendo validados.

Um SDC pode passar a `stable` quando:

- contrato de slots/props está documentado;
- não conhece storage/entidade;
- presenter preserva Theme API/cache/access;
- CSS/JS exclusivos têm ownership claro;
- estados de acessibilidade relevantes foram validados;
- não depende de contexto Twig implícito;
- possui referência documental suficiente para humanos e agentes de IA.

## Primeiro componente-modelo

`aculta:editorial-card` é o primeiro SDC `stable` do sistema.

Ele estabelece o padrão:

- presenter Drupal preserva Theme API e adiciona o wrapper de integração;
- SDC recebe somente slots renderáveis;
- CSS exclusivo vive em `components/editorial-card/editorial-card.css`;
- Drupal anexa o CSS automaticamente quando o componente é usado;
- regras do VVJB permanecem fora do card;
- nenhum JavaScript é necessário para o card.

Futuros cards devem copiar o contrato arquitetural, não necessariamente o mesmo markup.

## Anti-regressão para agentes e humanos

Não:

- converter Twig em massa para SDC;
- mover regra de negócio ao tema;
- consultar banco/storage a partir de componente;
- acoplar SDC a `node.field_x`, Commerce ou LMS;
- reimplementar Bootstrap/VVJB;
- criar timers/estado paralelo para engines contrib;
- colocar assets específicos em `aculta/global` por conveniência;
- adicionar UI Suite/segundo design system sem decisão arquitetural explícita;
- mover CSS apenas para atingir uma estrutura de diretórios ideal.

Sempre:

- atualizar documentação na mesma mudança;
- manter commits pequenos e reversíveis;
- preservar fonte de verdade Drupal/contrib/Portal;
- validar desktop/mobile/teclado/reduced motion quando houver mudança visual;
- registrar por que um componente existe e quem é dono de seus dados.

## Referências upstream

- Drupal SDC — criação e convenção de assets: https://www.drupal.org/docs/develop/theming-drupal/using-single-directory-components/creating-a-single-directory-component
- Drupal SDC — visão geral e library automática: https://www.drupal.org/docs/develop/theming-drupal/using-single-directory-components/about-single-directory-components
- Bootstrap5 base theme: https://www.drupal.org/project/bootstrap5

Essas referências explicam mecanismo upstream; os contratos ACULTA deste documento continuam sendo a regra do projeto.

## Roadmap da Fase H

| Fase | Objetivo | Estado |
| --- | --- | --- |
| H1 | formalizar o Component Design System | concluído |
| H2 | tornar `editorial-card` o primeiro SDC completo | concluído |
| H3 | primitives reutilizáveis | planejado |
| H4 | família de cards | planejado |
| H5 | patterns compostos, incluindo carousel/rail | planejado |
| H6 | revisão seletiva do shell | planejado |
| H7 | reduzir assets globais conforme ownership real | planejado |
| H8 | catálogo visual / Style Guide | planejado |
| H9 | integração editorial com ferramentas de composição | futura, após maturidade dos componentes |
| H10 | light/dark/auto/alto contraste por tokens | futura |

O roadmap é direcional, não autorização para implementar fases seguintes sem revisar o estado real do código.
