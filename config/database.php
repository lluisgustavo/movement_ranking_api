<?php

$envFile = dirname(__DIR__) . '/.env';

if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        if (str_contains($line, '=')) {
            [$name, $value] = explode('=', $line, 2);
            $_ENV[trim($name)] = trim($value, " \t\n\r\0\x0B\"'");
        }
    }
} 

return function (): PDO {
    $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
    $port = $_ENV['DB_PORT'] ?? '3306';
    $db_name = $_ENV['DB_NAME'] ?? 'movement_ranking';
    $db_charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';
    $db_user = $_ENV['DB_USER'] ?? 'root';
    $db_pass = $_ENV['DB_PASS'] ?? '';
    $dataSourceName = "mysql:host=$host;port=$port;dbname=$db_name;charset=$db_charset";
    
    return new PDO($dataSourceName, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
};