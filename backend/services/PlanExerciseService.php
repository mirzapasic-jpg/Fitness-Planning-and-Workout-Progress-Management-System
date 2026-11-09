<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/PlanExerciseDao.php';
require_once __DIR__ . '/../dao/WorkoutPlanDao.php';
require_once __DIR__ . '/../dao/ExerciseDao.php';

class PlanExerciseService extends BaseService
{
    protected array $fields = ['plan_id', 'exercise_id', 'position', 'target_sets', 'target_reps', 'target_weight_kg'];

    public function __construct(?PDO $connection = null)
    {
        parent::__construct($connection);
        $this->dao = new PlanExerciseDao($this->connection);
    }

    protected function validate(array $data): array
    {
        $this->reference($data, 'plan_id', new WorkoutPlanDao($this->connection));
        $this->reference($data, 'exercise_id', new ExerciseDao($this->connection));
        $this->number($data, 'position', 1, 65535, true);
        $this->number($data, 'target_sets', 1, 65535, true, true);
        $this->number($data, 'target_reps', 1, 65535, true, true);
        $this->number($data, 'target_weight_kg', 0, 9999.99, false, true);
        return $data;
    }
}
