# Hardening

Data da revisão: 2026-10-07.

## Ordem de revisão

### 1. Access

- route/entity/revision access;
- ownership;
- User A/B;
- admin parcial/completo;
- wrong-host.

Não confiar em esconder links como controle de acesso.

### 2. Cache

- contexts;
- tags;
- max-age;
- Views/BigPipe;
- Domain;
- user/user.permissions;
- grants/revisions.

Testar alternando User A/B.

### 3. Mutações

Forms, AJAX, comentários, Profile/Address, e-mail, Social Auth, Commerce e ações
admin devem usar mecanismos Core/contrib, método/access/CSRF/confirmations
adequados.

### 4. Sessão e multidomínio

Login/logout, reset, OAuth, cookies, shared session e wrong-host.

### 5. Integrações

SMTP, Social Auth, Turnstile, CEP, Commerce/Mercado Pago, Search e integrações
Google ativas. Simular indisponibilidade.

### 6. Performance

Medir antes de otimizar:

- N+1/entity loads;
- Views/Search queries;
- orders/payments;
- agregações;
- CSS/JS/AJAX volume.

Não criar cache compartilhado inseguro para dados privados.

### 7. Cron / queues / logs

Inventariar jobs; exigir execução repetível/failure recovery; minimizar dados em
logs e nunca registrar segredo/token.

### 8. Browser/Apache

Revisar headers de transporte, CSP conforme integrações reais, referrer policy,
framing/content type/permissions policy.

### 9. Dependências

```sh
composer validate
composer audit
vendor/bin/drush status
```

Advisory conhecido não deve ser mascarado. Falha de rede é inconclusiva.

### 10. Portabilidade

Custom SQL/schema deve funcionar no Runtime SQLite e permanecer compatível com
MariaDB de produção.

### 11. Lifecycle

Fresh install e upgrade path antes de alterar/remover update hooks históricos.

### 12. Rollback

Definir backup/restore point, config/dependency rollback e reversibilidade de DB
antes de mudanças Runtime significativas.

## Severidade

- **Critical**: exposição grave, bypass, corrupção/perda de dados.
- **High**: access incorreto relevante, mutação insegura, segredo exposto,
  payment/session/domain inseguro.
- **Medium**: cache, failure handling, a11y, performance ou observabilidade.
- **Low**: manutenção/melhoria não bloqueante.

Release estável não aceita Critical/High abertos.

## Finding

Finding pertence ao issue/PR e deve registrar severidade, impacto, evidência,
correção, retest e status.
