<?php
session_start();

// Проверяем, авторизован ли пользователь
if (isset($_SESSION['user_id'])) {
    // Редирект в зависимости от роли
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin.php");
    } elseif ($_SESSION['role'] === 'user' || $_SESSION['role'] === 'premium') {
        header("Location: dashboard.php");
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Novel App</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f4f4;
            margin: 0;
        }
        .container {
            text-align: center;
            background-color: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        h1 {
            font-size: 2rem;
        }
        .button {
            display: inline-block;
            margin: 10px 0;
            padding: 15px 30px;
            font-size: 1.1rem;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
        .button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Welcome to Novel App!</h1>
    <p>To continue, please log in or register.</p>
    <a href="login.php" class="button">Log In</a>
    <br>
    <a href="register.php" class="button">Register</a>
</div>

</body>
</html>
