<?php
// =========================================
// Highrise – Fetch Costs List for Mapping Dropdown
// =========================================

ini_set('display_errors', 0);
error_reporting(0);

require_once '../DAL/DAL.php';

header('Content-Type: application/json');

$result = $conn->query("
    SELECT
        cl.id,
        cl.name,
        cg.name AS group_name
    FROM costs_list cl
    JOIN cost_groups_list cg ON cg.id = cl.cost_group_id
    ORDER BY cg.name ASC, cl.name ASC
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
