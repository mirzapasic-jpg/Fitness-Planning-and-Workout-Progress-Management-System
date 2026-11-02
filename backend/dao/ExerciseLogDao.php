<?php
require_once __DIR__ . '/BaseDao.php';

class ExerciseLogDao extends BaseDao
{
    public function __construct(?PDO $connection = null)
    {
        parent::__construct('exercise_logs', ['session_id', 'exercise_id', 'sets', 'reps', 'weight_kg'], $connection);
    }
}

