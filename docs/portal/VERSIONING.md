# Versionamento do ACULTA Portal

O Portal evolui de forma independente da linha de refatoração do tema.

## Tags

Usar tags prefixadas:

```text
portal-v0.10.0
portal-v0.11.0
portal-v0.12.0
...
portal-v1.0.0
```

Não usar tags genéricas `vX.Y.Z` para o Portal porque o mesmo repositório contém
outros produtos técnicos.

## SemVer

- MAJOR: quebra deliberada de contratos Portal/integradores;
- MINOR: capacidade funcional completa;
- PATCH: correção compatível e validada.

## Branches

Formato preferido:

```text
portal/v0.10.0-foundation
portal/v0.11.0-forum
portal/v0.12.0-participation
```

Branches devem ser curtas e nascer do `main` atualizado.

## Commits

Um commit deve representar:

```text
alteração lógica
+ testes que passaram
+ documentação mínima correspondente
```

Prefixos:

- `docs(portal):`
- `chore(portal):`
- `refactor(portal):`
- `feat(portal):`
- `fix(portal):`
- `test(portal):`

Evitar commits WIP e mega-commits.

## Regra de teste

Um passo funcional só vira commit depois dos testes definidos em
[TESTING.md](TESTING.md).

Quando um teste não pode ser executado por limitação externa, isso deve ser
registrado explicitamente; não converter "inconclusivo" em "PASS".

## Documentação

Arquitetura, decisões e roadmap são mantidos em `docs/portal/`.

O Codex deve atualizar documentação de execução e CHANGELOG quando implementar
uma feature, mas não deve reescrever arquitetura/roadmap sem uma decisão
explícita.

## Release

Uma versão só recebe tag depois de:

1. PR integrado ao main;
2. testes obrigatórios PASS;
3. configuração exportável limpa;
4. CHANGELOG atualizado;
5. documentação da versão coerente.
