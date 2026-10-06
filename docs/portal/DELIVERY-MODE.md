# Modo de entrega: GitHub-first / Runtime-last

## Contexto

Enquanto a disponibilidade do Codex no ambiente de desenvolvimento estiver
limitada, o projeto adota temporariamente um fluxo **GitHub-first /
Runtime-last**.

O objetivo é avançar tudo que pode ser decidido, revisado e preparado sem
consumir o ambiente Drupal, concentrando Composer/Drush/HTTP/testes integrados
em uma janela posterior.

## Fonte autoritativa

A partir desta decisão:

**`origin/main` é a única base autoritativa.**

Trabalho local antigo ainda não publicado, inclusive a antiga linha 9.3B, é
considerado **descartado/superseded**.

Não tentar reconstruir o roadmap a partir de worktree antigo.

## Próximo retorno ao Codex

Quando o ambiente voltar a ser usado, a primeira ação autorizada é descartar
mudanças locais não publicadas e sincronizar com o remoto:

```sh
git fetch --prune origin
git switch main
git reset --hard origin/main
git clean -fd
```

Se existir a branch local antiga e ela não corresponder a trabalho remoto que
precise ser preservado:

```sh
git branch -D chore/fase-9.3b-estado-integral 2>/dev/null || true
```

Não usar `git clean -fdx`: arquivos ignorados, settings locais, Runtime e
segredos do ambiente não devem ser apagados por esse procedimento.

Não criar stash do trabalho antigo.

## O que pode entrar no main sem Runtime

Pode ser integrado depois de revisão Git/GitHub:

- documentação;
- ADRs;
- inventários;
- source-of-truth maps;
- contratos de componentes;
- planos de teste;
- matrizes de access/cache;
- decisões de arquitetura;
- pesquisa upstream;
- mudanças puramente documentais/metadata sem efeito executável.

## O que pode ser preparado, mas fica em Draft

Pode ser implementado em branch/PR para economizar trabalho futuro, mas não
deve ser considerado validado nem integrado como feature até passar no
Homelab:

- PHP funcional;
- routing/services;
- Event Subscribers;
- JavaScript comportamental;
- config/sync nova;
- integração de módulo contrib;
- alterações de Composer;
- Domain/alias novos;
- AJAX/HTMX;
- mudanças de cache/access;
- integração Forum/Search/Flag/Comment Notify;
- alterações de Commerce/LMS/Profile.

Cada PR desse tipo deve declarar:

`RUNTIME STATUS: DEFERRED`

e listar exatamente quais testes faltam.

## Bootstrap Component Design System

Durante o modo GitHub-first, é especialmente produtivo preparar:

- contratos presenter -> SDC;
- catálogo de componentes;
- props/slots;
- estados visuais;
- mapeamento Bootstrap;
- ownership de CSS/JS;
- matriz de acessibilidade;
- componentes candidatos do Portal.

Mudanças executáveis no tema continuam com os agentes responsáveis pelo tema.

O Portal pode preparar seus presenters/view-models em draft, mas não deve
alterar o tema avançado apenas para viabilizar uma feature ainda não testada.

## Runtime debt

Todo trabalho draft deve alimentar uma fila explícita de validação.

Nenhuma versão funcional recebe tag enquanto houver gate Runtime pendente.

## Janela final de Runtime

Quando o Codex voltar ao Homelab, executar na seguinte ordem:

1. reset/sync para `origin/main`;
2. validar baseline Apache/SQLite;
3. aplicar uma branch/PR preparada por vez;
4. instalar/atualizar dependências necessárias;
5. importar/exportar configuração;
6. executar lint/static tooling disponível;
7. Drush bootstrap/cache/config/updatedb;
8. testes Domain/HTTP;
9. User A/User B;
10. AJAX/access/cache;
11. regressão dos subsistemas existentes;
12. corrigir findings;
13. somente então merge/release/tag.

## Princípio

**Preparar cedo; afirmar PASS somente depois do Runtime.**

Documentação pode dizer "planejado", "preparado" ou "runtime deferred".

Nunca dizer "funciona", "PASS" ou "concluído" sem a evidência correspondente.
