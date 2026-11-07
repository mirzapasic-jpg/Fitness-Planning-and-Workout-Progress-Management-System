<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/ExerciseDao.php';

class ExerciseService extends BaseService
{
    protected array $fields = ['name', 'muscle_group'];

    public function __construct(?PDO $connection = null)
    {
        parent::__construct($connection);
        $this->dao = new ExerciseDao($this->connection);
    }

    protected function validate(array $data): array
    {
        $this->text($data, 'name', 100);
        $this->text($data, 'muscle_group', 50);
        return $data;
    }
}
