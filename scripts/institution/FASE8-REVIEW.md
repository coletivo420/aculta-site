# Fase 8 — revisão local

**Resultado: PARCIAL — NO-GO para Release Candidate local.** A estrutura visual e a navegação foram revisadas e os testes locais de domínio, sessão e logout passaram. Permanecem pendências de sitemap, auditoria Security Review e inspeção visual em navegador.

## Ambiente e domínios

- Drupal 11.4.8, PHP 8.5.10; MariaDB conectado.
- Os sete nomes `*.aculta.test` resolvem para `127.0.0.1`; um Host não permitido retornou HTTP 400.
- `scripts/local/start-aculta-multidomain.ps1` inicia o servidor em `127.0.0.1:8080` e agora define o document root como diretório de trabalho, necessário para o carregamento de templates Twig.
- Teste HTTP: MAIN 200; ACCOUNT 403 anônimo na visão privada; SUPPORT 200; MAGAZINE 200; WIKI, SHOP e COURSES 404 reservados.
- Domain purpose foi confirmado nos sete hosts: `main`, `account`, `support`, `magazine`, `wiki`, `shop`, `courses`.
- Um usuário descartável autenticou por HTTP. O mesmo UID foi lido nos sete hosts. Após `/sair`, a sessão foi encerrada e todos os hosts retornaram UID 0. O navegador termina em `/entrar` (200), sem erro na Home privada.
- O cookie da sessão foi observado como `Domain=.aculta.test`, `HttpOnly`, `Secure=false`; a configuração Drupal carregada mantém `SameSite=Lax`.
- Windows hosts já continha as sete entradas locais corretas e nenhum domínio `.org`.

## Breadcrumbs e visual

- Builder Drupal central `AcultaBreadcrumbBuilder`, com raiz por propósito, cache contexts para rota, caminho e domínio, e ocultação de rotas técnicas. O tema usa markup `<nav aria-label="Trilha de navegação">`, lista ordenada e `aria-current="page"`.
- As páginas verificadas de projetos, login e recuperação renderizam breadcrumb e URLs de raiz adequadas. O breadcrumb não aparece na Home nem em rotas técnicas.
- CSS mantém a paleta ACULTA, foco visível e `prefers-reduced-motion`. Não foi possível fazer inspeção visual/screenshot responsiva porque não há navegador automatizado disponível nesta máquina; revisão manual nos breakpoints 360, 390, 768, 1024 e 1440 px permanece pendente.
- Contraste calculado: verde escuro sobre branco 12,40:1; vermelho sobre branco 4,48:1 e sobre creme 4,10:1; verde `#689427` sobre branco 3,59:1; amarelo sobre branco 1,58:1. O vermelho sobre branco fica ligeiramente abaixo de 4,5:1 para texto pequeno. Mantive as cores institucionais aprovadas; revisar o uso em textos pequenos durante a inspeção visual.
- 403 anônimo da área privada e 404 dos hosts/áreas reservadas foram observados via HTTP. Acesso administrativo não foi redesenhado.

## Canonical e sitemap

- A Home MAGAZINE era servida como “Notícias”, mas usava canonical/Open Graph de MAIN. Foi criado override nativo de Domain Config para `revista.aculta.org`; após exportação, canonical e `og:url` da Revista apontam para `https://revista.aculta.org/`.
- O sitemap permanece PARCIAL: a versão instalada do Simple XML Sitemap publica a mesma lista nos hosts, com base `https://aculta.org`, inclusive `/noticias`, que redireciona para a Home editorial em MAGAZINE. Não encontrei suporte instalado para sitemap isolado por Domain; não criei gerador paralelo. Resolver a separação de sitemap/canonical editorial antes de indexação pública.
- MAIN e SUPPORT continuam usando conteúdo/rotas reais; os domínios reservados não receberam páginas fictícias.

## Segurança e verificações

- Security Review executado em `http://aculta.test:8080`: sucesso em Account Creation, permissões administrativas, extensões de upload, tags perigosas, tentativas de login, headers, senha, erros de query, arquivos temporários, Trusted Hosts, vendor e cron. Falhas reportadas pelo módulo: conta administrativa bloqueada, PHP executável, permissões de arquivos, formatos de texto e acesso de Views. Arquivos privados e error reporting aparecem como informação. O comando também emitiu avisos internos sobre `result`/`findings`; nenhum autofix foi aplicado.
- Não houve chamada a ViaCEP, OAuth, SMTP ou gateway de pagamento nesta fase. O gateway permanece sem transação real.
- Revisão manual de conteúdo alternativo de imagens não foi concluída.

## Qualidade

- `drush cr`, `drush updatedb:status` e `drush config:status`: concluídos; sem updates pendentes e sem diferenças entre banco e sync.
- `composer validate --no-check-publish`: válido, com avisos de constraints exatas existentes para Bootstrap5 e Pathauto.
- `composer audit --no-dev`: sem advisories conhecidos nos pacotes cobertos. `commerce_mercado_pago` continua em RC, conforme a dependência já adotada.
- PHP lint: 26 arquivos aprovados. `node --check`: JavaScript custom aprovado. `git diff --check`: executado antes do snapshot.
- O scanner de segredos encontrou apenas arquivos/campos de configuração e referências contendo os nomes `secret`, `password` ou `token`; a revisão dos arquivos de chaves/gateway/SMTP não identificou credenciais literais para versionar. `settings.local.php` e credenciais locais permanecem ignorados.
- O snapshot não inclui o cache Python `__pycache__`; `.gitattributes` marca formatos binários como binários para que o `diff --check` não processe PNGs como texto.

## Alterações desta fase

- Breadcrumbs centralizados e markup acessível em `web/modules/custom/aculta_portal/src/Domain/AcultaBreadcrumbBuilder.php`, `web/modules/custom/aculta_portal/aculta_portal.services.yml`, `web/themes/custom/aculta/aculta.theme`, `web/themes/custom/aculta/templates/navigation/breadcrumb.html.twig` e `web/themes/custom/aculta/css/style.css`.
- Enforcement de domínio mantém 404 em host incorreto. Após logout, o redirecionamento leva ao login de ACCOUNT.
- O script multidomínio inicia no diretório `web`; configuração local de renderer preserva as condições de auto-placeholder do Drupal e cache context `domain`.
- Canonical e Open Graph da Home MAGAZINE foram sobrescritos via Domain Config e exportados em `config/sync/domain/revista_aculta_org/metatag.metatag_defaults.front.yml`.

## Pendências para liberar RC local

1. Separar o sitemap por propósito de domínio usando uma integração compatível, ou definir a política de publicação do sitemap antes de indexar.
2. Tratar as falhas do Security Review depois de confirmar cada achado e repetir a verificação.
3. Fazer inspeção manual no navegador em desktop/mobile, incluindo cabeçalho, footer, breadcrumbs, formulário e foco por teclado.
4. Revisar contraste do vermelho em texto pequeno e textos alternativos ausentes com decisão editorial.

Nenhum commit, push ou deploy faz parte deste relatório. O snapshot GitHub, autorizado pelo adendo, é executado separadamente após a validação final do estado local.
