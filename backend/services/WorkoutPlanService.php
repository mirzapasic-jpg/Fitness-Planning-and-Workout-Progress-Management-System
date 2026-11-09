<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/WorkoutPlanDao.php';
require_once __DIR__ . '/../dao/UserDao.php';
require_once __DIR__ . '/../dao/WorkoutSessionDao.php';

class WorkoutPlanService extends BaseService
{
    protected array $fields = ['user_id', 'name', 'description', 'planned_date'];

    public function __construct(?PDO $connection = null)
    {
        parent::__construct($connection);
        $this->dao = new WorkoutPlanDao($this->connection);
    }

    public function update(int $id, array $data): array
    {
        if (isset($data['user_id']) && is_numeric($data['user_id'])) {
            foreach ((new WorkoutSessionDao($this->connection))->getAll() as $session) {
                if ((int) $session['plan_id'] === $id && (int) $session['user_id'] !== (int) $data['user_id']) {
                    throw new InvalidArgumentException('A plan linked to workout history cannot be transferred to another user.');
                }
            }
        }
        return parent::update($id, $data);
    }

    protected function validate(array $data): array
    {
        $this->reference($data, 'user_id', new UserDao($this->connection));
        $this->text($data, 'name', 100);
        $this->text($data, 'description', 65535, true);
        $this->date($data, 'planned_date');
        return $data;
    }
}
