# Shell multidomínio — direção arquitetural

Status: **planejado pós-0.1.0**.

Este documento fixa a direção do shell público sem antecipar o redesign para a
Foundation. A 0.1.0 deve apenas impedir contratos que tornem essa evolução difícil.

## Hierarquia

```text
Plataforma/instituição
ACULTA
       ↓
Domain purpose/produto
Wiki420 / Coletivo420 / Cursos / Loja / Institucional / ...
       ↓
Conteúdo da página
```

O shell possui duas camadas:

1. **Institution Bar** — global, baixa e discreta; representa a Associação Cultural
   Antiproibicionista/ACULTA e concentra utilitários globais como Conta, sair e
   futuros acessos ao ecossistema.
2. **Domain Header** — identidade visual principal do purpose corrente; pode variar
   logo, nome, navegação e accent sem trocar a arquitetura do shell.

## Ownership e resolução

O tema nunca escolhe identidade por hostname.

```text
Domain
  ↓
DomainPurposeManager
  ↓
aculta_portal
  ↓
contexto de apresentação
  ↓
ACULTA420
```

`aculta_portal` continua responsável por resolver o contexto funcional. O tema
apenas apresenta dados já preparados.

Contrato conceitual futuro:

```text
domain_presentation
├── purpose
├── title
├── short_title
├── home_url
├── logo
├── logo_alt
├── navigation
└── optional accent
```

O contrato concreto só deve ser congelado quando houver implementação e testes.
Não passar entidade `Domain`, storage, services ou regra de negócio para Twig/SDC.

## Branding e fallback

Branding próprio não é requisito para criar um purpose.

```text
logo específico do purpose
        ↓ se inexistente
branding ACULTA padrão
        ↓
título textual
```

Variantes light/dark de logo podem existir no futuro, mas não fazem parte do
contrato mínimo.

## Navegação por purpose

O shell é comum; o menu pode variar por purpose. Exemplos conceituais:

```text
MAIN:    Institucional | Projetos | Atividades | Notícias | Transparência
WIKI:    Explorar | Categorias | Recentes | Colabore
COURSES: Cursos | Trilhas | Meus cursos
```

O tema não decide esses menus por hostname. A origem/seleção é preparada pelo
Portal e apresentada pelo shell.

## Composição Twig/SDC

`page.html.twig` permanece compositor do shell. Não criar um SDC monolítico da
página.

Composição alvo:

```text
aculta-shell
├── institution-bar
└── domain-header
    ├── domain-brand
    ├── primary-navigation
    └── domain-actions
```

Quando o contrato estiver estável, `domain-header` pode virar SDC próprio com
props simples e slots de apresentação.

## Direção visual

A referência escolhida é próxima ao conceito “Design B”:

- fundo geral verde muito suave;
- superfícies principais brancas;
- Institution Bar branca, compacta e visualmente secundária;
- Domain Header branco, mais alto e dominante;
- linha inferior verde estrutural;
- item atual amarelo com texto vermelho;
- texto base verde escuro;
- logo ACULTA compacto na faixa institucional;
- logo principal variável no Domain Header.

Essas cores não devem ser espalhadas como literais. A implementação deve nascer
sobre semantic tokens.

Vocabulário alvo:

```css
--aculta-surface-page: ...;
--aculta-surface-raised: ...;
--aculta-surface-header: ...;
--aculta-text-primary: ...;
--aculta-text-secondary: ...;
--aculta-border-subtle: ...;
--aculta-shell-institution-bg: ...;
--aculta-shell-domain-bg: ...;
```

## Color modes

Seguir Bootstrap:

```text
data-bs-theme="light"
data-bs-theme="dark"
auto via prefers-color-scheme
```

Modo de cor troca tokens e assets compatíveis; não troca geometria nem markup.

## Sticky e mobile

Começar por `position: sticky`, não `fixed`, por conviver melhor com Drupal
Toolbar, zoom/reflow e ausência de compensação artificial no `body`.

Referência de densidade, não contrato rígido:

- Institution Bar: aproximadamente 28–34 px no desktop;
- Domain Header: aproximadamente 56–68 px no desktop.

No mobile:

```text
institution bar mínima

[logo do domain] [nome]                     [menu]
```

Bootstrap continua dono de Collapse/Offcanvas. Ações secundárias podem migrar
para Offcanvas quando necessário; não criar menu lateral custom em JavaScript.

## Anti-regressão

- nunca usar hostname para escolher logo/menu;
- nunca mover `DomainPurposeManager` para o tema;
- nunca passar entidade `Domain` diretamente a SDC/Twig;
- nunca obrigar branding próprio para um purpose;
- nunca criar um header diferente por subdomínio; o shell é único e os dados variam;
- não acoplar light/dark a markup específico;
- não criar segunda engine de navegação quando Bootstrap já oferece Collapse/Offcanvas;
- não implementar o redesign completo dentro da Foundation 0.1.0.
