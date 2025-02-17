<?php
// Генерация нового CSRF-токена
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Проверка валидности CSRF-токена
function verifyCsrfToken($token) {
    return isset($token) && $token === $_SESSION['csrf_token'];
}

// Обновление токена после успешной проверки
function refreshCsrfToken() {
    unset($_SESSION['csrf_token']);
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
