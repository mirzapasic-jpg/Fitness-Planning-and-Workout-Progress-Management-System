<?php
ini_set('display_errors', '0');
date_default_timezone_set('Europe/Sarajevo');

// Allow only the documentation assets through the optional PHP development server.
if (PHP_SAPI === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (in_array($path, ['/docs/', '/docs/index.html', '/docs/openapi.yaml'], true)) {
        return false;
    }
}

try {
    require_once __DIR__ . '/vendor/autoload.php';
    require_once __DIR__ . '/routes/Api.php';

    Flight::set('flight.log_errors', false);
    Flight::map('error', function (Throwable $exception) {
        $status = 500;
        $message = 'An unexpected server error occurred. Please try again later.';
        if ($exception instanceof InvalidArgumentException) {
            $status = 400;
            $message = $exception->getMessage();
        } elseif ($exception instanceof OutOfBoundsException) {
            $status = 404;
            $message = $exception->getMessage();
        } elseif ($exception instanceof PDOException && in_array((string) $exception->getCode(), ['23000', '22001', '22003', '22007'], true)) {
            $status = 400;
            $message = 'The data conflicts with an existing record, a field limit, or a related record. Referenced exercises cannot be deleted.';
        } elseif ($exception instanceof RuntimeException && $exception->getCode() === 503) {
            $status = 503;
            $message = $exception->getMessage();
        }
        Flight::response()->clearBody();
        Flight::json(['error' => $message], $status);
        Flight::response()->send();
    });
    Flight::map('notFound', function () {
        Flight::json(['error' => 'Endpoint not found.'], 404);
    });
    Flight::route('GET /', function () {
        Flight::json(['message' => 'Forma Milestone 3 API', 'documentation' => 'docs/']);
    });
    foreach (['users', 'exercises', 'workout-plans', 'plan-exercises', 'workout-sessions', 'exercise-logs', 'meals'] as $route) {
        require __DIR__ . '/routes/' . $route . '.php';
    }
    try {
        Flight::start();
    } catch (Throwable $exception) {
        Flight::error($exception);
    }
} catch (Throwable $exception) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Backend setup failed. Check Composer installation and local configuration.']);
}
