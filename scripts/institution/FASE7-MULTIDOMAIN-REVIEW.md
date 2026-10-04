# Fase 7 — arquitetura multidomínio e limpeza do MAIN

Execução local, pré-deploy. Nenhuma implantação ou configuração externa foi
realizada. O backup do banco anterior a alterações de schema desta fase está
fora do Git em `C:\Users\PiradoPirata\aculta-backups\2026-10-04_1015_aculta_pre-fase7.sql`.

## Propósitos e domínios

| Purpose | Local | Produção | ID gerado pelo Domain 3.0.1 | Situação local |
| --- | --- | --- | --- | --- |
| MAIN | `aculta.test:8080` | `aculta.org` | `aculta_org` | entidade, alias e default configurados |
| ACCOUNT | `conta.aculta.test:8080` | `conta.aculta.org` | `conta_aculta_org` | entidade, alias e front page configurados |
| SUPPORT | `apoio.aculta.test:8080` | `apoio.aculta.org` | `apoio_aculta_org` | entidade, alias e front page configurados |
| MAGAZINE | `revista.aculta.test:8080` | `revista.aculta.org` | `revista_aculta_org` | entidade, alias e front page configurados |
| WIKI | `wiki.aculta.test:8080` | `wiki.aculta.org` | `wiki_aculta_org` | infraestrutura e alias configurados |
| SHOP | `loja.aculta.test:8080` | `loja.aculta.org` | `loja_aculta_org` | infraestrutura e alias configurados |
| COURSES | `cursos.aculta.test:8080` | `cursos.aculta.org` | `cursos_aculta_org` | infraestrutura e alias configurados |

`domain`, `domain_alias`, `domain_config` e `domain_source` estão habilitados;
`domain_access` não está habilitado. Os aliases locais incluem `:8080` e
usam environment `local`. O API gerou IDs a partir dos hosts canônicos; os IDs
não contêm `.test`. O teste local do condition plugin selecionou corretamente
os sete purposes quando cada Domain foi ativado pela API.

## Limpeza do MAIN

**Status: PARCIAL.** MAIN permanece o site institucional. As páginas,
projetos, documento institucional e atividades existentes continuam no banco;
nenhum Node foi apagado ou duplicado. A separação usa `field_domain_source` e
restrição central por host.

| Item | Tipo / quantidade | Purpose e host | Ação e risco |
| --- | --- | --- | --- |
| `page` | Content type, 9 Nodes; inclui a página com alias `/noticias` | MAIN, exceto a página de entrada editorial que tem source MAGAZINE | mantidos os Nodes; a rota canônica é limitada pelo source |
| `project` | 4 Nodes | MAIN | mantidos como apresentação institucional |
| `document` | 1 Node | MAIN | mantido |
| `activity` | 0 Nodes | MAIN | bundle reservado |
| `article` (“Notícia”) | 0 Nodes | MAGAZINE | padrão `field_domain_source` MAGAZINE; alias Pathauto existente `/noticias/[node:title]` preservado |
| `editorial_highlight` | 3 Nodes | MAGAZINE | permanecem únicos; placement de teasers duplicados na Home MAIN desativado, sem apagar Nodes ou View |
| Observatório da Maconha | nenhum bundle/conteúdo identificável na auditoria | MAGAZINE quando modelado | não criado nem duplicado; pendente de modelagem/publicação editorial |
| autores/categorias editoriais | vocabulários `editorial_author` (1 termo) e `editorial_category` (0 termos) | MAGAZINE | termos não duplicados; rotas canônicas limitadas ao purpose MAGAZINE |

Views `aculta_news`, `home_editorial_highlights` e `aculta_related_news` têm
somente display default e block, sem display page. `aculta_news` é visível
somente no path `/noticias` e em MAGAZINE. `home_editorial_highlights` não
está colocado em nenhuma Home após a revisão de redundância. Blocos
institucionais de Home, projetos, atividades, contato e transparência usam
purpose MAIN. `aculta_related_news` não tem block placement exportado nesta
configuração. Os Webforms
`aculta_contact` e `aculta_participation` continuam classificados como MAIN.

