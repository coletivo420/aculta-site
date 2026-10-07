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

### Shim de compatibilidade

A versão 0.1.0 mantém temporariamente `web/themes/custom/aculta/` com apenas um
`aculta.info.yml` mínimo. Ele existe para que ambientes que ainda possuem
`aculta` instalado consigam inicializar e importar a configuração que habilita
`aculta420`.

O shim não possui libraries, templates, SDCs ou apresentação do tema antigo.
Não desenvolver nada nele.

Em ambiente de desenvolvimento:

1. criar backup/restore point;
2. atualizar código — o shim `aculta` e o novo `aculta420` devem estar presentes;
3. `drush theme:enable aculta420 -y`;
4. `drush config:set system.theme default aculta420 -y`;
5. importar a configuração sincronizada;
6. reconstruir cache;
7. confirmar `system.theme: default=aculta420`;
8. confirmar que `core.extension` não lista `aculta`;
9. confirmar blocos posicionados;
10. validar library/SDC discovery;
11. validar todos os Domain purposes.

Não desinstalar `aculta` manualmente **antes** do config import: blocos ainda
associados ao tema antigo podem ser removidos pelo processo de uninstall. A
configuração sincronizada deve conduzir a troca.

O diretório shim só será removido em patch posterior, depois que todos os
ambientes tiverem migrado.

## Compatibilidade

0.1.0 não promete compatibilidade de provider com `aculta`; essa quebra é
intencional e ocorre antes de 1.0.

Após 0.1.0, `aculta420` é o provider estável do roadmap.
