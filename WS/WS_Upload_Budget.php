<?php
// =========================================
// Highrise – Budget Upload Handler
// Handles merged cells in both item and cost code columns
// =========================================

// Suppress xDebug/warnings from polluting JSON output
ini_set('display_errors', 0);
error_reporting(0);

require_once '../DAL/DAL.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
header('Content-Type: application/json');

// ---- Validate file ----
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'No file received.']);
    exit;
}

$file    = $_FILES['file'];
$ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($ext, ['xlsx', 'xls'])) {
    echo json_encode(['success' => false, 'error' => 'Please upload .xlsx or .xls.']);
    exit;
}

// ---- Save file ----
$uploadDir = '../uploads/budget/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
$savePath = $uploadDir . date('Y-m-d_H-i-s') . '_budget.' . $ext;

if (!move_uploaded_file($file['tmp_name'], $savePath)) {
    echo json_encode(['success' => false, 'error' => 'Failed to save file.']);
    exit;
}

// ---- Validate project ID ----
$projectId = isset($_POST['project_id']) && $_POST['project_id'] !== '' ? intval($_POST['project_id']) : null;

if (!$projectId) {
    echo json_encode(['success' => false, 'error' => 'No project selected.']);
    exit;
}

// ---- Load spreadsheet ----
try {
    $reader = IOFactory::createReaderForFile($savePath);
    $reader->setReadDataOnly(true);
    $spreadsheet = $reader->load($savePath);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Failed to read file: ' . $e->getMessage()]);
    exit;
}

// ---- Find "Cost codes" sheet ----
$sheet = null;
foreach ($spreadsheet->getSheetNames() as $name) {
    if (stripos($name, 'Cost codes') !== false) {
        $sheet = $spreadsheet->getSheetByName($name);
        break;
    }
}

if (!$sheet) {
    echo json_encode(['success' => false, 'error' => 'Sheet containing "Cost codes" not found.']);
    exit;
}

$highestRow      = $sheet->getHighestRow();
$highestCol      = $sheet->getHighestColumn();
$highestColIndex = Coordinate::columnIndexFromString($highestCol);

// ---- Find header row ----
$dataStart = null;
for ($i = 1; $i <= $highestRow; $i++) {
    $val = strtolower(trim($sheet->getCell('A' . $i)->getValue() ?? ''));
    if (strpos($val, 'item') !== false || strpos($val, 'description') !== false) {
        $dataStart = $i + 1;
        $headerRow = $i;
        break;
    }
}

if (!$dataStart) {
    echo json_encode(['success' => false, 'error' => 'Header row not found.']);
    exit;
}

// ---- Find cost code and budget columns ----
$costCodeCol = null;
$budgetCol   = null;
$budgetYear  = null;

for ($col = 1; $col <= $highestColIndex; $col++) {
    $colLetter = Coordinate::stringFromColumnIndex($col);
    $val       = strtolower(trim($sheet->getCell($colLetter . $headerRow)->getValue() ?? ''));
    if ($val === '') continue;

    if (strpos($val, 'cost') !== false || strpos($val, 'code') !== false) {
        $costCodeCol = $colLetter;
    }
    if (strpos($val, 'proposed') !== false || strpos($val, 'budget') !== false) {
        $budgetCol = $colLetter;
        if (preg_match('/(\d{4})/', $val, $yearMatch)) {
            $budgetYear = intval($yearMatch[1]);
        }
    }
}

$itemCol = 'A';

// ---- Build merged cell map ----
// For each row, store the actual value of the master cell it belongs to
// This resolves both item and cost code merged cells correctly

function buildMergeMap($sheet, $col, $dataStart, $highestRow) {
    $map = []; // row => value

    // First pass: get raw values row by row
    for ($r = $dataStart; $r <= $highestRow; $r++) {
        $map[$r] = trim($sheet->getCell($col . $r)->getValue() ?? '');
    }

    // Second pass: resolve merged ranges
    foreach ($sheet->getMergeCells() as $mergeRange) {
        $cells = Coordinate::extractAllCellReferencesInRange($mergeRange);
        if (empty($cells)) continue;

        // Get master cell (first in range)
        preg_match('/([A-Z]+)(\d+)/', $cells[0], $m);
        if ($m[1] !== $col) continue; // only care about our column

        $masterRow = intval($m[2]);
        $masterVal = trim($sheet->getCell($col . $masterRow)->getValue() ?? '');

        // Apply master value to all rows in the merge
        foreach ($cells as $cellRef) {
            preg_match('/([A-Z]+)(\d+)/', $cellRef, $mc);
            if ($mc[1] !== $col) continue;
            $r = intval($mc[2]);
            if ($r >= $dataStart) {
                $map[$r] = $masterVal;
            }
        }
    }

    return $map;
}

