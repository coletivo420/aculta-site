# Documentação ACULTA420 0.1.0

Esta pasta contém a documentação normativa do tema e design system.

## Ler primeiro

1. [architecture.md](architecture.md) — fronteiras e direção de dependências.
2. [features.md](features.md) — o que existe hoje.
3. [design-system.md](design-system.md) — foundations e linguagem visual.
4. [shell.md](shell.md) — contrato multidomínio planejado e limites da Foundation.
5. [components.md](components.md) — contratos SDC e catálogo.
6. [development.md](development.md) — workflow e anti-regressão.
7. [accessibility.md](accessibility.md) — requisitos de acessibilidade.
8. [decisions.md](decisions.md) — decisões que não devem ser rediscutidas sem
   evidência nova.
9. [roadmap.md](roadmap.md) — evolução versionada.
10. [versioning.md](versioning.md) — SemVer e releases.
11. [migration-0.1.0.md](migration-0.1.0.md) — rename `aculta` -> `aculta420`.

## Fonte de verdade

A documentação corrente descreve o estado suportado. Histórico de commits e
fases antigas não deve ser usado como especificação de implementação.

Prioridade quando houver conflito:

1. código/config da branch atual;
2. este conjunto de docs;
3. ADRs do projeto;
4. CHANGELOG/histórico.

## Documentação externa relacionada

- Portal: `docs/portal/`
- arquitetura global: `docs/architecture/`
- ADRs: `docs/decisions/`
- módulos: `docs/modules/`

## Convenções

- **ACULTA420** = produto técnico/tema/design system;
- `aculta420` = machine name do tema;
- **ACULTA** = marca/plataforma institucional quando aplicável;
- `aculta_portal` = módulo de integração;
- `.aculta-*` e `--aculta-*` = vocabulário visual mantido por decisão
  arquitetural; não são o machine name do tema.

## Provider funcional e shim

`web/themes/custom/aculta420/` é o único provider funcional do tema.
`web/themes/custom/aculta/` é somente o shim temporário da migração 0.1.0 e não
pode receber documentação de feature, Twig, CSS, JS, libraries ou SDC.
