# S3.9 — Inventário de assets de avatar

Data: 2026-10-06

Status: **concluída documentalmente**

## Inventário

Diretório:

`web/modules/custom/aculta_portal/assets/avatars/`

No baseline auditado:

- 112 arquivos totais;
- 107 PNGs;
- 112.925.719 bytes em PNG (~107,7 MiB);
- 5 arquivos de documentação/metadados;
- 38.575 bytes de metadados.

Metadados:

- README.txt;
- meta/avatar-families.json;
- meta/avatars-aliens.json;
- meta/avatars-monsters.json;
- meta/avatars.json.

## Referências

Busca estática no repositório por:

- `assets/avatars`;
- `avatars.json`;
- `avatar-families.json`;
- nomes concretos de PNGs;

não encontrou consumidor em código/config versionado.

Isso **não prova** que os assets estão mortos: conteúdo Drupal, files públicos,
estado antigo, documentação externa ou feature ainda não integrada podem
referenciá-los.

## Classificação

**DEAD-ASSET CANDIDATE / FEATURE-INVENTORY REQUIRED**

Nenhum arquivo é removido nesta fase.

## Problemas atuais

### Peso de Git

~108 MiB de imagens dentro de um módulo funcional aumenta:

- clone/fetch;
- checkout;
- CI;
- cache;
- revisão;
- distribuição do módulo.

### Ownership

Imagens de conteúdo/personalização de usuário não são naturalmente código PHP.

Possíveis destinos futuros:

- Media/File gerenciado pelo Drupal;
- pacote/release asset;
- object storage/CDN;
- coleção otimizada versionada separadamente;
- remoção, se a feature for realmente abandonada.

## Decisão antes de remover/migrar

Responder no Runtime/prod inventory:

1. existe UI de seleção de avatar ativa ou planejada?
2. algum user/profile/file referencia essas imagens?
3. os JSONs definem IDs que já foram persistidos?
4. origem/licença de cada coleção está documentada?
5. precisamos de todos os PNGs originais?
6. o crop/avatar atual baseado em `user_picture` substituiu a coleção?

## Regra de segurança

Não:

- apagar 107 imagens apenas por não haver grep;
- registrar novos IDs sem migração;
- mover para public files sem política de deploy;
- misturar avatar gerado/selecionável com `user_picture` sem especificação.

## Runtime audit futuro

Pesquisar:

- managed_file/file entities;
- user_picture;
- Profile fields;
- config/state/key_value;
- database textual para paths/IDs somente quando seguro;
- public/private files;
- UI/route antiga de avatar.

## Possíveis resultados

### A — coleção abandonada

Remover em PR próprio, com documentação histórica e impacto no tamanho.

### B — feature futura válida

Definir arquitetura antes:

- source of truth;
- campo que salva escolha;
- copyright/licença;
- otimização/resoluções;
- cache/CDN;
- fallback;
- acessibilidade.

### C — assets de conteúdo

Migrar para Drupal Media/File ou infraestrutura de assets, não manter como
payload do módulo.

## Próxima fase

S3.3A — AJAX boundary / plano de consolidação da Minha Conta.
