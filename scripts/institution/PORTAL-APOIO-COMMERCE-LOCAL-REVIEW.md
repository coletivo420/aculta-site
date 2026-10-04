# Revisão local — Portal, Apoio e Commerce

Data: 3 de outubro de 2026
Escopo: ambiente local; nenhuma conexão com produção ou transação real.

> **Atualização de arquitetura posterior — fase Portal, 03/10/2026:** este
> documento registra a auditoria histórica da fase Commerce. A função
> institucional que estava no módulo custom independente `aculta_apoio` foi
> posteriormente transferida para `aculta_portal/src/Support/`. Na Fase 3,
> por decisão do responsável, o módulo foi removido e seus quatro registros
> `aculta_contribution`, dois recibos, entidade e tabelas também foram apagados;
> nada foi migrado para Commerce. Portanto, todas as referências abaixo a
> registros retidos, entity ID e tabela descrevem apenas o estado histórico e
> não a arquitetura atual. Referências a “módulo aculta_apoio” descrevem o
> estado daquela fase e não o estado atual. O gateway, Store, checkout e trabalho
> Mercado Pago seguem pausados e não foram continuados na fase de integrações
> da conta. A configuração atualmente revisada está em
> `PORTAL-ACCOUNT-CONFIG-MANIFEST.json`; o relatório atual é
> `ACCOUNT-INTEGRATIONS-LOCAL-REVIEW.md`.

## Resumo e estado inicial

O ambiente detectado usa Drupal 11.4.8, PHP 8.5.10, Composer e Drush 13.8. O tema público continua `aculta` e o tema administrativo continua Claro. Antes desta rodada já estavam instalados `aculta_apoio`, Drupal Commerce e Commerce Mercado Pago; `aculta_portal` e Profile também já constavam como instalados. O módulo Key estava habilitado, mas não havia nenhuma chave cadastrada. Não existia módulo `aculta_mercadopago`.

Inventário local anterior e confirmado nesta rodada:

| Entidade | Quantidade |
| --- | ---: |
| Registros legados `aculta_contribution` | 4 |
| Commerce orders | 0 |
| Commerce payments | 0 |
| Commerce stores | 0 |
| Profiles | 0 |
| Payment gateways Commerce | 0 |

Os quatro registros de contribuição foram mantidos. Eles não foram convertidos em pagamentos Commerce porque não há orders/payments locais que confirmem o vínculo. A entidade continua disponível somente como arquivo legado restrito e read-only.

## Arquitetura final desta rodada

- **MANTIDO:** Drupal Commerce como autoridade sobre orders e payments; Profile; `aculta_portal`; páginas Minha Conta; conteúdo e visual público já aprovados.
- **ADAPTADO:** `aculta_apoio` agora mantém a página e configurações editoriais de `/apoie`, a leitura administrativa do arquivo legado e a apresentação dos orders próprios do usuário.
- **REMOVIDO:** cliente HTTP Mercado Pago próprio, leitor de credenciais, implementação custom de assinatura x-signature, webhook custom, fila de reconciliação e manager que criava Orders/Preapproval diretamente. O endpoint custom `/apoie/webhook` foi removido. Nenhuma camada `aculta_mercadopago` foi criada.
- **RETIRADO:** Drupal Key, desinstalado e removido por Composer depois de confirmar zero entidades Key e nenhuma dependência de outro pacote. Não foi criada tela de segredos paralela.
- **NÃO CRIADO:** Store e gateway Commerce. Não havia credenciais sandbox fornecidas/configuradas nesta execução; nenhum gateway vazio ou dado institucional fictício foi criado.

`/apoie` permanece público, mas mostra aviso de indisponibilidade e não inicia pagamento até existir fluxo de order Commerce seguro. A opção mensal não é exibida. Pix continua removido da interface e da configuração ativa.

## Segurança e acesso administrativo

As configurações do Apoio ficam em `/admin/config/aculta/apoio`, protegidas por `administer aculta apoio` (`restrict access: true`). Esse formulário edita somente textos, valores sugeridos e limites; não contém credenciais. A configuração de gateway existe somente nas telas Commerce e exige `administer commerce_payment_gateway` (`restrict access: true`). A consulta dos quatro registros legados exige `view aculta contributions`, também restrita. Nenhuma permissão sensível foi dada a papéis não administrativos.

O script `scripts/validate-portal-commerce-security.php` executou **76 verificações read-only**. Rotas de configuração, gateway e registros foram negadas a anônimos e usuários autenticados comuns e permitidas ao administrador simulado. O papel autenticado só recebeu `access aculta portal` e permissões Profile de criar/ver/editar o próprio perfil; não recebeu administração de Apoio, gateways, pagamentos ou Profile. Também foi verificada a igualdade dos 18 objetos selecionados entre configuração ativa e `config/sync`, e que nenhum gateway está exportado.

