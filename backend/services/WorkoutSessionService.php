<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/WorkoutSessionDao.php';
require_once __DIR__ . '/../dao/UserDao.php';
require_once __DIR__ . '/../dao/WorkoutPlanDao.php';

class WorkoutSessionService extends BaseService
{
    protected array $fields = ['user_id', 'plan_id', 'workout_date', 'notes'];

    public function __construct(?PDO $connection = null)
    {
        parent::__construct($connection);
        $this->dao = new WorkoutSessionDao($this->connection);
    }

    protected function validate(array $data): array
    {
        $this->reference($data, 'user_id', new UserDao($this->connection));
        $planDao = new WorkoutPlanDao($this->connection);
        $this->reference($data, 'plan_id', $planDao, true);
        if ($data['plan_id'] !== null && (int) $planDao->getById($data['plan_id'])['user_id'] !== $data['user_id']) {
            throw new InvalidArgumentException('The workout plan must belong to the session user.');
        }
        $this->date($data, 'workout_date', true);
        $this->text($data, 'notes', 65535, true, true);
        return $data;
    }
}
