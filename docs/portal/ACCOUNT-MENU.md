# Menu da Minha conta

Data da revisão: 2026-10-10. Versões: `0.2.0-dev.26` a `0.2.0-dev.29`.

O menu lateral da conta (`/conta-interna`, `/dados`, `/seguranca`, `/conexoes`, `/meus-cursos`, `/meu-apoio`, `/configuracoes`) tem duas apresentações. As duas usam as mesmas rotas, a mesma ordem e o mesmo item ativo.

## Desktop (a partir de 768px)

Lista vertical de links com destaque no item atual. Ícones e setas ficam ocultos. Nada mudou no desktop.

## Celular (até 767.98px)

Sanfona nativa (`details`/`summary`, sem JavaScript de abertura):

- cada seção vira um item com ícone, rótulo e seta;
- ao abrir um item, o conteúdo completo da seção é carregado por AJAX e entra no painel;
- só uma seção fica aberta por vez: abrir uma recolhe as demais;
- a seção atual começa aberta e carrega sozinha;
- "Sair" continua como linha simples, sem painel.

### Como a carga funciona

- `account-navigation.js` busca a página da seção com `requestDocument` (mesma origem, cabeçalho `X-Requested-With`) e copia o `.portal-account__body` da resposta para o painel. Assim a autorização e o cache são os da própria página.
- O painel tem `data-portal-account-accordion-panel` e `data-src` com o endereço da seção.
- No celular, a coluna de conteúdo lateral (`[data-portal-account-content]`) sai do DOM. Assim não há IDs duplicados.
- Se a carga falhar, o painel mostra um link para abrir a página.

### Ícones por seção (Bootstrap Icons)

| Seção | Ícone |
| --- | --- |
| Visão geral | `bi-grid` |
| Meu Apoio | `bi-heart` |
| Cursos | `bi-mortarboard` |
| Meus Dados | `bi-person-vcard` |
| Conexões | `bi-link-45deg` |
| Segurança | `bi-shield-lock` |
| Configurações | `bi-sliders` |
| Sair | `bi-box-arrow-right` |

## Arquivos

- `src/AccountShellBuilder.php`: monta os dois menus e a sanfona.
- `templates/aculta-portal-shell.html.twig`: `menu` (desktop) e `menu_accordion` (celular).
- `css/account-portal.css`: regras de desktop e de celular.
- `js/account-navigation.js`: carga AJAX da sanfona e a regra de uma seção aberta por vez.

## Limites e pendências

- A carga autenticada (com login) não foi verificada pela automação: precisa de conferência no celular.
- Formulários dentro dos painéis dependem dos comportamentos do Drupal (`attachBehaviors`). Qualquer formulário que não funcionar deve ser reportado.