O endpoint HTTP de gateway fica sob Commerce, e `/apoie/webhook` custom agora retorna 404. `/admin/config/aculta/apoio`, `/admin/commerce/support` e `/admin/commerce/config/payment-gateways` retornaram 403 sem sessão administrativa. A página pública `/apoie` retornou 200. As páginas privadas do portal retornaram 403 quando acessadas sem login.

**Limite de segurança do gateway:** o formulário do contrib apresenta credenciais em campos de texto e as armazena na configuração da entidade de gateway; essa configuração é exportável. Nenhum gateway foi criado nem credencial foi inserida, então não há segredo atual em `config/sync`. Não configurar segredo real até haver estratégia de segredo por ambiente/deploy que evite exportação. Não foi feita alteração em contrib. A revisão do código contrib também não confirmou validação de `x-signature` própria; esse comportamento precisa de auditoria antes de habilitar notificações em produção.

## Commerce Mercado Pago e recorrência

Pacotes travados no Composer:

- `drupal/commerce` **3.3.10**, estável e compatível com Drupal ^10.3/^11.
- `drupal/commerce_mercado_pago` **3.0.0-rc3**, exige Commerce ^2/^3, Drupal ^9/^10/^11 e usa `mercadopago/dx-php` 3.x.
- `drupal/profile` **1.14.0**, compatível com Drupal ^9/^10/^11.
- `drupal/key` foi removido; `drupal/commerce_recurring` não foi instalado.

O Drupal.org identifica o gateway Mercado Pago como **release candidate** e explicitamente fora da política de Security Advisories. O plugin oferece Checkout Pro com redirecionamento, configurações de parcelamento e exclusão de meios/tipos de pagamento, ambientes de teste/stage/produção e suporte a reembolso. A leitura local mostra checkout baseado em `PreferenceClient`/Preferences API; métodos de pagamento são marcados não reutilizáveis. A depuração detalhada registra mais informações e deve permanecer desativada até revisão de privacidade.

**Apoio mensal — NÃO SUPORTADO AGORA.** O Mercado Pago oferece API de Assinaturas (`POST/GET/PUT /preapproval`, consulta de faturas e pagamentos), mas o gateway Drupal instalado não implementa essa ponte nem fornece método de pagamento reutilizável. Commerce Recurring requer gateway on-site com método tokenizado/reutilizável. Portanto a mensalidade foi retirada do frontend, sem instalar Commerce Recurring nem inventar integração custom paralela.

## Profile e Portal

Profile `participante` contém Nome, Sobrenome, Telefone, WhatsApp, Cidade e UF, todos opcionais e com `profile_private: true`. Não são coletados CPF, RG, nascimento ou endereço residencial. `/minha-conta`, `/minha-conta/perfil` e `/minha-conta/seguranca` exigem permissão de acesso ao portal; o perfil é carregado pelo usuário atual. `/minha-conta/apoio` consulta somente orders cujo `uid` seja o usuário autenticado e desativa cache da resposta personalizada.

O painel não oferece links vazios para compras, cursos, atividades ou certificados.

## Configuração e Composer

A configuração ativa foi reconciliada de forma explícita com `config/sync` usando `scripts/sync-portal-apoio-config.php` e o manifesto `scripts/institution/PORTAL-APOIO-COMMERCE-CONFIG-MANIFEST.json`. Foram exportados 18 objetos previamente selecionados: extensão, papel autenticado, configuração editorial do Apoio, Profile participante, seis field storages, seis field instances e displays de formulário/visualização. Configuração de gateway não foi selecionada. Não houve export cego.

O post-update `aculta_apoio_post_update_remove_direct_transfer_option` remove flags antigas de gateway customizado e Pix sem tocar nas quatro contribuições legadas. Drush `updatedb` tentou iniciar o wrapper `vendor/bin/drush` e falhou porque esse ambiente Windows não tem `sh`; executei o post-update pendente pelo `update.post_update_registry` do Drupal e confirmei `updatedb:status` sem atualizações pendentes. Cache rebuild foi concluído.

`composer validate --no-check-publish`: válido, com avisos preexistentes de constraints exatas para Bootstrap5 e Pathauto. `composer audit --no-dev`: nenhum advisory. A remoção do Key atualizou `composer.json` e `composer.lock`; Commerce/Commerce Mercado Pago/Profile permanecem como dependências.

## Documentação oficial consultada em 03/10/2026

