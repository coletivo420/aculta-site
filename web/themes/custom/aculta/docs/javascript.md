# JavaScript

O JavaScript do tema é progressive enhancement. Bootstrap e módulos contrib continuam donos de suas engines.

## Estado atual

A library global carrega dois arquivos, um por responsabilidade:

```text
js/
├── navigation.js
└── editorial-carousel.js
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

Os dois arquivos permanecem em `aculta/global` e compartilham as dependências atuais:

- `bootstrap5/bootstrap5-js-latest`;
- `core/drupal`;
- `core/once`.

O Commit D não introduz library separada por componente nem attachment condicional. Esse tipo de otimização deve ser avaliado em commit próprio para não misturar split estrutural com mudança de carregamento.

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
