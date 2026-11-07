<?php

class MealService
{
    private string $url;

    public function __construct()
    {
        // TheMealDB's public educational test key is 1; no private key is required.
        $this->url = 'https://www.themealdb.com/api/json/v1/1/search.php?s=chicken';
    }

    public function getRecoveryMeals(): array
    {
        try {
            $response = $this->request();
            if ($response['status'] !== 200) {
                throw new RuntimeException('Upstream HTTP status ' . $response['status']);
            }
            $payload = json_decode($response['body'], true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($payload) || !isset($payload['meals']) || !is_array($payload['meals']) || $payload['meals'] === []) {
                throw new RuntimeException('Upstream response has no meal list.');
            }
            $meals = [];
            foreach (array_slice($payload['meals'], 0, 3) as $meal) {
                if (!is_array($meal) || empty($meal['idMeal']) || empty($meal['strMeal'])) {
                    throw new RuntimeException('Upstream meal is missing required fields.');
                }
                $ingredients = [];
                for ($i = 1; $i <= 20; $i++) {
                    $name = trim((string) ($meal['strIngredient' . $i] ?? ''));
                    if ($name !== '') {
                        $ingredients[] = ['name' => $name, 'measure' => trim((string) ($meal['strMeasure' . $i] ?? ''))];
                    }
                }
                $meals[] = ['id' => (string) $meal['idMeal'], 'name' => $meal['strMeal'],
                    'image' => $meal['strMealThumb'] ?? null, 'category' => $meal['strCategory'] ?? null,
                    'ingredients' => $ingredients];
            }
            return ['meals' => $meals, 'source' => 'TheMealDB',
                'note' => 'Chicken recipe ideas for Fuel your recovery. Nutritional values and dietary suitability are not provided.'];
        } catch (Throwable $exception) {
            error_log('[' . date('c') . '] TheMealDB failure: ' . $exception->getMessage() . PHP_EOL,
                3, __DIR__ . '/../logs/error.log');
            throw new RuntimeException('Meal suggestions are temporarily unavailable. Please try again later.', 503);
        }
    }

    protected function request(): array
    {
        $handle = curl_init($this->url);
        curl_setopt_array($handle, [CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => 8, CURLOPT_FOLLOWLOCATION => false, CURLOPT_HTTPHEADER => ['Accept: application/json']]);
        $body = curl_exec($handle);
        $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        $failed = $body === false;
        curl_close($handle);
        if ($failed) {
            throw new RuntimeException('Could not connect to the recipe provider within the timeout.');
        }
        return ['status' => $status, 'body' => $body];
    }
}
