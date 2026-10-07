# Módulos upstream e política de reutilização

Data da revisão: 2026-10-06.

O objetivo desta lista é impedir que `aculta_portal` replique capacidades
mantidas por Drupal Core ou contrib.

Os números de versão abaixo são referências pesquisadas nesta data. Antes de
instalar/atualizar qualquer pacote, confirmar novamente no Drupal.org e com
`composer show`.

## Já adotados

### Drupal Core — Language, Locale e Config Translation

**Status:** fonte de verdade da internacionalização de interface/configuração.

Uso:

- `language`: idioma padrão e negociação;
- `locale`: traduções oficiais de Core, módulos e temas;
- `config_translation`: tradução de textos configuráveis.

O projeto usa `pt-br` como idioma padrão. Catálogos oficiais de Core/contrib
são importados pelo Locale conforme as versões instaladas. O Portal mantém
somente um catálogo local mínimo para lacunas confirmadas no Runtime.

`content_translation` permanece fora de escopo até existir requisito editorial
multilíngue.

Detalhes: [LOCALIZATION.md](LOCALIZATION.md).

### Profile + Address

**Status:** fonte de verdade.

Uso:

- dados pessoais estruturados;
- profile `customer`;
- endereço canônico compartilhado com Commerce.

Portal:

- centraliza a experiência em "Meus dados";
- restringe o formulário ao contexto necessário;
- sincroniza apenas através das entidades reais.

Não criar profile/endereço paralelo.

### CEP Autocomplete for Address

**Pacote:** `drupal/cep_autocomplete:^1.0`  
**Release atual pesquisada:** 1.0.0  
**Drupal:** ^10 || ^11  
**Security Advisory Policy:** não coberto.

O módulo upstream já fornece:

- Address nativo;
- endpoint ViaCEP;
- cache server-side;
- preenchimento de UF/cidade/logradouro/bairro;
- suporte a forms reinjetados por AJAX;
- base path/prefix handling.

A customização local do Portal **é deliberada e pode permanecer** quando cobre
necessidades da central de dados:

- labels/placeholder ACULTA;
- acessibilidade e aria-live;
- stale-response protection;
- foco pós-preenchimento;
- adaptação ao Profile customer usado no painel;
- sincronização necessária entre dados pessoais e endereço.

O Portal **não deve** duplicar:

- endpoint ViaCEP;
- cliente HTTP server-side;
- cache;
- storage de endereço.

Por não estar sob Security Advisory Policy, manter revisão periódica e escopo
mínimo da integração.

### Drupal LMS + Group

**Status:** fonte de verdade de cursos.

Portal pode manter adapters read-only como `AccountCoursesManager` quando:

- Group continua dono da matrícula;
- LMS continua dono do progresso;
- `access()` é respeitado;
- nenhum dado paralelo é persistido.

### Commerce + Donation Flow + Payment

**Status:** fonte de verdade financeira.

Portal pode:

- apresentar entrada do fluxo;
- mostrar histórico/resumos do usuário;
- adaptar UX e segurança.

Portal não mantém ledger próprio.

### Domain suite

**Status:** fonte de verdade de hosts/contextos.

Portal pode manter `DomainPurposeManager` e políticas de isolamento porque
"purpose" é conceito específico da arquitetura ACULTA.

## A adotar em versões futuras

### Forum

**Pacote planejado:** `drupal/forum:^1.1`  
**Release atual pesquisada:** 1.1.3  
**Drupal:** ^11 || ^12  
**Security Advisory Policy:** coberto.

É a continuação do módulo Forum removido do Core no Drupal 11.

Usa:

- Taxonomy para containers/fóruns;
- Node para tópicos;
- Comment para respostas.

Decisão:

**não criar fórum próprio no Portal.**

O Portal adicionará:

- purpose FORUM;
- integração Domain;
- links cross-domain;
- cards "Minha participação";
- resumos administrativos;
- composição com Views/APIs.

