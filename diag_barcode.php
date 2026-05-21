<?php
require_once 'config/conf.php';
header('Content-Type: text/plain');

// 1. Products table structure
echo "=== PRODUCTS TABLE STRUCTURE ===\n";
$r = $conn->query('DESCRIBE products');
while ($row = $r->fetch_assoc()) {
    echo implode(' | ', $row) . "\n";
}

// 2. Sample barcodes stored
echo "\n=== SAMPLE PRODUCT BARCODES (first 20 with stock > 0) ===\n";
$r = $conn->query("SELECT product_id, name, barcode, stock FROM products WHERE stock > 0 LIMIT 20");
while ($row = $r->fetch_assoc()) {
    echo implode(' | ', $row) . "\n";
}

// 3. Try a specific barcode lookup if passed
if (!empty($_GET['barcode'])) {
    $b = $conn->real_escape_string($_GET['barcode']);
    echo "\n=== LOOKUP: {$b} ===\n";
    $r = $conn->query("SELECT product_id, name, barcode, stock FROM products WHERE barcode = '$b'");
    echo "Rows: " . $r->num_rows . "\n";
    while ($row = $r->fetch_assoc()) {
        echo implode(' | ', $row) . "\n";
    }
    // Also try digits-only
    $digits = preg_replace('/\D+/', '', $b);
    echo "\n=== LOOKUP digits-only: {$digits} ===\n";
    $r2 = $conn->query("SELECT product_id, name, barcode, stock FROM products WHERE barcode = '$digits'");
    echo "Rows: " . $r2->num_rows . "\n";
    while ($row = $r2->fetch_assoc()) {
        echo implode(' | ', $row) . "\n";
    }
    // Fuzzy: LIKE match
    echo "\n=== LIKE %{$b}% ===\n";
    $r3 = $conn->query("SELECT product_id, name, barcode, stock FROM products WHERE barcode LIKE '%$b%'");
    echo "Rows: " . $r3->num_rows . "\n";
    while ($row = $r3->fetch_assoc()) {
        echo implode(' | ', $row) . "\n";
    }
}
