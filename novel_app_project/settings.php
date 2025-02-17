<?php
require_once 'session_config.php';
require_once 'db.php';
require_once 'checkPassword/checkPasswordStrength.php';
require_once 'logs.php';
require_once 'csrf.php';

if (!isset($_SESSION['user_id'])) {
    logEvent(null, 'unauthorized_access', 'Attempt to access settings without authentication.');
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Получаем роль пользователя из базы данных
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch();
$user_role = $user['role']; // Получаем роль пользователя (например, 'admin', 'user', 'premium')

logEvent($user_id, 'settings_access', 'User accessed settings page.');

// Обработка смены пароля
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    if (!verifyCsrfToken($_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }
    
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Получаем текущий пароль пользователя из базы данных
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = :id");
    $stmt->execute(['id' => $user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        logEvent($user_id, 'error', 'User not found during password change attempt.');
        die("Error: User not found.");
    }

    // Проверяем старый пароль
    if (password_verify($old_password, $user['password'])) {
        
        // Check the strength of the new password
        $passwordCheck = checkPasswordStrength($new_password, $username, $user['email']);
        if ($passwordCheck !== true) {
            $error = $passwordCheck; // If password is weak, show the error message
            logEvent($user_id, 'password_change_failed', 'New password did not meet security requirements.');
        } else {
            // Проверяем, что новый пароль и подтверждение совпадают
            if ($new_password === $confirm_password) {
                // Хэшируем новый пароль
                $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

                // Обновляем пароль в базе данных
                $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
                $stmt->execute(['password' => $hashed_password, 'id' => $user_id]);

                $success = "Password successfully updated!";
                logEvent($user_id, 'password_changed', 'User successfully changed password.');
            } else {
                $error = "New password and confirmation do not match.";
                logEvent($user_id, 'password_change_failed', 'New password and confirmation did not match.');
            }
        }
    } else {
        $error = "Old password is incorrect.";
        logEvent($user_id, 'password_change_failed', 'Incorrect old password entered.');
    }
    
    refreshCsrfToken();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings</title>
    <link rel="stylesheet" href="styles_settings.css">
</head>

<body>

<div class="form-container">
    <h2>Change Password</h2>

    <?php if (isset($success)): ?>
        <p class="success"><?php echo $success; ?></p>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <form action="settings.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

        <!-- Old Password Field -->
        <div class="password-container">
            <input type="password" id="old_password" name="old_password" placeholder="Old Password" required>
            <button type="button" id="toggleOldPassword">👁</button>
        </div>

        <!-- New Password Field -->
        <div class="password-container">
            <input type="password" id="new_password" name="new_password" placeholder="New Password" required>
            <button type="button" id="toggleNewPassword">👁</button>
        </div>

        <!-- Confirm Password Field -->
        <div class="password-container">
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm New Password" required>
            <button type="button" id="toggleConfirmPassword">👁</button>
        </div>

        <button type="submit">Change Password</button>
    </form>

    <!-- Conditional Link Based on User Role -->
    <?php
    // Redirect to appropriate page based on user role
    if ($user_role == 'admin') {
        echo '<a href="admin.php" class="button back">Back to Admin Dashboard</a>';
    } else {
        echo '<a href="dashboard.php" class="button back">Back to Dashboard</a>';
    }
    ?>
</div>

<script src="script_settings.js" defer></script>
</body>
</html>
