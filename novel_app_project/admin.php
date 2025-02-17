<?php
require_once 'session_config.php';
require_once 'db.php';
require_once 'csrf.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Обработка изменения роли пользователя
if (isset($_POST['change_role'])) {
    if (!verifyCsrfToken($_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }
    
    $user_id = $_POST['user_id'];
    $new_role = $_POST['new_role'];

    $stmt = $pdo->prepare("UPDATE users SET role = :role WHERE id = :id");
    $stmt->execute(['role' => $new_role, 'id' => $user_id]);

    refreshCsrfToken();
    header("Location: admin.php"); // Перезагружаем страницу
}

// Обработка удаления пользователя
if (isset($_POST['delete_user'])) {
    if (!verifyCsrfToken($_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }
    
    $user_id = $_POST['user_id'];

    // Удаление пользователя
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute(['id' => $user_id]);

    refreshCsrfToken();
    header("Location: admin.php"); // Перезагружаем страницу
}

// Обработка удаления новеллы через POST
if (isset($_POST['delete_novel'])) {
    if (!verifyCsrfToken($_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }
    
    $novel_id = $_POST['novel_id'];

    // Удаление новеллы
    $stmt = $pdo->prepare("DELETE FROM novels WHERE id = :id");
    $stmt->execute(['id' => $novel_id]);

    refreshCsrfToken();
    header("Location: admin.php"); // Перезагружаем страницу
}

// Получаем всех пользователей
$sql = "SELECT * FROM users";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$users = $stmt->fetchAll();

// Получаем все новеллы
$sql = "SELECT * FROM novels";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$novels = $stmt->fetchAll();

// Получаем все логи
$sql = "SELECT * FROM logs ORDER BY event_time DESC LIMIT 100"; // Ограничим вывод 100 последними записями
$stmt = $pdo->prepare($sql);
$stmt->execute();
$logs = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="container">
    <h1>Admin Dashboard</h1>

    <a href="settings.php" class="button">🔧 Account Settings</a>

    <section class="manage-users">
        <h2>Manage Users</h2>
        <table class="user-table">
            <tr>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Action</th>
            </tr>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td>
                        <form method="POST" class="role-form">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <select name="new_role" class="role-select">
                                <option value="user" <?php if ($user['role'] == 'user') echo 'selected'; ?>>User</option>
                                <option value="premium" <?php if ($user['role'] == 'premium') echo 'selected'; ?>>Premium</option>
                                <option value="admin" <?php if ($user['role'] == 'admin') echo 'selected'; ?>>Admin</option>
                            </select>
                            <button type="submit" name="change_role" class="button">Change Role</button>
                        </form>
                    </td>
                    <td>
                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');" class="delete-form">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <button type="submit" name="delete_user" class="button delete-button">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </section>

    <section class="manage-novels">
        <h2>Manage Novels</h2>
        <?php foreach ($novels as $novel): ?>
            <div class="novel">
                <span><?php echo htmlspecialchars($novel['title']); ?></span>
                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this novel?');" class="delete-form">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    <input type="hidden" name="novel_id" value="<?php echo $novel['id']; ?>">
                    <button type="submit" name="delete_novel" class="button delete-button">Delete</button>
                </form>
            </div>
        <?php endforeach; ?>
    </section>

    <section class="logs">
        <h2>Logs</h2>
        <table class="log-table">
            <tr>
                <th>Event Time</th>
                <th>User ID</th>
                <th>Event Type</th>
                <th>Event Description</th>
            </tr>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?php echo htmlspecialchars($log['event_time']); ?></td>
                    <td><?php echo htmlspecialchars($log['user_id']); ?></td>
                    <td><?php echo htmlspecialchars($log['event_type']); ?></td>
                    <td><?php echo htmlspecialchars($log['event_description']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </section>

    <a href="logout.php" class="logout">Logout</a>
</div>

</body>
</html>
