<?php
// =========================================
// Highrise – Fetch Supplier Stats
// =========================================

ini_set('display_errors', 0);
error_reporting(0);

require_once '../DAL/DAL.php';

header('Content-Type: application/json');

$month      = isset($_GET['month'])       && $_GET['month']       !== '' ? intval($_GET['month'])       : null;
$year       = isset($_GET['year'])        && $_GET['year']        !== '' ? intval($_GET['year'])        : null;
$supplierId = isset($_GET['supplier_id']) && $_GET['supplier_id'] !== '' ? $_GET['supplier_id']         : null;

$where = [];
if ($month !== null) $where[] = "MONTH(sd.Date) = $month";
if ($year  !== null) $where[] = "YEAR(sd.Date)  = $year";
$whereSQL = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// ---- Total owed vs paid (all suppliers) ----
$totalsRes = $conn->query("
    SELECT
        COALESCE(SUM(sd.Paid_Amount), 0)  AS total_paid,
        COALESCE(SUM(sd.Due_Amount),  0)  AS total_due
    FROM suppliers_dues sd
    $whereSQL
");
$totals = $totalsRes->fetch_assoc();

// ---- Per-supplier breakdown ----
$suppWhere = $whereSQL ? $whereSQL . " AND sl.Supplier_ID = '" . $conn->real_escape_string($supplierId) . "'"
                       : "WHERE sl.Supplier_ID = '" . $conn->real_escape_string($supplierId) . "'";

$perSupplierData = ['paid' => 0, 'due' => 0];
if ($supplierId) {
    $sRes = $conn->query("
        SELECT
            COALESCE(SUM(sd.Paid_Amount), 0) AS paid,
            COALESCE(SUM(sd.Due_Amount),  0) AS due
        FROM suppliers_dues sd
        JOIN suppliers_list sl ON sl.Supplier_ID = sd.Supplier_ID
        $suppWhere
    ");
    if ($sRes) $perSupplierData = $sRes->fetch_assoc();
}

// ---- Suppliers list for dropdown (exclude zero balance) ----
$suppListRes = $conn->query("
    SELECT sl.Supplier_ID, sl.Name
    FROM suppliers_list sl
    JOIN suppliers_dues sd ON sd.Supplier_ID = sl.Supplier_ID
    GROUP BY sl.Supplier_ID, sl.Name
    HAVING SUM(sd.Paid_Amount) > 0 OR SUM(sd.Due_Amount) > 0
    ORDER BY sl.Name ASC
");
$suppliers   = [];
while ($r = $suppListRes->fetch_assoc()) {
    $suppliers[] = $r;
}

$conn->close();

echo json_encode([
    'success'      => true,
    'total_paid'   => floatval($totals['total_paid']),
    'total_due'    => floatval($totals['total_due']),
    'supplier'     => [
        'paid' => floatval($perSupplierData['paid']),
        'due'  => floatval($perSupplierData['due'])
    ],
    'suppliers'    => $suppliers
]);
exit;
?>
