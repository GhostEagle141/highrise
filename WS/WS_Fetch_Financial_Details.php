<?php
// =========================================
// Highrise – Fetch Financial Details
// Expenses filtered by cost group
// =========================================

ini_set('display_errors', 0);
error_reporting(0);

require_once '../DAL/DAL.php';

header('Content-Type: application/json');

$costGroupId = isset($_GET['cost_group_id']) && $_GET['cost_group_id'] !== '' ? intval($_GET['cost_group_id']) : null;
$month       = isset($_GET['month'])         && $_GET['month']         !== '' ? intval($_GET['month'])         : null;
$year        = isset($_GET['year'])          && $_GET['year']          !== '' ? intval($_GET['year'])          : null;

// ---- Build WHERE ----
$where = [];

if ($costGroupId) {
    $where[] = "crc.cost_id IN (
        SELECT id FROM costs_list WHERE cost_group_id = $costGroupId
    )";
}
if ($month !== null) $where[] = "MONTH(re.Trans_Date) = $month";
if ($year  !== null) $where[] = "YEAR(re.Trans_Date)  = $year";

$whereSQL = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// ---- Fetch expenses ----
$query = "
    SELECT
        re.Related_Account_No   AS account_no,
        rel.name                AS expense_name,
        re.Amount               AS amount,
        cur.Name                AS currency,
        re.Currency_ID          AS currency_id,
        cur.Rate                AS rate,
        CASE
            WHEN re.Currency_ID = 1 THEN re.Amount
            ELSE re.Amount / NULLIF(cur.Rate, 0)
        END                     AS amount_usd,
        re.Trans_Date           AS trans_date
    FROM real_expanses re
    JOIN currencies_list cur    ON cur.ID                 = re.Currency_ID
    LEFT JOIN real_expanses_list rel ON rel.related_Account_no = re.Related_Account_No
    LEFT JOIN costs_related_cost_codes crc ON crc.related_cost_code = re.Related_Account_No
    $whereSQL
    GROUP BY re.Related_Account_No, re.Amount, re.Currency_ID, re.Trans_Date
    ORDER BY re.Trans_Date DESC
    LIMIT 500
";

$result = $conn->query($query);

if (!$result) {
    echo json_encode(['success' => false, 'error' => $conn->error]);
    exit;
}

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// ---- Fetch cost groups for dropdown ----
$groupsRes = $conn->query("SELECT id, name FROM cost_groups_list ORDER BY name ASC");
$groups = [];
while ($g = $groupsRes->fetch_assoc()) {
    $groups[] = $g;
}

$conn->close();

echo json_encode([
    'success' => true,
    'data'    => $data,
    'groups'  => $groups
]);
exit;
?>
