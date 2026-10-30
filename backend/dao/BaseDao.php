<?php
require_once __DIR__ . '/../config/Database.php';

abstract class BaseDao
{
    protected PDO $connection;
    private string $table;
    private array $columns;

    protected function __construct(string $table, array $columns, ?PDO $connection = null)
    {
        $this->connection = $connection ?? Database::connect();
        $this->table = $table;
        $this->columns = $columns;
    }

    public function getAll(): array
    {
        $statement = $this->connection->prepare("SELECT * FROM `{$this->table}` ORDER BY id");
        $statement->execute();
        return $statement->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $statement = $this->connection->prepare("SELECT * FROM `{$this->table}` WHERE id = :id");
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();
        return $row === false ? null : $row;
    }

    public function create(array $data): int
    {
        $this->validateColumns($data);
        $columns = '`' . implode('`, `', array_keys($data)) . '`';
        $parameters = ':' . implode(', :', array_keys($data));
        $statement = $this->connection->prepare("INSERT INTO `{$this->table}` ($columns) VALUES ($parameters)");
        $statement->execute($data);
        return (int) $this->connection->lastInsertId();
    }

    // Returns whether the record exists, including an update with unchanged values.
    public function update(int $id, array $data): bool
    {
        $this->validateColumns($data);
        $assignments = [];
        foreach (array_keys($data) as $column) {
            $assignments[] = "`$column` = :$column";
        }
        $sql = "UPDATE `{$this->table}` SET " . implode(', ', $assignments) . ' WHERE id = :record_id';
        $statement = $this->connection->prepare($sql);
        $statement->execute($data + ['record_id' => $id]);
        return $this->getById($id) !== null;
    }

    public function delete(int $id): bool
    {
        $statement = $this->connection->prepare("DELETE FROM `{$this->table}` WHERE id = :id");
        $statement->execute(['id' => $id]);
        return $statement->rowCount() > 0;
    }

    private function validateColumns(array $data): void
    {
        if ($data === []) {
            throw new InvalidArgumentException('Provide at least one column.');
        }
        foreach (array_keys($data) as $column) {
            if (!in_array($column, $this->columns, true)) {
                throw new InvalidArgumentException('Unknown or non-writable column.');
            }
        }
    }
}
