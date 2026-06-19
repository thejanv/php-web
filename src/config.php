<?php
$host = getenv('DB_HOST') ?: 'mysql';
$db   = getenv('DB_NAME') ?: 'ashadi_app';
$user = getenv('DB_USER') ?: 'appuser';
$pass = getenv('DB_PASSWORD') ?: '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    die("Connection failed.");
}