# ACULTA - Instruções para agentes

## Projeto

Este repositório contém o site institucional da:

ACULTA - Associação Cultural Antiproibicionista

Domínio de produção:

aculta.org

O site é institucional e pertence a uma associação cultural antiproibicionista sem fins lucrativos.

O objetivo inicial é construir um site institucional profissional, acessível, seguro, rápido e adequado à validação institucional do domínio pela Google for Nonprofits.

## Stack

- Drupal 11
- PHP 8.5
- Apache como baseline definitivo de servidor web
- PHP-FPM no Homelab
- SQLite para o Runtime de desenvolvimento e Estados versionados
- MariaDB em produção
- Composer
- Drush
- Git
- GitHub
- Codex CLI

Ambiente de desenvolvimento principal:

Debian Homelab + Apache + PHP-FPM + SQLite

Produção:

Hostinger + Apache + PHP + MariaDB

Document root do Drupal:

web/

## Estrutura do desenvolvimento customizado

Tema customizado:

web/themes/custom/aculta420/

Módulos customizados:

web/modules/custom/

Configuração exportável do Drupal:

config/sync/

## Padrão Drupal 11+ do ACULTA Portal

O módulo `web/modules/custom/aculta_portal` adota **Drupal Core 11.3+ como baseline arquitetural**.

Este padrão é obrigatório para:
- desenvolvedores humanos;
- Codex;
- ChatGPT e outros agentes;
- demais ferramentas de IA que produzam ou revisem código neste repositório.

A referência normativa é `docs/portal/DRUPAL-11-STANDARDS.md`. Antes de alterar o Portal, leia esse documento e preserve as fronteiras Core/contrib → `aculta_portal` → contrato neutro → ACULTA420.

Regras resumidas:
- Core/contrib continuam fonte de verdade; não criar storage paralelo quando a capacidade já existir;
- preferir dependency injection em classes; não introduzir novos service locators `\Drupal::...` em `src/`;
- hooks runtime novos/refatorados usam OOP `#[Hook]` quando suportado pelo Core instalado; lifecycle permanece procedural quando exigido;
- tratar como deprecada no projeto qualquer prática runtime legada contrária ao padrão moderno Drupal 11+ (por exemplo `#[FormAlter]`, hook procedural migrável, novo callback global ou novo service locator), mesmo quando ainda tolerada por compatibilidade; exceções precisam de evidência da API upstream;
- EntityQuery declara `accessCheck(TRUE|FALSE)` conscientemente;
- Form API em Drupal 11.3+ prefere callbacks resolvidos pelo `CallableResolver` e serviços DI;
- subscribers usam `EventSubscriberInterface` e o tag `event_subscriber`; prioridades funcionais não mudam por estética;
- Render API, access e cacheability fazem parte do contrato funcional;
- nenhuma mudança é chamada de “Drupal 12/13 ready” sem verificar Core, change records e módulos contrib;
- execute `php scripts/validate-aculta-portal-drupal11.php` em mudanças do Portal e elimine, não expanda, a dívida técnica registrada pelo gate.

A documentação deve registrar o motivo arquitetural das regras, não apenas sua forma.

## Servidor web

Apache é o baseline definitivo do ACULTA no Homelab e em produção.

- Não introduzir novos exemplos, regras ou dependências específicas de Nginx.
- Não manter compatibilidade com Nginx como requisito do projeto.
- Preservar o `web/.htaccess` do Drupal e validar rewrites/headers/proteção de arquivos em Apache.
- No Homelab, validar `mod_rewrite`, `mod_headers` e a integração PHP-FPM por `proxy_fcgi`.
- VirtualHosts, caminhos, usuários, certificados e configuração Virtualmin/Hostinger continuam específicos de cada ambiente.
- Referências a Nginx em relatórios antigos são históricas e não definem a arquitetura atual.

## Regras fundamentais

