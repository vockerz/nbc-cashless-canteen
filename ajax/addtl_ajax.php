<?php
ini_set('session.gc_maxlifetime', 86400);
session_start();
require_once '../config/conf.php';

header('Content-Type: application/json');

if (!isset($_SESSION['insta_acc'])) {
	echo json_encode(['success' => false, 'error' => 'Not authenticated']);
	exit;
}

$user = trim((string)($_SESSION['nameinsta'] ?? ''));
$user_alt = trim((string)($_SESSION['usernameinsta'] ?? ''));
if ($user === '' && $user_alt !== '') {
	$user = $user_alt;
}
if ($user === '') {
	$user = trim((string)($_SESSION['insta_acc'] ?? ''));
}
if ($user_alt === '' || strcasecmp($user_alt, $user) === 0) {
	$user_alt = $user;
}
// Cache max_credit in session to skip DB query on every request
if (!isset($_SESSION['sys_max_credit'])) {
	$settingsRow = $conn->query("SELECT max_credit FROM settings LIMIT 1");
	$settingsObj = $settingsRow ? $settingsRow->fetch_object() : null;
	$_SESSION['sys_max_credit'] = $settingsObj ? (float)($settingsObj->max_credit ?? 1000) : 1000;
}
$maxCredit = $_SESSION['sys_max_credit'];

function getCart($conn, $user, $user_alt) {
	$stmt = $conn->prepare(
		"SELECT product_id, MAX(product_name) as product_name, MAX(price) as price, sum(qty) as xqty, sum(amount) as xamount
		 FROM transactions WHERE (TRIM(user) = TRIM(?) OR TRIM(user) = TRIM(?)) and active = 1 GROUP BY product_id"
	);
	$stmt->bind_param("ss", $user, $user_alt);
	$stmt->execute();
	$result = $stmt->get_result();
	$items = [];
	$total_qty = 0;
	$total_amount = 0;
	while ($row = $result->fetch_object()) {
		$items[] = [
			'product_id'   => (int)$row->product_id,
			'product_name' => $row->product_name,
			'price'        => (float)$row->price,
			'qty'          => (float)$row->xqty,
			'amount'       => (float)$row->xamount,
		];
		$total_qty    += $row->xqty;
		$total_amount += $row->xamount;
	}
	return [
		'items'        => $items,
		'total_qty'    => (float)$total_qty,
		'total_amount' => (float)$total_amount,
	];
}

function getActiveCartTotal($conn, $user, $user_alt) {
	$stmt = $conn->prepare("SELECT COALESCE(sum(amount), 0) as amt FROM transactions WHERE (TRIM(user) = TRIM(?) OR TRIM(user) = TRIM(?)) and active = 1");
	$stmt->bind_param("ss", $user, $user_alt);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_object();
	return (float)($row->amt ?? 0);
}

function getCurrentDeduction($conn, $rfid_no) {
	if (date("Y-m-d") <= date("Y-m-15")) {
		$date_cond = " and dttm BETWEEN '" . date("Y-m-01") . " 00:00:00' and '" . date("Y-m-15") . " 23:59:59'";
	} else {
		$date_cond = " and dttm BETWEEN '" . date("Y-m-16") . " 00:00:00' and '" . date("Y-m-t") . " 23:59:59'";
	}
	$stmt = $conn->prepare(
		"SELECT COALESCE(sum(amount),0) as amt FROM transactions
		 WHERE active = 2 and isload = 0 and rfid_no = ?" . $date_cond
	);
	$stmt->bind_param("s", $rfid_no);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_object();
	return (float)($row->amt ?? 0);
}

$action = trim($_POST['action'] ?? '');

