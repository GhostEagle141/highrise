<?php
// =========================================
// Highrise – Login Handler
// =========================================

ini_set('display_errors', 0);
error_reporting(0);

require_once '../DAL/DAL.php';

header('Content-Type: application/json');
session_start();

$name     = isset($_POST['name'])     ? trim($_POST['name'])     : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

if ($name === '' || $password === '') {
    echo json_encode(['success' => false, 'error' => 'Username and password are required.']);
    exit;
}

$escaped = $conn->real_escape_string($name);
$result  = $conn->query("SELECT * FROM users WHERE Name = '$escaped' LIMIT 1");

if (!$result || $result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid credentials.']);
    exit;
}

$user  = $result->fetch_assoc();
$valid = false;

// Check MD5 hash (primary method)
if (md5($password) === $user['Password']) {
    $valid = true;
}
// Fallback: bcrypt (legacy)
elseif (password_verify($password, $user['Password'])) {
    // Upgrade to MD5
    $hashed = md5($password);
    $conn->query("UPDATE users SET Password = '$hashed' WHERE Name = '$escaped'");
    $valid = true;
}
// Fallback: plain text (first-time migration)
elseif ($password === $user['Password']) {
    $hashed = md5($password);
    $conn->query("UPDATE users SET Password = '$hashed' WHERE Name = '$escaped'");
    $valid = true;
}

if (!$valid) {
    echo json_encode(['success' => false, 'error' => 'Invalid credentials.']);
    exit;
}

$_SESSION['user']         = $user['Name'];
$_SESSION['user_type_id'] = intval($user['User_type_id']);
$conn->close();

echo json_encode(['success' => true]);
exit;
?>
