# JavaScript

O JavaScript do tema é progressive enhancement. Bootstrap e módulos contrib continuam donos de suas engines.

## Estado atual

O JavaScript está separado por responsabilidade e por escopo de carregamento:

```text
js/
├── navigation.js          # global
└── editorial-carousel.js  # contextual: View home_editorial_highlights
```

Os selectors, IDs de `once()`, eventos e chamadas às APIs públicas permanecem os mesmos do arquivo monolítico anterior.

### `navigation.js` — `acultaNavigation`

Responsabilidade:

- localizar navbar, toggle e collapse;
- só ativar enhancement quando Bootstrap existe;
- adicionar a classe `aculta-navigation-ready`;
- fechar menu mobile com Escape;
- devolver foco ao toggle.

Não deve:

- reimplementar Bootstrap Collapse;
- comparar URL para descobrir item atual;
- depender de jQuery.

### `editorial-carousel.js` — `acultaEditorialFocus`

Responsabilidade:

- integrar foco com a API pública do VVJB;
- pausar quando o carousel inicializado recebe foco;
- observar `vvj:ready`;
- tratar localmente `AbortError` de ViewTransition em navegação rápida.

Não deve:

- criar timer paralelo;
- avançar slides por conta própria;
- substituir controles/estado interno do VVJB.

## Padrões obrigatórios

- `Drupal.behaviors`;
- `once()`;
- suporte a attach em carregamento inicial e conteúdo AJAX;
- JavaScript nativo;
- progressive enhancement;
- eventos/APIs públicos de contrib.

Não introduzir jQuery novo.

## Carregamento

`js/navigation.js` permanece em `aculta/global`, pois o shell público usa navegação em todas as páginas.

`js/editorial-carousel.js` pertence à library `aculta/editorial-carousel` e é anexado por `templates/views-view-vvjb.html.twig` somente quando a View é `home_editorial_highlights`.

A library contextual declara suas próprias dependências em `core/drupal` e `core/once`. O CSS do carrossel permanece global neste passo porque `css/components/editorial-carousel.css` ainda contém estilos do card editorial usados fora da engine VVJB; a separação visual card/engine deve ocorrer em mudança posterior e específica.

## Testes

Após alteração:

- menu desktop;
- menu mobile;
- Escape;
- foco retornando ao toggle;
- attach após AJAX quando aplicável;
- VVJB mouse/teclado;
- pause por foco;
- Play manual;
- reduced motion;
- console sem exceptions.