switch ($action) {

	case 'cart':
		session_write_close();
		$cart = getCart($conn, $user, $user_alt);
		echo json_encode(['success' => true] + $cart);
		break;

	case 'rfid':
		$rfid_no = trim($_POST['rfid'] ?? '');
		if ($rfid_no === '') {
			echo json_encode(['success' => false, 'error' => 'No RFID provided']);
			exit;
		}

		// Clear any orphaned (no rfid assigned) cart rows for this user
		$stmtx = $conn->prepare(
			"UPDATE transactions SET active = 0 WHERE rfid_no IS NULL and active = 1 and (TRIM(user) = TRIM(?) OR TRIM(user) = TRIM(?))"
		);
		$stmtx->bind_param("ss", $user, $user_alt);
		$stmtx->execute();
		$_SESSION['total'] = 0;

		$stmt = $conn->prepare(
			"SELECT rfid_no, lname, fname, mname, balance, type, address,
			        department, position, green_mark, max_credit
			 FROM members WHERE rfid_no = ?"
		);
		$stmt->bind_param("s", $rfid_no);
		$stmt->execute();
		$result = $stmt->get_result();

		if ($result->num_rows === 0) {
			echo json_encode(['success' => false, 'error' => 'No record found']);
			exit;
		}

		$row = $result->fetch_object();

		$_SESSION['receipt']['rfid_no'] = $row->rfid_no;
		$_SESSION['type']               = $row->type;
		$_SESSION['user_bal']           = (float)$row->balance;
		$_SESSION['receipt']['name']    = $row->lname . ', ' . $row->fname . ', ' . $row->mname;

		$mc = ((float)$row->max_credit > 0) ? (float)$row->max_credit : $maxCredit;
		$_SESSION['max_credit'] = $mc;
		$deduction = getCurrentDeduction($conn, $row->rfid_no);
		$_SESSION['deduction']  = $deduction;

		if (empty($_SESSION['receipt']['orno'])) {
			$_SESSION['receipt']['orno'] = 'R-' . str_pad(rand(0, 999999), 10, '0', STR_PAD_LEFT);
		}

		session_write_close();

		$has_photo = file_exists("images/photos/" . $row->rfid_no . ".jpg");

		echo json_encode([
			'success'       => true,
			'rfid_no'       => $row->rfid_no,
			'balance'       => number_format((float)$row->balance),
			'type'          => $row->type,
			'name'          => $row->lname . ', ' . $row->fname . ', ' . $row->mname,
			'address'       => $row->address ?? '',
			'department'    => $row->department ?? '',
			'position'      => $row->position ?? '',
			'for_deduction' => str_replace('.00', '', number_format($deduction, 2)),
			'green_mark'    => (int)$row->green_mark,
			'has_photo'     => $has_photo,
			'photo'         => $has_photo ? 'images/photos/' . $row->rfid_no . '.jpg' : '',
			'user_bal'      => (float)$row->balance,
		]);
		break;

	case 'rfid_deduction':
		$rfid_no = trim($_POST['rfid'] ?? '');
		if ($rfid_no === '') { echo json_encode(['success' => false]); exit; }
		$deduction = getCurrentDeduction($conn, $rfid_no);
		$_SESSION['deduction'] = $deduction;
		session_write_close();

		echo json_encode([
			'success'       => true,
			'for_deduction' => str_replace('.00', '', number_format($deduction, 2)),
		]);
		break;

	case 'barcode':
		$barcodeRaw = trim($_POST['barcode'] ?? '');
		$barcodeRaw = preg_replace('/^\][A-Za-z][0-9]/', '', $barcodeRaw); // strip AIM symbology prefix
		$barcodeRaw = preg_replace('/[\x00-\x1F\x7F]/', '', $barcodeRaw); // strip ASCII control chars (GS, STX, etc.)
		$barcode   = trim($barcodeRaw);
		$barcodeDigits = preg_replace('/\D+/', '', $barcodeRaw);
		$qty       = max(1, (int)($_POST['qty'] ?? 1));
		$isload    = isset($_POST['isload']) ? (int)$_POST['isload'] : 0;

		$rfid_no   = $_SESSION['receipt']['rfid_no'] ?? '';
		$user_bal  = (float)($_SESSION['user_bal']   ?? 0);
		$type      = $_SESSION['type']               ?? '';
		$total     = getActiveCartTotal($conn, $user, $user_alt);
		$mc        = (float)($_SESSION['max_credit'] ?? $maxCredit);
		$deduction = (float)($_SESSION['deduction']  ?? 0);

		if ($barcode === '') {
			echo json_encode(['success' => false, 'error' => 'No barcode provided']);
			exit;
		}

		$stmt = $conn->prepare("SELECT product_id, name, price, stock FROM products WHERE TRIM(barcode) = TRIM(?)");
		$stmt->bind_param("s", $barcode);
		$stmt->execute();
		$result = $stmt->get_result();

		if ($result->num_rows === 0 && $barcodeDigits !== '' && $barcodeDigits !== $barcode) {
			$stmt = $conn->prepare("SELECT product_id, name, price, stock FROM products WHERE TRIM(barcode) = TRIM(?)");
			$stmt->bind_param("s", $barcodeDigits);
			$stmt->execute();
			$result = $stmt->get_result();
			if ($result->num_rows > 0) {
				$barcode = $barcodeDigits;
			}
		}

		// Fallback: try GTIN-14 / GS1 variants (strip leading '01' prefix or a single leading zero)
		if ($result->num_rows === 0 && strlen($barcodeDigits) >= 13) {
			$gs1Variants = [];
			if (strlen($barcodeDigits) >= 14 && substr($barcodeDigits, 0, 2) === '01') {
				$gtin = substr($barcodeDigits, 2);
				$gs1Variants[] = $gtin;
				if (strlen($gtin) >= 13 && $gtin[0] === '0') {
					$gs1Variants[] = substr($gtin, 1);
				}
			}
			if ($barcodeDigits[0] === '0') {
				$gs1Variants[] = substr($barcodeDigits, 1);
			}
			foreach ($gs1Variants as $variant) {
				if ($variant === '' || $variant === $barcode || $variant === $barcodeDigits) continue;
				$stmt = $conn->prepare("SELECT product_id, name, price, stock FROM products WHERE TRIM(barcode) = TRIM(?)");
				$stmt->bind_param("s", $variant);
				$stmt->execute();
				$result = $stmt->get_result();
				if ($result->num_rows > 0) {
					$barcode = $variant;
					break;
				}
			}
		}

		if ($result->num_rows === 0) {
			echo json_encode(['success' => false, 'error' => 'Product not found',
				'_debug' => ['raw' => bin2hex($_POST['barcode'] ?? ''), 'clean' => $barcode, 'digits' => $barcodeDigits]]);
			exit;
		}

		$row = $result->fetch_object();

		// One-per-employee restriction for special barcode
		if ($barcode === '48031837') {
			$qty    = 1;
			$stmtR  = $conn->prepare("SELECT active FROM transactions WHERE product_id = ? and rfid_no = ?");
			$stmtR->bind_param("ss", $row->product_id, $rfid_no);
			$stmtR->execute();
			$resultR = $stmtR->get_result();
			if ($resultR->num_rows > 0) {
				$rowR = $resultR->fetch_object();
				$msg  = ((int)$rowR->active === 2)
					? 'Employee already availed one ' . $row->name
					: 'Only one ' . $row->name . ' can be purchased.';
				echo json_encode(['success' => false, 'error' => $msg]);
				exit;
			}
		}

		if ($row->stock <= 0) {
			echo json_encode(['success' => false, 'error' => 'No more stock for ' . $row->name]);
			exit;
		}

		$amount = $qty * (float)$row->price;

		if ($user_bal < ($amount + $total) && $type === 'Agency') {
			echo json_encode(['success' => false, 'error' => 'Not enough load.']);
			exit;
		}

		if (!$isload && $mc < ($deduction + $amount + $total)) {
			echo json_encode(['success' => false, 'error' => 'You will exceed allowable amount: ' . number_format($mc, 2)]);
			exit;
		}

		// Check if already in cart
		$stmtC  = $conn->prepare("SELECT 1 FROM transactions WHERE product_id = ? and (TRIM(user) = TRIM(?) OR TRIM(user) = TRIM(?)) and active = 1 LIMIT 1");
		$stmtC->bind_param("sss", $row->product_id, $user, $user_alt);
		$stmtC->execute();
		$inCart = $stmtC->get_result()->num_rows > 0;

		if ($inCart) {
			if ($barcode === '48031837') {
				echo json_encode(['success' => false, 'error' => 'Only one ' . $row->name . ' can be purchased.']);
				exit;
			}
			$stmtU = $conn->prepare(
				"UPDATE transactions SET qty = (qty + ?), amount = (amount + ?)
				 WHERE product_id = ? AND (TRIM(user) = TRIM(?) OR TRIM(user) = TRIM(?)) and active = 1"
			);
			$stmtU->bind_param("sssss", $qty, $amount, $row->product_id, $user, $user_alt);
			if (!$stmtU->execute()) {
				echo json_encode(['success' => false, 'error' => 'Unable to update cart item. ' . $stmtU->error]);
				exit;
			}
		} else {
			$stmtI = $conn->prepare(
				"INSERT INTO transactions (product_id, product_name, price, qty, amount, user, active, isload)
				 VALUES (?, ?, ?, ?, ?, ?, '1', 0)"
			);
			$stmtI->bind_param("ssssss", $row->product_id, $row->name, $row->price, $qty, $amount, $user);
			if (!$stmtI->execute()) {
				echo json_encode(['success' => false, 'error' => 'Unable to insert cart item. ' . $stmtI->error]);
				exit;
			}
		}

		$cart = getCart($conn, $user, $user_alt);
		session_write_close();
		echo json_encode([
			'success' => true,
			'scanned_barcode' => $barcode,
			'affected_product_id' => (int)$row->product_id,
			'qty_delta' => (int)$qty,
		] + $cart);
		break;

	case 'delete':
		$product_id = (int)($_POST['product_id'] ?? 0);

		$stmtD = $conn->prepare(
			"DELETE FROM transactions
			 WHERE (TRIM(user) = TRIM(?) OR TRIM(user) = TRIM(?)) and product_id = ? and rfid_no is null
			   and member_name is null and receipt is null
			   and dttm like '" . date("Y-m-d") . "%'"
		);
		$stmtD->bind_param("ssi", $user, $user_alt, $product_id);
		$stmtD->execute();

		$cart = getCart($conn, $user, $user_alt);
		session_write_close();
		echo json_encode(['success' => true] + $cart);
		break;

	case 'save':
		$print = (int)($_POST['print'] ?? 0);
		$isload = isset($_POST['isload']) ? (int)$_POST['isload'] : 0;

		$rfid_no = $_SESSION['receipt']['rfid_no'] ?? '';
		$member_name = $_SESSION['receipt']['name'] ?? '';
		$total = getActiveCartTotal($conn, $user, $user_alt);
		$user_bal = (float)($_SESSION['user_bal'] ?? 0);
		$type = $_SESSION['type'] ?? '';
		$mc = (float)($_SESSION['max_credit'] ?? $maxCredit);
		$deduction = (float)($_SESSION['deduction'] ?? 0);

		if ($rfid_no === '') {
			echo json_encode(['success' => false, 'error' => 'No employee selected.']);
			exit;
		}

		if ($total > 0 && $isload === 0) {
			$isload = 1;
		}
		if ($user_bal < $total && $type === 'Direct') {
			$isload = 0;
		}

		if ($user_bal < $total && $isload > 0) {
			echo json_encode(['success' => false, 'error' => 'Not enough load.']);
			exit;
		}

		if (!$isload && $mc < ($deduction + $total)) {
			echo json_encode(['success' => false, 'error' => 'You will exceed to allowable amount: ' . number_format($mc, 2)]);
			exit;
		}

		if (empty($_SESSION['receipt']['orno'])) {
			$_SESSION['receipt']['orno'] = 'R-' . str_pad(rand(0, 999999), 10, '0', STR_PAD_LEFT);
		}

		$cartRowsStmt = $conn->prepare("SELECT product_id, SUM(qty) as qty FROM transactions WHERE (TRIM(user) = TRIM(?) OR TRIM(user) = TRIM(?)) and active = 1 GROUP BY product_id");
		$cartRowsStmt->bind_param("ss", $user, $user_alt);
		$cartRowsStmt->execute();
		$cartRowsResult = $cartRowsStmt->get_result();
		$productQty = [];
		while ($cartRow = $cartRowsResult->fetch_object()) {
			$productQty[] = ['product_id' => (int)$cartRow->product_id, 'qty' => (float)$cartRow->qty];
		}

		$txStmt = $conn->prepare("UPDATE transactions SET isload = ?, receipt = ?, member_name = ?, rfid_no = ?, active = 2 WHERE (TRIM(user) = TRIM(?) OR TRIM(user) = TRIM(?)) and active = 1");
		$txStmt->bind_param("isssss", $isload, $_SESSION['receipt']['orno'], $member_name, $rfid_no, $user, $user_alt);
		if (!$txStmt->execute()) {
			echo json_encode(['success' => false, 'error' => 'Unable to save transaction.']);
			exit;
		}

		$stockStmt = $conn->prepare("UPDATE products SET stock = (stock - ?) WHERE product_id = ?");
		foreach ($productQty as $pq) {
			$qtyToDeduct = $pq['qty'];
			$pid = $pq['product_id'];
			$stockStmt->bind_param("di", $qtyToDeduct, $pid);
			$stockStmt->execute();
		}

		if ($isload === 1) {
			$stmtx = $conn->prepare("UPDATE members SET balance = (balance - ?) WHERE rfid_no = ?");
			$stmtx->bind_param("ss", $total, $rfid_no);
			$stmtx->execute();
		}

		$itemsStmt = $conn->prepare(
			"SELECT product_id, MAX(product_name) as product_name, MAX(price) as price, SUM(qty) as xqty, SUM(amount) as xamount, MAX(member_name) as member_name, MAX(dttm) as dttm, MAX(receipt) as receipt
			 FROM transactions
			 WHERE (TRIM(user) = TRIM(?) OR TRIM(user) = TRIM(?)) and active = 2 and receipt = ?
			 GROUP BY product_id"
		);
		$itemsStmt->bind_param("sss", $user, $user_alt, $_SESSION['receipt']['orno']);
		$itemsStmt->execute();
		$itemsResult = $itemsStmt->get_result();
		$items = [];
		while ($item = $itemsResult->fetch_object()) {
			$items[] = $item;
		}

		$text = 'Receipt: ' . $_SESSION['receipt']['orno'] . "\n";
		$text .= 'RFID No.: ' . $rfid_no . "\n";
		$text .= 'Total: ' . $total . "\n";
		$text .= 'Transaction:' . print_r($items, true);
		generateTextFile($text, $_SESSION['receipt']['orno']);

		$redirect = $print === 1
			? 'addtl_only?86d178a053b97f10a65771b2c1ff9621=' . urlencode($rfid_no) . '&print=' . urlencode($_SESSION['receipt']['orno'])
			: 'addtl_only';

		unset($_SESSION['receipt']);
		session_write_close();

		echo json_encode([
			'success' => true,
			'redirect' => $redirect,
			'message' => ($print === 1 ? 'Save and print' : 'Save succesful'),
		]);
		break;

	default:
		echo json_encode(['success' => false, 'error' => 'Unknown action']);
}
