# S3.6 — Public CSS ownership

Data: 2026-10-06

Status: **concluída documentalmente**

## Objetivo

Definir quem é dono do CSS público hoje existente no `aculta_portal` antes de
qualquer migração para o ACULTA Bootstrap Component Design System.

Não mover CSS por organização estética. Só mover quando o componente visual e
seu ownership estiverem comprovados.

## Arquivos atuais

### account-portal.css

Ownership misto e temporário.

Grupos principais:

- shell/navigation da Conta;
- identidade/avatar/photo editor;
- data tabs/content;
- security/integration cards;
- course summary/cards;
- loading/status da navegação parcial.

Destino:

| Grupo | Destino |
| --- | --- |
| navegação/transport state | Portal/AJAX até consolidação |
| account shell | tema quando contrato H6 existir |
| avatar/photo editor | tema somente após paridade Form API/dialog/crop |
| data section | tema após contrato visual estável |
| security/integration cards | tema quando H4/H5 comprovar componente |
| course card | tema H4 |
| loading/error de AJAX | Portal/behavior ou pattern, não card |

### support.css

Responsabilidade pública de Apoio.

Destino:

- layout/card/CTA: candidato ao design system;
- estados funcionais: presenter Portal;
- checkout/payment: Commerce mantém markup/behavior;
- não migrar CSS de gateway/checkout para componente ACULTA.

### requirements-report.css

Tela administrativa/diagnóstico.

Decisão:

**permanece no módulo**.

Admin UI não deve ser forçada ao design system público.

## Ordem de migração

1. course card, quando H4 entregar contrato;
2. security/integration presentation;
3. support public presentation;
4. account shell;
5. data section;
6. photo editor por último.

## Regra de remoção

Uma regra antiga só pode sair do módulo quando:

- consumidor novo existe;
- visual parity foi verificada;
- mobile foi verificado;
- keyboard/focus foi verificado;
- AJAX reattachment foi verificado;
- fallback full-page foi verificado;
- não há outro selector consumidor.

## Tokens

Cores, tipografia e spacing ACULTA não devem permanecer hardcoded no módulo
quando o design system já possui token equivalente.

Porém a substituição deve ocorrer junto do componente/pattern dono da regra, não
em um sweep global sem Runtime.

## Bootstrap

Preferir:

- utilities;
- grid;
- cards/alerts/list-group quando adequados;
- states/accessibility já fornecidos.

Não copiar implementação Bootstrap para CSS Portal.

## AJAX

CSS de estado de transporte pode continuar no módulo enquanto
`account-navigation.js` existir.

Exemplos:

- loading;
- aria-busy companion state;
- erro de navegação;
- transição da região.

Esses estados não devem ser colocados dentro de um SDC de conteúdo apenas
porque aparecem na mesma página.

## Tema

Nenhum arquivo do tema é alterado pela S3.6.

A migração física deve ser coordenada com as fases H do tema, uma peça por vez.

## Próxima fase

S3.7 — Procedural hooks preparation.
