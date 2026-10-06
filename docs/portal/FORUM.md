# Fórum ACULTA

## Objetivo

Integrar um fórum antiproibicionista ao mesmo Drupal multidomínio, sem criar
engine própria de fórum.

Hosts planejados:

- produção: `forum.aculta.org`;
- Homelab: `forum.aculta.toca.net.br`.

Purpose:

`forum`.

## Fundação funcional

Módulo escolhido para a primeira implementação:

`drupal/forum:^1.1`

Release estável pesquisada em 2026-10-06:

`1.1.3`.

O Forum usa:

- Taxonomy para containers/fóruns;
- Node para tópicos;
- Comment para respostas.

Essas estruturas continuam fontes de verdade.

O `aculta_portal` não deve criar:

- entity Topic custom;
- entity Reply custom;
- tabela de fórum;
- tabela de comentários;
- contador paralelo de participação.

## Domain

Novo Domain canônico planejado:

`forum_aculta_org`

Hostname:

`forum.aculta.org`

Alias Homelab:

`forum.aculta.toca.net.br`

Adicionar ao `DomainPurposeManager` somente na versão que implementar o Fórum:

```php
'forum' => 'forum_aculta_org',
```

Não hardcodar hostname em controllers.

## Front page

A intenção é usar a landing nativa do Forum como base da experiência pública.

Antes de configurar o front do Domain, confirmar a rota real fornecida pela
versão instalada. A documentação não deve assumir uma route name sem validar o
código instalado.

Não criar `ForumController` apenas para reconstruir a listagem nativa.

## Isolamento

Objetivo:

- Fórum público servido no FORUM purpose;
- administração continua MAIN;
- tópicos Forum têm canonical no FORUM;
- acesso direto ao mesmo tópico em host incorreto deve ser bloqueado conforme a
  política multidomínio;
- sessão compartilhada continua usando a mesma aplicação Drupal.

A implementação deve mapear rotas nativas Forum/Node/Comment antes de escrever
subscriber custom.

## Conta / Minha participação

O Fórum também deve aparecer dentro do painel ACCOUNT.

Isso não significa servir a experiência pública inteira do fórum no ACCOUNT.

A Conta deve agregar:

- meus tópicos;
- minhas respostas;
- tópicos acompanhados quando Flag for adotado;
- links para continuar a conversa no FORUM Domain;
- atividade resumida.

Preferir Views filtradas pelo usuário atual.

## Comentários

Core Comment continua fonte de verdade.

O Portal pode:

- exibir contagens;
- agregar respostas do usuário;
- criar links;
- fornecer moderação resumida no admin hub;
- integrar notificações futuras.

Não duplicar comentários.

## Notificações

Candidato:

`drupal/comment_notify:^1.5`.

Só habilitar depois de validar:

- política de envio;
- SMTP;
- opt-in/opt-out;
- privacidade;
- unsubscribe;
- carga de e-mail.

## Acompanhar/favoritar

Candidato:

`drupal/flag:^5.1`.

Flag deve ser preferido a storage custom para:

- acompanhar tópico;
- favoritos;
- outras marcações por usuário aprovadas.

## Busca

A versão inicial do Fórum pode usar a navegação nativa.

Busca integrada entra com Search API em versão posterior.

## Moderação

A moderação deve reutilizar permissões, Comment, Node, Views e ferramentas
administrativas existentes.

O Portal Admin pode servir como hub de acesso/resumo, mas não deve implementar
um segundo CRUD de moderação.

## Primeira versão funcional: Portal 0.11.0

Escopo:

1. instalar Forum;
2. habilitar módulos necessários;
3. criar Domain FORUM;
4. criar alias Homelab;
5. integrar purpose;
6. definir front;
7. aplicar isolamento;
8. testar tópico e resposta;
9. provar login compartilhado;
10. documentar config exportada.

Não incluir ainda:

- Flag;
- Comment Notify;
- Search API;
- hub completo de participação.

Esses itens entram em versões posteriores.

## Gates da 0.11.0

- Composer validate PASS;
- Composer audit PASS quando rede disponível;
- config import/export limpo;
- Forum host 200;
- criação de tópico por usuário autorizado PASS;
- criação de resposta PASS;
- acesso anônimo conforme permissões PASS;
- host incorreto não serve conteúdo especializado;
- ACCOUNT continua funcional;
- logout global continua funcional;
- User A/User B conforme regras públicas/privadas;
- SQLite PASS;
- compatibilidade MariaDB não quebrada no código custom;
- documentação atualizada.
