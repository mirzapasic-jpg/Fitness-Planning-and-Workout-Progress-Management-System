<?php
require_once __DIR__ . '/../services/MealService.php';

Flight::route('GET /api/meals/recovery', function () {
    Flight::json((new MealService())->getRecoveryMeals());
});
