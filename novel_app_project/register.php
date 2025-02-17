<?php
session_start();
require_once 'db.php'; // Connect to the database
require_once 'email/send_email.php'; // File to send email
require_once 'checkPassword/checkPasswordStrength.php'; // Include password strength check function
require_once 'logs.php';
require_once 'csrf.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    if (!verifyCsrfToken($_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }
    
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Проверка длины username
    if (strlen($username) < 3 || strlen($username) > 20) {
        $error = "Username must be between 3 and 20 characters.";
        
        // Логируем ошибку с неверной длиной имени пользователя
        logEvent(NULL, 'failed_registration', 'Invalid username length for username: ' . $username);
    } else {
        // Check password strength
        $passwordCheck = checkPasswordStrength($password, $username, $email);
        if ($passwordCheck !== true) {
            $error = $passwordCheck; // If the check fails, display the error
            
            // Логируем ошибку при проверке пароля
            logEvent(NULL, 'failed_registration', 'Weak password attempted for username: ' . $username);
        } else {
            $passwordHash = password_hash($password, PASSWORD_BCRYPT); // Hash the password
            $verification_token = bin2hex(random_bytes(16)); // Generate a unique token for verification

            // Check if the user exists
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
            $stmt->execute(['username' => $username, 'email' => $email]);
            $user = $stmt->fetch();

            if ($user) {
                $error = "Invalid username or password.";  // Message before: A user with this username or email already exists.
                
                // Логируем ситуацию, когда пользователь уже существует
                logEvent(NULL, 'failed_registration', 'User with username or email already exists: ' . $username . ' / ' . $email);
            } else {
                // Insert the data into the database (with the token)
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, verification_token) VALUES (:username, :email, :password, :verification_token)");
                $stmt->execute(['username' => $username, 'email' => $email, 'password' => $passwordHash, 'verification_token' => $verification_token]);
                
                // Логируем успешную регистрацию
                logEvent(NULL, 'successful_registration', 'New user registered with username: ' . $username);

                // Send email with verification link
                if (sendVerificationEmail($email, $verification_token)) {
                    echo "A verification email has been sent to your email address with instructions.";

                    // Логируем успешную отправку email
                    logEvent(NULL, 'email_sent', 'Verification email sent to: ' . $email);
                } else {
                    // Логируем ошибку при отправке email
                    logEvent(NULL, 'email_send_failed', 'Failed to send verification email to: ' . $email);
                }
            }
        }
    }
    
    refreshCsrfToken();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
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
            display: inline-block;
            width: 95%;
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
    <h2>Register</h2>
    <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
    <form action="register.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="email" name="email" placeholder="Email" required><br>

        <div style="display: flex; align-items: center; width: 100%;">
            <input type="password" id="password" name="password" placeholder="Password" required
                style="width: 90%; padding: 10px; font-size: 14px;">
            <button type="button" id="togglePassword"
                onclick="togglePasswordVisibility('password', 'togglePassword')"
                style="width: 10%; max-width: 40px; margin-left: 5px; border: none; background: none; cursor: pointer; padding: 5px; font-size: 16px;">
                👁
            </button>
        </div>

        <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login</a></p>
</div>

</body>
</html>
