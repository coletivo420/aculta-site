# ACULTA Secrets Contract

## Objetivo

Manter credenciais fora do Git, do armazenamento comum do Drupal e dos
exports, com o mesmo contrato em Homelab, Hostinger Web/Cloud, VPS, containers
e CI.

## Princípios

- Drupal Key é a interface única para integrações.
- Providers Key permanecem `env`; código e configuração Drupal usam somente o
  Key ID e o nome estável da variável.
- No estado final, configuração bruta e sincronizada não contêm valores reais;
  a limpeza do Runtime legado é a migração R0.4.
- A configuração efetiva pode conter o valor em memória após o Key Config
  Override. Isso não significa que o valor foi persistido.
- O código Drupal não conhece caminhos físicos, serviços systemd, PHP-FPM ou
  detalhes do provedor de hospedagem.
- Segredos usam least privilege e ficam fora do document root.

## Estado desta preparação

R0.3.5 versiona o contrato e o loader, mas não migra os valores reais. No
Runtime Homelab atual, os valores legados Google continuam no storage bruto e
as Keys Environment não resolvem sem provisioning. A migração operacional e a
limpeza desses campos pertencem à R0.4.

## Variáveis atuais

| Key ID | Environment | Sensível | Encoding |
| --- | --- | --- | --- |
| `google_oauth_client_id` | `GOOGLE_OAUTH_CLIENT_ID` | Não, identificador público | plain |
| `google_oauth_client_secret` | `GOOGLE_OAUTH_CLIENT_SECRET` | Sim | plain |
| `mercadopago_webhook_secret` | `MERCADOPAGO_WEBHOOK_SECRET` | Sim | plain |
| `smtp2go_username` | `SMTP2GO_USERNAME` | Sim, credencial | plain |
| `smtp2go_password` | `SMTP2GO_PASSWORD` | Sim | plain |
| `turnstile` | `TURNSTILE_KEYS_JSON` | Sim, contém secret | Base64 |

## Arquitetura

```text
              ACULTA Secrets Contract
                        │
            variáveis com nomes estáveis
                        │
        ┌───────────────┴───────────────┐
        │                               │
Native process environment   Secure bootstrap adapter
        │                               │
Homelab / VPS / container     Managed hosting / Hostinger
        └───────────────┬───────────────┘
                        │
                 PHP process env
                        │
                 Drupal Key/env
                        │
             Key Configuration Override
                        │
             integração contrib
```

O código Drupal e a configuração sincronizada dependem somente do nome lógico
da Key e do nome da variável de ambiente. Caminhos físicos, gerenciadores de
serviço e mecanismos de provisionamento pertencem ao ambiente; não podem virar
dependência do Portal ou do tema.

### Native Environment Adapter

Indicado para Homelab Debian, VPS, containers e CI. A plataforma pode usar
systemd `EnvironmentFile`, variáveis explicitamente permitidas no PHP-FPM,
container secrets, CI variables ou mecanismo equivalente. systemd não é um
requisito arquitetural.

### Secure Bootstrap Adapter

Indicado para hospedagem gerenciada quando não controlamos o ambiente do
processo. Um settings local ignorado aponta para um arquivo secreto fora do
document root e inclui `web/sites/default/aculta.secrets.php`. O loader
versionado publica somente a allowlist aprovada com `putenv()` e não imprime
dados. Ele não usa shell, `source`, `eval` ou dependência dotenv.

O arquivo usa linhas `NAME=value`. Valores com espaços ou caracteres especiais
devem ser quoted no formato INI. O parser não expande outras variáveis.

## Precedência

Environment nativo não vazio tem precedência sobre o arquivo bootstrap. O
loader não substitui uma variável nativa já definida. O arquivo pode conter
qualquer subconjunto da allowlist; integrações opcionais não são obrigatórias
globalmente.

## Homelab

`settings.homelab.php` continua local e ignorado pelo Git. O Homelab pode usar
environment nativo no processo PHP ou o adapter bootstrap apontado pelo próprio
settings local. O arquivo `/etc/aculta/secrets.env` é uma opção operacional do
host, não uma dependência do Drupal.

