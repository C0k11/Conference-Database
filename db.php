<?php

declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $customDsn = getenv('DB_DSN');
    $driver = getenv('DB_DRIVER') ?: 'mysql';
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = getenv('DB_PORT') ?: '3306';
    $database = getenv('DB_NAME') ?: 'conferenceDB';
    $charset = getenv('DB_CHARSET') ?: 'utf8mb4';
    $username = getenv('DB_USER') ?: 'root';
    $password = getenv('DB_PASS');

    if ($password === false) {
        $password = '';
    }

    if ($customDsn !== false && $customDsn !== '') {
        $dsn = $customDsn;
    } else {
        $dsn = sprintf(
            '%s:host=%s;port=%s;dbname=%s;charset=%s',
            $driver,
            $host,
            $port,
            $database,
            $charset
        );
    }

    $pdo = new PDO(
        $dsn,
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    return $pdo;
}
