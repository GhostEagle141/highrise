<?php
// =========================================
// Highrise – Dashboard Stats
// Total budget, expenses, remaining, tenants
// =========================================

ini_set('display_errors', 0);
error_reporting(0);

require_once '../DAL/DAL.php';

header('Content-Type: application/json');

$projectId = isset($_GET['project_id']) && $_GET['project_id'] !== '' ? intval($_GET['project_id']) : 1;
$month     = isset($_GET['month'])      && $_GET['month']      !== '' ? intval($_GET['month'])      : null;
$year      = isset($_GET['year'])       && $_GET['year']       !== '' ? intval($_GET['year'])       : null;

// ---- Total Budget (full year, filtered by project) ----
$budgetRes = $conn->query("
    SELECT COALESCE(SUM(budget_amount), 0) AS total
    FROM budget_list
    WHERE project_id = $projectId
");
$totalBudget = floatval($budgetRes->fetch_assoc()['total']);

// ---- Total Expenses (converted to USD, filtered by month/year) ----
$expWhere = [];
if ($month !== null) $expWhere[] = "MONTH(re.Trans_Date) = $month";
if ($year  !== null) $expWhere[] = "YEAR(re.Trans_Date)  = $year";
$expWhereSQL = count($expWhere) ? 'WHERE ' . implode(' AND ', $expWhere) : '';

$expRes = $conn->query("
    SELECT COALESCE(SUM(
        CASE
            WHEN re.Currency_ID = 1 THEN re.Amount
            ELSE re.Amount / NULLIF(cur.Rate, 0)
        END
    ), 0) AS total
    FROM real_expanses re
    JOIN currencies_list cur ON cur.ID = re.Currency_ID
    $expWhereSQL
");
$totalExpenses = floatval($expRes->fetch_assoc()['total']);

// ---- Total Tenants ----
$tenantRes    = $conn->query("SELECT COUNT(*) AS total FROM tenants_list");
$totalTenants = intval($tenantRes->fetch_assoc()['total']);

// ---- Paid / Unpaid Tenants ----
$today = date('Y-m-d');
$paidWhere = $expWhereSQL ? $expWhereSQL . " AND p.due_by_date < '$today' AND p.dues_total <> 0"
                           : "WHERE p.due_by_date < '$today' AND p.dues_total <> 0";

$unpaidRes  = $conn->query("SELECT COUNT(DISTINCT p.tenant_id) AS total FROM tenants_payments_list p $paidWhere");
$unpaidCount = intval($unpaidRes->fetch_assoc()['total']);
$paidCount   = $totalTenants - $unpaidCount;

// ---- Available months/years for dropdowns ----
$rangeRes = $conn->query("
    SELECT DISTINCT MONTH(Trans_Date) AS month, YEAR(Trans_Date) AS year
    FROM real_expanses
    WHERE Trans_Date IS NOT NULL
    ORDER BY year DESC, month DESC
");
$available = [];
while ($r = $rangeRes->fetch_assoc()) {
    $available[] = $r;
}

$conn->close();

echo json_encode([
    'success'        => true,
    'total_budget'   => $totalBudget,
    'total_expenses' => round($totalExpenses, 2),
    'remaining'      => round($totalBudget - $totalExpenses, 2),
    'total_tenants'  => $totalTenants,
    'paid_tenants'   => $paidCount,
    'unpaid_tenants' => $unpaidCount,
    'available'      => $available
]);
exit;
?>
