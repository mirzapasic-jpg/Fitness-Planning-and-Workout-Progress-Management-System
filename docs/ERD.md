# Draft relational schema — Milestone 1

Open ERD.html directly in a browser for a self-contained diagram (no network or database required).

| Entity | Attributes |
| --- | --- |
| Users | **id PK**, name, email UNIQUE, password_hash (future real auth only), role, age nullable, height_cm nullable, weight_kg nullable, fitness_goal |
| Exercises | **id PK**, name UNIQUE, muscle_group |
| Workout Plans | **id PK**, **user_id FK → Users.id**, name, description, planned_date |
| Plan Exercises | **id PK**, **plan_id FK → Workout Plans.id**, **exercise_id FK → Exercises.id**, position, target_sets nullable, target_reps nullable, target_weight_kg nullable |
| Workout Sessions | **id PK**, **user_id FK → Users.id**, **plan_id FK nullable → Workout Plans.id**, workout_date, notes nullable |
| Exercise Logs | **id PK**, **session_id FK → Workout Sessions.id**, **exercise_id FK → Exercises.id**, sets, reps, weight_kg |

## Cardinalities

- User 1 → 0..many Workout Plans; every plan belongs to exactly one user.
- User 1 → 0..many Workout Sessions; every session belongs to exactly one user.
- Workout Plan 1 → 0..many Plan Exercises; each assignment references one plan and one exercise. The UI requires at least one assignment on save.
- Exercise 1 → 0..many Plan Exercises. Plans and exercises form a many-to-many relationship through assignments.
- Workout Plan 0..1 ← 0..many Workout Sessions: a session may be independent of a plan.
- Workout Session 1 → 0..many Exercise Logs; each log belongs to exactly one session.
- Exercise 1 → 0..many Exercise Logs; each log references exactly one exercise.

## Constraints and lifecycle

Use numeric IDs, normalized lowercase unique emails, role enum User/Admin, positive integer sets/reps, and nonnegative decimal weight. Record dates cannot be in the future. Assignment position is unique within a plan; prevent duplicate exercise assignments. Same-user ownership must be checked when linking a session to a plan. No real passwords are stored in current mock data.

Planned deletion behavior: deleting a plan cascades its assignments and sets session.plan_id to NULL; deleting a session cascades its logs; deleting a user cascades owned sessions/plans (confirm explicitly in UI). Exercise deletion is restricted while referenced, preserving history. User IDs and ownership must be verified by backend authorization later.

The frontend currently flattens session/log fields and counts distinct user/date pairs as workouts. Later, one explicit session per user/date (UNIQUE user_id, workout_date) will preserve that definition. Multiple exercises can be logged per date; the database provides session IDs. Profile fields live on Users. Planned sets/reps/weight are optional future plan prescription fields, not currently editable in M1. No SQL or database is implemented in this milestone.
