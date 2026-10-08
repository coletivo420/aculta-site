# Camadas anti-regressão

Data da revisão: 2026-10-07.

Este documento consolida regras duráveis que antes estavam espalhadas por
snapshots de fases e runbooks históricos.

## Arquitetura e ownership

- Drupal Core/contrib são a fonte de verdade das capacidades que já fornecem.
- `aculta_portal` integra/orquestra; não cria storage paralelo sem necessidade.
- o tema funcional `aculta420` é apresentação; não decide autenticação, access,
  Domain, Commerce, LMS ou persistência.
- SDC recebe dados preparados; não consulta storage/serviços/entidades diretamente.
- não criar segunda suíte de design system concorrente ao Bootstrap5 + ACULTA420.

## Dependency Injection e hooks

- preferir DI explícita e hooks OOP do Drupal 11 em código novo/refatorado;
- não introduzir novo service locator `\Drupal::...` em classes onde DI cabe;
- hooks do ACULTA420 vivem em `src/Hook/` com `#[Hook]`; não recriar arquivo `.theme` procedural;
- callbacks procedurais registrados nominalmente pelo Form API podem permanecer
  procedurais enquanto o contrato exigir o nome da função;
- refactor de hook não pode alterar comportamento/access/cache como efeito colateral.

## Domain

- URLs e hosts especializados são resolvidos por purpose/Domain, nunca por
  hostname hardcoded;
- wrong-host deve falhar conforme a política definida, normalmente 404;
- canonical público usa `*.aculta.org`; aliases Homelab não viram canonical;
- geração de URL não pode mutar persistentemente a entidade Domain;
- FORUM só entra no mapa quando sua feature/configuração forem realmente ativadas;
- apresentação por subdomínio usa **Domain purpose**, nunca hostname, como chave;
- `DomainPurposeManager` permanece no `aculta_portal`; o tema não replica nem move
  essa resolução.

## Autenticação

- Drupal User continua fonte de verdade para conta, senha, sessão e status;
- Social Auth continua fonte dos vínculos OAuth;
- Google OAuth não é um submit alternativo de formulário Drupal;
- `username_enumeration_prevention` permanece no baseline;
- `user_registrationpassword` foi removido e não deve ser reintroduzido enquanto
  houver conflito de responsabilidade;
- Turnstile é o único challenge CAPTCHA e falha fechado;
- não criar fallback para Math CAPTCHA/reCAPTCHA/outro challenge;
- usuário `authenticated` usa `skip CAPTCHA`; anônimo protegido vê Turnstile;
- segredos OAuth/Turnstile/SMTP não entram no Git.
- ACULTA420 só apresenta formulários e estruturas de autenticação com classes semânticas neutras;
- Social Auth, disponibilidade de providers, destinos/callbacks OAuth, account linking e política CAPTCHA pertencem ao `aculta_portal`;
- Core/contrib implementa protocolos e providers; credenciais pertencem à infraestrutura e chegam por Key/environment;
- é proibido consultar Social Auth config, construir rotas OAuth, escolher CAPTCHA ou implementar Turnstile no tema/Twig;
- uma integração nova segue: Core/contrib → Portal → contrato neutro de apresentação → ACULTA420.

## E-mail e verificação de conta

- Email Confirmer/Change Mail são o baseline enquanto atenderem ao projeto;
- ferramenta própria de verificação só nasce se não houver alternativa
  Core/contrib adequada;
- nunca manter dois sistemas concorrentes de confirmação;
- futura solução deve cobrir contas não-OAuth e mudança de e-mail com token
  único/expirável, flood control e proteção contra enumeração.

## AJAX e Conta

- AJAX é progressive enhancement; rotas normais/full-page continuam válidas;
- não transformar a Conta em SPA paralela;
- OAuth, checkout/pagamento e confirmação externa de e-mail ficam fora de AJAX genérico;
- CEP mantém integração específica enquanto upstream for fonte;
- VVJT pode ser usado em Views específicas, não como engine da navegação da Conta;
- remover infraestrutura de `account-navigation.js` apenas por fluxo e após
  paridade de URL/history/focus/a11y/behaviors/fallback.

## Commerce

- Commerce é fonte de verdade para order/payment/checkout;
- não criar ledger paralelo de apoio;
- validar entity access antes de expor itens, totais ou pagamentos;
- credenciais Mercado Pago nunca entram em config versionada;
- não remover hardening custom do gateway sem substituto comprovado.

## LMS / Group

- Group/LMS são fontes de matrícula, membership, progresso e avaliação;
- não copiar esses estados para User/Profile como storage primário;
- links de curso respeitam purpose COURSES;
- presenter/SDC só apresenta dados autorizados.

## Editorial / Wiki

- Wiki continua Node + Taxonomy + Views + revisions/workflow;
- não substituir Views/entidades por storage custom;
- acesso a unpublished/revisions precisa preceder exposição de metadata;
- busca antiga só é removida depois de substituto com paridade funcional.

## Tema e CSS

