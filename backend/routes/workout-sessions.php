<?php
require_once __DIR__ . '/../services/WorkoutSessionService.php';
registerCrudRoutes('workout-sessions', WorkoutSessionService::class);

