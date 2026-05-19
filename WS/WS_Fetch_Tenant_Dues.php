<?php
// =========================================
// Highrise – Fetch Tenant Dues
// Supports filtering by month/year
// Default: latest due_by_date available
// =========================================

require_once '../DAL/DAL.php';

header('Content-Type: application/json');

$month = isset($_GET['month']) && $_GET['month'] !== '' ? intval($_GET['month']) : null;
$year  = isset($_GET['year'])  && $_GET['year']  !== '' ? intval($_GET['year'])  : null;

// If no filter provided, default to the latest due_by_date in the table
if ($month === null && $year === null) {
    $latestRes = $conn->query("SELECT MAX(due_by_date) AS latest FROM tenants_payments_list");
    $latestRow = $latestRes->fetch_assoc();
    if ($latestRow['latest']) {
        $latestDate = new DateTime($latestRow['latest']);
        $month = intval($latestDate->format('m'));
        $year  = intval($latestDate->format('Y'));
    }
}

// Build WHERE clause
$where = [];
if ($month !== null) $where[] = "MONTH(p.due_by_date) = $month";
if ($year  !== null) $where[] = "YEAR(p.due_by_date)  = $year";
$whereSQL = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$query = "
    SELECT
        t.ID                AS tenant_id,
        t.Name              AS tenant_name,
        p.dues_total        AS due_amount,
        p.advances_total    AS advance_amount,
        p.due_by_date       AS due_date
    FROM tenants_payments_list p
    JOIN tenants_list t ON t.ID = p.tenant_id
    $whereSQL
    ORDER BY p.due_by_date ASC, t.Name ASC
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

// Also return available months/years for the dropdowns
$rangeRes = $conn->query("
    SELECT DISTINCT MONTH(due_by_date) AS month, YEAR(due_by_date) AS year
    FROM tenants_payments_list
    ORDER BY year DESC, month DESC
");

$available = [];
while ($row = $rangeRes->fetch_assoc()) {
    $available[] = $row;
}

$conn->close();

echo json_encode([
    'success'   => true,
    'data'      => $data,
    'available' => $available,
    'active'    => ['month' => $month, 'year' => $year]
]);
exit;
?>
