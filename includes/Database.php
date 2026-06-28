<?php
/**
 * PDO Database Connection Singleton
 */

declare(strict_types=1);

class Database
{
    private static ?PDO $instance = null;

    /**
     * Get PDO database connection instance
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $config = require APP_ROOT . '/config/database.php';

            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                $config['host'],
                $config['dbname'],
                $config['charset']
            );

            self::$instance = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                $config['options']
            );
        }

        return self::$instance;
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent unserialization
     */
    public function __wakeup()
    {
        throw new Exception('Cannot unserialize singleton');
    }
}
