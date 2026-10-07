# Login, Turnstile e redirecionamentos

## Login e página de retorno

Os links de login da navegação da ACULTA levam à Conta e carregam o caminho
visível da página atual, além do `Domain purpose` de origem. O destino fica na
sessão durante o login tradicional ou o ciclo OAuth; após autenticação, o
Portal reconstrói a URL no Domain correto. Rotas de login, logout, recuperação
e OAuth não podem virar destinos de retorno.

O parâmetro Drupal `destination`, quando já fornecido por uma rota protegida,
tem precedência. Um acesso novo e direto a `/entrar` sem destino válido limpa
qualquer destino abandonado da sessão. Social Auth preserva `destination`
durante o redirecionamento ao provedor; quando o parâmetro ACULTA de purpose não
é propagado pelo módulo upstream, o Portal conserva o purpose já capturado para
o mesmo path em vez de sobrescrevê-lo com ACCOUNT. Quando não há página anterior ou destino explícito, o destino público padrão é
a raiz do Domain ACCOUNT (`/` em `conta.aculta.org` ou no alias Homelab).
A configuração Domain de ACCOUNT resolve essa raiz internamente para
`/conta-interna`, rota `aculta_portal.dashboard`, sem expor esse caminho
técnico como URL pós-login.

A página genérica de perfil do Drupal não é publicada. A rota técnica
`user.page` continua registrada porque Core Navigation, recuperação de senha e
outros fluxos upstream ainda geram URLs para ela. Em qualquer host onde seja
alcançada por um usuário autorizado, ela redireciona para a raiz absoluta do
Domain ACCOUNT e nunca renderiza o perfil genérico. `/identidade` também não é
destino de login.

## Endpoints HTTP de autenticação

O projeto não publica `user.login.http` nem `user.pass.http`. Esses endpoints
JSON do Core autenticam sem construir Form API e, portanto, não participam da
política Turnstile aplicada aos formulários públicos. Mantê-los ao lado do login
tradicional protegido criaria um caminho alternativo sem CAPTCHA.

Se uma API de autenticação for necessária no futuro, ela deve ser desenhada e
protegida explicitamente; não reativar esses endpoints como atalho.

## Google OAuth

O início do fluxo é `/oauth/google`; o retorno é
`/oauth/google/retorno`. As URIs cadastradas para o cliente Web são:

| Ambiente | URI de callback | Origem JavaScript |
| --- | --- | --- |
| Homelab | `https://conta.aculta.toca.net.br/oauth/google/retorno` | `https://conta.aculta.toca.net.br` |
| Produção | `https://conta.aculta.org/oauth/google/retorno` | `https://conta.aculta.org` |

Client ID e segredo não são documentados nem impressos. O responsável
configurou o cliente OAuth Google; nenhuma alteração foi feita na Hostinger ou
no Drupal de produção neste trabalho.

### Vínculos em Minha Conta

A página `/conexoes` lê os vínculos da entidade Social Auth do usuário atual.
Para Google, o identificador persistido é `social_auth_google`; `google` é
somente o nome curto usado na URL. A consulta da Conta deve usar o identificador
persistido, ou um vínculo salvo pode existir sem aparecer como conectado na
interface. Após um callback autenticado, o Portal registra o e-mail retornado
pelo Google em `additional_data.provider_email` na entidade Social Auth; a área
privada exibe esse e-mail e não mostra tokens. Vínculos antigos sem esse dado
continuam identificados como conectados, informam que o endereço ainda não está
disponível e oferecem “Atualizar conexão Google” para buscar novamente o perfil.

O Homelab usa Social API 4.0.2. Para PHP 8.4, o projeto aplica pelo Composer
uma correção pequena proposta no
[issue 3593752 do Social API](https://www.drupal.org/project/social_api/issues/3593752):
o parâmetro opcional `Request` recebe tipo nullable explícito. Isso elimina o
aviso de depreciação visto durante a inicialização OAuth; o aviso isolado não
prova a causa de uma falha no callback. O fluxo completo com uma conta Google
real continua pendente de validação interativa.

## Turnstile

O Turnstile é o desafio ativo para visitantes anônimos nos formulários públicos
em rotas não administrativas. O CAPTCHA global cobre formulários sem uma regra
específica; os pontos configurados também permanecem ativos. As chaves são
mantidas na configuração de Key do runtime; seus valores não pertencem ao
código nem à documentação. Após o
responsável confirmar que o Turnstile estava funcionando, foi desativado o
fallback de CAPTCHA solicitado. A aparência é `always`, para deixar o widget
visível desde o carregamento; `interaction-only` o ocultava para a maioria das
pessoas.

CAPTCHA é uma barreira para visitantes anônimos: formulários públicos em rotas
não administrativas exigem Turnstile, salvo quando o fluxo não submete um
formulário Drupal. Isso inclui o formulário anônimo de cadastro e formulários
públicos de contato/participação. O papel `authenticated` recebe a permissão
oficial `skip CAPTCHA`, então qualquer usuário autenticado pode preencher os
formulários sem desafio. A exceção de cadastro sem CAPTCHA é o fluxo OAuth:
login e criação de conta Google ocorrem no redirecionamento/callback do
provedor, sem submeter o formulário Drupal de cadastro. Essa exceção não cria
um bypass de CAPTCHA para formulários anônimos. Formulários continuam sujeitos
às demais validações, permissões e proteções normais. O login tradicional
anônimo continua bloqueado quando não apresenta token Turnstile válido; não
existe bypass por JavaScript.

O smoke HTTP desta revisão confirmou que o formulário de login carrega o
JavaScript e o markup do widget Turnstile, sem markup de CAPTCHA de imagem ou
reCAPTCHA. A validação de token e o login por senha não foram repetidos com
interação de navegador nesta alteração.

## Estado operacional desta revisão

- Homelab: config ativa do Social Auth aponta o fallback para `/conta-interna`.
- a UI genérica de `/user` foi removida da experiência: `user.page` é mantida
  apenas como rota técnica de compatibilidade e redireciona usuários autenticados
  para a raiz ACCOUNT; `/identidade` não é destino de login;
- `/conta-interna` permanece somente como front page interna do Domain ACCOUNT.
- Os links de login renderizados em MAIN, MAGAZINE, WIKI e COURSES carregaram
  `destination` com o caminho visível e o purpose respectivo.
- O link Google gerado em `/entrar` preservou `destination`; o início OAuth
  respondeu com redirecionamento para Google e callback Homelab correto.
- A revisão de `/conexoes` identificou que a entidade Social Auth usa o plugin
  ID `social_auth_google`, enquanto o Portal consultava o nome curto `google`.
  A consulta foi corrigida e a renderização reconhece o vínculo persistido
  durante a validação Drupal. Isso valida a leitura pela Conta, não substitui
  um novo teste interativo de consentimento/callback Google.
- A depreciação PHP 8.4 deixou de aparecer no início OAuth após aplicar o
  patch Composer. O retorno com consentimento real do Google não foi executado
  nesta revisão.
- Nenhuma configuração ou deploy de produção foi alterado.


## Ownership de apresentação

A supressão do Page Title duplicado no shell privado pertence ao
`aculta_portal`, que conhece o render array `aculta_portal_shell`. O tema não
inspeciona nomes de rotas do Portal para decidir comportamento funcional. Essa
separação evita afetar Wiki, Cursos, Apoio ou outros routes `aculta_portal.*`.
