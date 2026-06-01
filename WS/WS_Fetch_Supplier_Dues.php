<?php
// =========================================
// Highrise – Fetch Supplier Dues
// =========================================

ini_set('display_errors', 0);
error_reporting(0);

require_once '../DAL/DAL.php';

header('Content-Type: application/json');

$month = isset($_GET['month']) && $_GET['month'] !== '' ? intval($_GET['month']) : null;
$year  = isset($_GET['year'])  && $_GET['year']  !== '' ? intval($_GET['year'])  : null;

// Default to latest date if no filter
if ($month === null && $year === null) {
    $latestRes = $conn->query("SELECT MAX(Date) AS latest FROM suppliers_dues");
    $latestRow = $latestRes->fetch_assoc();
    if ($latestRow['latest']) {
        $d     = new DateTime($latestRow['latest']);
        $month = intval($d->format('m'));
        $year  = intval($d->format('Y'));
    }
}

$where = [];
if ($month !== null) $where[] = "MONTH(sd.Date) = $month";
if ($year  !== null) $where[] = "YEAR(sd.Date)  = $year";
$whereSQL = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$query = "
    SELECT
        sl.Supplier_ID  AS supplier_id,
        sl.Name         AS supplier_name,
        sd.Paid_Amount  AS paid_amount,
        sd.Due_Amount   AS due_amount,
        sd.Advance_Amount AS advance_amount,
        sd.Date         AS due_date,
        cur.Name        AS currency
    FROM suppliers_dues sd
    JOIN suppliers_list sl  ON sl.Supplier_ID = sd.Supplier_ID
    JOIN currencies_list cur ON cur.ID        = sd.Currency_ID
    $whereSQL
    ORDER BY sd.Date ASC, sl.Name ASC
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

// Available months/years for dropdowns
$rangeRes = $conn->query("
    SELECT DISTINCT MONTH(Date) AS month, YEAR(Date) AS year
    FROM suppliers_dues
    ORDER BY year DESC, month DESC
");
$available = [];
while ($r = $rangeRes->fetch_assoc()) {
    $available[] = $r;
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
