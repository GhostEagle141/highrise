<?php
// =========================================
// Highrise – Budget vs Real Expenses
// Expenses converted to USD via Rate
// =========================================

ini_set('display_errors', 0);
error_reporting(0);

require_once '../DAL/DAL.php';

header('Content-Type: application/json');

$projectId = isset($_GET['project_id']) && $_GET['project_id'] !== '' ? intval($_GET['project_id']) : 1;

$query = "
    SELECT
        cg.id                                       AS group_id,
        cg.name                                     AS label,
        SUM(bl.budget_amount)                       AS budget_total,
        COALESCE((
            SELECT SUM(
                CASE
                    WHEN re.Currency_ID = 1 THEN re.Amount
                    ELSE re.Amount / NULLIF(cur.Rate, 0)
                END
            )
            FROM costs_related_cost_codes crc
            JOIN real_expanses_list rel ON rel.related_Account_no = crc.related_cost_code
            JOIN real_expanses re       ON re.Related_Account_No  = rel.related_Account_no
            JOIN currencies_list cur    ON cur.ID                 = re.Currency_ID
            WHERE crc.cost_id IN (
                SELECT id FROM costs_list WHERE cost_group_id = cg.id
            )
        ), 0) AS expense_total
    FROM cost_groups_list cg
    JOIN costs_list cl  ON cl.cost_group_id = cg.id
    JOIN budget_list bl ON bl.cost_id       = cl.id
                       AND bl.project_id    = $projectId
    GROUP BY cg.id, cg.name
    ORDER BY budget_total DESC
";

$result = $conn->query($query);

if (!$result) {
    echo json_encode(['success' => false, 'error' => $conn->error]);
    exit;
}

$labels        = [];
$budgetTotals  = [];
$expenseTotals = [];

while ($row = $result->fetch_assoc()) {
    $labels[]        = $row['label'];
    $budgetTotals[]  = floatval($row['budget_total']);
    $expenseTotals[] = round(floatval($row['expense_total']), 2);
}

$conn->close();

echo json_encode([
    'success'  => true,
    'labels'   => $labels,
    'budget'   => $budgetTotals,
    'expenses' => $expenseTotals
]);
exit;
?>
