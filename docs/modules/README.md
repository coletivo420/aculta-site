# Módulos Drupal do ACULTA

Data da revisão: 2026-10-07.

Esta pasta documenta por domínio os módulos Core/contrib usados pelo projeto,
por que existem, qual é a fonte de verdade e quais combinações não devem ser
reintroduzidas.

## Regras gerais

- Core/contrib permanecem donos das capacidades que já fornecem;
- `aculta_portal` integra os módulos, mas não duplica storage ou regra de negócio;
- toda inclusão/remoção de módulo deve atualizar Composer, Configuration Sync e
  esta documentação;
- conflitos conhecidos devem ser registrados com uma decisão explícita;
- módulos removidos não podem voltar apenas porque aparecem em um recipe ou
  instalação antiga.

## Índice

- [Autenticação e identidade](AUTHENTICATION.md)
