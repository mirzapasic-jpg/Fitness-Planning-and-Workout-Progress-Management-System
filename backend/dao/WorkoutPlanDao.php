<?php
require_once __DIR__ . '/BaseDao.php';

class WorkoutPlanDao extends BaseDao
{
    public function __construct(?PDO $connection = null)
    {
        parent::__construct('workout_plans', ['user_id', 'name', 'description', 'planned_date'], $connection);
    }
}

