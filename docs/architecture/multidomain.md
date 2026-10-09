# Multidomínio

Purposes estáveis atuais:

`main`, `account`, `support`, `magazine`, `wiki`, `shop`, `courses`.

Purpose planejado:

`forum`.

O purpose `forum` só passa a ser estável depois da implementação e validação
da feature de participação (ver `docs/portal/ROADMAP.md`, F3).

Código customizado depende do purpose, não do hostname de ambiente.
`DomainPurposeManager` centraliza Domain ID, URL correta, canonical de produção
e purpose atual. Rotas podem declarar `_aculta_domain_purpose`; o subscriber
central bloqueia hosts incorretos.

A mesma regra vale para apresentação: logo, título, navegação e outros dados de
branding específicos são resolvidos a partir do purpose pelo `aculta_portal` e
entregues ao tema como contexto simples. ACULTA420 não escolhe identidade por
hostname e não recebe entidade `Domain` em Twig/SDC. Ver o contrato planejado em
`web/themes/custom/aculta420/docs/shell.md`.

| Purpose | Produção | Homelab | Estado |
| --- | --- | --- | --- |
| main | aculta.org | aculta.toca.net.br | ativo |
| account | conta.aculta.org | conta.aculta.toca.net.br | ativo |
| support | apoio.aculta.org | apoio.aculta.toca.net.br | ativo |
| magazine | coletivo420.aculta.org | coletivo420.aculta.toca.net.br | ativo |
| wiki | wiki420.aculta.org | wiki420.aculta.toca.net.br | ativo |
| shop | loja.aculta.org | loja.aculta.toca.net.br | Domain ativo; catálogo futuro |
| courses | cursos.aculta.org | cursos.aculta.toca.net.br | ativo |
| forum | forum.aculta.org | forum.aculta.toca.net.br | planejado |

A sessão Drupal é compartilhada entre subdomínios da mesma raiz. Portanto todos
os hosts que recebem o cookie compartilhado pertencem ao mesmo trust boundary.

Canonicals usam produção; aliases Homelab não viram canonical.

Integrações de plataforma, como Google Analytics, devem distinguir
hostname/purpose sem introduzir snippets específicos em cada tema.

## Página de apoio (SUPPORT)

Informado pelo responsável do projeto: a página de apoio fica a cargo do subdomínio
da plataforma de doações, purpose `support`. Homelab: `apoio.aculta.toca.net.br`;
produção: `apoio.aculta.org`.

- O host principal (`main`) não serve a página de apoio. Rotas de apoio no host
  principal não são referência de validação.
- A página pertence ao purpose `support`. O fluxo de carrinho, checkout e pagamento
  continua em `main` (ver `docs/portal/PAYMENT-DOMAIN-POLICY.md`).
- Rota raiz: `config/sync/domain/apoio_aculta_org/system.site.yml` define `front: /apoie`.
  Portanto, no host de apoio, `/` e `/apoie` são a mesma página inicial.
- Grafia da rota: a configuração e o validador de navegador usam `/apoie`; o responsável
  indicou `/apoio`. Confirmar antes de fixar a rota em validadores.
