<?php
require_once __DIR__ . '/../services/ExerciseService.php';
registerCrudRoutes('exercises', ExerciseService::class);