1. Nunca modificar arquivos do Drupal Core diretamente.

2. Nunca modificar código dentro de:

web/core/

3. Nunca modificar diretamente módulos contrib:

web/modules/contrib/

4. Nunca modificar diretamente temas contrib:

web/themes/contrib/

5. Todo desenvolvimento específico da ACULTA deve ficar preferencialmente em:

web/themes/custom/aculta420/

ou:

web/modules/custom/

6. Dependências PHP devem ser instaladas e gerenciadas pelo Composer.

7. Não adicionar vendor/ ao Git.

8. Nunca versionar:

web/sites/default/settings.php

9. Nunca inserir no código:

- senhas
- credenciais de banco
- tokens
- API keys
- segredos
- credenciais da Hostinger
- credenciais do GitHub

10. Não remover ou sobrescrever regras de segurança existentes no .gitignore sem necessidade explícita.

11. Manter compatibilidade com Drupal 11 e PHP 8.5.

12. Priorizar APIs oficiais e padrões do Drupal.

13. Evitar módulos adicionais quando a funcionalidade puder ser implementada adequadamente com Drupal Core.

14. Antes de adicionar uma dependência Composer, explicar sua necessidade.

15. Não executar alterações destrutivas no banco de dados sem autorização explícita.

16. Não apagar conteúdo existente sem autorização.

17. Fazer alterações incrementais e fáceis de revisar no Git.

## Git

Branch principal:

main

Repositório:

coletivo420/aculta-site

Antes de alterações significativas, verificar:

git status

Não fazer force push.

Não reescrever o histórico da branch main.

Não fazer commit de credenciais.

## Drush

No Homelab Debian, usar o Drush instalado pelo Composer:

```sh
php vendor/drush/drush/drush.php status
php vendor/drush/drush/drush.php cr
```

Em uma workstation Windows opcional, o equivalente é:

```powershell
php .\vendor\drush\drush\drush.php status
php .\vendor\drush\drush\drush.php cr
```

## Identidade institucional

Nome:

ACULTA - Associação Cultural Antiproibicionista

A ACULTA é uma associação cultural antiproibicionista.

O site deve comunicar de forma clara:

- identidade institucional
- finalidade da associação
- atuação cultural
- defesa dos direitos humanos
- debate público sobre políticas de drogas
- projetos e atividades
- transparência institucional
- formas de contato

Evitar linguagem que faça o site parecer uma loja, dispensário ou plataforma comercial de cannabis.

## Arquitetura inicial do site

### Home

Apresentação clara da ACULTA e de sua atuação.

### Institucional

- Quem somos
- Missão e objetivos
- Organização

### Atividades

Apresentação das atividades realizadas pela associação.

### Projetos

Projetos culturais e sociais da ACULTA.

### Notícias

Notícias, artigos e atualizações institucionais.

### Transparência

Área destinada a documentos institucionais, incluindo quando disponíveis:

- Estatuto
- CNPJ
- Atas
- Relatórios
- documentos institucionais

### Contato

Informações oficiais de contato.

### Política de Privacidade

Página com política de privacidade do site.

## Google for Nonprofits

Uma das prioridades iniciais do projeto é permitir que aculta.org represente claramente uma organização real e identificável.

O site deve apresentar claramente:

- nome da organização
- missão
- atividades
- projetos
- informações institucionais
- informações de contato
- transparência
- domínio institucional

Não criar afirmações institucionais ou jurídicas sem fonte fornecida pelo responsável pelo projeto.

Não inventar:

- CNPJ
- endereço
- nomes de dirigentes
- datas
- números de registro
- parceiros
- financiadores
- certificações

Quando uma informação institucional necessária não estiver disponível, utilizar placeholder claramente identificado ou solicitar a informação.

## Front-end

Tema público atual:

ACULTA420

Machine name:

aculta420

Provider funcional:

web/themes/custom/aculta420/

