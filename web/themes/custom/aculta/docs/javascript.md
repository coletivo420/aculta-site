# JavaScript

O JavaScript do tema é progressive enhancement. Bootstrap e módulos contrib continuam donos de suas engines.

## Estado atual

`js/aculta.js` contém dois Drupal behaviors.

### `acultaNavigation`

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

### `acultaEditorialFocus`

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

## Modularização futura

A divisão natural, se mantiver responsabilidades atuais, é:

```text
js/
├── navigation.js
└── editorial-carousel.js
```

Primeiro separar arquivos mantendo comportamento e carregamento equivalentes. Otimizar libraries/attachment somente em commit posterior e testável.

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
