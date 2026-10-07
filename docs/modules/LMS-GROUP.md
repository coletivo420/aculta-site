# Módulos — Cursos, grupos e progresso

Data da revisão: 2026-10-07.

| Módulo | Papel |
| --- | --- |
| group | memberships e grupos |
| lms | cursos, treinamentos e progresso |
| lms_answer_plugins | plugins de resposta do LMS |

`aculta_portal` apresenta e agrega informações, mas não cria storage paralelo de matrícula, progresso ou nota.

## Conta

`AccountCoursesManager` e presenters adaptam LMS/Group para ACCOUNT.

Regras:

- membership vem de Group/LMS;
- progresso/score/estado vêm do LMS;
- entity access precede metadata privada;
- URLs de curso respeitam COURSES;
- tema/SDC apenas apresentam o view-model.

### Anti-regressão

Não recalcular progresso no tema, salvar matrícula paralela ou hardcodar host de cursos.
