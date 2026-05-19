<?php
// =========================================
// Highrise – Financial Data Upload Handler
// Syncs tenants + inserts into tenants_payments_list
// =========================================

require_once '../DAL/DAL.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

header('Content-Type: application/json');

// ---- Validate file ----
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'No file received.']);
    exit;
}

$file    = $_FILES['file'];
$ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed = ['xlsx', 'xls', 'csv'];

if (!in_array($ext, $allowed)) {
    echo json_encode(['success' => false, 'error' => 'Invalid file type. Please upload .xlsx, .xls, or .csv.']);
    exit;
}

// ---- Save raw file ----
$uploadDir = '../uploads/financial/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$filename = date('Y-m-d_H-i-s') . '_financial.' . $ext;
$savePath = $uploadDir . $filename;

if (!move_uploaded_file($file['tmp_name'], $savePath)) {
    echo json_encode(['success' => false, 'error' => 'Failed to save file.']);
    exit;
}

// ---- Parse spreadsheet ----
try {
    $spreadsheet = IOFactory::load($savePath);
    $sheet       = $spreadsheet->getActiveSheet();
    $rows        = $sheet->toArray(null, true, true, false);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Failed to read file: ' . $e->getMessage()]);
    exit;
}

// ---- Find header row ----
// Expected: Account no | Account Name | Auxiliary | Name | Cur. | Dues From Tenants Balances | Advances from Tenants Balances | Due Date
$headerRow = null;
$dataStart = 0;

foreach ($rows as $i => $row) {
    $normalized = array_map(function($v) { return strtolower(trim($v ?? '')); }, $row);
    if (in_array('auxiliary', $normalized) && in_array('name', $normalized)) {
        $headerRow = $normalized;
        $dataStart = $i + 1;
        break;
    }
}

if ($headerRow === null) {
    echo json_encode(['success' => false, 'error' => 'Could not find expected headers in the file.']);
    exit;
}

// ---- Map column indexes ----
$colIndex = array_flip($headerRow);

$idxAccountNo  = $colIndex['account no']                        ?? null;
$idxAuxiliary  = $colIndex['auxiliary']                         ?? null;
$idxName       = $colIndex['name']                              ?? null;
$idxCurrency   = $colIndex['cur.']                              ?? null;
$idxDues       = $colIndex['dues from tenants balances']        ?? null;
$idxAdvances   = $colIndex['advances from tenants balances']    ?? null;
$idxDueDate    = $colIndex['due date']                          ?? null;

if ($idxAuxiliary === null || $idxName === null || $idxAccountNo === null ||
    $idxCurrency  === null || $idxDues  === null || $idxAdvances  === null || $idxDueDate === null) {
    echo json_encode(['success' => false, 'error' => 'Missing one or more required columns.']);
    exit;
}

// ---- Cache currencies_list ----
$currencyMap = [];
$curResult = $conn->query("SELECT ID, Name FROM currencies_list");
while ($row = $curResult->fetch_assoc()) {
    $currencyMap[strtolower(trim($row['Name']))] = $row['ID'];
}

// ---- Prepare statements ----

// 1. Upsert into tenants_list (add missing tenants)
$stmtTenant = $conn->prepare("
    INSERT INTO tenants_list (id, name, related_account_id)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE
        name               = VALUES(name),
        related_account_id = VALUES(related_account_id)
");

// 2. Upsert into tenants_payments_list
$stmtPayment = $conn->prepare("
    INSERT INTO tenants_payments_list
        (tenant_id, currency_id, dues_total, advances_total, due_by_date)
    VALUES (?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
        dues_total     = VALUES(dues_total),
        advances_total = VALUES(advances_total)
");

if (!$stmtTenant || !$stmtPayment) {
    echo json_encode(['success' => false, 'error' => 'Statement prepare failed: ' . $conn->error]);
    exit;
}

// ---- Process rows ----
$tenantsUpserted  = 0;
$paymentsUpserted = 0;
$skipped          = 0;
$errors           = [];

for ($i = $dataStart; $i < count($rows); $i++) {
    $row = $rows[$i];

    $auxiliary         = trim($row[$idxAuxiliary] ?? '');
    $name              = trim($row[$idxName]      ?? '');
    $accountNo         = trim($row[$idxAccountNo] ?? '');
    $currencyRaw       = trim($row[$idxCurrency]  ?? '');
    $duesRaw           = trim($row[$idxDues]      ?? '');
    $advancesRaw       = trim($row[$idxAdvances]  ?? '');
    $dueDateRaw        = trim($row[$idxDueDate]   ?? '');

    // Skip blank rows
    if ($auxiliary === '' || $name === '') {
        $skipped++;
        continue;
    }

    // ---- Step 1: Upsert tenant ----
    $stmtTenant->bind_param('sss', $auxiliary, $name, $accountNo);
    if ($stmtTenant->execute()) {
        $tenantsUpserted++;
    } else {
        $errors[] = "Row $i tenant upsert: " . $stmtTenant->error;
        continue;
    }

    // ---- Step 2: Resolve currency ----
    $currencyId = $currencyMap[strtolower($currencyRaw)] ?? null;
    if ($currencyId === null) {
        $errors[] = "Row $i: Currency '$currencyRaw' not found in currencies_list. Row skipped.";
        $skipped++;
        continue;
    }

    // ---- Step 3: Clean numeric values ----
    $dues     = is_numeric(str_replace(',', '', $duesRaw))     ? floatval(str_replace(',', '', $duesRaw))     : 0.00;
    $advances = is_numeric(str_replace(',', '', $advancesRaw)) ? floatval(str_replace(',', '', $advancesRaw)) : 0.00;

    // ---- Step 4: Parse date string (expects DD/MM/YYYY) ----
    $dueDate = null;
    if ($dueDateRaw !== '') {
        $parsed = DateTime::createFromFormat('d/m/Y', $dueDateRaw);
        if ($parsed) {
            $dueDate = $parsed->format('Y-m-d');
        } else {
            // Try other common formats as fallback
            $parsed = date_create($dueDateRaw);
            $dueDate = $parsed ? date_format($parsed, 'Y-m-d') : null;
        }
    }

    if ($dueDate === null) {
        $errors[] = "Row $i: Could not parse due date '$dueDateRaw'. Row skipped.";
        $skipped++;
        continue;
    }

    // ---- Step 5: Upsert payment record ----
    $stmtPayment->bind_param('ssdds', $auxiliary, $currencyId, $dues, $advances, $dueDate);
    if ($stmtPayment->execute()) {
        $paymentsUpserted++;
    } else {
        $errors[] = "Row $i payment upsert: " . $stmtPayment->error;
    }
}

$stmtTenant->close();
$stmtPayment->close();
$conn->close();

// ---- Response ----
echo json_encode([
    'success'          => true,
    'tenants_upserted' => $tenantsUpserted,
    'payments_upserted'=> $paymentsUpserted,
    'skipped'          => $skipped,
    'errors'           => $errors,
    'message'          => "$paymentsUpserted payment record(s) saved, $tenantsUpserted tenant(s) synced."
]);
exit;
?>
