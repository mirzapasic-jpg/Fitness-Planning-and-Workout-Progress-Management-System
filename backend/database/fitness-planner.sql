CREATE DATABASE IF NOT EXISTS fitness_planner CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fitness_planner;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('User', 'Admin') NOT NULL DEFAULT 'User',
    age SMALLINT UNSIGNED NULL,
    height_cm DECIMAL(5,2) NULL,
    weight_kg DECIMAL(6,2) NULL,
    fitness_goal VARCHAR(255) NOT NULL,
    CONSTRAINT chk_user_email CHECK (BINARY email = BINARY LOWER(TRIM(email))),
    CONSTRAINT chk_user_age CHECK (age IS NULL OR age > 0),
    CONSTRAINT chk_user_height CHECK (height_cm IS NULL OR height_cm > 0),
    CONSTRAINT chk_user_weight CHECK (weight_kg IS NULL OR weight_kg >= 0)
) ENGINE=InnoDB;

CREATE TABLE exercises (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    muscle_group VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE workout_plans (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    planned_date DATE NOT NULL,
    CONSTRAINT fk_plan_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE plan_exercises (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    plan_id INT UNSIGNED NOT NULL,
    exercise_id INT UNSIGNED NOT NULL,
    position SMALLINT UNSIGNED NOT NULL,
    target_sets SMALLINT UNSIGNED NULL,
    target_reps SMALLINT UNSIGNED NULL,
    target_weight_kg DECIMAL(6,2) NULL,
    CONSTRAINT uq_plan_position UNIQUE (plan_id, position),
    CONSTRAINT uq_plan_exercise UNIQUE (plan_id, exercise_id),
    CONSTRAINT chk_plan_position CHECK (position > 0),
    CONSTRAINT chk_target_sets CHECK (target_sets IS NULL OR target_sets > 0),
    CONSTRAINT chk_target_reps CHECK (target_reps IS NULL OR target_reps > 0),
    CONSTRAINT chk_target_weight CHECK (target_weight_kg IS NULL OR target_weight_kg >= 0),
    CONSTRAINT fk_assignment_plan FOREIGN KEY (plan_id) REFERENCES workout_plans(id) ON DELETE CASCADE,
    CONSTRAINT fk_assignment_exercise FOREIGN KEY (exercise_id) REFERENCES exercises(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE workout_sessions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    plan_id INT UNSIGNED NULL,
    workout_date DATE NOT NULL,
    notes TEXT NULL,
    CONSTRAINT uq_user_workout_date UNIQUE (user_id, workout_date),
    CONSTRAINT fk_session_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_session_plan FOREIGN KEY (plan_id) REFERENCES workout_plans(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE exercise_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id INT UNSIGNED NOT NULL,
    exercise_id INT UNSIGNED NOT NULL,
    sets SMALLINT UNSIGNED NOT NULL,
    reps SMALLINT UNSIGNED NOT NULL,
    weight_kg DECIMAL(6,2) NOT NULL,
    CONSTRAINT chk_log_sets CHECK (sets > 0),
    CONSTRAINT chk_log_reps CHECK (reps > 0),
    CONSTRAINT chk_log_weight CHECK (weight_kg >= 0),
    CONSTRAINT fk_log_session FOREIGN KEY (session_id) REFERENCES workout_sessions(id) ON DELETE CASCADE,
    CONSTRAINT fk_log_exercise FOREIGN KEY (exercise_id) REFERENCES exercises(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

START TRANSACTION;
INSERT INTO users (id, name, email, password_hash, role, age, height_cm, weight_kg, fitness_goal) VALUES
(1, 'Alex Morgan', 'user@forma.demo', '!locked-demo-user', 'User', 24, 178, 76, 'Build strength'),
(2, 'Sam Taylor', 'admin@forma.demo', '!locked-demo-admin', 'Admin', 28, 172, 68, 'Stay active');
INSERT INTO exercises (id, name, muscle_group) VALUES
(1, 'Bench Press', 'Chest'), (2, 'Squat', 'Legs'), (3, 'Deadlift', 'Back'),
(4, 'Shoulder Press', 'Shoulders'), (5, 'Biceps Curl', 'Arms'),
(6, 'Lat Pulldown', 'Back'), (7, 'Plank', 'Core');
INSERT INTO workout_plans (id, user_id, name, description, planned_date) VALUES
(1, 1, 'Push day', 'Upper body strength.', '2026-10-20'),
(2, 1, 'Leg day', 'Lower body strength and stability.', '2026-10-22'),
(3, 2, 'Core routine', 'Core stability.', '2026-10-23');
INSERT INTO plan_exercises (plan_id, exercise_id, position, target_sets, target_reps, target_weight_kg) VALUES
(1, 1, 1, 3, 10, 50), (1, 4, 2, 3, 10, 25),
(2, 2, 1, 4, 8, 65), (2, 7, 2, 3, 30, 0), (3, 7, 1, NULL, NULL, NULL);
INSERT INTO workout_sessions (id, user_id, plan_id, workout_date, notes) VALUES
(1, 1, 1, '2026-10-20', 'Good upper body session.'),
(2, 1, 2, '2026-10-22', 'Increased squat weight.'),
(3, 2, NULL, '2026-10-23', NULL);
INSERT INTO exercise_logs (session_id, exercise_id, sets, reps, weight_kg) VALUES
(1, 1, 3, 10, 50), (1, 4, 3, 10, 25),
(2, 2, 4, 8, 65), (2, 7, 3, 30, 0), (3, 7, 3, 30, 0);
COMMIT;
