# ACULTA420 0.2-B.1 — Inventário da fronteira Domain Presentation

Status: **inventário documental; sem alteração de Runtime**.

Este documento delimita a fronteira entre Drupal Domain, `aculta_portal` e o
tema `aculta420` antes da implementação do shell multidomínio 0.2-C/0.2-D.

## Objetivo

Transformar uma fronteira hoje implícita em um contrato explícito:

```text
Core/contrib + Domain
        ↓
resolução funcional
        ↓
aculta_portal
        ↓
view-model de apresentação
        ↓
ACULTA420
        ↓
Twig / SDC / Bootstrap
```

O tema não conhece Domain, hostname, storage, entidades de negócio ou serviços
do Portal. O Portal não define geometria, CSS, tokens ou markup do shell.

## Inventário atual

### Resolução funcional já existente no Portal

`DomainPurposeManager` é a fonte operacional para:

- purpose corrente;
- associação purpose → Domain ID;
- carregamento da entidade Domain quando necessário no Portal;
- URLs por purpose respeitando aliases do ambiente;
- URLs canônicas de produção;
- paths cross-domain.

Purposes existentes:

| Purpose | Domain ID | Papel funcional |
| --- | --- | --- |
| `main` | `aculta_org` | institucional/principal |
| `account` | `conta_aculta_org` | conta |
| `support` | `apoio_aculta_org` | apoio |
| `magazine` | `coletivo420_aculta_org` | Observatório/Coletivo 420 |
| `wiki` | `wiki420_aculta_org` | Wiki420 |
| `shop` | `loja_aculta_org` | loja |
| `courses` | `cursos_aculta_org` | cursos |

`ContentPurposeResolver` resolve ownership funcional de rotas e conteúdo.
`DomainPurposeRequestSubscriber` aplica fail-closed/wrong-host e fluxos de
destino. Nenhuma dessas responsabilidades deve migrar para o tema.

### Apresentação já preparada pelo Portal

O Portal já injeta dados de apresentação em pontos específicos:

- `preprocess_breadcrumb`: título corrente via `AcultaBreadcrumbBuilder`;
- `preprocess_block`: URL de transparência para bloco institucional;
- `preprocess_menu`: reescrita de URLs cross-domain;
- `preprocess_links`: links editoriais no purpose correto;
- `preprocess_page`: shell da Conta, aviso de edição e URL de Segurança.

Esses casos provam o padrão correto: o Portal resolve significado/URL e entrega
ao sistema de tema um valor simples ou renderable.

### Apresentação atualmente pertencente ao tema

`ACULTA420` atualmente:

- compõe o shell em `page.html.twig`;
- move Page Title, breadcrumb, menu de conta e help para posições visuais no
  `ThemeHooks::preprocessPage()`;
- apresenta o block `system_branding_block`;
- apresenta o menu `main` na região `primary_menu`;
- apresenta o menu `account` como faixa utilitária;
- possui tokens, CSS, responsividade, Bootstrap Collapse e JS de navegação.

Isso é responsabilidade correta do tema enquanto os dados já chegam resolvidos.

### Acoplamentos que ainda são implícitos

Hoje não existe um serviço/view-model único que diga ao tema:

- qual é o purpose de apresentação;
- qual título curto deve representar esse purpose;
- qual URL representa sua home;
- qual branding opcional deve ser usado;
- qual navegação primária foi preparada;
- quais ações pertencem à camada institucional;
- quais cache contexts/tags acompanham esses dados.

O shell depende de block placements globais e de `system_branding_block`,
portanto ainda não existe um contrato explícito purpose → apresentação.

## Fronteira normativa

A partir de 0.2-B, a fronteira é dividida em três níveis.

### 1. Functional Domain Context — Portal only

Pode conhecer:

- `DomainPurposeManager`;
- `DomainInterface`;
- Domain Negotiator;
- Domain IDs;
- aliases/hostname;
- Request;
- route ownership;
- access;
- entities e configuração funcional.

Não pode ser consumido diretamente por Twig/SDC.

### 2. Domain Presentation View-model — Portal → tema

É a única estrutura específica de purpose autorizada a atravessar a fronteira.

Contrato alvo inicial:

```text
domain_presentation
├── purpose              string estável
├── title                string
├── short_title          string|null
├── home_url             Url|string seguro
├── branding
│   ├── logo             renderable|string|null
│   └── logo_alt         string|null
├── navigation           render array|null
├── actions              render array|null
└── cacheability         metadata preservada no render array/contexto
```

Esse formato é deliberadamente pequeno. Campos só entram quando existe
consumidor real.

O view-model:

- não contém entidade `Domain`;
- não contém hostname;
- não contém service IDs;
- não contém objetos de storage;
- não contém regra de access;
- não contém cores literais ou classes CSS específicas de purpose;
- não contém decisão light/dark;
- não contém markup estrutural do header.

### 3. Shell presentation — ACULTA420 only

