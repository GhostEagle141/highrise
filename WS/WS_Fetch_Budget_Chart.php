<?php
// =========================================
// Highrise – Fetch Budget Chart Data
// Groups budget totals by cost group
// =========================================

ini_set('display_errors', 0);
error_reporting(0);

require_once '../DAL/DAL.php';

header('Content-Type: application/json');

$projectId = isset($_GET['project_id']) && $_GET['project_id'] !== '' ? intval($_GET['project_id']) : 1;

$query = "
    SELECT
        cg.id              AS group_id,
        cg.name            AS group_name,
        SUM(bl.budget_amount) AS total
    FROM budget_list bl
    JOIN costs_list cl       ON cl.id  = bl.cost_id
    JOIN cost_groups_list cg ON cg.id  = cl.cost_group_id
    WHERE bl.project_id = $projectId
    GROUP BY cg.id, cg.name
    ORDER BY total DESC
";

$result = $conn->query($query);

if (!$result) {
    echo json_encode(['success' => false, 'error' => $conn->error]);
    exit;
}

$labels    = [];
$values    = [];
$group_ids = [];
$groups    = [];

while ($row = $result->fetch_assoc()) {
    $labels[]    = $row['group_name'];
    $values[]    = floatval($row['total']);
    $group_ids[] = intval($row['group_id']);
    $groups[]    = ['id' => intval($row['group_id']), 'name' => $row['group_name']];
}

$conn->close();

echo json_encode([
    'success'   => true,
    'labels'    => $labels,
    'values'    => $values,
    'group_ids' => $group_ids,
    'groups'    => $groups
]);
exit;
?>
