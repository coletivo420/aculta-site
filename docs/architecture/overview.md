# Visão geral

```text
Drupal Core + módulos funcionais
 User Profile Address Commerce LMS Group
 Node Taxonomy Comment Forum Views Search Domain
                    |
                    v
               aculta_portal
          integração e orquestração
                    |
                    v
                  aculta
               apresentação
```

## Regra arquitetural

`aculta_portal` integra capacidades existentes. Pode organizar rotas, Domain
purpose, experiência da conta, painéis, subscribers, adapters e integrações,
mas não substitui storage ou regras dos módulos especializados.

O tema `aculta` cuida da apresentação e não implementa autenticação, pagamento,
matrícula, progresso, autorização, fórum, comentários, Wiki ou integrações
Google.

## Integrações externas

Serviços que atravessam múltiplos subsistemas, como Google Analytics, Search
Console, OAuth e futura integração Classroom, ficam documentados em
[`docs/integrations`](../integrations/README.md).

Essas integrações devem ser desacopladas do tema e falhar de forma segura sem
interromper o Drupal.
