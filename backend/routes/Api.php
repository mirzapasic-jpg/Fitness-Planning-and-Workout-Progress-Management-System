<?php

function apiId($value): int
{
    if (!is_string($value) || !preg_match('/^[1-9][0-9]*$/D', $value) || strlen($value) > 10 || (float) $value > 4294967295) {
        throw new InvalidArgumentException('id must be a positive integer no greater than 4294967295.');
    }
    return (int) $value;
}

function apiBody(): array
{
    $contentType = strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0]));
    if ($contentType !== 'application/json') {
        throw new InvalidArgumentException('Send a JSON object with Content-Type: application/json.');
    }
    try {
        $body = json_decode(file_get_contents('php://input'), false, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        throw new InvalidArgumentException('The request contains invalid JSON.');
    }
    if (!$body instanceof stdClass) {
        throw new InvalidArgumentException('The JSON body must be an object.');
    }
    return (array) $body;
}

function registerCrudRoutes(string $path, string $serviceClass): void
{
    Flight::route('GET /api/' . $path, function () use ($serviceClass) {
        Flight::json((new $serviceClass())->getAll());
    });
    Flight::route('GET /api/' . $path . '/@id', function ($id) use ($serviceClass) {
        $id = apiId($id);
        Flight::json((new $serviceClass())->getById($id));
    });
    Flight::route('POST /api/' . $path, function () use ($serviceClass) {
        $body = apiBody();
        Flight::json((new $serviceClass())->create($body), 201);
    });
    Flight::route('PUT /api/' . $path . '/@id', function ($id) use ($serviceClass) {
        $id = apiId($id);
        $body = apiBody();
        Flight::json((new $serviceClass())->update($id, $body));
    });
    Flight::route('DELETE /api/' . $path . '/@id', function ($id) use ($serviceClass) {
        $id = apiId($id);
        (new $serviceClass())->delete($id);
        Flight::json(['message' => 'Record deleted.']);
    });
}
