<?php
require_once 'session_config.php'; // Подключаем конфиг сессий
require_once 'logs.php'; // Логирование событий

if (isset($_SESSION['user_id'])) {
    logEvent($_SESSION['user_id'], 'logout', 'User logged out.');
} else {
    logEvent(null, 'logout_attempt', 'Logout attempt without active session.');
}

// Очищаем все сессионные переменные
$_SESSION = [];

// Удаляем CSRF токен
unset($_SESSION['csrf_token']);

// Удаляем cookie сессии
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);
}

// Уничтожаем сессию
session_destroy();

// Перенаправляем пользователя на главную страницу
header("Location: index.php");
exit;
?>
