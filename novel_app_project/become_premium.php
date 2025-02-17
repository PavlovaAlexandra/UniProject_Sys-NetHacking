<?php
require_once 'session_config.php';
require_once 'db.php';
require_once 'email/send_email.php'; // File to send email
require_once 'logs.php';
require_once 'csrf.php';

if (!isset($_SESSION['user_id'])) {
    logEvent(null, 'unauthorized_access', 'Unauthorized access attempt to become_premium.php from IP: ' . $_SERVER['REMOTE_ADDR']);
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }
    
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username']; // User's username
    $user_email = $_SESSION['email'];  // User's email
    
    // Логируем запрос на получение премиум-статуса
    logEvent($user_id, 'premium_request', 'User ' . $username . ' (ID: ' . $user_id . ') requested premium status.');

    // Send a request for premium status to the administrator
    sendPremiumRequestEmail($username, $user_email, $user_id);
    
    refreshCsrfToken();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Status Request</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to CSS -->
</head>
<body>

<div class="container">
    <h2>Premium Status Request</h2>

    <form action="become_premium.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
        
        <p>You want to become a premium user. After submitting the request, the administrator will contact you.</p>
        <button type="submit" class="button">Submit Request</button>
    </form>

    <a href="dashboard.php" class="button back">Back</a>
</div>

</body>
</html>
