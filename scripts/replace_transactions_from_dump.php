<?php
set_time_limit(0);

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'nbc';
$dumpPath = __DIR__ . '/../nbc.sql';
$tmpTable = 'transactions_latest_tmp';

function out($msg) {
    echo $msg . PHP_EOL;
}

if (!file_exists($dumpPath)) {
    out('ERROR: Dump file not found: ' . $dumpPath);
    exit(1);
}

$conn = @new mysqli($host, $user, $pass, $db);
if ($conn->connect_errno) {
    out('ERROR: DB connect failed: ' . $conn->connect_error);
    exit(1);
}
$conn->set_charset('utf8');

$createStmt = '';
$insertStmts = array();
$alterStmts = array();
$current = '';
$mode = '';

$fh = fopen($dumpPath, 'r');
if ($fh === false) {
    out('ERROR: Unable to open dump file.');
    exit(1);
}

while (($line = fgets($fh)) !== false) {
    $trim = ltrim($line);

    if ($mode === '') {
        if (stripos($trim, 'CREATE TABLE `transactions`') === 0) {
            $mode = 'create';
            $current = $line;
        } elseif (stripos($trim, 'INSERT INTO `transactions`') === 0) {
            $mode = 'insert';
            $current = $line;
        } elseif (stripos($trim, 'ALTER TABLE `transactions`') === 0) {
            $mode = 'alter';
            $current = $line;
        }
    } else {
        $current .= $line;
    }

    if ($mode !== '' && strpos($line, ';') !== false) {
        if ($mode === 'create') {
            $createStmt = $current;
        } elseif ($mode === 'insert') {
            $insertStmts[] = $current;
        } elseif ($mode === 'alter') {
            $alterStmts[] = $current;
        }
        $mode = '';
        $current = '';
    }
}
fclose($fh);

if ($createStmt === '') {
    out('ERROR: Could not find CREATE TABLE `transactions` in dump.');
    exit(1);
}
if (count($insertStmts) === 0) {
    out('ERROR: Could not find INSERT INTO `transactions` statements in dump.');
    exit(1);
}

$createStmt = str_replace('`transactions`', '`' . $tmpTable . '`', $createStmt);
foreach ($insertStmts as $k => $stmt) {
    $insertStmts[$k] = str_replace('`transactions`', '`' . $tmpTable . '`', $stmt);
}
foreach ($alterStmts as $k => $stmt) {
    $alterStmts[$k] = str_replace('`transactions`', '`' . $tmpTable . '`', $stmt);
}

$conn->query('SET FOREIGN_KEY_CHECKS=0');
$conn->query('DROP TABLE IF EXISTS `' . $tmpTable . '`');

if (!$conn->query($createStmt)) {
    out('ERROR: Failed creating temp table: ' . $conn->error);
    exit(1);
}
out('Created temp table `' . $tmpTable . '`.');

$insertCount = 0;
foreach ($insertStmts as $stmt) {
    if (!$conn->query($stmt)) {
        out('ERROR: Failed insert batch #' . ($insertCount + 1) . ': ' . $conn->error);
        exit(1);
    }
    $insertCount++;
    if ($insertCount % 25 === 0) {
        out('Inserted batches: ' . $insertCount);
    }
}
out('Finished inserts. Total batches: ' . $insertCount);

foreach ($alterStmts as $stmt) {
    if (!$conn->query($stmt)) {
        out('ERROR: Failed ALTER statement: ' . $conn->error);
        exit(1);
    }
}
if (count($alterStmts) > 0) {
    out('Applied ALTER statements: ' . count($alterStmts));
}

$resTmp = $conn->query('SELECT COUNT(*) AS c FROM `' . $tmpTable . '`');
$tmpCount = $resTmp ? (int)$resTmp->fetch_assoc()['c'] : 0;
if ($tmpCount <= 0) {
    out('ERROR: Temp table is empty; aborting swap.');
    exit(1);
}

$hasMain = false;
$resMain = $conn->query("SHOW TABLES LIKE 'transactions'");
if ($resMain && $resMain->num_rows > 0) {
    $hasMain = true;
}

$backupTable = 'transactions_backup_' . date('Ymd_His');
while (true) {
    $chk = $conn->query("SHOW TABLES LIKE '" . $conn->real_escape_string($backupTable) . "'");
    if (!$chk || $chk->num_rows === 0) {
        break;
    }
    $backupTable = 'transactions_backup_' . date('Ymd_His') . '_' . rand(100, 999);
}

if ($hasMain) {
    $swapSql = 'RENAME TABLE `transactions` TO `' . $backupTable . '`, `' . $tmpTable . '` TO `transactions`';
    if (!$conn->query($swapSql)) {
        out('ERROR: Failed swapping tables: ' . $conn->error);
        exit(1);
    }
    out('Swap complete. Backup table: `' . $backupTable . '`.');
} else {
    if (!$conn->query('RENAME TABLE `' . $tmpTable . '` TO `transactions`')) {
        out('ERROR: Failed renaming temp table to transactions: ' . $conn->error);
        exit(1);
    }
    out('Created `transactions` from temp table (no previous transactions table found).');
}

$resNew = $conn->query('SELECT COUNT(*) AS c FROM `transactions`');
$newCount = $resNew ? (int)$resNew->fetch_assoc()['c'] : 0;
out('Final rows in `transactions`: ' . $newCount);

$conn->query('SET FOREIGN_KEY_CHECKS=1');
out('Done.');
