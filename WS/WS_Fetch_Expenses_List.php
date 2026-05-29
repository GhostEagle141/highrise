<?php
// =========================================
// Highrise – Fetch Expenses List for Mapping
// =========================================

ini_set('display_errors', 0);
error_reporting(0);

require_once '../DAL/DAL.php';

header('Content-Type: application/json');

$result = $conn->query("
    SELECT
        ID                AS id,
        name,
        related_Account_no
    FROM real_expanses_list
    ORDER BY name ASC
");

if (!$result) {
    echo json_encode(['success' => false, 'error' => $conn->error]);
    exit;
}

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

$conn->close();
echo json_encode(['success' => true, 'data' => $data]);
exit;
?>
