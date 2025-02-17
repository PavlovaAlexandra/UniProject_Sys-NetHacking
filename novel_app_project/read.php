<?php
//session_start();
require_once 'session_config.php';
require_once 'db.php';
require_once 'logs.php';

if (!isset($_SESSION['user_id'])) {
    logEvent(null, 'access_denied', 'Unauthorized access attempt to read.php');
    header("Location: login.php");
    exit;
}

define('UPLOADS_DIR', dirname(__DIR__) . '/uploads/novels/'); // Путь к файлам

$user_id = $_SESSION['user_id'];

// Получаем информацию о пользователе
$sql = "SELECT role FROM users WHERE id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch();

if (!$user) {
    logEvent($user_id, 'error', 'User not found in database.');
    die("Error: User not found.");
}

$user_role = $user['role']; // 'user' или 'premium'

// Получаем ID новеллы из URL
$id = $_GET['id'] ?? null;
if (!$id) {
    logEvent($user_id, 'error', 'Attempt to access novel without ID.');
    die("Error: Novel not found.");
}

// Получаем информацию о новелле
$sql = "SELECT * FROM novels WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $id]);
$novel = $stmt->fetch();

if (!$novel) {
    logEvent($user_id, 'error', "Novel with ID $id not found.");
    die("Error: Novel not found.");
}

// Проверяем, является ли новелла премиумной
if ($novel['is_premium'] && $user_role !== 'premium') {
    logEvent($user_id, 'access_denied', "User tried to access premium novel ID $id without permissions.");
    die("Access denied: This novel is for premium users only.");
}

// Формируем путь к файлу
$file_path = UPLOADS_DIR . basename($novel['file_path']);

logEvent($user_id, 'read_novel', "User accessed novel ID $id ({$novel['title']}).");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($novel['title']); ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="container">
    <h2><?php echo htmlspecialchars($novel['title']); ?></h2>

    <?php if ($novel['type'] === 'txt'): ?>
        <?php if (file_exists($file_path)): ?>
            <div class='novel-content'>
                <pre><?php echo htmlspecialchars(file_get_contents($file_path)); ?></pre>
            </div>
        <?php else: ?>
            <p class='error'>Error: File not found.</p>
        <?php endif; ?>
    <?php elseif ($novel['type'] === 'pdf'): ?>
        <p>This is a PDF novel. Click the button below to download it:</p>
        <a href="download.php?id=<?php echo $novel['id']; ?>" class="button">📥 Download PDF</a>
    <?php else: ?>
        <p class="error">Unsupported format.</p>
    <?php endif; ?>

    <a href="dashboard.php" class="button back">⬅ Go back</a>
</div>

</body>
</html>
