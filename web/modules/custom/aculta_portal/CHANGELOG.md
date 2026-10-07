# Changelog — ACULTA Portal

Todas as mudanças relevantes do `aculta_portal` devem ser registradas aqui.

O Portal usa tags `portal-vX.Y.Z`.

## [Unreleased]

### Autenticação

- Preserva query string no destination de login/OAuth e adiciona `url.query_args` ao cache do menu, mantendo buscas, filtros e paginação após autenticação.
- Corrige o identificador do usuário externo no callback Google para `SocialAuthUserInterface::getId()`.
- Impede desconexão Google quando a conta Social Auth ainda não possui senha local escolhida, reutilizando o marcador `social_auth_password_unset` via DI de `UserDataInterface`.
- Desabilita os endpoints JSON de login/recuperação do Core neste site para impedir uma rota paralela que não passa pelo Form API/Turnstile; OAuth continua sendo o fluxo alternativo suportado.
- Preserva `user.page` somente como redirect técnico compatível com Core Navigation/recuperação de senha, sempre apontando para a raiz ACCOUNT sem expor o perfil genérico.
- Limpa destinos de login abandonados e preserva o Domain purpose original durante a transição login -> OAuth.
- Aplica Turnstile globalmente aos formulários em páginas públicas para visitantes anônimos; o papel `authenticated` ignora CAPTCHA em todos os formulários. Login e cadastro concluídos pelo OAuth não submetem os formulários Drupal protegidos pelo CAPTCHA.
- Corrige o indicador de conexão Google em Minha Conta: a entidade Social Auth é gravada com o plugin ID `social_auth_google`; `google` é apenas o nome curto da rota e não encontra os vínculos salvos.
- Exibe o e-mail da conta Google em Conexões, guardando o endereço retornado pelo Google nos dados adicionais da entidade Social Auth após callback autenticado; vínculos antigos sem esse dado oferecem atualização da conexão.
- Define `/oauth/{provedor}` como início do Social Auth e `/oauth/{provedor}/retorno` como callback; Google usa `/oauth/google` e `/oauth/google/retorno`.
- Faz links de login preservarem a última página visitada e o Domain purpose para login tradicional e OAuth; o callback retorna ao host da página anterior. Sem destino anterior, o retorno usa a raiz do Domain ACCOUNT. `user.page` é preservada apenas para compatibilidade com redirects do Core e redireciona para a raiz da Conta sem renderizar o perfil genérico; `/identidade` não é publicada.

### Processo

- Macrofase S encerrada no limite seguro sem Runtime; drafts funcionais continuam DEFERRED e a próxima execução começa em R0.

### Refatoração preparada

- Internacionalização pt-BR usa Language/Locale/Core como fonte de verdade; `config_translation` é habilitado e um catálogo local pt-BR mínimo cobre apenas lacunas confirmadas de login/recuperação, preservando a política global de Turnstile definida na autenticação.
- S3.2A introduz `AccountCoursePresenter` como fronteira semântica entre LMS/Group e a apresentação da Conta, usa a chave compartilhada `action` da S3.2B sem repassar `score`/`finished` brutos, mantém o render atual e o tema inalterado até Runtime PASS; o controller de Cursos usa DI explícita e não depende dos helpers lazy de `ControllerBase`.
- S3.5 separa resolução de purpose do enforcement HTTP e evita mutação de Domain em URLs locais.
- S3.5 passou os gates Runtime em R1.2B; `pathUrl()` agora respeita o Domain purpose e o alias de ambiente.

### Roadmap

- Portal 0.18.1 adiciona a padronização transversal de slugs públicos amigáveis em português para todos os Domains/purposes, com redirects/canonical/sitemap e preservação das rotas técnicas upstream.

### Documentação

- S2 Static Portal Audit concluído sem alterar runtime.
- S2.1 documenta a matriz Minha Conta: SDC, integrações, AJAX e fallback.
- S3.2B define semântica compartilhada de status, ações, empty states e summaries sem antecipar SDCs não aprovados pelo tema.
- Mapa de refatoração classifica KEEP, REFACTOR, UPSTREAM/CONFIG e RUNTIME-SENSITIVE.
- Bootstrap Component Design System definido como destino da apresentação pública do Portal.

### Planejado

- Portal 0.10.0: fundação documental e disciplina de desenvolvimento.
- Portal 0.11.0: fundação do Fórum.
- Portal 0.12.0: participação do Fórum na Conta.
- Portal 0.13.0: hub integrado de participação.
- Portal 0.14.0: hub administrativo — especificação concluída.
- Portal 0.15.0: consolidação AJAX — especificação concluída.
- Portal 0.16.0: Search API — especificação concluída.
- Portal 0.17.0: engagement — especificação concluída.
- Portal 0.18.0: deduplicação — especificação concluída.
- Portal 0.19.0: hardening — especificação concluída.
- Portal 1.0.0: gates de release especificados; release ainda bloqueado por Runtime.

## 0.10.0 — Foundation

Em preparação.

Objetivo: organizar responsabilidades, fontes de verdade, módulos upstream,
AJAX, Fórum, Wiki, Revista, Loja, integrações Google, Bootstrap Component Design
System, testes, versionamento e roadmap antes de novas features.

O desenvolvimento opera temporariamente em modo GitHub-first / Runtime-last;
features executáveis preparadas sem Homelab permanecem draft até validação.
