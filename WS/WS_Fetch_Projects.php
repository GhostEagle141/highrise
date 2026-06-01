<?php
// =========================================
// Highrise – Fetch Projects
// =========================================

require_once '../DAL/DAL.php';

header('Content-Type: application/json');

$result = $conn->query("SELECT ID, Name FROM projects_list ORDER BY Name ASC");

if (!$result) {
    echo json_encode(['success' => false, 'error' => $conn->error]);
    exit;
}

$projects = [];
while ($row = $result->fetch_assoc()) {
    $projects[] = $row;
}

$conn->close();
echo json_encode(['success' => true, 'data' => $projects]);
exit;
?>