Itens de menu para Notícias e Apoie são reescritos pelo tema/module hook para
MAGAZINE e SUPPORT; destinos de identidade são reescritos para ACCOUNT. A
Home MAIN conserva seu conteúdo institucional, sem teaser editorial duplicado. A rota pública
SUPPORT `/apoie` existe apenas em SUPPORT. A rota privada `/apoio` permanece
em ACCOUNT. A rota antiga `/apoie` em MAIN respondeu 404 no servidor em uso.
Rotas antigas de conta também foram substituídas pelas rotas da central
ACCOUNT; detalhes de access e path estão no inventário abaixo.

### SEO, sitemap e links

O `field_domain_source` e a URL de canonical editorial usam o resolvedor
central; os tokens Schema de canonical e imagem não fixam mais `aculta.org`.
O canonical institucional da Home e o identificador da Organização continuam
em MAIN. Pathauto editorial existente foi mantido. Não foi encontrado link
absoluto antigo em texto publicado na amostra varrida; links internos em menus
conhecidos são gerados para o host correto.

`simple_sitemap.custom_links.default` não anuncia mais `/apoie` no sitemap MAIN.
O módulo está usando um único sitemap/configuração compartilhada e o
`base_url` canônico ainda é MAIN. Não foi comprovada geração de índices
separados por domínio nem a exclusão completa de URLs MAGAZINE desse arquivo.
**Sitemap multidomínio continua pendente e impede declarar SEO por host como
concluído.** Não foi criado mecanismo paralelo nem feita chamada externa ao
sitemap.

## Rotas principais

| Função | Route name | Purpose | Path atual/canônico | Antigo / observação |
| --- | --- | --- | --- | --- |
| Login | `user.login` | ACCOUNT | `/entrar` | `/user/login` removido pelo route subscriber |
| Cadastro | `user.register` | ACCOUNT | `/criar-conta` | `/user/register` removido; página informativa visível sem formulário público; `admin_only` mantido |
| Recuperação | `user.pass` | ACCOUNT | `/recuperar-senha` | `/user/password` removido |
| Reset | rota Core preservada | ACCOUNT | `/recuperar-acesso/{uid}/{timestamp}/{hash}` e variantes Core | assinatura Core preservada |
| Confirmação | route Email Confirmer preservada | ACCOUNT | `/confirmar-email/{email_confirmer_confirmation}/{hash}` | confirmação nativa, sem token custom |
| Logout | `user.logout` | ACCOUNT | `/sair` | controller/CSRF Core preservados |
| Visão geral | `aculta_portal.dashboard` | ACCOUNT | raiz via Domain Config, rota interna `/conta-interna` | UI `/minha-conta` retirada |
| Histórico de apoio | `aculta_portal.support_my` | ACCOUNT | `/apoio` | dados Commerce do usuário atual |
| Dados/endereço/conexões/segurança | rotas `aculta_portal.*` | ACCOUNT | `/dados`, `/dados/endereco`, `/conexoes`, `/seguranca` | sem formulário genérico de usuário |
| Apoio público | `aculta_portal.support_form` | SUPPORT | `/apoie`, front page exposta na raiz | `/apoie` em MAIN dá 404 |
| Notícias | Node `/noticias` e View block `aculta_news` | MAGAZINE | `/noticias` | `/noticias` em MAIN dá 404 pelo source |
| OAuth Google | Social Auth start/callback | ACCOUNT | `/acesso/{network}` e `/acesso/{network}/retorno` | callback real não foi testado sem credenciais |
| Commerce notify | `commerce_payment.notify` | MAIN técnico | `/integracoes/pagamentos/{commerce_payment_gateway}/notificacao` | gateway continua desabilitado; antigo `/payment/notify/*` não está no router novo |
| Admin | rotas Core/contrib | MAIN | `/painel-administrativo` | `/admin` renomeado; HTTP cross-host aguarda servidor atualizado |

## Segurança e ambiente local

