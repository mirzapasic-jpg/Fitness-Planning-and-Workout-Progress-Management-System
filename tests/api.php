<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once __DIR__ . '/../backend/services/MealService.php';
$base = rtrim($argv[1] ?? 'http://localhost/MirzaPasic/Fitness-Planning-and-Workout-Progress-Management-System/backend', '/');
$checks = 0;
$created = [];
function check(bool $condition, string $message): void
{
    global $checks;
    if (!$condition) {
        throw new RuntimeException($message);
    }
    $checks++;
    echo "PASS $message\n";
}
function request(string $method, string $path, $body = null, bool $raw = false, string $type = 'application/json'): array
{
    global $base;
    $handle = curl_init($base . $path);
    $headers = ['Accept: application/json'];
    $options = [CURLOPT_RETURNTRANSFER => true, CURLOPT_CUSTOMREQUEST => $method, CURLOPT_TIMEOUT => 15];
    if ($body !== null) {
        $headers[] = 'Content-Type: ' . $type;
        $options[CURLOPT_POSTFIELDS] = $raw ? $body : json_encode($body, JSON_THROW_ON_ERROR);
    }
    $options[CURLOPT_HTTPHEADER] = $headers;
    curl_setopt_array($handle, $options);
    $content = curl_exec($handle);
    $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
    $contentType = curl_getinfo($handle, CURLINFO_CONTENT_TYPE);
    $error = curl_error($handle);
    curl_close($handle);
    if ($content === false) {
        throw new RuntimeException('HTTP request failed: ' . $error);
    }
    if (!str_starts_with((string) $contentType, 'application/json')) {
        throw new RuntimeException("Expected JSON at $path; received HTTP $status.");
    }
    return [$status, json_decode($content, true, 512, JSON_THROW_ON_ERROR)];
}
function expectStatus(string $method, string $path, int $status, $body = null, bool $raw = false, string $type = 'application/json'): array
{
    [$actual, $data] = request($method, $path, $body, $raw, $type);
    check($actual === $status, "$method $path returns $status (actual $actual)");
    if ($status >= 400) {
        check(isset($data['error']) && !preg_match('/SQLSTATE|Stack trace|C:\\\\xampp/i', $data['error']), 'safe JSON error message');
    }
    return $data;
}
function createRecord(string $path, array $data): array
{
    global $created;
    $row = expectStatus('POST', '/api/' . $path, 201, $data);
    check(isset($row['id']) && $row['id'] > 0, "$path returns a generated ID");
    $created[] = [$path, $row['id']];
    return $row;
}

// Override only transport in CLI tests: no production HTTP endpoint can select a test URL.
class TestMealService extends MealService
{
    public array $response = [];
    public bool $connectionFailure = false;
    protected function request(): array
    {
        if ($this->connectionFailure) {
            throw new RuntimeException('Simulated connection timeout');
        }
        return $this->response;
    }
}