### Search API

**Pacote planejado:** `drupal/search_api:^1.41`  
**Release atual pesquisada:** 1.41  
**Drupal:** ^10.3 || ^11  
**Security Advisory Policy:** coberto.

Uso planejado:

- busca Wiki;
- busca Fórum;
- eventual busca agregada.

A busca atual da Wiki baseada em `EntityQuery + LIKE` é dívida técnica e
candidata à substituição após o índice Search API estar validado.

Não criar engine de busca custom.

### Flag

**Pacote planejado:** `drupal/flag:^5.1`  
**Release atual pesquisada:** 5.1.0  
**Drupal:** ^10.3 || ^11 || ^12  
**Security Advisory Policy:** coberto.

Fornece:

- flags por usuário;
- toggles JavaScript;
- integração Views;
- padrão de bookmarks.

Uso possível:

- acompanhar tópico;
- favoritos;
- marcar conteúdo;
- ações de participação aprovadas futuramente.

Não criar tabela Portal de bookmarks/follows.

### Comment Notify

**Pacote candidato:** `drupal/comment_notify:^1.5`  
**Release estável pesquisada:** 1.5  
**Drupal:** ^9 || ^10 || ^11  
**Security Advisory Policy:** coberto.

Fornece notificações de novos comentários/respostas e unsubscribe.

Uso futuro deve ser avaliado com SMTP e política de privacidade antes de
habilitar.

O Portal pode expor preferências/status; não deve construir um sistema paralelo
de notificações por e-mail.

## Candidatos para deduplicação, não aprovados como dependência

### Easy Breadcrumb

**Pacote candidato:** `drupal/easy_breadcrumb:^2.0`  
**Release pesquisada:** 2.0.10  
**Drupal:** ^9.2 || ^10 || ^11.

Avaliar contra:

- `AcultaBreadcrumbBuilder`;
- breadcrumbs próprios do Forum;
- Domain purposes;
- requisitos de cache.

Não instalar ou remover builder custom até existir teste de paridade.

### Domain Menu Access

**Pacote candidato:** `drupal/domain_menu_access:^2.0`  
**Release pesquisada:** 2.0.1  
**Drupal:** ^10.2 || ^11.

Pode reduzir customização de menus por Domain, mas deve ser avaliado contra a
versão atual de Domain 3.x e a semântica específica de purpose.

Não adotar automaticamente.

## Auditar antes de manter customização

### Schema Metatag / Metatag

O projeto já depende de Metatag e Schema Metatag.

Classes custom atuais relacionadas a:

- `alternateName`;
- `legalName`;
- `email`;
- `taxID`;
- PostalAddress;
- WebPage name/url;

devem ser comparadas com a versão realmente instalada do upstream antes de
qualquer remoção.

Regra:

1. confirmar plugin equivalente upstream;
2. comparar ID e propriedade Schema.org;
3. testar export config;
4. testar output;
5. só então remover código duplicado.

## Regra para nova dependência

Antes de `composer require`:

- confirmar release estável atual;
- confirmar Drupal 11;
- verificar Security Advisory Policy;
- verificar manutenção recente;
- mapear storage/fonte de verdade;
- registrar motivo neste documento;
- definir testes e rollback.

Código de outro projeto não deve ser copiado para dentro do Portal quando o
módulo pode ser usado como dependência. Quando um padrão upstream for estudado,
preferir adaptar sua API/documentação; qualquer código incorporado exige
compatibilidade de licença, atribuição e justificativa.

## Engagement — versões pesquisadas em 2026-10-06

- `drupal/flag:^5.1`: stable 5.1.0, Drupal ^10.3 || ^11 || ^12, security-covered.
- `drupal/comment_notify:^1.6`: stable 1.6, Drupal ^9 || ^10 || ^11, security-covered.

Instalação permanece adiada para Portal 0.17 Runtime.
