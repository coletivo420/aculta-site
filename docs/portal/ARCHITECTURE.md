# Arquitetura do ACULTA Portal

## Papel do módulo

`aculta_portal` é a camada de integração e customização da plataforma ACULTA.

```text
Drupal Core + módulos contrib
            |
            v
      aculta_portal
 integração / orquestração
            |
      +-----+------+
      |            |
painel usuário  painel admin
      |            |
      +-----+------+
            |
            v
        tema aculta
```

O Portal pode combinar informações de diferentes subsistemas em uma mesma tela,
adaptar textos e formulários, definir navegação multidomínio, adicionar
acessibilidade, integrar AJAX, aplicar cache metadata e oferecer painéis.

Ele não deve criar armazenamento paralelo quando Drupal Core ou um módulo
funcional já possui a informação.

## Limites

### O Portal pode

- agregar User, Profile, Address, Commerce, LMS, Wiki e Fórum;
- construir dashboards e resumos;
- adaptar formulários nativos;
- compor Views e render arrays;
- aplicar Domain purpose às rotas;
- construir links entre subdomínios;
- adaptar UX de CEP sem duplicar endpoint/cache;
- integrar participação do usuário;
- integrar administração;
- adicionar adapters pequenos quando uma API upstream exigir composição;
- manter comportamento AJAX com APIs do Drupal;
- adicionar cache contexts/tags/max-age apropriados.

### O Portal não pode

- criar autenticação própria;
- criar perfil/endereço paralelo;
- criar ledger financeiro;
- criar matrícula/progresso paralelo;
- criar entidades próprias de tópico/resposta quando Forum/Comment atendem;
- criar tabela própria de comentários;
- duplicar busca textual se Search API atender;
- duplicar bookmarks/follows se Flag atender;
- duplicar transporte AJAX genérico do Drupal sem justificativa;
- depender do tema para regras de negócio;
- colocar regra de negócio no tema.

## Painel do usuário

A Conta é uma experiência integrada, não uma fonte de verdade.

A evolução prevista inclui:

- visão geral;
- meus dados;
- endereço + CEP;
- foto;
- segurança;
- e-mail;
- conexões sociais;
- meu apoio;
- meus cursos;
- minha participação;
- contribuições Wiki;
- tópicos e comentários no Fórum;
- favoritos/acompanhamentos quando adotados.

Cada card ou seção consulta sua fonte real.

## Painel administrativo

O Portal deve evoluir para um hub de administração que **encaminha e resume** as
ferramentas existentes, sem recriar seus CRUDs.

Áreas previstas:

- pessoas/perfis;
- Domains;
- Wiki;
- Fórum e moderação;
- cursos/LMS/Group;
- Commerce/apoio/pagamentos;
- Webforms;
- requisitos;
- segurança.

## Multidomínio

Purposes atuais:

| Purpose | Produção | Homelab |
| --- | --- | --- |
| MAIN | aculta.org | aculta.aculta.toca.net.br |
| ACCOUNT | conta.aculta.org | conta.aculta.toca.net.br |
| SUPPORT | apoio.aculta.org | apoio.aculta.toca.net.br |
| MAGAZINE | coletivo420.aculta.org | coletivo420.aculta.toca.net.br |
| WIKI | wiki420.aculta.org | wiki420.aculta.toca.net.br |
| SHOP | loja.aculta.org | loja.aculta.toca.net.br |
| COURSES | cursos.aculta.org | cursos.aculta.toca.net.br |
| FORUM | forum.aculta.org | forum.aculta.toca.net.br |

FORUM é planejado e só passa a ser ativo quando a versão correspondente for
implementada e validada.

Rotas especializadas devem operar no purpose correto e, salvo decisão
arquitetural explícita, retornar 404 no host incorreto.

## Tema

O tema `web/themes/custom/aculta` é responsável por apresentação visual,
componentes, Twig, CSS, responsividade e identidade.

O `aculta_portal` fornece render arrays, dados, forms e integração.

A refatoração do tema ocorre em uma linha separada. Mudanças Portal não devem
reescrever o tema como efeito colateral.

## Bancos

Atual:

- Homelab Runtime: SQLite;
- Estados: SQLite;
- produção: MariaDB.

Código Portal deve usar Entity API, Form API, serviços públicos e Database API
portável. SQL específico de um banco exige justificativa e teste.

## Critério para código custom

Antes de adicionar uma classe/função custom, responder:

- Core já oferece isso?
- contrib instalado já oferece isso?
- outro contrib estável e coberto por Security Advisory oferece isso?
- Views/configuração resolve sem PHP?
- é realmente integração específica da ACULTA?

Se as quatro primeiras respostas forem negativas ou insuficientes, código
custom pode ser justificado.
