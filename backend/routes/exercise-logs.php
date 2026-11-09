<?php
require_once __DIR__ . '/../services/ExerciseLogService.php';
registerCrudRoutes('exercise-logs', ExerciseLogService::class);