Não recriar provider, alias, shim ou camada de compatibilidade de tema com machine name diferente de `aculta420`.

Prioridades:

- mobile first
- responsividade
- acessibilidade
- HTML semântico
- boa performance
- CSS organizado
- JavaScript mínimo
- compatibilidade com Drupal
- SEO técnico
- facilidade de manutenção

Evitar page builders pesados.

Evitar dependências JavaScript desnecessárias.

## Segurança

Nunca expor mensagens de erro detalhadas em produção.

Nunca colocar credenciais no repositório.

Não alterar configurações de segurança sem explicar a mudança.

O ambiente local pode utilizar configurações de desenvolvimento diferentes das configurações de produção.

Credenciais de integrações devem usar Drupal Key com provider `env`; nunca
preencher segredo diretamente em config, exportar configuração com segredo,
mover credenciais para o tema ou hardcodar caminhos Homelab/Hostinger no
Portal. Adicionar credencial exige Key, nome de variável, atualização do
ACULTA Secrets Contract, gate anti-regressão e provisioning por ambiente.

## Bancos e Sistema de Estados

- Desenvolvimento usa `var/database/aculta-runtime.sqlite`, uma cópia mutável restaurada de `estados/`.
- `estados/*.sqlite` são snapshots imutáveis e integrais; não são sanitizados.
- Por decisão explícita do projeto, Estados integrais podem ser versionados neste repositório público. Nunca adicionar deliberadamente senhas, API keys, tokens de serviços externos ou credenciais de produção ao Runtime/Estado.
- Produção continua usando MariaDB. Nunca implantar `estados/*.sqlite` nem apontar produção para o Runtime.
- Não editar um Estado imutável. Mudanças operacionais depois do restore pertencem somente ao Runtime.
- Código custom deve usar APIs Drupal e permanecer compatível com SQLite e MariaDB. SQL específico exige justificativa.
- `web/sites/default/settings.local.php` e configurações locais permanecem fora do Git. Credenciais MariaDB de produção nunca entram em settings versionados.
- Existe intenção futura de migrar o Runtime do Homelab para MariaDB quando o BDTGN estiver maduro para a integração. Até essa decisão ser executada, SQLite continua sendo a fonte operacional do Homelab e os Estados continuam snapshots SQLite.

## Integrações Google e serviços externos

- Consultar `docs/integrations/GOOGLE.md` antes de qualquer integração Google.
- Não inserir Google Tag, Analytics, Search Console verification, OAuth ou Classroom diretamente em Twig/JS do tema.
- Credenciais e secrets ficam em ambiente/Key; nunca no Git.
- Integrações devem poder permanecer desabilitadas sem quebrar o Drupal.
- Homelab não deve enviar telemetria real por padrão.
- Google Analytics/Tag exige revisão de consentimento e não pode enviar PII.
- Search Console deve preferir verificação de domínio/DNS quando possível.
- Google Classroom é integração futura; Drupal LMS continua fonte de verdade de cursos, matrícula e progresso.
- Não solicitar scopes OAuth que não correspondam a uma feature ativa e aprovada.
- Produtos Google for Nonprofits pós-aprovação não devem ser tratados como disponíveis antes da ativação real.

## Política de domínio administrativo

