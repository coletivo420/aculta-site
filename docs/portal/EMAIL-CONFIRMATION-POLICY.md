# Política de confirmação de e-mail

Status: **proposta registrada; implementação pendente de decisões do responsável** (ver seção 6).

## 1. Regra

Usuário com e-mail não confirmado:

- **Pode:** acessar o site, fazer login, navegar e usar a área "Minha conta".
- **Não pode:** realizar cursos (matrícula e acesso às aulas) e editar a wiki (criar ou alterar verbetes).

## 2. Aviso em "Minha conta"

Enquanto o e-mail não estiver confirmado, a área "Minha conta" exibe um aviso em destaque vermelho com:

- texto: o e-mail precisa ser confirmado, com um link **"Clique aqui para reenviar"**;
- **não pode ser descartado** (sem botão de fechar, nem lembrar-depois);
- desaparece somente quando o e-mail for confirmado.

## 3. Definição de "e-mail confirmado"

Um e-mail é confirmado quando o usuário abre o link de confirmação enviado ao endereço. O estado deve ser gravado
pelo Portal (flag por usuário, por exemplo em `user.data` do módulo `aculta_portal`), e não inferido de outro campo.

## 4. Mecanismo técnico (a decidir)

- Hoje o Core bloqueia a conta até a verificação quando `user.settings:verify_mail` está ligado. Isso impede o login
  de quem não confirmou, o que contradiz a regra 1. Para cumprir a regra, a verificação passa a ser feita pelo Portal:
  `verify_mail` desligado, conta ativa, flag de "não confirmado" e confirmação pelo `email_confirmer` (realm próprio de
  cadastro).
- O reenvio usa a rota de reenvio do `email_confirmer`, com o intervalo de `resendrequest_delay` (900 s) para evitar abuso.
- O link de confirmação é gerado sempre no host da conta (como na troca de e-mail).

## 5. Pontos de aplicação

- Cursos: matrícula e acesso às aulas (`aculta_portal` / LMS). Bloquear com `access` do Portal, sem alterar o LMS.
- Wiki: criação e edição de verbetes e categorias (permissões de conteúdo e `entity_access` do Portal).
- Aviso: formulário/seção de "Minha conta" do shell do Portal, apenas para o usuário dono da conta.

Esta política não restringe leitura pública da wiki nem páginas institucionais.

## 6. Decisões do responsável (pendentes)

1. **Contas existentes:** consideradas confirmadas (recomendado) ou precisam confirmar agora?
2. **Confirmação de cadastro:** confirmar também via link do `email_confirmer` (recomendado), mantendo a verificação
   do Core desligada.
3. **Escopo:** além de cursos e edição da wiki, há outras ações a bloquear (por exemplo, apoio, comentários)?
4. **Expiração do link de cadastro:** usar o mesmo `hash_expiration` (24 h) da troca de e-mail, ou outro prazo?

## 7. Segurança

- Nenhum endereço de e-mail nem valor de link é gravado em log além do necessário para diagnóstico (nome e usuário).
- O reenvio é limitado pelo intervalo de `resendrequest_delay`.
- A confirmação segue o hash de 43 caracteres de uso único e com validade; o login automático na confirmação está
  registrado em `docs/portal/` como risco aceito (ver CHANGELOG do Portal 0.2.0-dev.14).