- Trusted hosts local: sete nomes `.test` explícitos, mais localhost/loopback
  usados pelas ferramentas locais. O template de produção lista sete nomes
  `.org` explícitos; não há wildcard de subdomínio.
- Cookie local: `.aculta.test`, SameSite Lax e parâmetros Core preservados.
  Os sete Domain entities produziram os sete aliases locais. Secure em HTTP
  local não foi forçado. UID idêntico e logout entre hosts não foram provados
  por HTTP.
- `scripts/local/configure-aculta-hosts.ps1` está sintaticamente válido e
  recusa alteração sem administrador. Não foi possível elevá-lo nesta sessão;
  hosts/DNS local permanecem pendentes. O comando manual é
  `powershell -ExecutionPolicy Bypass -File .\scripts\local\configure-aculta-hosts.ps1`.
- `scripts/local/start-aculta-multidomain.ps1` usa `web/.ht.router.php`,
  bind `127.0.0.1:8080`, e lista os sete hosts. A porta já estava em uso antes
  do trabalho; o processo não foi encerrado para evitar interromper o servidor
  que já estava aberto. Assim, requests HTTP feitos durante a execução usaram
  seu container antigo e não validam as mudanças de rotas/subscribers.
- `drush domain:list` chama `checkResponse()` nos hosts canônicos e retornou
  `500 - No server`; a chamada foi executada durante auditorias locais e não
  alterou dados ou configurações. Não usar esse comando para validar hosts.
- SMTP, Google e credenciais reais Mercado Pago/Turnstile não foram usados.
  Gateway Mercado Pago disabled; Store 1, Orders 0, Payments 0.
- Domain Access não foi ativado e nenhum usuário recebeu permissão
  administrativa adicional.
- O nível Drupal `verbose` permanece no config versionado
  (`config/sync/system.logging.yml`) e também no override local solicitado.

## Verificação

PASS: `drush cr`, `drush updatedb:status`, `drush config:status` (sem
diferenças após export), `composer validate --no-check-publish` (válido, com
avisos preexistentes de constraints exatas), `composer audit --no-dev` (sem
advisories), PHP lint dos arquivos customizados, parsing de 694 arquivos YAML,
`git diff --check`, registro e avaliação dos sete purposes, contagens Commerce
e estado do gateway. Testes diretos do subscriber retornaram o comportamento
esperado para `/seguranca` nos sete hosts, para conteúdo editorial em
MAIN/MAGAZINE, para conteúdo institucional em MAIN/MAGAZINE e para o termo de
autor editorial somente em MAGAZINE. Após reconstruir o cache, HTTP local
retornou 200 para MAIN, `/entrar`, `/criar-conta`, `/recuperar-senha`, Revista
e Apoio; `/entrar` e `/user/login` em MAIN retornaram 404. A Home MAIN não
renderiza mais destaques editoriais; Revista e Apoio não renderizam os blocos
institucionais de “Quem Somos”, projetos e transparência. O link anônimo
“Entrar” no header gera `http://conta.aculta.test:8080/entrar`. Cadastro
continua `admin_only` e `/criar-conta` apresenta somente estado informativo.
A geração local dos links de menu
“Notícias” e “Apoie” foi verificada como
`http://revista.aculta.test:8080/noticias` e
`http://apoio.aculta.test:8080/`; a geração canônica de editorial usa
`revista.aculta.org` fora do ambiente local.

O scan de credenciais encontrou somente nomes de variáveis de ambiente vazios
em `scripts/validate-portal-commerce-security.php`; nenhum valor de segredo foi
identificado nos arquivos de configuração/código revisados.

PENDENTE: atualização do arquivo Windows hosts (elevação), teste HTTP real
com servidor reiniciado, login/UID/logout cross-host, sessão compartilhada
empírica, login/callback Google sem credenciais, sitemap separado por host,
varredura visual/manual e execução completa do Security Review (a chamada
ficou sem output por mais de 40 segundos e foi interrompida; nenhum resultado
foi presumido).

O comando de Security Review não foi configurado para persistir ou registrar
resultados; ao não terminar, não há relatório confiável a classificar como
PASS/WARN/FAIL.
