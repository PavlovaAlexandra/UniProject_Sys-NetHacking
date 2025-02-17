<?php
//session_start();
require_once 'session_config.php';

require_once 'db.php';
require_once 'logs.php';

if (!isset($_SESSION['user_id'])) {
    logEvent(null, 'unauthorized_download_attempt', 'Unauthorized download attempt from IP: ' . $_SERVER['REMOTE_ADDR']);
    header("Location: login.php");
    exit;
}

// Определяем путь к загруженным файлам
define('UPLOADS_DIR', dirname(__DIR__) . '/uploads/novels/'); // Путь на уровень выше папки проекта

// Получаем ID новеллы из URL
$id = $_GET['id'] ?? null;
if (!$id) {
    logEvent($user_id, 'download_error', "User $username attempted to download a novel without providing an ID.");
    die("Error: No novel ID provided.");
}

$sql = "SELECT * FROM novels WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $id]);
$novel = $stmt->fetch();

if (!$novel) {
    logEvent($user_id, 'download_error', "User $username attempted to download a non-existent novel (ID: $id).");
    die("Error: Novel not found.");
}

// Формируем путь к файлу
$file_path = UPLOADS_DIR . basename($novel['file_path']);

if ($novel['type'] === 'pdf' && file_exists($file_path)) {
    logEvent($user_id, 'novel_download', "User $username downloaded the novel '{$novel['title']}' (ID: $id).");

    // Отправляем PDF-файл пользователю
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
    header('Content-Length: ' . filesize($file_path));
    readfile($file_path);
    exit();
} else {
    logEvent($user_id, 'download_error', "User $username attempted to download a missing or unsupported file type (ID: $id).");
    echo "File not found or not a PDF.";
}
?>
