<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/UserDao.php';

class UserService extends BaseService
{
    protected array $fields = ['name', 'email', 'password_hash', 'role', 'age', 'height_cm', 'weight_kg', 'fitness_goal'];

    public function __construct(?PDO $connection = null)
    {
        parent::__construct($connection);
        $this->dao = new UserDao($this->connection);
    }

    protected function validate(array $data): array
    {
        $this->text($data, 'name', 100);
        $this->text($data, 'email', 255);
        $data['email'] = strtolower($data['email']);
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Provide a valid email address.');
        }
        $this->text($data, 'password_hash', 255);
        if (password_get_info($data['password_hash'])['algo'] === null && !str_starts_with($data['password_hash'], '!locked-')) {
            throw new InvalidArgumentException('password_hash must be an existing PHP password hash or a !locked- placeholder; never send a plaintext password.');
        }
        $data['role'] = $data['role'] ?? 'User';
        if (!in_array($data['role'], ['User', 'Admin'], true)) {
            throw new InvalidArgumentException('role must be User or Admin.');
        }
        $this->number($data, 'age', 1, 65535, true, true);
        $this->number($data, 'height_cm', 0.01, 999.99, false, true);
        $this->number($data, 'weight_kg', 0, 9999.99, false, true);
        $this->text($data, 'fitness_goal', 255);
        return $data;
    }

    protected function present(array $row): array
    {
        unset($row['password_hash']);
        return $row;
    }
}
