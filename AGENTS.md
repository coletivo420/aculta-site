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
- validadores do tema devem reconhecer seletores dark alternativos inclusive dentro de pseudo-classes e em `tokens.css`, PHP `switch/case` (incluindo `endswitch`) e `match`, JavaScript `switch/case` por labels com discriminantes aninhados, writes simples/compostos de `dataset`, ternários Twig e identificadores `colorScheme`; comentários não devem causar falso positivo e mudanças de sintaxe exigem fixtures positivas e negativas;
- o contrato de superfície dark charcoal/graphite é explícito; resolver todos os tokens ACULTA/Bootstrap por modo, validar mappings Bootstrap light/dark e todos os pares RGB, rejeitar aliases/ciclos/referências ausentes/cores inválidas/duplicatas e calcular contraste com alpha composto;
- scripts PHP estáticos do tema devem aceitar roots absolutos POSIX e Windows (drive letter/UNC) sem traversal `..`, sem exigir Bash/WSL para executar;
- componente não conhece light, dark ou `prefers-color-scheme`; uma futura variante de asset de logo mantém espaço, dimensões e layout;
- não criar seletor, persistência ou JavaScript de modo antes da fase prevista;
- o tema nunca escolhe cores/branding por Domain purpose ou hostname; apresentação por purpose chega preparada pelo Portal;
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
