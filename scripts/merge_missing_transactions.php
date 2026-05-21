<?php
set_time_limit(0);

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'nbc';
$backupTable = 'transactions_backup_20260513_063628';

function out($msg) {
    echo $msg . PHP_EOL;
}

$conn = @new mysqli($host, $user, $pass, $db);
if ($conn->connect_errno) {
    out('ERROR: DB connect failed: ' . $conn->connect_error);
    exit(1);
}
$conn->set_charset('utf8');

$preMergeBackup = 'transactions_premerge_' . date('Ymd_His');
while (true) {
    $chk = $conn->query("SHOW TABLES LIKE '" . $conn->real_escape_string($preMergeBackup) . "'");
    if (!$chk || $chk->num_rows === 0) {
        break;
    }
    $preMergeBackup = 'transactions_premerge_' . date('Ymd_His') . '_' . rand(100, 999);
}

if (!$conn->query("CREATE TABLE `" . $preMergeBackup . "` AS SELECT * FROM `transactions`")) {
    out('ERROR: Failed creating pre-merge backup: ' . $conn->error);
    exit(1);
}
out('Created pre-merge backup: ' . $preMergeBackup);

$missingRes = $conn->query(
    "SELECT COUNT(*) AS c
     FROM `" . $backupTable . "` b
     LEFT JOIN `transactions` t ON t.transaction_id = b.transaction_id
     WHERE t.transaction_id IS NULL"
);
if (!$missingRes) {
    out('ERROR: Failed counting missing rows: ' . $conn->error);
    exit(1);
}
$missingCount = (int)$missingRes->fetch_assoc()['c'];
out('Missing rows detected: ' . $missingCount);

if ($missingCount > 0) {
    $sql =
        "INSERT INTO `transactions`
         (`transaction_id`,`rfid_no`,`member_name`,`product_id`,`product_name`,`price`,`qty`,`amount`,`user`,`dttm`,`active`,`receipt`,`isload`,`isreserve`)
         SELECT
           b.`transaction_id`,b.`rfid_no`,b.`member_name`,b.`product_id`,b.`product_name`,b.`price`,b.`qty`,b.`amount`,b.`user`,b.`dttm`,b.`active`,b.`receipt`,b.`isload`,b.`isreserve`
         FROM `" . $backupTable . "` b
         LEFT JOIN `transactions` t ON t.transaction_id = b.transaction_id
         WHERE t.transaction_id IS NULL";

    if (!$conn->query($sql)) {
        out('ERROR: Failed inserting missing rows: ' . $conn->error);
        exit(1);
    }
    out('Inserted missing rows: ' . $conn->affected_rows);
} else {
    out('No missing rows to merge.');
}

$newCountRes = $conn->query("SELECT COUNT(*) AS c FROM `transactions`");
$backupCountRes = $conn->query("SELECT COUNT(*) AS c FROM `" . $backupTable . "`");
$finalCount = $newCountRes ? (int)$newCountRes->fetch_assoc()['c'] : -1;
$backupCount = $backupCountRes ? (int)$backupCountRes->fetch_assoc()['c'] : -1;

out('Final transactions count: ' . $finalCount);
out('Backup transactions count: ' . $backupCount);

if ($finalCount === $backupCount) {
    out('Merge complete: counts now match.');
} else {
    out('Merge finished but counts still differ.');
}
