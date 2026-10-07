# S3.8 — Lifecycle/install audit

Data: 2026-10-06

Status: **concluída documentalmente**

## Objetivo

Auditar `aculta_portal.install` antes de qualquer limpeza de install/update
hooks sem executar fresh install ou update path.

## Estado observado

O arquivo possui:

- `aculta_portal_install()`;
- update 11001;
- update 11003;
- update 11004;
- update 11006;
- update 11007;
- update 11008.

Há somente um arquivo próprio em `config/install`:

- `aculta_portal.support.yml`.

Não há `config/optional` no módulo.

## Classificação

### hook_install

Cria/configura o Profile `participante` e infraestrutura histórica associada.

Classificação:

**RUNTIME-SENSITIVE**

Não substituir por YAML cegamente enquanto fresh install não provar paridade.

### update 11001

Permissões do authenticated para Portal/Profile.

Classificação:

**HISTORICAL UPDATE — PRESERVAR**

Permissões desejadas atuais podem ser representadas por config/recipes no
futuro, mas o update antigo continua necessário para sites que atravessam essa
versão.

### update 11003

Nickname/address/photo/crop/image style e configuração associada.

Classificação:

**HISTORICAL UPDATE — ALTO RISCO**

Mistura evolução de schema/config e precisa de fresh-install/update-path tests
antes de qualquer consolidação.

### update 11004

Keys/env, CAPTCHA points e integrações de infraestrutura.

Classificação:

**PRESERVAR / REVIEW PARA NOVAS INSTALAÇÕES**

A política atual de segredos via environment/Key continua correta.

Não mover credentials para config sync.

### update 11006

Permissão restrita de Social Auth para remover vínculo próprio.

Classificação:

**HISTORICAL UPDATE — PRESERVAR**

Validar posteriormente se a permissão também está representada em baseline de
config/role para instalações novas.

### update 11007

Agreement bypass apenas para administrator.

Classificação:

**HISTORICAL UPDATE — PRESERVAR**

Não ampliar bypass.

### update 11008

Migração resumível de endereço participante -> Commerce customer Profile.

Classificação:

**MIGRAÇÃO DE DADOS CRÍTICA — NÃO REESCREVER SEM RUNTIME**

Características que devem ser preservadas:

- sandbox/batches;
- idempotência;
- não sobrescrever propriedades Commerce já preenchidas;
- remoção de duplicação somente após migração;
- retomada segura.

## Dívida estrutural

O módulo cresceu por update hooks porque várias capacidades nasceram antes da
disciplina atual de Configuration Sync.

Destino futuro:

```text
fresh install baseline
      +
config/install ou recipe quando apropriado
      +
update hooks somente para migração real
```

Isso **não** significa apagar updates históricos.

## Regras para novas features

A partir desta auditoria:

- configuração declarativa exportável primeiro;
- update hook só quando instalação existente precisa transformar estado;
- migração de dados sempre idempotente/resumível quando aplicável;
- secrets nunca em config sync;
- permissions/config de instalação nova precisam ter baseline claro;
- qualquer mudança em `.install` exige teste de fresh install e upgrade path.

## Gates Runtime futuros

### Fresh install

Em instalação limpa:

- módulo instala;
- Profile participante existe;
- fields/form displays necessários existem;
- Key/env providers existem sem secrets;
- permissions corretas;
- suporte/config correto;
- config export não produz drift inesperado.

### Upgrade path

Partindo de snapshot anterior:

- cada update pendente executa;
- updatedb termina NONE;
- rerun/idempotência quando aplicável;
- 11008 pode ser interrompido/retomado;
- endereços não perdem dados;
- roles/permissions ficam corretos.

## Decisão

Nenhum código de `aculta_portal.install` é alterado na Macrofase S.

Mudanças estruturais ficam para R3/Hardening após testes de fresh install e
upgrade path.

## Próxima fase

S3.9 — assets/avatar inventory.
