<?php
require_once 'db.php'; // Connecting to the database
require_once 'logs.php';

// Get the token from the URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    logEvent(null, 'account_verification_attempt', "Attempt to verify account with token: {$token}");

    // Check if a user exists with this token
    $stmt = $pdo->prepare("SELECT * FROM users WHERE verification_token = :token");
    $stmt->execute(['token' => $token]);
    $user = $stmt->fetch();

    if ($user) {
        // Activate the user and clear the token
        $stmt = $pdo->prepare("UPDATE users SET verification_token = NULL WHERE verification_token = :token");
        $stmt->execute(['token' => $token]);
        
        logEvent($user['id'], 'account_verified', "User '{$user['username']}' successfully verified their account.");

        echo "Your account has been successfully verified. You can now log in.";
    } else {
        logEvent(null, 'invalid_verification_token', "Invalid or expired verification token used: {$token}");
        echo "Invalid confirmation link.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Confirmation</title>
</head>
<body>

<div class="form-container">
    <h2>Account Confirmation</h2>
    <p>Your account has been successfully verified. You can now <a href="login.php">log in</a>.</p>
</div>

</body>
</html>
