# Migração 0.1.0 — aculta -> ACULTA420

## Objetivo

Criar uma fundação técnica nova sem apagar a história do tema anterior.

## Mudanças de provider

| Antes | 0.1.0 |
| --- | --- |
| `web/themes/custom/aculta` | `web/themes/custom/aculta420` |
| `aculta.info.yml` | `aculta420.info.yml` |
| `aculta.libraries.yml` | `aculta420.libraries.yml` |
| `aculta.theme` | `aculta420.theme` |
| `aculta/global` | `aculta420/global` |
| `aculta/editorial-carousel` | `aculta420/editorial-carousel` |
| `aculta:editorial-card` | `aculta420:editorial-card` |
| `aculta.settings` | `aculta420.settings` |
| default theme `aculta` | `aculta420` |

Hooks passam de `aculta_preprocess_*` para `aculta420_preprocess_*`.

## Componentes

`editorial-card` passa para:

`components/content/editorial-card/`

O ID continua baseado no nome do componente e novo provider:

`aculta420:editorial-card`.

## Branding assets

O diretório técnico vira:

`assets/branding/aculta420/`

Arquivos de tema recebem nomes ACULTA420. O conteúdo visual da marca não é
redesenhado nesta migração.

## Configuration Sync

Atualizados:

- `core.extension`;
- `system.theme`;
- settings do tema;
- dependência/campo `theme` dos block placements.

Os IDs dos block placements `aculta_*` permanecem para estabilidade de
configuração. Eles não identificam o provider do tema.

## Implantação

O rename de machine name deve ser tratado como substituição de extensão, não
como simples troca visual.

Em ambiente de desenvolvimento:

1. atualizar código;
2. garantir que `aculta420` é descoberto;
3. importar/sincronizar config;
4. reconstruir cache;
5. confirmar `system.theme: default=aculta420`;
6. confirmar blocos posicionados;
7. validar library/SDC discovery;
8. validar todos os Domain purposes.

Não remover o tema antigo manualmente de um ambiente antes de a configuração
nova estar pronta para importação.

## Compatibilidade

0.1.0 não promete compatibilidade de provider com `aculta`; essa quebra é
intencional e ocorre antes de 1.0.

Após 0.1.0, `aculta420` é o provider estável do roadmap.
