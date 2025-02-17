<?php
session_start();
require_once 'db.php'; // Connect to the database
require_once 'checkPassword/checkPasswordStrength.php'; // Include password strength check function
require_once 'logs.php';
require_once 'csrf.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    if (!verifyCsrfToken($_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }
    
    $new_password = $_POST['new_password'];
    $reset_token = $_GET['token']; // Reset password token from URL
    
    if (!$reset_token) {
        logEvent(null, 'error', 'Password reset attempt without token.');
        die("Error: No reset token provided.");
    }

    // Check token validity
    $stmt = $pdo->prepare("SELECT * FROM users WHERE verification_token = :verification_token");
    $stmt->execute(['verification_token' => $reset_token]);
    $user = $stmt->fetch();

    if ($user) {
        $user_id = $user['id'];
        logEvent($user_id, 'password_reset_attempt', 'User attempted to reset password.');

        // Check new password strength
        $passwordCheck = checkPasswordStrength($new_password, $user['username'], $user['email']);
        if ($passwordCheck !== true) {
            $error = $passwordCheck; // If check fails, display the error
            logEvent($user_id, 'password_reset_failed', "Password does not meet security requirements.");
        } else {
            // Hash the new password
            $new_password_hash = password_hash($new_password, PASSWORD_BCRYPT);

            // Update password and clear the token
            $stmt = $pdo->prepare("UPDATE users SET password = :password, verification_token = NULL WHERE verification_token = :verification_token");
            $stmt->execute(['password' => $new_password_hash, 'verification_token' => $reset_token]);

            logEvent($user_id, 'password_reset_success', 'User successfully reset password.');
            echo "Your password has been successfully updated.";
        }
    } else {
        $error = "Invalid or expired token.";
        logEvent(null, 'password_reset_failed', "Invalid or expired token used.");
    }
    
    refreshCsrfToken();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
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

<script>
    function togglePasswordVisibility(inputId, toggleId) {
        const passwordInput = document.getElementById(inputId);
        const toggleButton = document.getElementById(toggleId);

        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            toggleButton.textContent = "🔒";
        } else {
            passwordInput.type = "password";
            toggleButton.textContent = "👁";
        }
    }
</script>

<body>

<div class="form-container">
    <h2>Reset Password</h2>
    <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
    <form action="reset_password.php?token=<?php echo $_GET['token']; ?>" method="POST">

        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

        <div style="display: flex; align-items: center; width: 100%;">
            <input type="password" id="password" name="new_password" placeholder="New Password" required
                style="width: 90%; padding: 10px; font-size: 14px;">
            <button type="button" id="togglePassword"
                onclick="togglePasswordVisibility('password', 'togglePassword')"
                style="width: 10%; max-width: 40px; margin-left: 5px; border: none; background: none; cursor: pointer; padding: 5px; font-size: 16px;">
                👁
            </button>
        </div>

        <button type="submit">Reset Password</button>
    </form>

    <div class="back-link">
        <p>Back to login page: <a href="login.php">Login</a></p>
    </div>
</div>

</body>
</html>
