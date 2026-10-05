# Multidomínio

Purposes estáveis: `main`, `account`, `support`, `magazine`, `wiki`, `shop`, `courses`.

Código customizado depende do purpose, não do hostname de ambiente. `DomainPurposeManager` centraliza Domain ID, URL correta, canonical de produção e purpose atual. Rotas podem declarar `_aculta_domain_purpose`; o subscriber central bloqueia hosts incorretos.

A sessão Drupal é compartilhada entre subdomínios da mesma raiz. Portanto todos os hosts que recebem o cookie compartilhado pertencem ao mesmo trust boundary. Canonicals usam produção; aliases Homelab não viram canonical.
