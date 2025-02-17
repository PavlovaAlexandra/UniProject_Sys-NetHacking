<?php
require_once 'session_config.php';
require_once 'db.php';
require_once 'logs.php';
require_once 'csrf.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Если пользователь уже вошёл, перенаправляем в зависимости от роли
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin.php");
    } elseif ($_SESSION['role'] === 'user' || $_SESSION['role'] === 'premium') {
        header("Location: dashboard.php");
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    logEvent(null, 'login_attempt', 'Login attempt with username: ' . $_POST['username'] . ' from IP: ' . $_SERVER['REMOTE_ADDR']);
    
    if (!verifyCsrfToken($_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }

    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();
    } catch (PDOException $e) {
        logEvent(null, 'db_error', 'Database query error: ' . $e->getMessage() . ' for username: ' . $username . ' from IP: ' . $_SERVER['REMOTE_ADDR']);
        die("Database query error: " . $e->getMessage());
    }

    if ($user) {
        $current_time = time();
        logEvent($user['id'], 'user_found', 'User found: ' . $user['username'] . ' from IP: ' . $_SERVER['REMOTE_ADDR']);

        // Если прошло более 10 минут с последней ошибки, сбрасываем счетчик
        if ($user['last_failed_attempt'] !== NULL && ($current_time - strtotime($user['last_failed_attempt'])) > 60) {
            $stmt = $pdo->prepare("UPDATE users SET failed_attempts = 0, lockout_time = NULL WHERE id = :id");
            $stmt->execute(['id' => $user['id']]);
            $user['failed_attempts'] = 0;
            logEvent($user['id'], 'failed_attempts_reset', 'Failed attempts reset due to timeout for user ' . $user['username']);
        }

        if ($user['failed_attempts'] >= 3 && $user['lockout_time'] > $current_time) {
            $error = "Too many failed attempts. Try again in 10 minutes.";
            logEvent($user['id'], 'account_locked', 'Account locked for user ' . $user['username']);
        } else {
            if (password_verify($password, $user['password'])) {
                if ($user['verification_token'] === NULL) {
                    // Удаляем старый идентификатор и создаём новый (защита от фиксации сессии)
                    session_regenerate_id(true);
                    
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['email'] = $user['email'];

                    $stmt = $pdo->prepare("UPDATE users SET failed_attempts = 0, lockout_time = NULL WHERE id = :id");
                    $stmt->execute(['id' => $user['id']]);

                    logEvent($user['id'], 'successful_login', 'User ' . $user['username'] . ' logged in successfully.');
                    
                    refreshCsrfToken();

                    header("Location: " . ($user['role'] === 'admin' ? "admin.php" : "dashboard.php"));
                    exit();
                } else {
                    $error = "Please confirm your email before entering.";
                    logEvent($user['id'], 'email_confirmation_required', 'User ' . $user['username'] . ' attempted to log in without confirming email.');
                }
            } else {
                $failed_attempts = $user['failed_attempts'] + 1;
                $lockout_time = ($failed_attempts >= 3) ? ($current_time + 600) : NULL; // Блокировка на 10 минут

                $stmt = $pdo->prepare("UPDATE users SET failed_attempts = :failed_attempts, lockout_time = :lockout_time, last_failed_attempt = NOW() WHERE id = :id");
                $stmt->execute([
                    'failed_attempts' => $failed_attempts,
                    'lockout_time' => $lockout_time,
                    'id' => $user['id']
                ]);

                $error = "Invalid username or password.";
                logEvent($user['id'], 'failed_login', 'Failed login attempt for user ' . $user['username'] . ' from IP: ' . $_SERVER['REMOTE_ADDR']);
            }
        }
    } else {
        $error = "Invalid username or password.";
        logEvent(null, 'failed_login', 'Failed login attempt with non-existent username from IP: ' . $_SERVER['REMOTE_ADDR']);
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles_login.css">
</head>

<body>

    <div class="form-container">
        <h2>Login</h2>
        <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
        <form action="login.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <input type="text" name="username" placeholder="Username" required><br>

            <div class="password-container">
                <input type="password" id="password" name="password" placeholder="Password" required>
                <button type="button" id="togglePassword">👁</button>
            
            </div>

            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Register</a></p>
        <p><a href="forgot_password.php">Forgot your password?</a></p>
    </div>

<script src="script.js"></script>
</body>
</html>
