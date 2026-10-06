# Integração Wiki420

## Objetivo

A Wiki420 é um subsistema editorial colaborativo do mesmo Drupal. Ela possui
experiência pública própria no Domain WIKI e participa do painel ACCOUNT sem
duplicar conteúdo, revisões ou permissões.

Hosts:

- produção: `wiki420.aculta.org`;
- Homelab: `wiki420.aculta.toca.net.br`.

Purpose:

`wiki`.

## Fonte de verdade

| Conceito | Fonte |
| --- | --- |
| verbete | Node `wiki_entry` |
| categorias | Taxonomy `wiki_category` |
| revisões | revisões de Node |
| comparação | Diff |
| locking | Content Lock |
| links internos | Freelinking |
| listagens | Views |
| workflow editorial | Workflow/Content Moderation configurado para Wiki |
| origem/domain | Domain Source |

O Portal não cria entidade, tabela ou índice próprio para espelhar verbetes.

## Experiência WIKI

O host Wiki deve concentrar:

- página inicial;
- navegação por categorias;
- listagem de verbetes;
- verbete canônico;
- criação/edição conforme permissões;
- revisões e Diff;
- referências e relacionados;
- busca.

O canonical de verbetes pertence ao host WIKI.

## Integração com ACCOUNT

O Portal pode agregar no painel do usuário:

- meus verbetes;
- minhas contribuições;
- links para edição;
- atividade/revisões quando a API permitir identificação confiável;
- atalhos para continuar na Wiki.

Essa integração deve consultar as entidades/revisões reais.

Não criar contador persistido no Portal apenas para dashboard.

## Busca

A implementação atual de busca custom é aceita como estado transitório.

Roadmap:

1. preservar comportamento atual;
2. introduzir Search API;
3. indexar Wiki;
4. reconstruir busca com Search API + Views;
5. provar paridade de acesso/Domain/cache;
6. remover a busca `LIKE` custom somente depois.

## Access e privacidade

Antes de expor título, resumo, imagem ou estado editorial através do ACCOUNT,
validar acesso de visualização.

Conteúdo unpublished/restrito não pode vazar metadata por cards ou busca.

## Cache

Listagens e cards devem carregar:

- cache tags das entidades;
- contexto `domain` quando o host altera URLs/visibilidade;
- contexto de usuário/permissões quando a resposta depende do usuário.

## Administração

O futuro Admin Hub pode apresentar:

- links para verbetes;
- categorias;
- workflow;
- revisões;
- locks;
- Views;
- permissões.

Ele não recria CRUD editorial.

## Testes

Manter como gates:

- WIKI front no host correto;
- Wiki routes em host incorreto bloqueadas;
- published/unpublished;
- User sem permissão;
- editor/contributor;
- revisão;
- Diff;
- Content Lock;
- Domain Source;
- canonical;
- busca;
- ACCOUNT sem vazamento de metadata.

## Evolução

A integração da Wiki no Participation Hub está prevista no Portal 0.13.0.

Search API entra no Portal 0.16.0.
