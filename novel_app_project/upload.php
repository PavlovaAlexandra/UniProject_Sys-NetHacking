<?php
require_once 'session_config.php';
require_once 'db.php';
require_once 'logs.php';
require_once 'csrf.php';

if (!isset($_SESSION['user_id'])) {
    logEvent(null, 'unauthorized_access', 'Attempt to access file upload page without authentication.');
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT role FROM users WHERE id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch();

$is_premium_user = ($user['role'] == 'premium') ? 1 : 0;
$username = $_SESSION['username'];

define('TEXT_LIMIT', 15000); // Character limit
define('UPLOADS_DIR', dirname(__DIR__) . '/uploads/novels/'); // Перенос папки вверх

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    if (!verifyCsrfToken($_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }
    
    try {
        $title = $_POST['title'];
        $is_premium = isset($_POST['is_premium']) ? 1 : 0;
        $type = $_POST['type'];

        $clean_title = preg_replace('/[^a-zA-Z0-9-_]/', '_', $title);

        if ($type == 'txt') {
            $content = $_POST['content'];

            // Check character limit
            if (mb_strlen($content) > TEXT_LIMIT) {
                throw new Exception('Character limit exceeded (max. ' . TEXT_LIMIT . ').');
            }

            $file_name = $clean_title . '.txt';
            $file_path = UPLOADS_DIR . $file_name;
            file_put_contents($file_path, $content);

            $sql = "INSERT INTO novels (title, type, file_path, is_premium, author) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$title, $type, $file_path, $is_premium, $username]);
            
            logEvent($user_id, 'text_uploaded', "Text file '{$file_name}' uploaded successfully.");

        } elseif ($type == 'pdf') {
            if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] == UPLOAD_ERR_OK) {
                $file_name = $clean_title . '.pdf';
                $file_path = UPLOADS_DIR . $file_name;
                $file_extension = pathinfo($_FILES['pdf_file']['name'], PATHINFO_EXTENSION);

                if (strtolower($file_extension) != 'pdf') {
                    throw new Exception('File must be in PDF format.');
                }

                if (move_uploaded_file($_FILES['pdf_file']['tmp_name'], $file_path)) {
                    $sql = "INSERT INTO novels (title, type, file_path, is_premium, author) VALUES (?, ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$title, $type, $file_path, $is_premium, $username]);
                    
                    logEvent($user_id, 'pdf_uploaded', "PDF file '{$file_name}' uploaded successfully.");
                } else {
                    throw new Exception('Error uploading PDF file.');
                }
            } else {
                throw new Exception('Please select a PDF file to upload.');
            }
        }

        logEvent($user_id, 'novel_created', "Novel '{$title}' added to database.");
        
        refreshCsrfToken();
        
        header('Location: dashboard.php');
        exit();

    } catch (Exception $e) {
        $error_message = $e->getMessage();
        logEvent($user_id, 'upload_error', "Error: {$error_message}");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Novel</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="container">
    <h2>Upload Novel</h2>

    <?php if (!empty($error_message)): ?>
        <p class="error"><?php echo $error_message; ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="upload-form">
        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
        <label for="title">Title:</label>
        <input type="text" name="title" id="title" required>

        <?php if ($is_premium_user == 1): ?>
            <label class="checkbox-label">
                <input type="checkbox" name="is_premium" id="is_premium"> Premium
            </label>
        <?php endif; ?>

        <label for="type">Format:</label>
        <select name="type" id="type">
            <option value="txt">TXT</option>
            <option value="pdf">PDF</option>
        </select>

        <div id="txtFields">
            <label for="content">Novel content (max. 15,000 characters):</label>
            <textarea name="content" id="content" required></textarea>
            <p class="char-counter">Remaining: <span id="charRemaining"><?php echo TEXT_LIMIT; ?></span> characters</p>
        </div>

        <div id="pdfFields" class="hidden">
            <label for="pdf_file">Upload PDF:</label>
            <input type="file" name="pdf_file" id="pdf_file" accept=".pdf" required>
        </div>

        <button type="submit" class="button">📤 Upload</button>
    </form>


    <a href="dashboard.php" class="button back">⬅ Back</a>
</div>

<script src="script_upload.js"></script>

</body>
</html>
