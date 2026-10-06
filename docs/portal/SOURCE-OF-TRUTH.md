# Fontes de verdade

O Portal agrega experiências; ele não deve se tornar dono acidental dos dados
dos módulos que integra.

| Capacidade | Fonte de verdade | Implementação | Papel do Portal | Purpose | Storage próprio do Portal |
| --- | --- | --- | --- | --- | --- |
| Login/conta | Drupal User | Core User | UX, rotas e shell | ACCOUNT | Não |
| Dados pessoais | Profile | Profile | formulário integrado | ACCOUNT | Não |
| Endereço | Profile customer + Address | Profile + Address | central de dados e UX | ACCOUNT | Não |
| CEP | Address preenchido via ViaCEP | CEP Autocomplete | acessibilidade e integração | ACCOUNT | Não |
| Foto | User/file/image/crop | Core + Crop/Image Widget Crop | editor integrado | ACCOUNT | Não |
| E-mail | Drupal User | Change Mail + Email Confirmer | fluxo integrado | ACCOUNT | Não |
| OAuth | contas Social Auth | Social Auth | conexões no painel | ACCOUNT | Não |
| Apoio | Commerce Order/Item | Commerce + Donation Flow | resumo e entrada do fluxo | SUPPORT/ACCOUNT | Não |
| Pagamento | Commerce Payment | Commerce + gateway | status/segurança | SUPPORT/ACCOUNT | Não |
| Cursos | Group LMS Course | Drupal LMS | catálogo/resumo | COURSES/ACCOUNT | Não |
| Matrícula | Group membership | Group | leitura/apresentação | COURSES/ACCOUNT | Não |
| Progresso | LMS Course Status | Drupal LMS | leitura/apresentação | COURSES/ACCOUNT | Não |
| Wiki | Node wiki_entry | Core Node | landing/participação | WIKI/ACCOUNT | Não |
| Categorias Wiki | Taxonomy | Core Taxonomy | navegação | WIKI | Não |
| Revisões Wiki | Node revisions/Diff | Core + Diff | atividade/links | WIKI/ACCOUNT | Não |
| Fórum | Forum topic Node | Forum + Node | integração/domain/painel | FORUM/ACCOUNT | Não |
| Respostas Fórum | Comment | Core Comment | participação/moderação | FORUM/ACCOUNT | Não |
| Estrutura Fórum | Taxonomy | Forum + Taxonomy | navegação/domain | FORUM | Não |
| Busca | índice Search API | Search API + Views | composição/domain | WIKI/FORUM | Não |
| Favoritos/seguir | Flag entities | Flag | controles/resumos | ACCOUNT/FORUM | Não |
| Notificação de comentários | Comment Notify | Comment Notify | preferências/links | ACCOUNT/FORUM | Não |
| Editorial | Nodes/Taxonomy | Core + Views | integração de links | MAGAZINE | Não |
| Loja | Commerce | Commerce | integração/domain | SHOP | Não |
| Host/contexto | Domain entities | Domain suite | purpose/URLs/isolation | todos | Não |

## Regra de gravação

Se uma feature precisa gravar estado, gravar através da API da fonte de verdade.

Exemplos:

- endereço -> Profile/Address;
- tópico -> Forum/Node;
- resposta -> Comment;
- matrícula -> Group/LMS;
- apoio -> Commerce;
- follow/bookmark -> Flag.

Nunca criar uma tabela `aculta_portal_*` apenas para espelhar esses dados.

## Regra de leitura

Preferência:

1. serviço público do módulo;
2. Entity API;
3. Views;
4. plugin/API específica do módulo;
5. Database API apenas quando não existir API pública adequada.

Consultas diretas a tabelas contrib exigem justificativa documental.
