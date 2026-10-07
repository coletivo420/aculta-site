# Versionamento do tema ACULTA

## Escopo

Este versionamento pertence exclusivamente ao tema customizado `aculta` e ao
**ACULTA Bootstrap Component Design System**. Ele não representa a versão do
site Drupal inteiro, do módulo `aculta_portal` nem dos módulos contrib.

Versão corrente: **0.1.0**.

## Esquema

O tema usa SemVer: `MAJOR.MINOR.PATCH`.

Enquanto estiver em `0.x`:

- **MINOR** representa uma entrega arquitetural/coesa do design system;
- **PATCH** representa correção compatível, documentação, acessibilidade,
  performance ou estabilização sem quebra intencional de contrato;
- prereleases podem usar `-dev.N`, `-beta.N` ou `-rc.N` quando necessário.

Exemplos:

```text
0.2.0-dev.1
0.2.0-rc.1
0.2.0
0.2.1
```

## Política de compatibilidade

### SDC `stable`

Mesmo antes de 1.0, componentes marcados como `stable` recebem proteção extra:

- PATCH não quebra props, slots, markup contratual ou significado semântico;
- mudança incompatível exige MINOR, nota de migração e justificativa;
- remoção deve ter substituto documentado quando houver consumidor real.

### SDC `experimental`

Pode evoluir entre versões MINOR antes de 1.0, mas toda quebra deve aparecer no
CHANGELOG e na documentação do componente.

### A partir de 1.0.0

- MAJOR: quebra de API/contrato estável;
- MINOR: funcionalidade compatível;
- PATCH: correções compatíveis.

## Tags Git

Como o repositório hospeda mais que o tema, não usar tags genéricas `v0.2.0`.

Formato:

```text
aculta-theme-v0.1.0
aculta-theme-v0.2.0
aculta-theme-v1.0.0
```

## Fonte de verdade da versão

A versão publicada deve aparecer em:

1. `aculta.info.yml`;
2. `CHANGELOG.md`;
3. `docs/roadmap.md`;
4. tag Git de release, quando criada.

Não atualizar a versão em apenas um desses locais.

## Checklist de release

Antes de uma versão MINOR ou MAJOR:

- atualizar CHANGELOG e roadmap;
- validar schemas SDC;
- `drush cr`;
- validar Twig/PHP/YAML;
- validar MAIN, ACCOUNT, SUPPORT, COLETIVO420, WIKI420, SHOP e COURSES;
- validar desktop/mobile;
- validar teclado, foco e reduced motion;
- validar componentes alterados com conteúdo vazio e títulos longos;
- revisar assets globais versus contextuais;
- confirmar ausência de nova regra de negócio no tema;
- registrar migração quando contratos estáveis mudarem.

PATCHes puramente documentais podem reduzir o conjunto de testes, desde que
não alterem metadata/runtime.
