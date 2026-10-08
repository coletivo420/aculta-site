# Semântica compartilhada da Minha Conta

## Objetivo

Definir uma linguagem de apresentação comum para a Minha Conta antes de criar
novos SDCs.

Esta fase não cria componentes no tema.

Ela define o que o `aculta_portal` deve entregar como **view-model semântico**
para que o tema possa decidir, com evidência de reutilização, quando um
primitive/component merece virar SDC.

## Compatibilidade com ACULTA420

- `status-badge`, `empty-state`, `summary-card` e `action-list` são
  **contratos semânticos do Portal**, não SDCs automaticamente aprovados;
- o tema poderá promovê-los a SDC somente quando houver reutilização real e
  ownership visual claro;
- o Portal não deve depender de um SDC futuro para funcionar.

## Camadas

```text
estado funcional
      ↓
manager/adapter
      ↓
presenter
      ↓
view-model semântico
      ↓
render atual OU futuro SDC
```

## 1. Status

### Objetivo

Padronizar a semântica de estado sem ensinar ao componente detalhes de LMS,
Commerce, OAuth, Forum ou Wiki.

### Estrutura conceitual

```text
status:
  code: valor funcional interno
  label: texto para o usuário
  tone: semântica visual genérica opcional
```

### Tones permitidos

Quando necessário, usar somente:

- `neutral`;
- `info`;
- `success`;
- `warning`;
- `danger`.

O `tone` não é fonte de verdade.

Ele é tradução de apresentação feita pelo presenter.

### Exemplos

| Subsistema | Estado funcional | Label possível | Tone possível |
| --- | --- | --- | --- |
| LMS | não iniciado | Não iniciado | neutral |
| LMS | progress | Em andamento | info |
| LMS | passed | Concluído | success |
| LMS | failed | Não aprovado | danger |
| LMS | needs_evaluation | Aguardando avaliação | warning |
| Commerce Payment | completed | Aprovado | success |
| Commerce Payment | authorization | Em processamento | info |
| Commerce Payment | refunded | Reembolsado | neutral |
| Commerce Payment | voided | Cancelado | danger |
| Social Auth | conectado | Conta conectada | success |
| Social Auth | não configurado | Indisponível no momento | neutral |
| E-mail | alteração pendente | Aguardando confirmação | warning |

Esses mapeamentos são exemplos para presenters. Não devem ser codificados no
SDC.

## 2. Ação

### Objetivo

Padronizar CTAs sem criar um `aculta:button` prematuramente.

A auditoria H3 decidiu manter button como primitive CSS/Bootstrap porque Form
API e Bootstrap já geram markup funcional.

### Estrutura conceitual

```text
action:
  label
  url
  kind
```

`kind` indica intenção sem impor markup:

- `primary`;
- `secondary`;
- `destructive`;
- `link`.

O tema decide como mapear isso para classes Bootstrap/ACULTA.

### Regra

Access é resolvido **antes** de criar a ação.

O componente não recebe uma ação proibida para decidir se deve escondê-la.

## 3. Empty state

### Objetivo

Evitar que cada seção invente sua própria estrutura para "nenhum resultado".

### Estrutura conceitual

```text
empty:
  title?
  message
  action?
```

### Exemplos

- nenhum curso;
- nenhum apoio;
- nenhuma contribuição Wiki;
- nenhum tópico;
- nenhuma integração conectada.

O empty state não consulta a fonte de dados.

O presenter sabe que a coleção está vazia e prepara o estado.

## 4. Summary

### Objetivo

Padronizar os resumos do dashboard da Conta.

### Estrutura conceitual

```text
summary:
  title
  text
  status?
  action?
  count?
```

### Exemplos

- Cursos;
- Apoio;
- Participação;
- Segurança;
- Integrações.

A contagem vem da fonte funcional/presenter.

O componente não executa query.

## 5. Action list

### Objetivo

Representar uma lista de ações já autorizadas.

### Estrutura conceitual

```text
actions:
  - label
    url
    kind
    current?
```

Pode alimentar:

- ações de segurança;
- conexões;
- links de participação;
- atalhos do dashboard.

Não substituir Menu API quando a fonte já for um menu Drupal.

## 6. Conteúdo renderable

Campos com markup Drupal seguro devem continuar renderables.

Exemplos:

- formatted text de descrição de curso;
- Form API;
- imagem;
- links render arrays quando necessário.

Não achatar formatted text para string apenas para simplificar view-model.

## 7. Cache

View-model não elimina cache metadata.

Presenter/controller deve preservar:

- entity cache tags;
- user context;
- user.permissions;
- route;
- domain/purpose quando necessário;
- max-age adequado.

O SDC não corrige cache metadata ausente.

## 8. Access

Regra transversal:

```text
access
  ↓
metadata autorizada
  ↓
view-model
  ↓
componente
```

Nunca:

```text
entity metadata
  ↓
componente
  ↓
decisão de access
```

Esse princípio é especialmente importante para:

- Commerce Orders;
- curso não publicado;
- conteúdo Wiki não publicado;
- conexões Social Auth;
- dados pessoais.

## 9. AJAX

Esses view-models são independentes de transporte.

Podem ser renderizados:

- full page;
- Drupal Ajax;
- Views AJAX;
- Form API AJAX;
- Core HTMX.

O mesmo contrato deve sobreviver ao re-render.

## 10. Readiness para SDC

Um contrato semântico pode virar SDC no tema quando houver:

1. pelo menos dois consumidores reais ou fronteira visual forte;
2. props/slots estáveis;
3. ownership de CSS claro;
4. benefício maior que duplicação de API;
5. acessibilidade conhecida;
6. nenhuma dependência de storage/business logic.

## Situação atual

| Contrato | Portal | Tema |
| --- | --- | --- |
| status | aprovado como semântica | SDC não aprovado ainda |
| action | aprovado como semântica | button segue CSS/Bootstrap |
| empty | aprovado como semântica | candidato futuro |
| summary | aprovado como semântica | candidato futuro |
| action list | aprovado como semântica | candidato futuro |
| course card | contrato semântico permitido | tema decide quando existir reutilização real |
| category label | não específico da Conta | seguir catálogo/roadmap atual do ACULTA420 |

## Evolução

Presenters podem ser extraídos do controller quando reduzirem acoplamento e
tornarem access/cache/semântica mais testáveis.

A extração não pode:

- alterar OAuth;
- substituir Form API;
- duplicar Social Auth/Email Confirmer/LMS/Commerce;
- criar dependência obrigatória de SDC ainda inexistente;
- transferir regra funcional para ACULTA420.
