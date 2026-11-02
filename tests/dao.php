<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
foreach (['User', 'Exercise', 'WorkoutPlan', 'PlanExercise', 'WorkoutSession', 'ExerciseLog'] as $entity) {
    require_once __DIR__ . '/../backend/dao/' . $entity . 'Dao.php';
}
$checks = 0;
function check(bool $condition, string $message): void
{
    global $checks;
    if (!$condition) {
        throw new RuntimeException($message);
    }
    $checks++;
    echo "PASS $message\n";
}
function expectDatabaseError(callable $action, array $codes, string $message): void
{
    try {
        $action();
    } catch (PDOException $exception) {
        check(in_array((int) ($exception->errorInfo[1] ?? 0), $codes, true), $message);
        return;
    }
    throw new RuntimeException("Expected database rejection: $message");
}
function expectInvalidArgument(callable $action, string $message): void
{
    try {
        $action();
    } catch (InvalidArgumentException $exception) {
        check(true, $message);
        return;
    }
    throw new RuntimeException("Expected invalid argument: $message");
}
$connection = null;
try {
    $connection = Database::connect();
    check($connection->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql', 'PDO MySQL connection');
    $daos = [new UserDao($connection), new ExerciseDao($connection), new WorkoutPlanDao($connection),
        new PlanExerciseDao($connection), new WorkoutSessionDao($connection), new ExerciseLogDao($connection)];
    $connection->beginTransaction();
    $before = array_map(fn($dao) => $dao->getAll(), $daos);
    foreach ($daos as $index => $dao) {
        check(count($before[$index]) > 0, get_class($dao) . ' reads sample data');
    }
    $token = bin2hex(random_bytes(6));
    $user = ['name' => 'DAO Test Athlete', 'email' => "dao-$token@example.test",
        'password_hash' => '!locked-test', 'role' => 'User', 'fitness_goal' => 'Strength'];
    $ids = [];
    $rows = [];
    $rows[0] = $user;
    $ids[0] = $daos[0]->create($rows[0]);
    $rows[1] = ['name' => "DAO Test Exercise $token", 'muscle_group' => 'Back'];
    $ids[1] = $daos[1]->create($rows[1]);
    $rows[2] = ['user_id' => $ids[0], 'name' => 'Test routine', 'description' => 'Temporary routine', 'planned_date' => '2026-10-01'];
    $ids[2] = $daos[2]->create($rows[2]);
    $rows[3] = ['plan_id' => $ids[2], 'exercise_id' => $ids[1], 'position' => 1,
        'target_sets' => null, 'target_reps' => null, 'target_weight_kg' => null];
    $ids[3] = $daos[3]->create($rows[3]);
    $rows[4] = ['user_id' => $ids[0], 'plan_id' => $ids[2], 'workout_date' => '2026-10-01', 'notes' => null];
    $ids[4] = $daos[4]->create($rows[4]);
    $rows[5] = ['session_id' => $ids[4], 'exercise_id' => $ids[1], 'sets' => 3, 'reps' => 10, 'weight_kg' => 20];
    $ids[5] = $daos[5]->create($rows[5]);
    $updates = [['name' => 'Updated Athlete'], ['muscle_group' => 'Legs'], ['description' => 'Updated routine'],
        ['target_sets' => 4], ['notes' => 'Updated session'], ['weight_kg' => 25.50]];
    foreach ($daos as $index => $dao) {
        $label = get_class($dao);
        check($ids[$index] > 0 && $dao->getById($ids[$index]) !== null, "$label creates and reads by ID");
        check($dao->update($ids[$index], $updates[$index]), "$label updates existing record");
        $row = $dao->getById($ids[$index]);
        $column = array_key_first($updates[$index]);
        check((string) $row[$column] === (string) $updates[$index][$column] ||
            (is_numeric($row[$column]) && (float) $row[$column] === (float) $updates[$index][$column]), "$label persists update");
        check($dao->update($ids[$index], $updates[$index]), "$label unchanged update succeeds");
        check($dao->getById(0) === null && !$dao->update(0, $updates[$index]) && !$dao->delete(0), "$label missing record behavior");
        expectInvalidArgument(fn() => $dao->create([]), "$label rejects empty create");
        expectInvalidArgument(fn() => $dao->update($ids[$index], []), "$label rejects empty update");
        expectInvalidArgument(fn() => $dao->update($ids[$index], ['id' => 1]), "$label protects primary key");
        expectInvalidArgument(fn() => $dao->create(['name` = NULL; --' => 'x']), "$label rejects SQL identifier injection");
    }
    $literal = "Test ', role = 'Admin'; --";
    $daos[0]->update($ids[0], ['name' => $literal]);
    $saved = $daos[0]->getById($ids[0]);
    check($saved['name'] === $literal && $saved['role'] === 'User', 'SQL-like values remain literal data');
    expectDatabaseError(fn() => $daos[0]->create($user), [1062], 'duplicate email rejected');
    expectDatabaseError(fn() => $daos[1]->create($rows[1]), [1062], 'duplicate exercise name rejected');
    expectDatabaseError(fn() => $daos[0]->create(array_replace($user, ['email' => "UPPER-$token@example.test"])), [4025, 3819], 'non-normalized email rejected');
    expectDatabaseError(fn() => $daos[2]->create(array_replace($rows[2], ['user_id' => 0])), [1452], 'plan requires existing user');
    expectDatabaseError(fn() => $daos[3]->create(array_replace($rows[3], ['plan_id' => 0])), [1452], 'assignment requires existing plan');
    expectDatabaseError(fn() => $daos[3]->create(array_replace($rows[3], ['exercise_id' => 0, 'position' => 2])), [1452], 'assignment requires existing exercise');
    expectDatabaseError(fn() => $daos[4]->create(array_replace($rows[4], ['user_id' => 0])), [1452], 'session requires existing user');
    expectDatabaseError(fn() => $daos[4]->create(array_replace($rows[4], ['plan_id' => 0, 'workout_date' => '2026-10-02'])), [1452], 'session plan must exist');
    expectDatabaseError(fn() => $daos[5]->create(array_replace($rows[5], ['session_id' => 0])), [1452], 'log requires existing session');
    expectDatabaseError(fn() => $daos[5]->create(array_replace($rows[5], ['exercise_id' => 0])), [1452], 'log requires existing exercise');
    expectDatabaseError(fn() => $daos[5]->update($ids[5], ['exercise_id' => 0]), [1452], 'foreign key checked on update');
    expectDatabaseError(fn() => $daos[3]->create(array_replace($rows[3], ['position' => 2])), [1062], 'duplicate exercise assignment rejected');
    $secondExercise = $daos[1]->create(['name' => "Second $token", 'muscle_group' => 'Core']);
    expectDatabaseError(fn() => $daos[3]->create(array_replace($rows[3], ['exercise_id' => $secondExercise])), [1062], 'duplicate plan position rejected');
    expectDatabaseError(fn() => $daos[4]->create($rows[4]), [1062], 'one workout per user and date');
    foreach (['sets' => 0, 'reps' => 0, 'weight_kg' => -1] as $column => $value) {
        expectDatabaseError(fn() => $daos[5]->update($ids[5], [$column => $value]), [4025, 3819], "invalid log $column rejected");
    }
    foreach (['position' => 0, 'target_sets' => 0, 'target_reps' => 0, 'target_weight_kg' => -1] as $column => $value) {
        expectDatabaseError(fn() => $daos[3]->update($ids[3], [$column => $value]), [4025, 3819], "invalid prescription $column rejected");
    }
    expectDatabaseError(fn() => $daos[1]->delete($ids[1]), [1451], 'referenced exercise deletion restricted');
    // Verify ordinary DELETE separately for all six DAOs, child-first.
    foreach (array_reverse(array_keys($daos)) as $index) {
        check($daos[$index]->delete($ids[$index]) && $daos[$index]->getById($ids[$index]) === null,
            get_class($daos[$index]) . ' deletes record');
    }
    $owner = $daos[0]->create($user);
    $plan = $daos[2]->create(array_replace($rows[2], ['user_id' => $owner]));
    $assignment = $daos[3]->create(array_replace($rows[3], ['plan_id' => $plan, 'exercise_id' => $secondExercise]));
    $session = $daos[4]->create(array_replace($rows[4], ['user_id' => $owner, 'plan_id' => $plan]));
    $log = $daos[5]->create(array_replace($rows[5], ['session_id' => $session, 'exercise_id' => $secondExercise]));
    $daos[2]->delete($plan);
    check($daos[3]->getById($assignment) === null, 'plan delete cascades assignments');
    check($daos[4]->getById($session)['plan_id'] === null && $daos[5]->getById($log) !== null, 'plan delete detaches session and preserves history');
    expectDatabaseError(fn() => $daos[1]->delete($secondExercise), [1451], 'log alone restricts exercise deletion');
    $daos[4]->delete($session);
    check($daos[5]->getById($log) === null, 'session delete cascades logs');
    $plan = $daos[2]->create(array_replace($rows[2], ['user_id' => $owner]));
    $assignment = $daos[3]->create(array_replace($rows[3], ['plan_id' => $plan, 'exercise_id' => $secondExercise]));
    expectDatabaseError(fn() => $daos[1]->delete($secondExercise), [1451], 'assignment alone restricts exercise deletion');
    $session = $daos[4]->create(array_replace($rows[4], ['user_id' => $owner, 'plan_id' => $plan]));
    $log = $daos[5]->create(array_replace($rows[5], ['session_id' => $session, 'exercise_id' => $secondExercise]));
    $daos[0]->delete($owner);
    check($daos[2]->getById($plan) === null && $daos[3]->getById($assignment) === null &&
        $daos[4]->getById($session) === null && $daos[5]->getById($log) === null, 'user delete cascades owned plans, assignments, sessions and logs');
    check($daos[1]->getById($secondExercise) !== null, 'user delete preserves shared exercise');
    $connection->rollBack();
    check(array_map(fn($dao) => $dao->getAll(), $daos) === $before, 'rollback restores all original rows');
    echo "RESULT: $checks checks passed; test records rolled back. Auto-increment counters may advance.\n";
} catch (Throwable $exception) {
    if ($connection instanceof PDO && $connection->inTransaction()) {
        $connection->rollBack();
    }
    fwrite(STDERR, 'FAIL: ' . $exception->getMessage() . "\n");
    exit(1);
}
