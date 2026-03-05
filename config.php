<?php
// Secure session configuration and start
// Use secure cookie flags where possible and SameSite=Lax to help protect session cookies.
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Lax'
]);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "movie_db");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