$failure = null;
try {
    $paths = ['users', 'exercises', 'workout-plans', 'plan-exercises', 'workout-sessions', 'exercise-logs'];
    $before = [];
    foreach ($paths as $path) {
        $before[$path] = expectStatus('GET', '/api/' . $path, 200);
        expectStatus('GET', '/api/' . $path . '/4294967295', 404);
        $field = ['users' => 'name', 'exercises' => 'name', 'workout-plans' => 'name', 'plan-exercises' => 'position', 'workout-sessions' => 'notes', 'exercise-logs' => 'sets'][$path];
        expectStatus('PUT', '/api/' . $path . '/4294967295', 404, [$field => 1]);
        expectStatus('DELETE', '/api/' . $path . '/4294967295', 404);
    }    $token = bin2hex(random_bytes(6));
    $userData = ['name' => 'M3 Test Athlete', 'email' => "m3-$token@example.test", 'password_hash' => '!locked-api-test', 'fitness_goal' => 'Strength'];
    $user = createRecord('users', $userData);
    check(!isset($user['password_hash']), 'created user hides password hash');
    $other = createRecord('users', array_replace($userData, ['email' => "other-$token@example.test"]));
    $exerciseData = ['name' => "M3 Test Exercise $token", 'muscle_group' => 'Back'];
    $exercise = createRecord('exercises', $exerciseData);
    $planData = ['user_id' => $user['id'], 'name' => 'M3 routine', 'description' => '', 'planned_date' => '2026-10-01'];
    $plan = createRecord('workout-plans', $planData);
    $assignmentData = ['plan_id' => $plan['id'], 'exercise_id' => $exercise['id'], 'position' => 1];
    $assignment = createRecord('plan-exercises', $assignmentData);
    $sessionData = ['user_id' => $user['id'], 'plan_id' => $plan['id'], 'workout_date' => '2026-10-01'];
    $session = createRecord('workout-sessions', $sessionData);
    $logData = ['session_id' => $session['id'], 'exercise_id' => $exercise['id'], 'sets' => 3, 'reps' => 10, 'weight_kg' => 0];
    $log = createRecord('exercise-logs', $logData);
    $rows = [$user, $exercise, $plan, $assignment, $session, $log];
    $updates = [['name' => 'Updated Athlete'], ['muscle_group' => 'Legs'], ['description' => 'Updated routine'], ['target_sets' => 4], ['notes' => 'Updated session'], ['weight_kg' => 25.5]];
    $invalid = [['email' => 'invalid'], ['name' => '   '], ['planned_date' => '2026-02-30'], ['position' => 0], ['workout_date' => '9999-12-31'], ['sets' => 0]];
    foreach ($paths as $i => $path) {
        $url = '/api/' . $path;
        $id = $rows[$i]['id'];
        $row = expectStatus('GET', "$url/$id", 200);
        check($row === $rows[$i], "$path read matches created row");
        $changed = expectStatus('PUT', "$url/$id", 200, $updates[$i]);
        $key = array_key_first($updates[$i]);
        check((string) $changed[$key] === (string) $updates[$i][$key] || (is_numeric($changed[$key]) && (float) $changed[$key] === (float) $updates[$i][$key]), "$path update persisted");
        expectStatus('PUT', "$url/$id", 200, $updates[$i]);
        expectStatus('PUT', "$url/$id", 400, $invalid[$i]);
        expectStatus('POST', $url, 400, '{}', true);
        expectStatus('PUT', "$url/$id", 400, '{}', true);
        expectStatus('POST', $url, 400, ['unknown' => 'x']);
        expectStatus('POST', $url, 400, '{', true);
        expectStatus('POST', $url, 400, '[]', true);
        expectStatus('POST', $url, 400, 'null', true);
        expectStatus('POST', $url, 400, '{}', true, 'text/plain');
        foreach (['0', '-1', 'abc', '01', '4294967296'] as $badId) {
            expectStatus('GET', "$url/$badId", 400);
        }
    }
    expectStatus('POST', '/api/users', 400, $userData);
    expectStatus('POST', '/api/exercises', 400, $exerciseData);
    expectStatus('POST', '/api/plan-exercises', 400, $assignmentData);
    expectStatus('POST', '/api/workout-sessions', 400, $sessionData);
    foreach ([['workout-plans',$planData,'user_id'], ['plan-exercises',$assignmentData,'plan_id'], ['plan-exercises',$assignmentData,'exercise_id'], ['workout-sessions',$sessionData,'user_id'], ['workout-sessions',$sessionData,'plan_id'], ['exercise-logs',$logData,'session_id'], ['exercise-logs',$logData,'exercise_id']] as [$path,$data,$field]) {
        expectStatus('POST', '/api/' . $path, 400, array_replace($data, [$field => 4294967295]));
    }
    expectStatus('PUT', '/api/exercise-logs/' . $log['id'], 400, ['exercise_id' => 4294967295]);
    expectStatus('PUT', '/api/exercise-logs/' . $log['id'], 400, ['reps' => 1.5]);
    expectStatus('PUT', '/api/exercise-logs/' . $log['id'], 400, ['weight_kg' => -1]);
    expectStatus('PUT', '/api/exercise-logs/' . $log['id'], 400, ['weight_kg' => 1.234]);
    expectStatus('PUT', '/api/exercise-logs/' . $log['id'], 400, ['weight_kg' => []]);
    expectStatus('PUT', '/api/users/' . $user['id'], 400, ['password_hash' => 'plaintext']);
    expectStatus('PUT', '/api/users/' . $user['id'], 400, ['role' => 'Manager']);
    expectStatus('PUT', '/api/users/' . $user['id'], 400, ['age' => 0]);
    expectStatus('PUT', '/api/users/' . $user['id'], 400, ['height_cm' => -1]);
    expectStatus('PUT', '/api/exercises/' . $exercise['id'], 400, ['name' => str_repeat('x',101)]);
    expectStatus('POST', '/api/workout-sessions', 400, array_replace($sessionData, ['user_id' => $other['id']]));
    expectStatus('PUT', '/api/workout-plans/' . $plan['id'], 400, ['user_id' => $other['id']]);
    expectStatus('PUT', '/api/workout-sessions/' . $session['id'], 400, ['workout_date' => "2026-10-01\0"]);
    $cleared = expectStatus('PUT', '/api/workout-sessions/' . $session['id'], 200, ['notes' => null]);
    check($cleared['notes'] === null, 'nullable notes can be cleared');
    $normalized = expectStatus('PUT', '/api/users/' . $user['id'], 200, ['email' => " M3-$token@EXAMPLE.TEST "]);
    check($normalized['email'] === "m3-$token@example.test", 'email is trimmed and lowercased');
    expectStatus('DELETE', '/api/exercises/' . $exercise['id'], 400);
    expectStatus('GET', '/api/no-such-endpoint', 404);
    $allUsers = expectStatus('GET', '/api/users', 200);
    check(!array_filter($allUsers, fn($row) => array_key_exists('password_hash', $row)), 'user list hides all password hashes');
    [$mealStatus,$mealData] = request('GET', '/api/meals/recovery');
    check(in_array($mealStatus,[200,503],true), 'live meal endpoint responds without breaking application');
    if ($mealStatus === 200) {
        check(count($mealData['meals']) > 0 && count($mealData['meals']) <= 3 && isset($mealData['meals'][0]['ingredients']), 'live TheMealDB returns structured recipes');
        echo "LIVE MEAL RESULT: 200, real recipes received.\n";
    } else {
        check($mealData['error'] === 'Meal suggestions are temporarily unavailable. Please try again later.', 'live upstream failure is friendly');
        echo "LIVE MEAL RESULT: 503, upstream unavailable; live success remains unverified.\n";
    }
    $fake = new TestMealService();
    $fake->response = ['status' => 200, 'body' => json_encode(['meals' => [['idMeal'=>'1','strMeal'=>'Test chicken','strIngredient1'=>'Chicken','strMeasure1'=>'1 cup']]])];
    $result = $fake->getRecoveryMeals();
    check($result['meals'][0]['ingredients'] === [['name'=>'Chicken','measure'=>'1 cup']], 'meal ingredient mapping and optional fields');
    $logPath = __DIR__ . '/../backend/logs/error.log';
    $beforeLog = is_file($logPath) ? filesize($logPath) : 0;
    foreach ([['status'=>500,'body'=>'{}'], ['status'=>200,'body'=>'{'], ['status'=>200,'body'=>'{"meals":null}'], ['status'=>200,'body'=>'{"meals":[]}'], ['status'=>200,'body'=>'{"meals":[{}]}']] as $response) {
        $fake->response = $response;
        try {
            $fake->getRecoveryMeals();
            throw new LogicException('Expected meal failure');
        } catch (RuntimeException $exception) {
            check($exception->getCode() === 503 && $exception->getMessage() === 'Meal suggestions are temporarily unavailable. Please try again later.', 'upstream HTTP/JSON/payload failure returns friendly 503 exception');
        }
    }
    $fake->connectionFailure = true;
    try {
        $fake->getRecoveryMeals();
        throw new LogicException('Expected connection failure');
    } catch (RuntimeException $exception) {
        check($exception->getCode() === 503, 'simulated connection timeout handled');
    }
    clearstatcache(true,$logPath);
    check(filesize($logPath) > $beforeLog, 'external failures appended to error log');
    check(str_contains(file_get_contents($logPath), 'Simulated connection timeout'), 'error log records actual simulated failure cause');
    expectStatus('GET', '/api/exercises', 200);
} catch (Throwable $exception) {
    $failure = $exception;
} finally {
    // Delete only IDs created by this run, children first. Existing rows are never mutated.
    foreach (array_reverse($created) as [$path,$id]) {
        try {
            expectStatus('DELETE', '/api/' . $path . '/' . $id, 200);
            expectStatus('GET', '/api/' . $path . '/' . $id, 404);
        } catch (Throwable $exception) {
            $failure = $failure ?? $exception;
            fwrite(STDERR, "Cleanup needs attention: $path/$id\n");
        }
    }
}
try {
    foreach ($before ?? [] as $path => $rows) {
        check(expectStatus('GET', '/api/' . $path, 200) === $rows, "$path original rows unchanged after HTTP cleanup");
    }
} catch (Throwable $exception) {
    $failure = $failure ?? $exception;
}
if ($failure !== null) {
    fwrite(STDERR,'FAIL: '.$failure->getMessage()."\n");
    exit(1);
}
echo "RESULT: $checks checks passed; temporary API rows removed. Auto-increment counters may advance.\n";
