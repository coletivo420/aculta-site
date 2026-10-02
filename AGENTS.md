# ACULTA - Instruções para agentes

## Projeto

Este repositório contém o site institucional da:

ACULTA - Associação Cultural Antiproibicionista

Domínio de produção:

aculta.org

O site é institucional e pertence a uma associação cultural antiproibicionista sem fins lucrativos.

O objetivo inicial é construir um site institucional profissional, acessível, seguro, rápido e adequado à validação institucional do domínio pela Google for Nonprofits.

## Stack

- Drupal 11
- PHP 8.5
- MariaDB
- Composer
- Drush
- Git
- GitHub
- Codex CLI

Ambiente local principal:

Windows + PowerShell

Document root do Drupal:

web/

## Estrutura do desenvolvimento customizado

Tema customizado:

web/themes/custom/aculta/

Módulos customizados:

web/modules/custom/

Configuração exportável do Drupal:

config/sync/

## Regras fundamentais

1. Nunca modificar arquivos do Drupal Core diretamente.

2. Nunca modificar código dentro de:

web/core/

3. Nunca modificar diretamente módulos contrib:

web/modules/contrib/

4. Nunca modificar diretamente temas contrib:

web/themes/contrib/

5. Todo desenvolvimento específico da ACULTA deve ficar preferencialmente em:

web/themes/custom/aculta/

ou:

web/modules/custom/

6. Dependências PHP devem ser instaladas e gerenciadas pelo Composer.

7. Não adicionar vendor/ ao Git.

8. Nunca versionar:

web/sites/default/settings.php

9. Nunca inserir no código:

- senhas
- credenciais de banco
- tokens
- API keys
- segredos
- credenciais da Hostinger
- credenciais do GitHub

10. Não remover ou sobrescrever regras de segurança existentes no .gitignore sem necessidade explícita.

11. Manter compatibilidade com Drupal 11 e PHP 8.5.

12. Priorizar APIs oficiais e padrões do Drupal.

13. Evitar módulos adicionais quando a funcionalidade puder ser implementada adequadamente com Drupal Core.

14. Antes de adicionar uma dependência Composer, explicar sua necessidade.

15. Não executar alterações destrutivas no banco de dados sem autorização explícita.

16. Não apagar conteúdo existente sem autorização.

17. Fazer alterações incrementais e fáceis de revisar no Git.

## Git

Branch principal:

main

Repositório:

coletivo420/aculta-site

Antes de alterações significativas, verificar:

git status

Não fazer force push.

Não reescrever o histórico da branch main.

Não fazer commit de credenciais.

## Drush no Windows

Neste ambiente, executar Drush através de:

php .\vendor\drush\drush\drush.php

Exemplo:

php .\vendor\drush\drush\drush.php status

Para limpar cache:

php .\vendor\drush\drush\drush.php cr

## Identidade institucional

Nome:

ACULTA - Associação Cultural Antiproibicionista

A ACULTA é uma associação cultural antiproibicionista.

O site deve comunicar de forma clara:

- identidade institucional
- finalidade da associação
- atuação cultural
- defesa dos direitos humanos
- debate público sobre políticas de drogas
- projetos e atividades
- transparência institucional
- formas de contato

Evitar linguagem que faça o site parecer uma loja, dispensário ou plataforma comercial de cannabis.

## Arquitetura inicial do site

### Home

Apresentação clara da ACULTA e de sua atuação.

### Institucional

- Quem somos
- Missão e objetivos
- Organização

### Atividades

Apresentação das atividades realizadas pela associação.

### Projetos

Projetos culturais e sociais da ACULTA.

### Notícias

Notícias, artigos e atualizações institucionais.

### Transparência

Área destinada a documentos institucionais, incluindo quando disponíveis:

- Estatuto
- CNPJ
- Atas
- Relatórios
- documentos institucionais

### Contato

Informações oficiais de contato.

### Política de Privacidade

Página com política de privacidade do site.

## Google for Nonprofits

Uma das prioridades iniciais do projeto é permitir que aculta.org represente claramente uma organização real e identificável.

O site deve apresentar claramente:

- nome da organização
- missão
- atividades
- projetos
- informações institucionais
- informações de contato
- transparência
- domínio institucional

Não criar afirmações institucionais ou jurídicas sem fonte fornecida pelo responsável pelo projeto.

Não inventar:

- CNPJ
- endereço
- nomes de dirigentes
- datas
- números de registro
- parceiros
- financiadores
- certificações

Quando uma informação institucional necessária não estiver disponível, utilizar placeholder claramente identificado ou solicitar a informação.

## Front-end

Criar um tema próprio chamado:

aculta

Local:

web/themes/custom/aculta/

Prioridades:

- mobile first
- responsividade
- acessibilidade
- HTML semântico
- boa performance
- CSS organizado
- JavaScript mínimo
- compatibilidade com Drupal
- SEO técnico
- facilidade de manutenção

Evitar page builders pesados.

Evitar dependências JavaScript desnecessárias.

## Segurança

Nunca expor mensagens de erro detalhadas em produção.

Nunca colocar credenciais no repositório.

Não alterar configurações de segurança sem explicar a mudança.

O ambiente local pode utilizar configurações de desenvolvimento diferentes das configurações de produção.

## Forma de trabalho esperada do Codex

Antes de uma alteração relevante:

1. analisar os arquivos relacionados;
2. verificar a arquitetura existente;
3. explicar resumidamente o que será alterado;
4. implementar somente o necessário;
5. verificar erros;
6. informar os arquivos modificados;
7. indicar comandos Drupal/Drush necessários após a alteração.

Não refatorar partes não relacionadas à tarefa sem necessidade.

Não criar funcionalidades que não foram solicitadas.

Quando houver dúvida arquitetural importante, perguntar antes de fazer uma alteração destrutiva.