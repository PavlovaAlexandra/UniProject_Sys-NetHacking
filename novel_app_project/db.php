<?php
require_once __DIR__ . '/vendor/autoload.php'; // Подключаем Composer для загрузки .env

use Dotenv\Dotenv;


// Загружаем .env
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();


// Конфигурация базы данных из .env
$host = $_ENV['DB_HOST'];
$dbname = $_ENV['DB_NAME'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];

try {
    // Подключение к базе данных через PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Включаем режим ошибок
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Ассоциативные массивы по умолчанию
        PDO::ATTR_EMULATE_PREPARES => false, // Отключаем эмуляцию подготовленных запросов
    ]);
} catch (PDOException $e) {
    error_log("Ошибка подключения к базе данных: " . $e->getMessage()); // Логируем ошибку
    die("Ошибка соединения с базой данных."); // Не показываем детали пользователю
}
?>
