<?php
require_once __DIR__ . '/../services/WorkoutPlanService.php';
registerCrudRoutes('workout-plans', WorkoutPlanService::class);

