# ACULTA Portal

`aculta_portal` é a camada de integração e customização da plataforma Drupal
da Associação Cultural Antiproibicionista.

> O Portal organiza, apresenta e conecta capacidades Drupal sem substituir suas
> fontes de dados e regras de negócio.

## Papel

O Portal integra a experiência de:

- Conta/User;
- Profile/Address/CEP;
- Social Auth;
- apoio/Commerce;
- cursos/LMS/Group;
- Wiki420;
- Fórum e comentários, quando a versão correspondente for implementada;
- participação do usuário;
- administração consolidada;
- Domain purposes.

Não é sistema próprio de autenticação, endereço, pagamento, LMS, matrícula,
progresso, Wiki, fórum ou comentários.

## Arquitetura

```text
Core + módulos contrib -> aculta_portal -> tema ACULTA420
```

O tema é apresentação. O Portal é integração. Os módulos funcionais continuam
fontes de verdade.

## Baseline Drupal 11+

O módulo adota Drupal Core **11.3+** como baseline arquitetural. O padrão
normativo está em
`docs/portal/DRUPAL-11-STANDARDS.md`.

Regras principais: hooks runtime OOP com `#[Hook]`; DI em `src/` sem
`\Drupal::*`; callbacks Form API por `service.id:method`; subscribers com
`event_subscriber`; Entity/Views APIs via services/storages; `strict_types=1`
em classes runtime; Render API/cacheability/access explícitos; lifecycle hooks
procedurais apenas quando o Core exige.

Gate:

```sh
php vendor/drush/drush/drush.php php:script validate-aculta-portal-drupal11 --script-path=../scripts
```

## Contratos

- trabalhar com Domain purpose, não hostname hardcoded;
- preferir DI, Entity API, Views e serviços públicos;
- em controllers novos/refatorados, dependências de runtime entram por DI explícita; não depender de helpers de `ControllerBase` que resolvam serviços de forma lazy;
- `ContainerInjectionInterface::create()` pode montar as dependências do controller, mas a lógica funcional não consulta o container;
- não consultar tabelas contrib diretamente quando houver API;
- não criar storage paralelo;
- respeitar entity access antes de expor metadata;
- dados privados variam por usuário e não usam cache compartilhado;
- segredos ficam fora de Configuration Sync e Git;
- AJAX usa preferencialmente APIs Drupal;
- toda feature identifica fonte de verdade, access, cache, Domain e testes.

### Breadcrumb público

`AcultaBreadcrumbBuilder` é a fonte de verdade para purpose público, rotas ocultas, raiz por domínio, hierarquia, cache metadata e resolução segura do título atual. O tema `aculta420` não replica essa política: consome os links Drupal e `currentTitle()` apenas para renderização.

## Documentação normativa

Consultar [docs/portal](../../../../docs/portal/README.md).

O roadmap e decisões de arquitetura vivem lá. Este README serve como entrada
rápida para quem está no diretório do módulo.

## Changelog

Consultar [CHANGELOG.md](CHANGELOG.md).
