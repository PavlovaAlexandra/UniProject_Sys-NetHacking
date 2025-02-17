<?php
session_start();
require_once 'db.php';
require_once 'email/send_email.php';
require_once 'logs.php';
require_once 'csrf.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }
    
    $email = $_POST['email'];
    logEvent(null, 'password_reset_request', "Password reset requested for email: $email");

    // Проверяем, есть ли пользователь с таким email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user) {
        $current_time = time();
        $last_attempt_time = $user['last_reset_attempt_time'];
        $reset_attempts = $user['password_reset_attempts'];

        // Если попыток сброса больше 3 за час, блокируем
        if ($reset_attempts >= 3 && ($current_time - $last_attempt_time < 120)) {
            $error = "Too many requests. Repeat in 1 hour.";
            logEvent($user['id'], 'password_reset_blocked', "Too many password reset attempts for email: $email");
        } else {
            // Генерируем токен сброса пароля
            $reset_token = bin2hex(random_bytes(16));

            // Обновляем базу данных
            $stmt = $pdo->prepare("UPDATE users SET verification_token = :token, password_reset_attempts = password_reset_attempts + 1, last_reset_attempt_time = :time WHERE email = :email");
            $stmt->execute([
                'token' => $reset_token,
                'time' => $current_time,
                'email' => $email
            ]);

            // Отправляем email
            sendResetPasswordEmail($email, $reset_token);
            
            logEvent($user['id'], 'password_reset_email_sent', "Password reset email sent to: $email");

            echo "Instructions for password reset sent to your email.";
        }
    } else {
        $error = "Email not found.";
        logEvent(null, 'password_reset_failed', "Password reset attempted for non-existent email: $email");
    }
    
    refreshCsrfToken();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password?</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
        }
        .form-container {
            width: 400px;
            margin: 100px auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Forgot Password?</h2>
    <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
    <form action="forgot_password.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
        <input type="email" name="email" placeholder="Your email" required><br>
        <button type="submit">Send password reset link</button>
    </form>
    <p>Remembered your password? <a href="login.php">Log in</a></p>
</div>

</body>
</html>
