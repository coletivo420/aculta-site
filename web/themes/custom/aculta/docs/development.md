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

Estrutura atual após o Commit B:

```text
css/
├── tokens.css
├── base.css
├── layout.css
└── style.css
```

A library global deve preservar esta ordem. `style.css` ainda concentra componentes e regras especializadas e será reduzido gradualmente, sem reordenar a cascade incidentalmente.

Antes de remover ou mover regra:

- verificar Twig;
- Views/configuração Drupal;
- classes geradas por módulos;
- estados Bootstrap;
- páginas especializadas.

Classes podem existir apenas em configuração e não aparecer em PHP/Twig.

Não minificar fontes no Git; agregação de produção pertence ao Drupal/infra.

## PHP/Twig

Não mover código apenas para eliminar `\Drupal::service()` cosmeticamente.

Primeiro classificar a lógica:

- apresentação legítima do tema;
- integração que pertence ao `aculta_portal`;
- funcionalidade que pertence a Core/contrib.

Twig não recebe regra de negócio.

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