- o painel administrativo Drupal é único e pertence ao purpose `main`;
- `/painel-administrativo/**` em subdomínio não representa um segundo painel: GET/HEAD deve canonicalizar para MAIN usando `DomainPurposeManager` e Domain Alias do ambiente;
- nunca hardcodar `aculta.org` ou hostname Homelab para essa canonicalização;
- requests administrativos mutáveis no host errado não são redirecionados entre Domains; falham fechado;
- wrong-purpose público continua 404 e não deve ser convertido em redirect genérico;
- preservar a exceção técnica do reset Core que reutiliza `entity.user.edit_form` em ACCOUNT com token one-time válido;
- ACULTA420/Twig/JavaScript não participam dessa política; ela pertence ao `aculta_portal` + Core/contrib access.
- redirects intencionais entre purposes usam `DomainPurposeManager` + `Drupal\Core\Routing\TrustedRedirectResponse`; não usar `Symfony RedirectResponse` cru para target em outro host;
- não depender de empate de prioridade entre response subscribers quando a ordem afeta redirect safety;
- sessão compartilhada entre purposes deve ser validada via `session.storage.options.cookie_domain` do ambiente; baseline `cookie_samesite: Lax`;
- AJAX/fetch do Portal é same-origin; não habilitar CORS para transportar estado/formulários entre purposes;
- carrinho/checkout/pagamento Drupal Commerce pertencem sempre ao purpose `main`, independentemente de origem SHOP/COURSES/SUPPORT; usar `DomainRoutePolicy`, nunca listas paralelas de rotas ou zona transacional por subdomínio;
- futura UI de carrinho na barra multidomínio deve usar presenter no Portal baseado em `CartProviderInterface`, preservando cache context `cart` e gerando `commerce_cart.page` no MAIN via `DomainPurposeManager`; não reutilizar o Cart Block cru se ele mantiver URL relativa ao host corrente; ACULTA420 só apresenta URL/contagem/estado neutros;
- centralizar a UI do carrinho em MAIN não proíbe o `Add to cart` nativo em SHOP/COURSES; esse formulário continua usando o Commerce para atualizar a mesma `commerce_order` antes da navegação ao carrinho MAIN;
- links de entrada no checkout devem apontar diretamente a MAIN quando o Portal puder resolvê-los; POST/PUT/PATCH/DELETE de payment/checkout em host errado não são redirecionados;
- referências canônicas: `docs/portal/ADMIN-DOMAIN-POLICY.md`, `docs/portal/CROSS-DOMAIN-REQUEST-POLICY.md` e `docs/portal/PAYMENT-DOMAIN-POLICY.md`.

## Fronteira de autenticação e anti-bot

- ACULTA420 é dono somente da apresentação de login/formulários e de classes semânticas neutras;
- autenticação, Social Auth, disponibilidade de provider, destinos OAuth, callbacks complementares, account linking e política de CAPTCHA pertencem ao `aculta_portal`;
- Drupal Core/contrib mantém a implementação de protocolo, Social Auth, CAPTCHA e Turnstile;
- infraestrutura fornece credenciais via Drupal Key e environment; o tema não lê configuração de integração nem contém secrets;
- é proibido mover lógica OAuth para o tema, consultar configuração Social Auth em Twig/CSS/JS/PHP do tema, implementar CAPTCHA em Twig, adicionar dependências desses módulos ao tema ou colocar secrets nas settings do tema;
- integração nova segue: contrib/Core → Portal → contrato neutro de apresentação → ACULTA420.

## Tokens semânticos ACULTA420

