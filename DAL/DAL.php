<?php
// =========================================
// Highrise – Database Access Layer
// =========================================

$host     = 'localhost';
$user     = 'root';
$password = '';
$database = 'projecthighrisetest';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die(json_encode([
        'success' => false,
        'error'   => 'Database connection failed: ' . $conn->connect_error
    ]));
}

$conn->set_charset('utf8mb4');
?>
