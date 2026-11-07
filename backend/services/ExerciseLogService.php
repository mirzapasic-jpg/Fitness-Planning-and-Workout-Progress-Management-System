<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/ExerciseLogDao.php';
require_once __DIR__ . '/../dao/WorkoutSessionDao.php';
require_once __DIR__ . '/../dao/ExerciseDao.php';

class ExerciseLogService extends BaseService
{
    protected array $fields = ['session_id', 'exercise_id', 'sets', 'reps', 'weight_kg'];

    public function __construct(?PDO $connection = null)
    {
        parent::__construct($connection);
        $this->dao = new ExerciseLogDao($this->connection);
    }

    protected function validate(array $data): array
    {
        $this->reference($data, 'session_id', new WorkoutSessionDao($this->connection));
        $this->reference($data, 'exercise_id', new ExerciseDao($this->connection));
        $this->number($data, 'sets', 1, 65535, true);
        $this->number($data, 'reps', 1, 65535, true);
        $this->number($data, 'weight_kg', 0, 9999.99);
        return $data;
    }
}