- o único provider público do tema é `aculta420`; não manter alias, shim ou provider legado de compatibilidade;
- libraries usam `aculta420/*` e SDCs usam `aculta420:*`;
- o tema não chama services/classes de `aculta_portal`; o Portal prepara contexto e o tema apresenta;
- logo, título, menu e accent por domain devem chegar ao tema como contexto de
  apresentação já resolvido pelo Portal; nunca escolher por hostname em Twig/PHP do tema;
- nunca passar entidade `Domain` diretamente para Twig/SDC;
- branding específico de purpose é opcional: fallback ACULTA e, depois, título textual;
- todos os purposes compartilham a mesma arquitetura de shell; variam dados, não um
  header paralelo por subdomínio;
- light/dark/auto troca tokens e assets compatíveis, não geometria ou markup do shell;
- light e dark são o mesmo ACULTA420; muda a luz, não a arquitetura;
- color mode no ACULTA420 é uma variação de tokens, não uma variação de layout;
- componentes não conhecem o color mode; seletores dark são permitidos somente em `aculta420/css/tokens.css`;
- o gate reconhece seletores dark em pseudo-classes funcionais, operadores de atributo CSS e flags `i`/`s`, inclusive em `tokens.css`; analisa somente predicados de ternário Twig (também parenthesizados), labels de `switch` PHP aninhado/`endswitch`, condições de `match`, e condições `if`/labels `switch` JavaScript balanceados; resultado textual e comentários não devem gerar finding;
- o gate detecta escritas `dataset` e `setAttribute()` em atributos de modo mesmo com valor dinâmico; valida mapeamentos semânticos de cores base Bootstrap e borda translúcida em light/dark, tokens RGB e pares, rejeita alpha inválido, aliases, ciclos, duplicatas e referências ausentes, e calcula contraste com alpha composto; representações numericamente equivalentes são aceitas, mas mudar cor e RGB juntos não contorna o contrato;
- testes do gate devem incluir regressões negativas e exemplos positivos para cada sintaxe suportada, sem tocar no Runtime; roots absolutos POSIX, Windows drive-letter e UNC são válidos, mas traversal `..` é rejeitado;
- uma variante futura de asset de logo por modo preserva espaço, dimensões e layout;
- novos componentes consomem semantic tokens quando a função já existe; palette primitives ficam centralizadas;
- purpose não define cor no tema e nunca é inferido por hostname;
- não adicionar color-mode JS, seletor ou persistência antes da fase prevista;
- não criar SDC apenas para encapsular classe Bootstrap simples sem contrato reutilizável comprovado;
- não criar segunda engine de navegação quando Bootstrap já fornece Collapse/Offcanvas;
- Bootstrap5 continua infraestrutura estrutural/comportamental;
- não reimplementar behavior Bootstrap/VVJ;
- não converter Twig em massa para SDC;
- não manter `css/style.css`, `css/responsive.css` ou outro catch-all residual no tema; CSS deve ter ownership explícito;
- mover CSS para SDC apenas quando ownership exclusivo do componente estiver comprovado;
- regra antiga só sai após paridade visual, mobile, teclado/foco, AJAX
  reattachment e fallback;
- CSS de admin/diagnóstico pode permanecer no módulo.
- CSS de autenticação pode estilizar formulários Core e classes neutras do Portal, como `.aculta-login__divider` e `.aculta-auth-provider`; não pode depender de seletores internos de Social Auth, Google ou CAPTCHA/Turnstile.

## Configuração e segredos

- Configuration Sync representa configuração aprovada, não um dump cego do Runtime;
- não executar/exportar config em massa apenas para “limpar drift” sem classificar paths;
- segredos permanecem em environment/Key;
- não versionar credenciais reais ou de teste;
- alterações de módulos/config exigem documentação correspondente.
- block placements do ACULTA420 devem usar região declarada e manter `plugin`,
  `settings.id` e a dependência de conteúdo alinhados ao UUID existente.

## Lifecycle / updates

- update hooks históricos não são reescritos só para “limpar” o arquivo;
- mudança em `.install` exige fresh-install e upgrade-path tests;
- migrações de dados devem ser idempotentes/resumíveis quando aplicável;
- não remover update de migração crítica sem provar caminho de upgrade.

## Assets

- ausência de referência por grep não prova que um asset é morto;
- coleções grandes só são removidas após inventário de Runtime/conteúdo;
- assets de conteúdo devem tender a Media/File/CDN, não payload PHP do módulo,
  quando houver migração segura.

## Cache, access e privacidade

- access vem antes de metadata privada;
- cacheability preserva contexts + tags + max-age necessários;
- não resolver performance de dados privados com cache compartilhado inseguro;
- testar User A/User B em Profile, Address, apoio, cursos e participação.

## Ambientes

- Homelab: Apache + PHP-FPM + SQLite;
- produção: Apache + PHP + MariaDB;
- Nginx não é baseline;
- custom SQL precisa preservar portabilidade SQLite/MariaDB;
- produção não é ambiente de experimento/desenvolvimento.
