<?php
require dirname(__DIR__) . '/config/conf.php';

$patterns = ['%backup%', '%premerge%'];
foreach ($patterns as $pat) {
    $r = $conn->query("SHOW TABLES LIKE '$pat'");
    while ($row = $r->fetch_row()) {
        $t = $row[0];
        $cnt = $conn->query("SELECT COUNT(*) AS c FROM `$t`")->fetch_object()->c;
        echo "$t: $cnt rows\n";
    }
}
$main = $conn->query("SELECT COUNT(*) AS c FROM transactions")->fetch_object()->c;
echo "transactions (main): $main rows\n";
