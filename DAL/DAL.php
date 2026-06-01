<?php
// =========================================
// Highrise – Database Access Layer
// =========================================

$host     = 'localhost';
$user     = 'u354286093_smsmanagement';
$password = 'SMS@2026Site';
$database = 'u354286093_smsdb';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die(json_encode([
        'success' => false,
        'error'   => 'Database connection failed: ' . $conn->connect_error
    ]));
}

$conn->set_charset('utf8mb4');
?>
