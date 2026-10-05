# Visão geral

```text
Drupal Core + módulos funcionais
 User Profile Commerce LMS Group Node Domain
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

`aculta_portal` integra capacidades existentes. Pode organizar rotas, Domain purpose, experiência da conta, subscribers, helpers e integrações, mas não substitui storage ou regras dos módulos especializados.

O tema `aculta` cuida da apresentação e não implementa autenticação, pagamento, matrícula, progresso ou autorização.
