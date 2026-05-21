<?php
$c = new mysqli('localhost', 'root', '', 'nbc');
if ($c->connect_errno) {
    echo 'connect error: ' . $c->connect_error . PHP_EOL;
    exit(1);
}

$backup = 'transactions_backup_20260513_063628';
$q = "SELECT (SELECT COUNT(*) FROM transactions) AS new_count, (SELECT COUNT(*) FROM `" . $backup . "`) AS backup_count";
$r = $c->query($q);
if (!$r) {
    echo 'query error: ' . $c->error . PHP_EOL;
    exit(1);
}
$o = $r->fetch_assoc();
echo 'new_count=' . $o['new_count'] . PHP_EOL;
echo 'backup_count=' . $o['backup_count'] . PHP_EOL;
