<?php
// Function to load the dictionary from a text file
function loadDictionary($filePath) {
    if (file_exists($filePath)) {
        $words = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        return $words;
    }
    return [];
}

// Function to check password strength
function checkPasswordStrength($password, $username, $email) {
    // Load the dictionary
    $dictionary = loadDictionary('2024-197_most_used_passwords.txt');
    
    // Minimum password length
    if (strlen($password) < 8) {
        return "The password must be at least 8 characters long.";
    }

    // Check for lowercase and uppercase letters, digits, and special characters
    if (!preg_match('/[a-z]/', $password)) {
        return "The password must contain at least one lowercase letter.";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return "The password must contain at least one uppercase letter.";
    }
    if (!preg_match('/\d/', $password)) {
        return "The password must contain at least one digit.";
    }
    if (!preg_match('/[\W_]/', $password)) {
        return "The password must contain at least one special character.";
    }

    // Check to ensure the password does not contain the username or email
    if (stripos($password, $username) !== false) {
        return "The password should not contain the username.";
    }/*
    if (stripos($password, $email) !== false) {
        return "The password should not contain your email.";
    }*/

    // Check if the password contains common words from the dictionary
    foreach ($dictionary as $word) {
        if (stripos($password, $word) !== false) {
            return "The password should not contain common dictionary words.";
        }
    }

    return true; // If all checks pass, return true
}
?>