$itemMap     = buildMergeMap($sheet, $itemCol, $dataStart, $highestRow);
$costCodeMap = $costCodeCol ? buildMergeMap($sheet, $costCodeCol, $dataStart, $highestRow) : [];

// ---- Helper: is row highlighted ----
function isHighlighted($sheet, $col, $row) {
    $fill    = $sheet->getStyle($col . $row)->getFill();
    $bgColor = strtoupper(trim($fill->getStartColor()->getRGB()));
    return (
        $fill->getFillType() === Fill::FILL_SOLID &&
        $bgColor !== '' &&
        $bgColor !== 'FFFFFF' &&
        $bgColor !== 'FF000000' &&
        $bgColor !== '000000'
    );
}

// ---- Helper: should skip ----
function shouldSkip($val) {
    if ($val === '' || preg_replace('/\s+/u', '', $val) === '') return true;
    if (stripos($val, 'total') !== false) return true;
    if (stripos($val, 'item') !== false && stripos($val, 'description') !== false) return true;
    if (strpos($val, '%') !== false) return true;
    return false;
}

// ---- Begin transaction ----
$conn->begin_transaction();

$stmtGroup    = $conn->prepare("INSERT IGNORE INTO cost_groups_list (name) VALUES (?)");
$stmtCost     = $conn->prepare("INSERT IGNORE INTO costs_list (name, cost_group_id) VALUES (?, ?)");
$stmtCostCode = $conn->prepare("INSERT IGNORE INTO costs_related_cost_codes (cost_id, related_cost_code) VALUES (?, ?)");
$stmtBudget   = $conn->prepare("
    INSERT INTO budget_list (cost_id, project_id, budget_amount, currency_id, year)
    VALUES (?, ?, ?, 1, ?)
    ON DUPLICATE KEY UPDATE
        budget_amount = IF(budget_amount = VALUES(budget_amount), budget_amount, VALUES(budget_amount))
");

if (!$stmtGroup || !$stmtCost || !$stmtCostCode || !$stmtBudget) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$groupsInserted    = 0;
$costsInserted     = 0;
$costCodesInserted = 0;
$budgetsInserted   = 0;
$skipped           = 0;
$errors            = [];

// ---- Pass 1: Insert cost groups ----
$seenGroups = [];
for ($row = $dataStart; $row <= $highestRow; $row++) {
    $itemVal = $itemMap[$row] ?? '';
    if (shouldSkip($itemVal)) continue;
    if (!isHighlighted($sheet, $itemCol, $row)) continue;
    if (isset($seenGroups[$itemVal])) continue; // don't re-insert same group

    $stmtGroup->bind_param('s', $itemVal);
    if ($stmtGroup->execute()) {
        if ($stmtGroup->affected_rows > 0) $groupsInserted++;
        $seenGroups[$itemVal] = true;
    } else {
        $errors[] = "Group '$itemVal': " . $stmtGroup->error;
    }
}

// ---- Build group name → ID map ----
$groupMap  = [];
$groupsRes = $conn->query("SELECT id, name FROM cost_groups_list");
while ($g = $groupsRes->fetch_assoc()) {
    $groupMap[strtolower(trim($g['name']))] = $g['id'];
}

// ---- Pass 2: Insert costs ----
// Track current group as we scan down
// Use merge maps so each row knows its true item and cost code

$currentGroupId = null;
$seenCosts      = []; // track "itemName|costCode" to avoid duplicates within this upload

for ($row = $dataStart; $row <= $highestRow; $row++) {
    $itemVal     = $itemMap[$row]     ?? '';
    $costCodeVal = $costCodeMap[$row] ?? '';

    if (shouldSkip($itemVal)) {
        $skipped++;
        continue;
    }

    // Highlighted = group header, update context
    if (isHighlighted($sheet, $itemCol, $row)) {
        $currentGroupId = $groupMap[strtolower($itemVal)] ?? null;
        continue;
    }

    // Skip if no group context
    if ($currentGroupId === null) {
        $skipped++;
        continue;
    }

    // Deduplicate costs by name within this upload
    $costKey = strtolower($itemVal) . '|' . $currentGroupId;
    if (!isset($seenCosts[$costKey])) {
        $seenCosts[$costKey] = null; // will store cost ID after insert

        $stmtCost->bind_param('si', $itemVal, $currentGroupId);
        if ($stmtCost->execute()) {
            if ($stmtCost->affected_rows > 0) {
                $costsInserted++;
                $seenCosts[$costKey] = $conn->insert_id;
            } else {
                // Already exists — fetch its ID
                $escName    = $conn->real_escape_string($itemVal);
                $existingRes = $conn->query("SELECT id FROM costs_list WHERE name = '$escName' AND cost_group_id = $currentGroupId LIMIT 1");
                if ($existingRes && $row2 = $existingRes->fetch_assoc()) {
                    $seenCosts[$costKey] = $row2['id'];
                }
            }
        } else {
            $errors[] = "Cost '$itemVal': " . $stmtCost->error;
        }
    }

    // Insert cost code if present
    $costId = $seenCosts[$costKey] ?? null;
    if ($costId !== null && $costCodeVal !== '') {
        $stmtCostCode->bind_param('is', $costId, $costCodeVal);
        if ($stmtCostCode->execute()) {
            if ($stmtCostCode->affected_rows > 0) $costCodesInserted++;
            else $skipped++;
        } else {
            $errors[] = "Cost code '$costCodeVal': " . $stmtCostCode->error;
        }
    }

    // Insert budget — only once per cost (not per cost code)
    $budgetKey = 'budget_' . $costKey;
    if ($costId !== null && $budgetCol !== null && $budgetYear !== null && !isset($seenCosts[$budgetKey])) {
        $seenCosts[$budgetKey] = true;
        $rawBudget  = trim($sheet->getCell($budgetCol . $row)->getCalculatedValue() ?? '');
        $budgetAmt  = is_numeric(str_replace(',', '', $rawBudget)) ? floatval(str_replace(',', '', $rawBudget)) : 0.00;
        $stmtBudget->bind_param('iidi', $costId, $projectId, $budgetAmt, $budgetYear);
        if ($stmtBudget->execute()) {
            $budgetsInserted++;
        } else {
            $errors[] = "Budget for cost '$itemVal': " . $stmtBudget->error;
        }
    }
}

$stmtGroup->close();
$stmtCost->close();
$stmtCostCode->close();
$stmtBudget->close();

// ---- Step 11: Parse "Real Expenses" sheet ----
$realExpensesInserted = 0;
$realSheet = null;

foreach ($spreadsheet->getSheetNames() as $sName) {
    if (stripos($sName, 'Real Expenses') !== false) {
        $realSheet = $spreadsheet->getSheetByName($sName);
        break;
    }
}

// ---- Step 11: Parse "GL" sheet and insert names into real_expanses_list ----
$realExpensesInserted = 0;
$glSheet = null;

foreach ($spreadsheet->getSheetNames() as $sName) {
    if (stripos($sName, 'GL') !== false) {
        $glSheet = $spreadsheet->getSheetByName($sName);
        break;
    }
}

if ($glSheet !== null) {
    $glHighestRow      = $glSheet->getHighestRow();
    $glHighestCol      = $glSheet->getHighestColumn();
    $glHighestColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($glHighestCol);

    // Find all needed columns
    $nameCol    = null;
    $accNoCol   = null;
    $debitCol   = null;
    $curCol     = null;
    $dateCol    = null;
    $glDataStart = null;

    for ($i = 1; $i <= min($glHighestRow, 20); $i++) {
        for ($col = 1; $col <= $glHighestColIndex; $col++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $val       = strtolower(trim($glSheet->getCell($colLetter . $i)->getValue() ?? ''));
            if ($val === 'name')                                              $nameCol  = $colLetter;
            if ($val === 'acc.no.' || $val === 'acc.no' || $val === 'acc no') $accNoCol = $colLetter;
            if ($val === 'debit')                                             $debitCol = $colLetter;
            if ($val === 'cur')                                               $curCol   = $colLetter;
            if ($val === 'date')                                              $dateCol  = $colLetter;
        }
        if ($nameCol !== null) {
            $glDataStart = $i + 1;
            break;
        }
    }

    // ---- Cache currencies ----
    $glCurrencyMap = [];
    $glCurRes = $conn->query("SELECT ID, Name FROM currencies_list");
    while ($r = $glCurRes->fetch_assoc()) {
        $glCurrencyMap[strtolower(trim($r['Name']))] = $r['ID'];
    }

    if ($nameCol !== null && $glDataStart !== null) {

        // Statement 1: real_expanses_list
        $stmtReal = $conn->prepare("INSERT IGNORE INTO real_expanses_list (name, related_Account_no) VALUES (?, ?)");

        // Statement 2: real_expanses
        $stmtExp  = $conn->prepare("
            INSERT IGNORE INTO real_expanses (Related_Account_No, Amount, Currency_ID, Trans_Date)
            VALUES (?, ?, ?, ?)
        ");

        $realExpensesInserted2 = 0;

        if ($stmtReal && $stmtExp) {
            for ($row = $glDataStart; $row <= $glHighestRow; $row++) {
                $nameVal  = trim($glSheet->getCell($nameCol . $row)->getValue() ?? '');
                $accNoVal = $accNoCol ? trim($glSheet->getCell($accNoCol  . $row)->getValue() ?? '') : null;
                $debitVal = $debitCol ? trim($glSheet->getCell($debitCol  . $row)->getCalculatedValue() ?? '') : '';
                $curVal   = $curCol   ? strtolower(trim($glSheet->getCell($curCol . $row)->getValue() ?? '')) : '';
                $dateRaw  = $dateCol  ? $glSheet->getCell($dateCol . $row)->getValue() : null;

                if ($nameVal === '' || preg_replace('/\s+/u', '', $nameVal) === '') continue;

                $accNoVal = $accNoVal !== '' ? $accNoVal : null;

                // ---- Insert into real_expanses_list ----
                $stmtReal->bind_param('ss', $nameVal, $accNoVal);
                if ($stmtReal->execute() && $stmtReal->affected_rows > 0) {
                    $realExpensesInserted++;
                }

                // ---- Insert into real_expanses ----
                // Clean amount
                $amount = is_numeric(str_replace(',', '', $debitVal))
                    ? floatval(str_replace(',', '', $debitVal))
                    : null;

                // Skip rows with no debit amount
                if ($amount === null || $amount == 0) continue;

                // Resolve currency
                $currencyId = isset($glCurrencyMap[$curVal]) ? $glCurrencyMap[$curVal] : null;
                if ($currencyId === null) continue;

                // Parse date — Excel stores dates as numeric serials
                if ($dateRaw !== null && $dateRaw !== '') {
                    if (is_numeric($dateRaw)) {
                        $transDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateRaw)->format('Y-m-d');
                    } else {
                        $parsed    = date_create(trim($dateRaw));
                        $transDate = $parsed ? date_format($parsed, 'Y-m-d') : null;
                    }
                } else {
                    $transDate = null;
                }

                if ($transDate === null) continue;

                $stmtExp->bind_param('sdis', $accNoVal, $amount, $currencyId, $transDate);
                if ($stmtExp->execute() && $stmtExp->affected_rows > 0) {
                    $realExpensesInserted2++;
                }
            }
            $stmtReal->close();
            $stmtExp->close();
        } else {
            $errors[] = 'GL prepare failed: ' . $conn->error;
        }
    } else {
        $errors[] = 'Could not find "Name" column in GL sheet.';
    }
} else {
    $errors[] = 'No sheet containing "GL" found — skipped.';
}

// ---- Commit or rollback ----
if ($groupsInserted === 0 && $costsInserted === 0 && !empty($errors)) {
    $conn->rollback();
    $conn->close();
    echo json_encode(['success' => false, 'error' => 'Nothing saved.', 'errors' => $errors]);
    exit;
}

$conn->commit();
$conn->close();

echo json_encode([
    'success'               => true,
    'groups_inserted'       => $groupsInserted,
    'costs_inserted'        => $costsInserted,
    'cost_codes_inserted'   => $costCodesInserted,
    'budgets_inserted'      => $budgetsInserted,
    'real_expenses_inserted'=> $realExpensesInserted,
    'real_expanses_inserted'=> $realExpensesInserted2 ?? 0,
    'skipped'               => $skipped,
    'errors'                => $errors,
    'message'               => "$groupsInserted group(s), $costsInserted cost(s), $costCodesInserted cost code(s), $budgetsInserted budget(s), $realExpensesInserted expense name(s) and " . ($realExpensesInserted2 ?? 0) . " expense transaction(s) saved successfully."
]);
exit;
?>