- novos componentes usam semantic token existente para a função visual; não espalhar palette primitives para representar contexto;
- Light e dark são o mesmo ACULTA420; muda a luz, não a arquitetura.
- Color mode no ACULTA420 é uma variação de tokens, não uma variação de layout.
- modos compartilham DOM, markup, hierarquia, componentes, tipografia, espaçamento, dimensões, grid, breakpoints, posicionamento, shell, navegação e comportamento;
- somente semantic visual tokens variam; não criar seletores dark fora de `css/tokens.css`;
- validadores do tema devem reconhecer seletores dark alternativos inclusive dentro de pseudo-classes, operadores de atributo CSS que selecionem dark/light, flags `i`/`s` e regras em `tokens.css`; analisar recursivamente somente predicados de ternário Twig/JavaScript, labels PHP `switch` aninhados em ambas as sintaxes/`endswitch` e condições `match`, além de labels JavaScript `switch` e condições `if` balanceadas, sem tratar valores de resultado como branches;
- detectar writes simples/compostos de `dataset`, chamadas `setAttribute()` regulares/opcionais e atribuições `className` a classes de modo; switches aninhados não podem misturar labels de escopos distintos; comentários não devem causar falso positivo e mudanças de sintaxe exigem fixtures positivas e negativas;
- preservar o predicado externo de condições Twig com ternários, arms de objeto em ternários JavaScript, case labels encerrados por `;`, `?.`/`??` e regex literals durante o balanceamento; detectar `getColorScheme()`, classes compostas/protegidas e operadores de atributo que selecionem parcialmente `dark`/`light`; resolver a união de tokens ACULTA/Bootstrap em ambos os modos;
- o gate examina JavaScript inline em templates Twig, ignora regex literals em corpos de `switch`, reconhece `var()` CSS sem distinção de caixa e valida o RGB `--aculta-surface-page-rgb`; `tokens.css` contém somente os dois blocos de custom properties autorizados, sem regras CSS arbitrárias;
- examinar também style blocks Twig quanto a seletores e cores literais, inclusive propriedades `border-*-color`, e persistência color-mode nos scripts Twig inline; leituras/escritas de storage com chave de modo (métodos e propriedades), inicializadores explícitos, leituras `dataset`/`getAttribute`, mutações `classList` inclusive `replace`, `className` com strings/template literals e ternários com `;` dentro de strings devem ser detectados sem falsos positivos; scanners preservam delimitadores em strings/regex e comentários CSS só valem fora de strings; palavras que só aparecem em texto comum não são decisão de modo, statements CSS top-level em `tokens.css` falham;
- fora de `tokens.css`, o gate remove comentários CSS e rejeita hex, funções modernas de cor (`hwb`, `lab`, `lch`, `oklab`, `oklch`, `color()`, `color-mix()`, `device-cmyk()`) e nomes CSS em declarações color-bearing suportadas; não é parser completo de valores CSS e os fixtures definem esse escopo;
- validar mapeamentos Bootstrap de cor base e borda translúcida para fontes/valores aprovados separadamente em light/dark, além dos pares RGB e superfícies dark; alteração coordenada de cor e RGB não pode contornar o contrato; representações de cor numericamente equivalentes devem ser aceitas, slash-alpha moderno só aparece após os três canais, e sintaxe CSS legacy com vírgula não mistura alpha por barra;
- o contrato de superfície dark charcoal/graphite é explícito; resolver todos os tokens ACULTA/Bootstrap por modo, validar mappings Bootstrap light/dark e todos os pares RGB, rejeitar aliases/ciclos/referências ausentes/cores inválidas/duplicatas e calcular contraste com alpha composto;
- scripts PHP estáticos do tema devem aceitar roots absolutos POSIX e Windows (drive letter/UNC) sem traversal `..`, sem exigir Bash/WSL para executar;
- componente não conhece light, dark ou `prefers-color-scheme`; uma futura variante de asset de logo mantém espaço, dimensões e layout;
- não criar seletor, persistência ou JavaScript de modo antes da fase prevista;
- o tema nunca resolve Domain, hostname ou regra funcional; a apresentação por purpose chega por um único contrato `domain_presentation` preparado pelo Portal;
- a integração segue o Theme API moderno: módulo injeta em `#[Hook('preprocess_page')]`, tema consome depois em hook OOP; não criar novo `template_preprocess_*` legado;
- `domain_presentation` separa identidade escalar de renderables; valores para props permanecem simples e navigation/actions/brand media permanecem render arrays para futuros slots;
- Domain ID, hostname, aliases, `DomainInterface`, storage, negotiator e serviços do Portal não atravessam a fronteira para Twig/SDC;
- URLs, título, branding disponível, navegação e ações chegam já resolvidos; access/cache são acumulados no Portal via Render API/`CacheableMetadata` e devem borbulhar no render tree;
- o Portal nunca referencia `#component: aculta420:*`; apenas o tema escolhe SDC/Bootstrap e mapeia dados neutros para props/slots, evitando acoplamento inverso;
- menus devem usar Menu API/MenuLinkTree em vez de listas manuais quando aplicável, preservando access/cache;
- não espalhar `match ($purpose)` por hooks/controllers/templates nem criar contratos paralelos de shell;
- não criar SDC apenas para substituir uma classe/utilitário Bootstrap simples sem contrato reutilizável.