No adapter bootstrap, use proprietário/grupo definidos pelos usuários reais do
Runtime e o menor acesso necessário para PHP-FPM e CLI. Por exemplo, diretório
`0750` e arquivo `0640` podem ser adequados quando a ACL/grupo permite somente
aos processos necessários a leitura. Não use permissões abertas para facilitar
Drush.

## Hostinger Web/Cloud

Estrutura conceitual:

```text
/home/ACCOUNT/
├── .aculta/
│   └── secrets.env
└── .../public_html/
    └── Drupal
```

O arquivo secreto fica fora de `public_html`. Prefira `0600` se PHP web e SSH
usarem o mesmo usuário. Caso o plano use identidades distintas, aplique o menor
acesso que permita ao Runtime ler. Um settings local ignorado define
`$settings['aculta_secrets_file']`; o exemplo versionado é apenas scaffolding,
não um settings completo. O banco MariaDB permanece definido na configuração
local de produção.

## Hostinger VPS

VPS pode usar o adapter nativo (systemd, PHP-FPM, container) ou o bootstrap
seguro, conforme a administração escolhida. O Drupal, as Keys e os Config
Overrides não mudam.

## Drupal Key

As entities Key devem usar provider `env` e o nome exato da variável da tabela
acima. Não criar provider custom nem fazer integração ler arquivos físicos.

## Key Configuration Override

Config Overrides aplicam as Keys de OAuth às opções de Social Auth Google.
Outras integrações seguem seus próprios overrides ou referências Key. O
override é a ponte entre a abstração Key e a configuração contrib.

## Config sync

Três camadas devem permanecer distintas:

1. **Raw config storage:** no estado final, sem credenciais; por exemplo,
   `client_id: ''` e `client_secret: ''`. O Runtime atual ainda aguarda R0.4.
2. **Key config:** somente Key ID, provider `env` e nome da variável.
3. **Effective config:** pode conter valores em memória pelo override durante
   a requisição. Não exportar essa visão efetiva como segredo persistente.

Nunca preencher credenciais reais diretamente no formulário de configuração
contrib como solução permanente, nem executar export que grave esses valores.

## Database

Não salvar secrets no SQLite ou MariaDB para simplificar o desenvolvimento.
O banco guarda entidades/configuração de referência; o valor vem do adapter do
ambiente.

## Backups

Após a migração R0.4, novos dumps do banco não devem transportar credenciais
Google. Até essa migração ser concluída, o Runtime existente ainda pode conter
valores legados no storage bruto. Backups e snapshots criados antes da limpeza
continuam sensíveis e precisam de inventário, retenção e permissões restritas.
Nunca publicar em Git um dump ou Estado SQLite que contenha secret persistido;
isso também se aplica aos Estados Homelab que, por decisão do projeto, podem
ser públicos quando sanitizados.

## CLI / Drush

Com o Secure Bootstrap Adapter, Drush carrega os mesmos settings do Drupal e
resolve as mesmas Keys sem `source secrets.env` no shell. Isso reduz divergência
entre web e CLI e evita comandos de export manual. O adapter nativo também é
válido quando a CLI já recebe as variáveis aprovadas.

Nunca imprimir Key values em gate, log ou diagnóstico. Gates devem emitir
somente estados como `SET`, `EMPTY`, `MATCH` ou `FAIL`.

## Rotation

Troque a credencial no provedor, atualize a fonte host-local do ambiente,
reinicie/recarregue somente o processo que precisa recebê-la e confirme a Key e
o fluxo funcional sem revelar o valor. Remova cópias temporárias depois de
validar o novo valor; preserve rollback restrito pelo período operacional
definido.

## Adding a new secret

1. criar/usar Key com provider `env`;
2. definir um nome estável de environment;
3. atualizar esta tabela e o validator do contrato;
4. adicionar Config Override se a integração exigir;
5. provisionar a variável por adapter em cada ambiente;
6. provar raw/sync vazios, Key resolvida e comportamento efetivo sem imprimir
   valores.

Nunca corrigir integração preenchendo secret em config, movendo secret para o
tema ou hardcoding caminho Homelab/Hostinger no Portal.
