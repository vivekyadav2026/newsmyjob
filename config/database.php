<?php
/**
 * Database Configuration
 */

declare(strict_types=1);

return [
    'host'     => 'localhost',
    // 'dbname'   => 'newsmyjob',
    // 'username' => 'root',
    'dbname'   => 'u798623491_myjobhubdb',
    'username' => 'u798623491_myjobhub',
    'password' => 'Myjobhub@2026%',
    
    'password' => '',
    'charset'  => 'utf8mb4',
    'options'  => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ],
];