Pode decidir:

- DOM e landmarks;
- hierarquia visual;
- Institution Bar / Domain Header;
- Bootstrap primitives;
- slots/props SDC;
- tokens;
- spacing;
- tipografia;
- responsividade;
- sticky/mobile;
- comportamento visual e acessibilidade complementar.

Não pode:

- chamar `DomainPurposeManager`;
- carregar Domain;
- consultar hostname;
- selecionar menu por hostname;
- montar URL cross-domain;
- buscar config funcional do Portal;
- decidir access;
- inventar fallback funcional.

## Ownership por dado

| Dado/decisão | Owner | Cruza a fronteira como |
| --- | --- | --- |
| purpose corrente | Portal | string |
| Domain ID/hostname/alias | Portal | **não cruza** |
| URL home do purpose | Portal | URL preparada |
| título do purpose | Portal | string |
| branding específico disponível | Portal | valor/renderable opcional |
| fallback de branding | Portal prepara disponibilidade; tema apresenta | props simples |
| menu correto para o purpose | Portal | render array |
| reescrita cross-domain | Portal | URL já correta |
| account/global actions | Portal | render array |
| access/permissões | Core/Portal | somente resultado autorizado |
| cache contexts/tags | Portal/Core | metadata preservada |
| cores/tokens | ACULTA420 | não vem do Portal |
| layout/header/spacing | ACULTA420 | não vem do Portal |
| dark/light | tokens/etapa futura de preferência | nunca purpose |
| Collapse/Offcanvas | Bootstrap/ACULTA420 | comportamento visual |

## Fallback de branding

O fallback deve ser resolvível sem tornar branding próprio obrigatório:

```text
branding específico preparado para o purpose
        ↓ ausente
branding ACULTA padrão
        ↓ indisponível
título textual
```

O Portal informa os valores disponíveis. O tema executa apenas a apresentação
do fallback já contratado; ele não consulta Domain/config para descobrir outra
fonte.

## Navegação

Estado atual:

- `system.menu.main` é global e está posicionado em `primary_menu`;
- `system.menu.account` é global e aparece no header/faixa utilitária;
- `preprocess_menu` do Portal já corrige destinos cross-domain.

0.2-B não deve criar menus duplicados por hostname. Se a 0.2-D exigir
navegação distinta por purpose, a seleção deve ser feita no Portal e entregue
como render array em `domain_presentation.navigation`.

## Cache e access

O contrato não pode transformar dados privados/variáveis em strings sem
cacheability.

Regras:

- access é resolvido antes da apresentação;
- render arrays mantêm `#cache`;
- no mínimo revisar contextos `domain`, `route`, `url.path`,
  `user`/`user.permissions` quando aplicável;
- entidades usadas pelo Portal entram como cacheable dependencies no Portal;
- o tema não compensa metadata perdida.

## Pontos de integração alvo

A implementação 0.2-B.2 deve preferir um serviço dedicado no Portal, por
exemplo conceitualmente:

```text
aculta_portal.presentation.domain
        ↓
DomainPresentationBuilder
        ↓
preprocess_page
        ↓
$variables['domain_presentation']
        ↓
ACULTA420 page.html.twig / futuros SDCs
```

O nome concreto será decidido na implementação, mas deve existir **um único
builder/presenter autoritativo** para o shell.

Não espalhar `match ($purpose)` por hooks, controllers e templates.

## O que não deve entrar em 0.2-B

- redesign visual da Institution Bar;
- novo Domain Header;
- sticky/mobile;
- UI light/dark/auto;
- persistência de preferência;
- novo sistema de menus;
- SDC monolítico de página;
- branding hardcoded por hostname;
- mudança de access/wrong-host;
- migração de dados Domain.

Esses itens pertencem às fases posteriores ou a outras linhas do roadmap.

## Gate da 0.2-B

A fase estará pronta para avançar à 0.2-C quando:

1. existir um único builder/presenter de `domain_presentation`;
2. todos os purposes conhecidos tiverem fallback seguro;
3. o tema receber somente valores simples/render arrays;
4. nenhuma entidade Domain chegar a Twig/SDC;
5. nenhum arquivo do tema consultar hostname, Domain ou serviços do Portal;
6. URLs cross-domain forem construídas pelo Portal;
7. cache/access forem preservados;
8. fixtures cobrirem purpose conhecido, purpose sem branding e fallback;
9. documentação e gate anti-regressão refletirem o contrato;
10. nenhuma mudança visual estrutural tiver sido introduzida na 0.2-B.

## Próximas etapas

- **0.2-B.1** — este inventário e fronteira normativa;
- **0.2-B.2** — implementar builder/presenter do contrato;
- **0.2-B.3** — integrar o contrato ao preprocess do shell sem redesign;
- **0.2-B.4** — gate/fixtures e fechamento da fronteira;
- **0.2-C** — Institution Bar;
- **0.2-D** — Domain Header.
