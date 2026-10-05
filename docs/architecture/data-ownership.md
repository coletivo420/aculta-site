# Responsabilidade pelos dados

| Capacidade | Fonte de verdade | Portal |
| --- | --- | --- |
| Login, senha, e-mail | Drupal User | UX e rotas |
| Perfil | Profile | composição |
| Endereço | Address/Profile | integração |
| OAuth | Social Auth | conexões |
| Pedidos/pagamentos | Commerce | apresentação |
| Apoio | Commerce Donation Flow | experiência |
| Cursos/progresso | Drupal LMS | apresentação |
| Matrículas | Group | apresentação |
| Wiki/editorial | Nodes/Taxonomy/Views | integração |
| Domínio | Domain | purpose/URLs/acesso |

Antes de criar storage customizado, identificar a API e a fonte de verdade existente. Se o conceito já pertence a um subsistema adotado, não duplicá-lo.
