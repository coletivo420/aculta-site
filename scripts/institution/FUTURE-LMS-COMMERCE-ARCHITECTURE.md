# Arquitetura futura: cursos, aprendizagem e transações

Este documento registra uma direção futura. Não instala nem habilita Drupal LMS ou Drupal Commerce e não altera a modelagem editorial/financeira atual.

## Responsabilidades

- **Drupal LMS** deverá administrar cursos, aulas, atividades de aprendizagem, matrículas, progresso e certificados.
- **Drupal Commerce** deverá administrar produtos/variações, preços, carrinho, pedidos, checkout e estado transacional.
- **Gateway de pagamento** deverá processar os meios de pagamento hospedados e devolver notificações verificáveis.
- Uma integração explícita deverá liberar ou suspender matrícula após confirmação do pedido/pagamento, com tratamento idempotente e trilha administrativa.

Curso não será modelado como Projeto, Evento ou Produto artesanal. Preço não será adicionado a Evento ou Projeto. O domínio institucional de apoio está organizado em `aculta_portal/src/Support/`; ele não processa cartões nem substitui Commerce. Qualquer futura cobrança deverá usar Commerce e um gateway validado.

Antes de instalar dependências, verificar versões estáveis compatíveis com Drupal/PHP vigentes, política de segurança Drupal, manutenção dos projetos, licenças, requisitos de hospedagem e necessidade real de catálogo/vendas. Definir migração, permissões, LGPD, retenção e recuperação de pagamentos antes de habilitar cobrança por cursos.
