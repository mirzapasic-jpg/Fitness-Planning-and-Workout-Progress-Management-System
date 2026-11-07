<?php
require_once __DIR__ . '/../services/PlanExerciseService.php';
registerCrudRoutes('plan-exercises', PlanExerciseService::class);

