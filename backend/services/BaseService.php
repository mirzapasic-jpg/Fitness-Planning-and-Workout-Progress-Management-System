<?php
require_once __DIR__ . '/../dao/BaseDao.php';

abstract class BaseService
{
    protected BaseDao $dao;
    protected PDO $connection;
    protected array $fields = [];

    public function __construct(?PDO $connection = null)
    {
        $this->connection = $connection ?? Database::connect();
    }

    public function getAll(): array
    {
        return array_map(fn($row) => $this->present($row), $this->dao->getAll());
    }

    public function getById(int $id): array
    {
        $row = $this->dao->getById($id);
        if ($row === null) {
            throw new OutOfBoundsException('Record not found.');
        }
        return $this->present($row);
    }

    public function create(array $data): array
    {
        $this->checkFields($data);
        $data = $this->validate($data);
        return $this->getById($this->dao->create($data));
    }

    public function update(int $id, array $data): array
    {
        $this->checkFields($data);
        $existing = $this->dao->getById($id);
        if ($existing === null) {
            throw new OutOfBoundsException('Record not found.');
        }
        unset($existing['id']);
        $data = $this->validate(array_replace($existing, $data));
        if (!$this->dao->update($id, $data)) {
            throw new OutOfBoundsException('Record not found.');
        }
        return $this->getById($id);
    }

    public function delete(int $id): void
    {
        if (!$this->dao->delete($id)) {
            throw new OutOfBoundsException('Record not found.');
        }
    }

    protected function present(array $row): array
    {
        return $row;
    }

    abstract protected function validate(array $data): array;

    private function checkFields(array $data): void
    {
        if ($data === [] || array_diff(array_keys($data), $this->fields)) {
            throw new InvalidArgumentException('Provide a nonempty object containing only writable fields.');
        }
    }

    protected function text(array &$data, string $field, int $max, bool $allowEmpty = false, bool $nullable = false): void
    {
        $value = $data[$field] ?? null;
        if ($nullable && $value === null) {
            $data[$field] = null;
            return;
        }
        if (!is_string($value)) {
            throw new InvalidArgumentException("$field must be text.");
        }
        $value = trim($value);
        if ((!$allowEmpty && $value === '') || mb_strlen($value) > $max || strlen($value) > 65535) {
            throw new InvalidArgumentException("$field is empty or too long (maximum $max characters).");
        }
        $data[$field] = $value;
    }

    protected function number(array &$data, string $field, float $min, float $max, bool $integer = false, bool $nullable = false): void
    {
        $value = $data[$field] ?? null;
        if ($nullable && $value === null) {
            $data[$field] = null;
            return;
        }
        if (is_string($value) && is_numeric($value)) {
            $value += 0;
        }
        if ((!is_int($value) && !is_float($value)) || !is_finite((float) $value) || $value < $min || $value > $max ||
            ($integer && floor($value) != $value) || (!$integer && abs($value * 100 - round($value * 100)) > 0.00001)) {
            throw new InvalidArgumentException("$field must be a number between $min and $max" . ($integer ? ' with no fraction.' : ' with at most two decimal places.'));
        }
        $data[$field] = $integer ? (int) $value : $value;
    }

    protected function reference(array &$data, string $field, BaseDao $dao, bool $nullable = false): void
    {
        $this->number($data, $field, 1, 4294967295, true, $nullable);
        if ($data[$field] !== null && $dao->getById($data[$field]) === null) {
            throw new InvalidArgumentException("$field must reference an existing record.");
        }
    }

    protected function date(array $data, string $field, bool $completed = false): void
    {
        $value = $data[$field] ?? null;
        $date = is_string($value) && preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/D', $value)
            ? DateTimeImmutable::createFromFormat('!Y-m-d', $value) : false;
        if (!$date || $date->format('Y-m-d') !== $value || $value < '1000-01-01' || $value > '9999-12-31') {
            throw new InvalidArgumentException("$field must be a real date in YYYY-MM-DD format.");
        }
        if ($completed && $value > date('Y-m-d')) {
            throw new InvalidArgumentException('Completed workouts cannot have a future date.');
        }
    }
}
