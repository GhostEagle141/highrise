<?php
// =========================================
// Highrise – Tenant Data Upload Handler
// Parses Excel and inserts into tenants_list
// =========================================

require_once '../DAL/DAL.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

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

if ($idxAuxiliary === null || $idxName === null) {
    echo json_encode(['success' => false, 'error' => 'Missing required columns: Auxiliary, Name.']);
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

// ---- Step 2: Parse "Suppliers Balances" sheet ----
$suppliersInserted = 0;
$suppliersSheet    = null;

foreach ($spreadsheet->getSheetNames() as $sName) {
    if (stripos($sName, 'suppliers') !== false) {
        $suppliersSheet = $spreadsheet->getSheetByName($sName);
        break;
    }
}

if ($suppliersSheet !== null) {
    $sHighestRow      = $suppliersSheet->getHighestRow();
    $sHighestCol      = $suppliersSheet->getHighestColumn();
    $sHighestColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($sHighestCol);

    // Find header row
    $sHeaderRow = null;
    $sDataStart = null;
    $sNameCol   = null;
    $sAuxCol    = null;

    for ($i = 1; $i <= min($sHighestRow, 20); $i++) {
        for ($col = 1; $col <= $sHighestColIndex; $col++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $val       = strtolower(trim($suppliersSheet->getCell($colLetter . $i)->getValue() ?? ''));
            if ($val === 'name')      $sNameCol = $colLetter;
            if ($val === 'auxiliary') $sAuxCol  = $colLetter;
        }
        if ($sNameCol && $sAuxCol) {
            $sDataStart = $i + 1;
            break;
        }
    }

    if ($sNameCol && $sAuxCol && $sDataStart) {

        // ---- Find all needed columns ----
        $sCurCol     = null;
        $sPaidCol    = null;
        $sDueCol     = null;
        $sAdvanceCol = null;
        $sDueDate    = null;

        for ($col = 1; $col <= $sHighestColIndex; $col++) {
            $colLetter  = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $headerVal  = strtolower(trim($suppliersSheet->getCell($colLetter . ($sDataStart - 1))->getValue() ?? ''));

            if ($headerVal === 'cur.' || $headerVal === 'cur') $sCurCol = $colLetter;

            if (strpos($headerVal, 'paid to suppliers') !== false) $sPaidCol = $colLetter;

            if (strpos($headerVal, 'dues to suppliers') !== false || strpos($headerVal, 'due to suppliers') !== false) {
                $sDueCol = $colLetter;
                // Parse date from column header e.g. "Dues to Suppliers Balance 31/03/2026"
                if (preg_match('/(\d{2})\/(\d{2})\/(\d{4})/', $headerVal, $dm)) {
                    $sDueDate = $dm[3] . '-' . $dm[2] . '-' . $dm[1];
                }
            }

            if (strpos($headerVal, 'advance') !== false) $sAdvanceCol = $colLetter;
        }

        // ---- Cache currencies ----
        $sCurrencyMap = [];
        $sCurRes = $conn->query("SELECT ID, Name FROM currencies_list");
        while ($cr = $sCurRes->fetch_assoc()) {
            $sCurrencyMap[strtolower(trim($cr['Name']))] = $cr['ID'];
        }

        // ---- Prepare statements ----
        $stmtSupplier = $conn->prepare("INSERT IGNORE INTO suppliers_list (Name, Supplier_ID) VALUES (?, ?)");
        $stmtDues     = $conn->prepare("
            INSERT INTO suppliers_dues (Supplier_ID, Currency_ID, Paid_Amount, Due_Amount, Advance_Amount, Date)
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                Paid_Amount     = VALUES(Paid_Amount),
                Due_Amount      = VALUES(Due_Amount),
                Advance_Amount  = VALUES(Advance_Amount)
        ");

        $suppliersDuesInserted = 0;

        if ($stmtSupplier && $stmtDues) {
            for ($row = $sDataStart; $row <= $sHighestRow; $row++) {
                $name       = trim($suppliersSheet->getCell($sNameCol . $row)->getValue() ?? '');
                $supplierId = trim($suppliersSheet->getCell($sAuxCol  . $row)->getValue() ?? '');

                if ($name === '' || $supplierId === '') continue;

                // Insert supplier
                $stmtSupplier->bind_param('ss', $name, $supplierId);
                if ($stmtSupplier->execute() && $stmtSupplier->affected_rows > 0) {
                    $suppliersInserted++;
                }

                // Insert dues
                $curRaw    = $sCurCol     ? strtolower(trim($suppliersSheet->getCell($sCurCol     . $row)->getValue() ?? '')) : '';
                $paidRaw   = $sPaidCol    ? trim($suppliersSheet->getCell($sPaidCol    . $row)->getCalculatedValue() ?? '') : 0;
                $dueRaw    = $sDueCol     ? trim($suppliersSheet->getCell($sDueCol     . $row)->getCalculatedValue() ?? '') : 0;
                $advRaw    = $sAdvanceCol ? trim($suppliersSheet->getCell($sAdvanceCol . $row)->getCalculatedValue() ?? '') : 0;

                $currencyId = isset($sCurrencyMap[$curRaw]) ? $sCurrencyMap[$curRaw] : null;
                if ($currencyId === null) continue;

                $paid    = is_numeric(str_replace(',', '', $paidRaw))  ? floatval(str_replace(',', '', $paidRaw))  : 0.00;
                $due     = is_numeric(str_replace(',', '', $dueRaw))   ? floatval(str_replace(',', '', $dueRaw))   : 0.00;
                $advance = is_numeric(str_replace(',', '', $advRaw))   ? floatval(str_replace(',', '', $advRaw))   : 0.00;
                $date    = $sDueDate ?? null;

                if ($date === null) continue;

                $stmtDues->bind_param('siddds', $supplierId, $currencyId, $paid, $due, $advance, $date);
                if ($stmtDues->execute() && $stmtDues->affected_rows > 0) {
                    $suppliersDuesInserted++;
                }
            }
            $stmtSupplier->close();
            $stmtDues->close();
        } else {
            $errors[] = 'Suppliers prepare failed: ' . $conn->error;
        }
    } else {
        $errors[] = 'Could not find "Name" or "Auxiliary" columns in Suppliers sheet.';
    }
} else {
    $errors[] = 'No sheet containing "Suppliers" found — skipped.';
}

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
    'success'                => true,
    'inserted'               => $inserted,
    'suppliers_inserted'     => $suppliersInserted,
    'supplier_dues_inserted' => $suppliersDuesInserted ?? 0,
    'skipped'                => $skipped,
    'errors'                 => $errors,
    'message'                => "$inserted tenant(s), $suppliersInserted supplier(s) and " . ($suppliersDuesInserted ?? 0) . " supplier due(s) saved successfully."
]);
exit;
?>
