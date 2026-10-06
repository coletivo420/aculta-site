# ADR-001: tema versus Portal

Status: Accepted

`aculta` é apresentação. `aculta_portal` é integração. Módulos especializados continuam donos de dados e regras. O tema pode consumir contexto de apresentação fornecido pelo Portal, mas não persiste estado funcional.

## Aplicação: breadcrumb público

A política de breadcrumb pertence ao `aculta_portal`: purpose, rotas ocultas, raiz, hierarquia, cache metadata e título atual. O tema pode adaptar esses dados para Twig, mas não deve repetir a decisão de domínio ou rota.

