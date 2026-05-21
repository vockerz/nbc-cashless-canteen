<?php
include 'config/conf.php';
$r = $conn->query('DESCRIBE products');
while ($row = $r->fetch_assoc()) {
    echo implode('|', $row) . PHP_EOL;
}

echo "\n--- Sample barcodes (stock > 0) ---\n";
$r2 = $conn->query("SELECT product_id, name, barcode, stock FROM products WHERE stock > 0 LIMIT 30");
while ($row = $r2->fetch_assoc()) {
    echo implode(' | ', $row) . PHP_EOL;
}

if (!empty($_SERVER['argv'][1])) {
    $bc = trim($_SERVER['argv'][1]);
    echo "\n--- Lookup: $bc ---\n";
    $stmt = $conn->prepare("SELECT * FROM products WHERE barcode = ?");
    $stmt->bind_param("s", $bc);
    $stmt->execute();
    $res = $stmt->get_result();
    echo "Exact match rows: " . $res->num_rows . "\n";
    while ($row = $res->fetch_assoc()) { echo implode(' | ', $row) . PHP_EOL; }

    $d = preg_replace('/\D+/', '', $bc);
    echo "\n--- Digits-only lookup: $d ---\n";
    $stmt2 = $conn->prepare("SELECT * FROM products WHERE barcode = ?");
    $stmt2->bind_param("s", $d);
    $stmt2->execute();
    $res2 = $stmt2->get_result();
    echo "Rows: " . $res2->num_rows . "\n";
    while ($row = $res2->fetch_assoc()) { echo implode(' | ', $row) . PHP_EOL; }
}
