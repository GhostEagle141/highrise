<?php
// =========================================
// Highrise – Register Handler
// =========================================

ini_set('display_errors', 0);
error_reporting(0);

require_once '../DAL/DAL.php';

header('Content-Type: application/json');

$name       = isset($_POST['name'])         ? trim($_POST['name'])         : '';
$password   = isset($_POST['password'])     ? trim($_POST['password'])     : '';
$userTypeId = isset($_POST['user_type_id']) ? intval($_POST['user_type_id']) : 1;
$statusId   = isset($_POST['status_id'])    ? intval($_POST['status_id'])    : 1;

if ($name === '' || $password === '') {
    echo json_encode(['success' => false, 'error' => 'Username and password are required.']);
    exit;
}

// Check if username already exists
$escaped = $conn->real_escape_string($name);
$check   = $conn->query("SELECT ID FROM users WHERE Name = '$escaped' LIMIT 1");
if ($check && $check->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'Username already exists.']);
    exit;
}

// Hash password with MD5
$hashed = md5($password);

$stmt = $conn->prepare("INSERT INTO users (Name, Password, User_type_id, status_id) VALUES (?, ?, ?, ?)");
if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param('ssii', $name, $hashed, $userTypeId, $statusId);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'User registered successfully.']);
} else {
    echo json_encode(['success' => false, 'error' => 'Registration failed: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
exit;
?>
