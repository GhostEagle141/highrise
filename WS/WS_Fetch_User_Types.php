<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAL/DAL.php';
header('Content-Type: application/json');

$result = $conn->query("SELECT ID, Name FROM users_types_list ORDER BY Name ASC");
if (!$result) {
    echo json_encode(['success' => false, 'error' => $conn->error]);
    exit;
}
$data = [];
while ($row = $result->fetch_assoc()) { $data[] = $row; }
$conn->close();
echo json_encode(['success' => true, 'data' => $data]);
exit;
?>
