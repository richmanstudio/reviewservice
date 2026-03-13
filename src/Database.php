<?php

declare(strict_types=1);

namespace Review;

use PDO;
use PDOException;
use RuntimeException;

class Database
{
    private static ?PDO $instance = null;
    private static array $config = [];

    public static function configure(array $config): void
    {
        self::$config = $config;
        self::$instance = null;
    }

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::$instance = self::createConnection(self::$config);
        }

        return self::$instance;
    }

    private static function createConnection(array $config): PDO
    {
        $driver = $config['driver'] ?? 'sqlite';

        try {
            if ($driver === 'sqlite') {
                $pdo = new PDO('sqlite:' . $config['path']);
            } elseif ($driver === 'mysql') {
                $dsn = sprintf(
                    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                    $config['host'],
                    $config['port'] ?? '3306',
                    $config['dbname'],
                    $config['charset'] ?? 'utf8mb4'
                );
                $pdo = new PDO($dsn, $config['username'], $config['password']);
            } else {
                throw new RuntimeException("Unsupported database driver: {$driver}");
            }

            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            return $pdo;
        } catch (PDOException $e) {
            throw new RuntimeException('Database connection failed: ' . $e->getMessage(), 0, $e);
        }
    }

    private function __construct() {}
    private function __clone() {}
}