- [Commerce Mercado Pago no Drupal.org](https://www.drupal.org/project/commerce_mercado_pago) e [releases](https://www.drupal.org/project/commerce_mercado_pago/releases): release RC, compatibilidade, ambientes e estado fora da política de segurança.
- [Commerce Core releases](https://www.drupal.org/project/commerce/releases): Commerce 3.3.10.
- [Commerce Recurring](https://www.drupal.org/project/commerce_recurring): necessidade de gateway on-site e métodos tokenizados/reutilizáveis; RC3 sem release estável suportado.
- [Mercado Pago — visão geral da API de Assinaturas](https://www.mercadopago.com.br/developers/pt/reference/online-payments/subscriptions/overview), [gerenciamento de assinaturas](https://www.mercadopago.com.br/developers/pt/docs/subscriptions/subscription-management) e [webhooks](https://www.mercadopago.com.br/developers/pt/docs/subscriptions/additional-content/your-integrations/notifications/webhooks).
- [Profile 1.14](https://www.drupal.org/project/profile/releases/8.x-1.14).

## Testes e arquivos

- Drush status: Drupal 11.4.8 / PHP 8.5.10; cache rebuild OK; nenhum post-update pendente.
- Acesso e arquitetura: 57 verificações read-only aprovadas.
- HTTP local: `/apoie` 200; rotas privadas/admin sem autenticação 403; endpoint custom antigo 404; nenhuma resposta testada continha padrões de credenciais.
- PHP lint: arquivos PHP dos módulos customizados e scripts sem erro; `node --check` passou para o JavaScript customizado.
- YAML: 619 arquivos em módulos customizados e `config/sync` parseados.
- `composer validate`: OK com os dois avisos de constraints exatas descritos acima.
- `composer audit`: sem advisories.
- Nenhum pagamento, assinatura, cancelamento ou chamada financeira real foi feito.

Arquivos de código principais alterados: `composer.json`, `composer.lock`, `web/modules/custom/aculta_apoio/` e `web/modules/custom/aculta_portal/`. Documentação criada: READMEs de ambos os módulos, este relatório e o manifesto de configuração. `scripts/validate-portal-commerce-security.php` contém as verificações locais.

## Pendências e classificação

1. Construir fluxo institucional de apoio único que crie order Commerce e associe o pagamento; `/apoie` está intencionalmente indisponível para cobrança.
2. Obter/configurar credenciais sandbox e validar checkout somente após definir armazenamento que não exporte segredos.
3. Auditar webhook/retornos do gateway, incluindo validação de assinatura, conciliação e idempotência.
4. Escolher estratégia segura para credenciais antes de qualquer configuração real; o gateway RC salva valores em configuração exportável.
5. Apoio mensal pendente de gateway recorrente realmente compatível.
6. Resolver arquivo/migração dos quatro registros `aculta_contribution` legados sem perda de conteúdo ou falsa equivalência com Commerce.
7. Criar Store institucional somente quando o fluxo de checkout for implementado.
8. Reexecutar validação visual autenticada e completar testes funcionais de checkout sandbox depois da implementação.

**NÃO PRONTO PARA REVISÃO LOCAL** — a separação de permissões está verificada, mas não há fluxo Commerce de apoio único ativo, gateway/store configurado ou estratégia segura para credenciais e webhooks. A página não simula funcionalidade de pagamento.

## Refinamento visual institucional

Esta rodada aplicou e validou os ajustes visuais locais sem alterar Core, contrib, Bootstrap, Composer ou o comportamento do VVJB.

- Arquivos diretamente relacionados: `web/themes/custom/aculta/css/style.css`, `web/themes/custom/aculta/templates/page.html.twig`, `web/themes/custom/aculta/templates/block--system-branding-block.html.twig`, `web/themes/custom/aculta/aculta.theme`, `web/modules/custom/aculta_apoio/aculta_apoio.links.menu.yml`, `web/modules/custom/aculta_apoio/aculta_apoio.post_update.php` e `config/sync/webform.webform.aculta_participation.yml`.
- Paleta consolidada em custom properties existentes do tema: institucional `#689427`, verde escuro `#0c3c29`, amarelo `#f2ca36`, vermelho `#d4452d`, creme `#fbf4e8` e branco `#ffffff`. Não há `color-mix()` no CSS institucional.
- Header, navbar e footer usam exatamente `#689427`; fundo editorial `#fbf4e8`; superfícies/cards `#ffffff`.
- A marca textual do header foi substituída pelo logo horizontal branco otimizado `web/themes/custom/aculta/assets/branding/aculta/web/logo-aculta-horizontal-branco-900x300.png` (900×300, proporção 3:1, transparência preservada). O original de 1800×600 em `source/` não foi alterado. Texto alternativo expõe o nome institucional.
- Menu mantém os oito itens sem quebra: teste visual a 1100px mostrou uma linha; em 1099px ativou o menu recolhível. Abertura, navegação e fechamento por Escape foram verificados. Hover/foco usam amarelo com texto verde escuro para preservar contraste; item atual usa verde escuro, texto branco e sublinhado amarelo.
- Rodapé: quatro colunas em desktop, empilhamento responsivo, quatro links de participação (Apoie, Faça Parte, Contato e Política de Privacidade) e remoção da duplicidade “Acompanhe nossas atividades” por desativação do link legado, sem apagar conteúdo. Títulos estão explicitamente em Oswald; descrição, dados e itens de navegação em Inter. O corpo permanece em 18,67px, bold: o branco sobre `#689427` tem contraste aproximado de 3,59:1, portanto reduzir abaixo do limite de texto grande deixaria de cumprir AA. A redução adicional de tamanho pedida não é compatível com manter simultaneamente o verde e o branco aprovados e AA.
- A regra reutilizável `.aculta-surface`/`.aculta-content-surface` organiza superfícies brancas sem transformar toda seção em card. “Nossa Missão”, “O Que Fazemos”, projetos, listas de notícias/atividades, destaques editoriais, Transparência e formulários usam branco somente onde cria hierarquia. Hovers são discretos e `prefers-reduced-motion` reduz movimento.
- O CSS contextual do VVJB foi preservado; nenhum JavaScript ou timer de carrossel foi acrescentado. No screenshot da Home a região editorial exibiu controles/indicadores, mas os cards não ficaram visíveis; isso requer verificação funcional própria do VVJB e permanece pendente, sem substituição artesanal.
- `/faca-parte` foi criado com Webform Drupal: nome/e-mail obrigatórios, contato opcional, cidade, interesses, mensagem e consentimento obrigatório desmarcado com link para Privacidade. Não cria conta nem associação automaticamente. Página respondeu HTTP 200. O telefone institucional existente e o e-mail foram apresentados; um WhatsApp direto não foi publicado porque o número indicado como possibilidade não aparece confirmado nos dados locais.
- O item “Apoie” permanece no footer; `/apoie` responde HTTP 200, mas não oferece checkout porque o fluxo Commerce seguro ainda não foi implementado. O smoke test institucional terminou com exit code 1 ao assinalar esse estado — o script ainda espera um botão de contribuição — e não por erro HTTP/PHP. Não houve pagamento.
- Ajuste solicitado em 03/10/2026: o slogan oficial “Lutando por um futuro livre da proibição.” é renderizado imediatamente abaixo da logo branca no cabeçalho, em Inter 12px/500; no desktop não quebra linha. Deixou de ser exibido no bloco institucional do rodapé, sem apagar o valor editorial original. Branding e menu alinham pelo topo para manter a leitura logo → slogan sem deslocar a navegação. Uma base flex herdada que reservava 18rem no eixo vertical foi removida; a imagem mantém sua proporção intrínseca.
- Ajuste seguinte: removida apenas a exibição de `aculta.org` do bloco de dados institucionais no rodapé, conforme solicitado; o valor armazenado permanece intacto e o domínio continua disponível nos demais contextos institucionais.
- Menu de conta: removido o link padrão do Drupal `user.page` (`/user/{user}`, renderizado como `/user/1` para a conta de teste). O link do portal `/minha-conta` permanece como destino único da conta; “Sair” continua disponível. A remoção é feita pelo hook `hook_menu_links_discovered_alter()` do módulo customizado, sem alterar Core ou apagar dados.
- Links de navegação do rodapé agora usam Inter regular (peso 400); títulos seguem em Oswald 700 para separação tipográfica mais clara.
- Testes: cache rebuild e Drush status OK (Drupal 11.4.8, PHP 8.5.10); PHP lint nos arquivos alterados OK; `git diff --check` OK; páginas institucionais e `/faca-parte` retornaram HTTP 200; navegador testou 1440, 1200, 1100, 1099, 1024, 768, 480 e 360px sem overflow; `/institucional`, `/transparencia` e `/contato` também foram checadas em mobile e desktop. O script abrangente identificou como pendências o checkout ainda inativo e a região de slides em branco.
- `config/sync/webform.webform.aculta_participation.yml` registra a configuração exportável do formulário. O post-update de menu foi executado no banco local; nenhuma exportação ampla de configuração foi feita.
- Estado Git continua sem stage/commit: `composer.json`, `composer.lock` modificados previamente; `config/`, `scripts/`, `web/modules/custom/` e `web/themes/custom/` não rastreados antes desta rodada. Nenhum commit, push ou deploy foi feito.

**PRONTO PARA REVISÃO VISUAL** — o refinamento visual e a página Faça Parte estão disponíveis localmente. O checkout inativo e a renderização vazia do VVJB estão documentados como pendências funcionais, sem simular comportamento inexistente.
