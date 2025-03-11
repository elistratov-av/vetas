<?php

if (!isset($dotenv) || !isset($_ENV['DB_HOST'])) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

return [
    "class" => "yii\db\Connection",
    "dsn" => "pgsql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']}",
    "username" => "{$_ENV['DB_USER']}",
    "password" => "{$_ENV['DB_PASSWORD']}",
    "charset" => "utf8",
];