<?php
require_once __DIR__ . '/BaseDao.php';

class PlanExerciseDao extends BaseDao
{
    public function __construct(?PDO $connection = null)
    {
        parent::__construct('plan_exercises', ['plan_id', 'exercise_id', 'position', 'target_sets', 'target_reps', 'target_weight_kg'], $connection);
    }
}

