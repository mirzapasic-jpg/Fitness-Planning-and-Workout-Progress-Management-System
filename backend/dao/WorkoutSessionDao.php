<?php
require_once __DIR__ . '/BaseDao.php';

class WorkoutSessionDao extends BaseDao
{
    public function __construct(?PDO $connection = null)
    {
        parent::__construct('workout_sessions', ['user_id', 'plan_id', 'workout_date', 'notes'], $connection);
    }
}

