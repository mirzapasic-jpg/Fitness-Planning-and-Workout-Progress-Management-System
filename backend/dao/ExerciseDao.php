<?php
require_once __DIR__ . '/BaseDao.php';

class ExerciseDao extends BaseDao
{
    public function __construct(?PDO $connection = null)
    {
        parent::__construct('exercises', ['name', 'muscle_group'], $connection);
    }
}

