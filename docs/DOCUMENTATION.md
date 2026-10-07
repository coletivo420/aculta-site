# Política de documentação

Data da revisão: 2026-10-07.

## Objetivo

Manter documentação útil para humanos e IA sem transformar o repositório em um
arquivo de snapshots de execução.

## Tipos permitidos

### Canônica

Descreve o estado atual do sistema e seus contratos.

Exemplos:

- arquitetura;
- fontes de verdade;
- módulos;
- integrações;
- regras de domínio;
- comportamento atual do Portal/tema.

### ADR

Registra uma decisão arquitetural que precisa de contexto e justificativa.

### Operacional

Runbooks duráveis de teste, hardening, deploy/release e Homelab.

### Roadmap

Somente trabalho futuro ainda relevante. Deve apontar para contratos canônicos,
não duplicá-los.

## O que não deve virar documento permanente

Não criar um novo arquivo apenas para:

- registrar um PR específico;
- guardar SHA/HEAD de uma rodada;
- copiar logs de Runtime;
- registrar uma fase já concluída;
- manter checklist temporária de uma correção;
- duplicar uma regra que já possui documento canônico.

Essas evidências pertencem ao PR, issue, CHANGELOG ou histórico Git.

## Regra anti-obsolescência

Quando uma fase termina:

1. migrar decisões duráveis para a documentação canônica;
2. migrar regras de segurança para [ANTI-REGRESSION.md](ANTI-REGRESSION.md);
3. atualizar roadmap/índices;
4. remover o documento transitório.

## Responsabilidade por mudança

Mudanças em:

- módulos → atualizar `docs/modules/`;
- autenticação/integrações → `docs/integrations/`;
- arquitetura/ownership → `docs/architecture/` ou ADR;
- Portal → `docs/portal/`;
- tema/SDC → documentação do tema;
- operação/release → `docs/operations/`.

## IA e automação

Agentes devem:

- começar por [README.md](README.md);
- ler [ANTI-REGRESSION.md](ANTI-REGRESSION.md) antes de refactor transversal;
- não recriar documentos de fase removidos;
- não converter histórico em requisito atual;
- atualizar documentação junto do código;
- preservar links relativos e evitar duplicação de fonte de verdade.
