<?php
// =========================================
// Highrise – Fetch Cost Group Chart Data
// Returns individual costs + budget amounts
// for a given cost group and project
// =========================================

ini_set('display_errors', 0);
error_reporting(0);

require_once '../DAL/DAL.php';

header('Content-Type: application/json');

$projectId   = isset($_GET['project_id'])   && $_GET['project_id']   !== '' ? intval($_GET['project_id'])   : 1;
$costGroupId = isset($_GET['cost_group_id']) && $_GET['cost_group_id'] !== '' ? intval($_GET['cost_group_id']) : null;

if (!$costGroupId) {
    // Default to first available cost group for this project
    $res = $conn->query("
        SELECT DISTINCT cg.id, cg.name
        FROM budget_list bl
        JOIN costs_list cl       ON cl.id  = bl.cost_id
        JOIN cost_groups_list cg ON cg.id  = cl.cost_group_id
        WHERE bl.project_id = $projectId
        ORDER BY cg.name ASC
        LIMIT 1
    ");
    if ($res && $row = $res->fetch_assoc()) {
        $costGroupId = intval($row['id']);
    }
}

if (!$costGroupId) {
    echo json_encode(['success' => true, 'labels' => [], 'values' => [], 'groups' => []]);
    exit;
}

// ---- Get all cost groups for dropdown ----
$groupsRes = $conn->query("
    SELECT DISTINCT cg.id, cg.name
    FROM budget_list bl
    JOIN costs_list cl       ON cl.id = bl.cost_id
    JOIN cost_groups_list cg ON cg.id = cl.cost_group_id
    WHERE bl.project_id = $projectId
    ORDER BY cg.name ASC
");

$groups = [];
while ($g = $groupsRes->fetch_assoc()) {
    $groups[] = ['id' => $g['id'], 'name' => $g['name']];
}

// ---- Get costs for selected group ----
$query = "
    SELECT
        cl.name            AS cost_name,
        SUM(bl.budget_amount) AS total
    FROM budget_list bl
    JOIN costs_list cl ON cl.id = bl.cost_id
    WHERE bl.project_id  = $projectId
      AND cl.cost_group_id = $costGroupId
    GROUP BY cl.id, cl.name
    ORDER BY total DESC
";

$result = $conn->query($query);

if (!$result) {
    echo json_encode(['success' => false, 'error' => $conn->error]);
    exit;
}

$labels = [];
$values = [];

while ($row = $result->fetch_assoc()) {
    $labels[] = $row['cost_name'];
    $values[] = floatval($row['total']);
}

$conn->close();

echo json_encode([
    'success'        => true,
    'labels'         => $labels,
    'values'         => $values,
    'groups'         => $groups,
    'active_group'   => $costGroupId
]);
exit;
?>
