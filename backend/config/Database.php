<?php

class Database
{
    public static function connect(): PDO
    {
        $path = __DIR__ . '/config.php';
        if (!is_file($path)) {
            throw new RuntimeException('Copy backend/config/config.example.php to config.php and configure the database.');
        }
        $config = require $path;
        foreach (['host', 'port', 'database', 'username', 'password'] as $key) {
            if (!is_array($config) || !array_key_exists($key, $config)) {
                throw new RuntimeException('Database configuration is missing required settings.');
            }
        }
        // Only fixed DSN components are accepted; credentials are separate PDO arguments.
        foreach (['host', 'database'] as $key) {
            if (!is_string($config[$key]) || !preg_match('/^[a-zA-Z0-9_.-]+$/D', $config[$key])) {
                throw new RuntimeException('Invalid database host or name.');
            }
        }
        if (filter_var($config['port'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]]) === false) {
            throw new RuntimeException('Invalid database port.');
        }
        try {
            return new PDO(
                "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
                $config['username'],
                $config['password'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                 PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                 PDO::ATTR_EMULATE_PREPARES => false]
            );
        } catch (PDOException $exception) {
            throw new RuntimeException('Database connection failed. Check the private configuration and MySQL server.', 0, $exception);
        }
    }
}
