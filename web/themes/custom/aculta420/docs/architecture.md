# Arquitetura

## Camadas

```text
Core + contrib
  User / Profile / Node / Views / Commerce / LMS / Group / Domain
                           |
                           v
                    aculta_portal
          access / cache / presenters / purpose
                           |
                           v
                      ACULTA420
 Foundations / Components / Patterns / Shell
                           |
                           v
                      Bootstrap 5
```

## Responsabilidades

### Drupal Core + contrib

São fonte de verdade de dados, permissões, entidades e regras funcionais.

### aculta_portal

Pode:

- integrar módulos;
- resolver Domain purpose;
- preparar presenters/view-models;
- preservar access/cache metadata;
- construir URLs de domínio;
- coordenar UX de Conta/Cursos/Apoio/Wiki.

Não deve empurrar storage ou regra de negócio para o tema.

### ACULTA420

É dono de:

- tokens e identidade visual;
- layout/shell público;
- Twig de apresentação;
- SDCs;
- CSS/JS de apresentação;
- responsividade;
- acessibilidade visual/comportamental complementar;
- progressive enhancement.

Não é dono de autenticação, pagamento, matrícula, progresso, Domain access,
persistência ou autorização.

### Bootstrap5

É infraestrutura. ACULTA420 não embarca outra cópia do Bootstrap e não
reimplementa Collapse, Dropdown, Offcanvas, Modal ou outras engines existentes.

## Presenter -> SDC

```text
Node -----------+
View -----------+
Commerce -------+--> presenter/render array --> SDC --> tokens + Bootstrap
LMS/Group ------+
Block ----------+
```

Presenter conhece Drupal. SDC conhece seu contrato de apresentação.

Presenter deve preservar, quando aplicável:

- `attributes`;
- `title_prefix` / `title_suffix`;
- access;
- cache tags/contexts/max-age;
- render arrays seguros.

SDC não recebe entidade inteira como atalho e não usa service locator.

## Namespace

O provider do tema é `aculta420`.

Exemplo:

```twig
{{ include('aculta420:editorial-card', {...}, with_context = false) }}
```

O ID do provider faz parte da API SDC. O rename 0.1.0 é deliberadamente o
ponto de ruptura entre o tema histórico e a nova fundação.

## IDs preservados

Nem todo identificador `aculta_*` foi renomeado.

Permanecem estáveis quando representam:

- marca institucional;
- Domain IDs;
- content types;
- menus;
- Views;
- webforms;
- block placement IDs existentes;
- CSS classes/tokens do design language.

Isso evita quebrar configuração/conteúdo sem benefício. O machine name do tema,
por outro lado, é sempre `aculta420`.

## SDC

- components podem viver em subdiretórios;
- schemas são obrigatórios no tema;
- assets com o mesmo nome do component são auto-descobertos;
- variants nativos devem ser usados quando a mesma família visual compartilha
  semântica/estrutura;
- componentização não autoriza duplicar markup Bootstrap nem mover regra de
  negócio.

Referências:
- https://www.drupal.org/docs/develop/theming-drupal/using-single-directory-components/creating-a-single-directory-component
- https://www.drupal.org/docs/develop/theming-drupal/using-single-directory-components/using-your-new-single-directory-component
- https://www.drupal.org/node/3517062
