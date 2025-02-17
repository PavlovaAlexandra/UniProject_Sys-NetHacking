<?php
// Protection from Clickjacking and XSS
header("Content-Security-Policy: default-src 'self'; frame-ancestors 'none'; style-src 'self'; script-src 'self';");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");

session_set_cookie_params([
    'lifetime' => 1800,           // 1800 seconds = 30 minutes
    'path' => '/',                // Available for all pages of the site
    'domain' => '',               // Leave empty if the session should only work on this domain
    'secure' => isset($_SERVER['HTTPS']), // Only HTTPS
    'httponly' => true,           // Ban JS-access (protection from XSS)
    'samesite' => 'Lax'           // Protection against CSRF (can be 'Strict' if no external transitions are needed)
]);
session_start();
?>