## Bootstrap Component Design System

- O tema `aculta420` implementa o **ACULTA420 Bootstrap Component Design System**.
- Bootstrap 5 é a infraestrutura estrutural/comportamental; SDC do Drupal Core é
  o mecanismo preferencial para componentes reutilizáveis.
- O `aculta_portal` prepara dados, access, cache, URLs e presenters; não move
  regra de negócio para SDC/Twig.
- Antes de criar markup/CSS custom do Portal, verificar Bootstrap + SDC já
  existentes.
- Não adotar `drupal/bootstrap_components`, UI Suite Bootstrap ou outra suíte
  concorrente sem nova decisão arquitetural.
- Não reiniciar a refatoração avançada do tema para adequá-la ao Portal.
- Consultar `docs/portal/COMPONENT-DESIGN-SYSTEM.md` e
  `web/themes/custom/aculta420/docs/design-system.md`.

## Regra de encerramento de fase

Toda fase ou subfase concluída deve terminar em **PR próprio**.

O PR deve:

- representar uma unidade lógica clara;
- listar entregas e limites;
- registrar `RUNTIME STATUS: DEFERRED` quando houver código não testado no Homelab;
- não misturar trabalho de outra fase;
- apontar a próxima fase;
- permanecer rastreável mesmo quando for integrado imediatamente.

Não considerar uma fase encerrada apenas porque existe commit local/branch.

## Coordenação Portal, tema e documentação

A evolução do Portal e a refatoração do tema são linhas separadas.

Para tarefas do `aculta_portal`:

- o modo atual é GitHub-first / Runtime-last; consultar `docs/portal/README.md` e `docs/operations/RELEASES.md`;
- `origin/main` é a base autoritativa; trabalho local antigo não publicado foi descartado;
- mudanças executáveis sem Runtime ficam em draft com `RUNTIME STATUS: DEFERRED`;
- não modificar `web/themes/custom/aculta420/**` sem autorização explícita;
- não recriar `web/themes/custom/aculta/` nem qualquer provider legado/alias do tema;
- consultar `docs/portal/` antes de implementar;
- manter as fontes de verdade definidas em `docs/portal/SOURCE-OF-TRUTH.md`;
- não instalar dependência planejada antes da versão correspondente;
- não reescrever roadmap/arquitetura por iniciativa própria;
- atualizar CHANGELOG e evidência de testes junto do código implementado;
- uma alteração lógica deve virar um commit atômico;
- documentação/preparação pode ser commitada sem Runtime;
- mudança funcional só é considerada concluída/mergeável/release após os testes adequados.

A documentação arquitetural e o roadmap são definidos fora da execução de
código. O Codex deve principalmente implementar, testar e registrar o resultado
da implementação.

O Portal usa tags `portal-vX.Y.Z`. Ver `docs/operations/RELEASES.md`.

## Forma de trabalho esperada do Codex

Antes de uma alteração relevante:

1. analisar os arquivos relacionados;
2. verificar a arquitetura existente;
3. explicar resumidamente o que será alterado;
4. implementar somente o necessário;
5. verificar erros;
6. informar os arquivos modificados;
7. indicar comandos Drupal/Drush necessários após a alteração.

Não refatorar partes não relacionadas à tarefa sem necessidade.

Não criar funcionalidades que não foram solicitadas.

Quando houver dúvida arquitetural importante, perguntar antes de fazer uma alteração destrutiva.
