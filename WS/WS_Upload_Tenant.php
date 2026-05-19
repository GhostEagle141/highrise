<?php
// =========================================
// Highrise – Tenant Data Upload Handler
// Parses Excel and inserts into tenants_list
// =========================================

require_once '../DAL/DAL.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

header('Content-Type: application/json');

// ---- Validate file was sent ----
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'No file received.']);
    exit;
}

$file     = $_FILES['file'];
$ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed  = ['xlsx', 'xls', 'csv'];

if (!in_array($ext, $allowed)) {
    echo json_encode(['success' => false, 'error' => 'Invalid file type. Please upload .xlsx, .xls, or .csv.']);
    exit;
}

// ---- Save raw file to disk ----
$uploadDir = '../uploads/tenant/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$filename  = date('Y-m-d_H-i-s') . '_tenant.' . $ext;
$savePath  = $uploadDir . $filename;

if (!move_uploaded_file($file['tmp_name'], $savePath)) {
    echo json_encode(['success' => false, 'error' => 'Failed to save file.']);
    exit;
}

// ---- Parse the spreadsheet ----
try {
    $spreadsheet = IOFactory::load($savePath);
    $sheet       = $spreadsheet->getActiveSheet();
    $rows        = $sheet->toArray(null, true, true, false);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Failed to read file: ' . $e->getMessage()]);
    exit;
}

// ---- Find header row ----
// Expected headers: Account no | Account Name | Auxiliary | Name
$headerRow  = null;
$dataStart  = 0;

foreach ($rows as $i => $row) {
    $normalized = array_map('strtolower', array_map('trim', $row));
    if (in_array('auxiliary', $normalized) && in_array('name', $normalized)) {
        $headerRow = $normalized;
        $dataStart = $i + 1;
        break;
    }
}

if ($headerRow === null) {
    echo json_encode(['success' => false, 'error' => 'Could not find expected headers (Auxiliary, Name, Account no) in the file.']);
    exit;
}

// Map column names to indexes
$colIndex = array_flip($headerRow);

$idxAccountNo  = $colIndex['account no']  ?? null;
$idxAuxiliary  = $colIndex['auxiliary']   ?? null;
$idxName       = $colIndex['name']        ?? null;

if ($idxAuxiliary === null || $idxName === null || $idxAccountNo === null) {
    echo json_encode(['success' => false, 'error' => 'Missing required columns: Account no, Auxiliary, Name.']);
    exit;
}

// ---- Begin transaction ----
$conn->begin_transaction();

// ---- Insert rows into tenants_list ----
$stmt = $conn->prepare("
    INSERT INTO tenants_list (id, name, related_account_id)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE
        name        = VALUES(name),
        related_account_id = VALUES(related_account_id)
");

if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Query prepare failed: ' . $conn->error]);
    exit;
}

$inserted = 0;
$skipped  = 0;
$errors   = [];

for ($i = $dataStart; $i < count($rows); $i++) {
    $row = $rows[$i];

    $id          = trim($row[$idxAuxiliary] ?? '');
    $name        = trim($row[$idxName]      ?? '');
    $related_account_id = trim($row[$idxAccountNo] ?? '');

    // Skip blank rows
    if ($id === '' || $name === '') {
        $skipped++;
        continue;
    }

    $stmt->bind_param('sss', $id, $name, $related_account_id);

    if ($stmt->execute()) {
        $inserted++;
    } else {
        $errors[] = "Row $i: " . $stmt->error;
    }
}

$stmt->close();

// ---- Commit or rollback ----
if (!empty($errors) && $inserted === 0) {
    $conn->rollback();
    $conn->close();
    echo json_encode([
        'success' => false,
        'error'   => 'All rows failed. No data was saved.',
        'errors'  => $errors
    ]);
    exit;
}

$conn->commit();
$conn->close();

// ---- Response ----
echo json_encode([
    'success'  => true,
    'inserted' => $inserted,
    'skipped'  => $skipped,
    'errors'   => $errors,
    'message'  => "$inserted tenant(s) saved successfully."
]);
exit;
?>
