# CHANGELOG — ACULTA420

Este changelog versiona o tema/design system ACULTA420.

## 0.1.0 — Foundation — 2026-10-07

### Identidade técnica

- novo nome: **ACULTA420**;
- novo machine name: `aculta420`;
- novo diretório funcional: `web/themes/custom/aculta420`;
- provider legado de compatibilidade removido antes do fechamento da Foundation; o único tema custom público é `aculta420`;
- provider SDC: `aculta420:*`;
- libraries: `aculta420/*`;
- settings: `aculta420.settings`;
- hooks PHP: `aculta420_preprocess_*`.

### Configuração

- a configuração sincronizada aponta exclusivamente para `aculta420`; compatibilidade com provider histórico não permanece no runtime final;
- `core.extension` passa a instalar `aculta420`;
- `system.theme` passa a usar `aculta420` como default;
- block placements passam a depender do novo tema;
- settings/favicons apontam para o novo diretório;
- IDs de conteúdo/config `aculta_*` são preservados quando não representam o
  provider do tema.

### Design system

- Bootstrap5 permanece infraestrutura;
- Core SDC permanece base de componentização;
- `enforce_prop_schemas: true` ativado;
- `editorial-card` organizado em `components/content/`;
- primeiro SDC stable passa a ser `aculta420:editorial-card`;
- CSS do card continua auto-carregado pelo SDC;
- VVJB permanece engine do carousel editorial.

### Documentação

A documentação histórica de refatoração foi consolidada e substituída por um
conjunto normativo:

- architecture;
- features;
- design-system;
- components;
- development;
- accessibility;
- decisions;
- roadmap;
- versioning;

### Validação Runtime R0.1

- O gate compila o Twig do SDC pelo ID canônico `aculta420:editorial-card`, resolvido pelo loader de componentes do Drupal.
- shell multidomínio planejado, com Institution Bar global e Domain Header por purpose;
- D-012 e regras anti-regressão para impedir hostname/Domain entity no tema;
- instruções de agentes consolidam `aculta420` como único provider e proíbem aliases/shims legados.

### Saneamento pós-rename

- relatório de requisitos do Portal passa a reconhecer `aculta420` como provider público;
- dados institucionais saem do tema e passam para `aculta_portal.settings`;
- validadores ativos passam a inspecionar o contrato atual do provider `aculta420`;
- instruções de agentes deixam de apontar para documentação Portal removida;
- gate de autenticação reflete o baseline atual sem `user_registrationpassword`.
### Limpeza da Foundation

- remove o diretório/provider de compatibilidade temporária;
- remove o runbook transitório de migração da documentação normativa;
- estabelece como gate da 0.1.0 a ausência de provider legado no código e na configuração;
- histórico da transição permanece no Git/ADR, sem virar contrato de runtime.

### Ownership de CSS

- remove o catch-all `css/style.css`;
- footer, institucional/participação, formulários e Conta passam a ter ownership explícito;
- o validador percorre a árvore CSS real em vez de depender de um arquivo agregado obsoleto;
- regra anti-regressão impede recriação de CSS residual genérico.

### Runtime mínimo

- remove override de ícone RSS sem display feed público consumidor;
- remove `css/responsive.css` e devolve media queries aos componentes donos;
- centraliza duração rápida/easing e sombra de hover em tokens;
- remove hook de login duplicado no Portal;
- remove exports web sem consumidor; preserva originais de branding e os assets efetivamente usados;
- alinha defaults de fresh install de logo/favicon ao config sync;
- hooks do tema usam OOP/DI em `src/Hook/ThemeHooks.php`; o arquivo procedural `.theme` deixa de existir.

- remove caminho morto de `system_powered_by_block`/`aculta_site_credit`, sem placement configurado.

- move CSS do carrossel editorial para a library contextual já anexada pela View VVJB;
- registra allowlist dos sete overrides Twig com delta comprovado.

### Gate da Foundation

- cria `scripts/validate-aculta420-foundation.php` como gate runtime exclusivo do tema;
- separa validação ACULTA420 de Portal/Commerce/security;
- valida OOP hooks, provider/config, libraries/assets, Twig/YAML, SDC e invariantes de CSS;
- valida consistência de plugin, settings e dependências por UUID nos block placements ativos e sincronizados;
- corrige documentação normativa do Portal para o provider `aculta420`.

### Base herdada

0.1.0 reaproveita a base madura do antigo tema `aculta`:

- tokens;
- CSS/JS organizados por responsabilidade;
- contextual asset loading;
- cleanup Twig;
- boundary Portal/theme;
- acessibilidade/progressive enhancement.

O histórico detalhado anterior permanece no Git; não é especificação corrente.
