<?php
//session_start();
require_once 'session_config.php';

require_once 'db.php';
require_once 'logs.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'user' && $_SESSION['role'] !== 'premium')) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
$user_role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// Логируем успешный вход пользователя на страницу
logEvent($user_id, 'page_access', "User $username accessed the dashboard.");

// Get all novels
$sql = "SELECT * FROM novels";
$stmt = $pdo->query($sql);
$novels = $stmt->fetchAll();

// Separate novels into regular and premium
$regular_novels = [];
$premium_novels = [];

foreach ($novels as $novel) {
    if ($novel['is_premium'] == 1) {
        $premium_novels[] = $novel;
    } else {
        $regular_novels[] = $novel;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Novels</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to CSS -->
</head>
<body>

<div class="container">
    <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>

    <!-- "Account Settings" button -->
    <a href="settings.php" class="button">🔧 Account Settings</a>

    <!-- "Become Premium" button for regular users -->
    <?php if ($user_role == 'user'): ?>
        <a href="become_premium.php" class="button premium-button">🌟 Become Premium</a>
    <?php endif; ?>

    <!-- Button to upload a new novel -->
    <a href="upload.php" class="button">➕ Upload New Novel</a>

    <div class="novel-list">
        <h3>📖 Regular Novels</h3>
        <?php if (!empty($regular_novels)): ?>
            <?php foreach ($regular_novels as $novel): ?>
                <div class="novel">
                    <a href="read.php?id=<?php echo $novel['id']; ?>">
                        <?php echo htmlspecialchars($novel['title']); ?>
                    </a>
                    <span class="format <?php echo $novel['type']; ?>">
                        <?php echo ($novel['type'] == 'txt') ? '📄 TXT' : '📜 PDF'; ?>
                    </span>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No available novels.</p>
        <?php endif; ?>
    </div>

    <?php if ($user_role == 'premium'): ?>
        <div class="novel-list">
            <h3>🌟 Premium Novels</h3>
            <?php if (!empty($premium_novels)): ?>
                <?php foreach ($premium_novels as $novel): ?>
                    <div class="novel">
                        <a href="read.php?id=<?php echo $novel['id']; ?>">
                            <?php echo htmlspecialchars($novel['title']); ?>
                        </a>
                        <span class="format <?php echo $novel['type']; ?>">
                            <?php echo ($novel['type'] == 'txt') ? '📄 TXT' : '📜 PDF'; ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No premium novels available yet.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Logout button -->
    <a href="logout.php" class="logout">Log out</a>
</div>

</body>
</html>
