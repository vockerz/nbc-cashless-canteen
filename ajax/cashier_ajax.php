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
		$total_qty += $row->xqty;
		$total_amount += $row->xamount;
	}
	return [
		'items' => $items,
		'total_qty' => (float)$total_qty,
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
	if (date('Y-m-d') >= date('Y-m-01') && date('Y-m-d') <= date('Y-m-15')) {
		$date_cond = " and dttm BETWEEN '" . date('Y-m-01') . " 00:00:00' and '" . date('Y-m-15') . " 23:59:59' ";
	} else {
		$date_cond = " and dttm BETWEEN '" . date('Y-m-16') . " 00:00:00' and '" . date('Y-m-t') . " 23:59:59' ";
	}
	$stmt = $conn->prepare("SELECT sum(amount) as amt FROM transactions WHERE isload = 0 and rfid_no = ?" . $date_cond);
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

		$stmtx = $conn->prepare(
			"UPDATE transactions SET active = 0 WHERE product_id in (1,2) and rfid_no IS NULL and active = 1 and (TRIM(user) = TRIM(?) OR TRIM(user) = TRIM(?))"
		);
		$stmtx->bind_param("ss", $user, $user_alt);
		$stmtx->execute();
		$_SESSION['total'] = 0;

		$stmt = $conn->prepare(
			"SELECT rfid_no, lname, fname, mname, balance, type, address, department, position
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

		$mealStmt = $conn->prepare(
			"SELECT product_id FROM transactions WHERE rfid_no = ? and active >= 1 and dttm >= (NOW() - INTERVAL 12 HOUR) and product_id in (1,2) GROUP BY product_id"
		);
		$mealStmt->bind_param("s", $rfid_no);
		$mealStmt->execute();
		$mealResult = $mealStmt->get_result();
		$has_breakfast = false;
		$has_lunch = false;
		while ($mealRow = $mealResult->fetch_object()) {
			if ((int)$mealRow->product_id === 1) {
				$has_breakfast = true;
			}
			if ((int)$mealRow->product_id === 2) {
				$has_lunch = true;
			}
		}

		$meal_label = '';
		$meal_param = '';
		$meal_alert = '';
		if (!$has_breakfast) {
			$meal_label = 'Breakfast';
			$meal_param = 'breakfast=1';
		} elseif (!$has_lunch) {
			$meal_label = 'Lunch';
			$meal_param = 'lunch=1';
		} else {
			$meal_alert = 'No more FREE MEAL, this transaction is for SALARY or LOAD DEDUCTION.';
		}

		$_SESSION['receipt']['rfid_no'] = $row->rfid_no;
		$_SESSION['type'] = $row->type;
		$_SESSION['user_bal'] = (float)$row->balance;
		$_SESSION['receipt']['name'] = $row->lname . ', ' . $row->fname . ', ' . $row->mname;
		if (empty($_SESSION['receipt']['orno'])) {
			$_SESSION['receipt']['orno'] = 'R-' . str_pad(rand(0, 999999), 10, '0', STR_PAD_LEFT);
		}
		$deduction = getCurrentDeduction($conn, $row->rfid_no);
		$_SESSION['deduction'] = $deduction;
		session_write_close();

		$photoPath = '../images/photos/' . $row->rfid_no . '.jpg';
		$has_photo = file_exists($photoPath);

		echo json_encode([
			'success' => true,
			'rfid_no' => $row->rfid_no,
			'balance' => number_format((float)$row->balance),
			'type' => $row->type,
			'name' => $row->lname . ', ' . $row->fname . ', ' . $row->mname,
			'address' => $row->address ?? '',
			'department' => $row->department ?? '',
			'position' => $row->position ?? '',
			'for_deduction' => str_replace('.00', '', number_format($deduction, 2)),
			'meal_label' => $meal_label,
			'meal_param' => $meal_param,
			'meal_alert' => $meal_alert,
			'has_photo' => $has_photo,
			'photo' => $has_photo ? 'images/photos/' . $row->rfid_no . '.jpg' : '',
			'user_bal' => (float)$row->balance,
		]);
		break;

	case 'rfid_deduction':
		$rfid_no = trim($_POST['rfid'] ?? '');
		if ($rfid_no === '') {
			echo json_encode(['success' => false]);
			exit;
		}
		$deduction = getCurrentDeduction($conn, $rfid_no);
		$_SESSION['deduction'] = $deduction;
		session_write_close();
		echo json_encode([
			'success' => true,
			'for_deduction' => str_replace('.00', '', number_format($deduction, 2)),
		]);
		break;

	case 'barcode':
		$barcodeRaw = trim($_POST['barcode'] ?? '');
		$barcodeRaw = preg_replace('/^\][A-Za-z][0-9]/', '', $barcodeRaw); // strip AIM symbology prefix
		$barcodeRaw = preg_replace('/[\x00-\x1F\x7F]/', '', $barcodeRaw); // strip ASCII control chars (GS, STX, etc.)
		$barcode = trim($barcodeRaw);
		$barcodeDigits = preg_replace('/\D+/', '', $barcodeRaw);
		$qty = max(1, (int)($_POST['qty'] ?? 1));
		$user_bal = (float)($_SESSION['user_bal'] ?? 0);
		$type = $_SESSION['type'] ?? '';
		$total = getActiveCartTotal($conn, $user, $user_alt);

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
		if ($row->stock <= 0) {
			echo json_encode(['success' => false, 'error' => 'No more stock']);
			exit;
		}

		$amount = $qty * (float)$row->price;
		if ($user_bal < ($amount + $total) && $type === 'Agency') {
			echo json_encode(['success' => false, 'error' => 'Not enough load.']);
			exit;
		}

		$stmtC = $conn->prepare("SELECT 1 FROM transactions WHERE product_id = ? and (TRIM(user) = TRIM(?) OR TRIM(user) = TRIM(?)) and active = 1 LIMIT 1");
		$stmtC->bind_param("sss", $row->product_id, $user, $user_alt);
		$stmtC->execute();
		$inCart = $stmtC->get_result()->num_rows > 0;

		if ($inCart) {
			$stmtU = $conn->prepare(
				"UPDATE transactions SET qty = (qty + ?), amount = (amount + ?) WHERE product_id = ? AND (TRIM(user) = TRIM(?) OR TRIM(user) = TRIM(?)) and active = 1"
			);
			$stmtU->bind_param("sssss", $qty, $amount, $row->product_id, $user, $user_alt);
			if (!$stmtU->execute()) {
				echo json_encode(['success' => false, 'error' => 'Unable to update cart item. ' . $stmtU->error]);
				exit;
			}
		} else {
			$stmtI = $conn->prepare(
				"INSERT INTO transactions (product_id, product_name, price, qty, amount, user, active, isload) VALUES (?, ?, ?, ?, ?, ?, '1', 0)"
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
			"DELETE FROM transactions WHERE (TRIM(user) = TRIM(?) OR TRIM(user) = TRIM(?)) and product_id = ? and rfid_no is null and member_name is null and receipt is null and dttm like '" . date('Y-m-d') . "%'"
		);
		$stmtD->bind_param("ssi", $user, $user_alt, $product_id);
		$stmtD->execute();
		$cart = getCart($conn, $user, $user_alt);
		session_write_close();
		echo json_encode(['success' => true] + $cart);
		break;

	case 'save':
		$print = (int)($_POST['print'] ?? 0);
		$meal = trim($_POST['meal'] ?? '');
		$isload = 0;
		$total = getActiveCartTotal($conn, $user, $user_alt);
		$user_bal = (float)($_SESSION['user_bal'] ?? 0);
		$type = $_SESSION['type'] ?? '';
		$rfid_no = $_SESSION['receipt']['rfid_no'] ?? '';
		$receipt_name = $_SESSION['receipt']['name'] ?? '';

		if ($rfid_no === '') {
			echo json_encode(['success' => false, 'error' => 'No employee selected.']);
			exit;
		}

		if ($total > 0) {
			$isload = 1;
		}
		if ($user_bal < $total && $type === 'Direct') {
			$isload = 0;
		} elseif ($user_bal >= $total && $type === 'Direct') {
			$isload = isset($_POST['isload']) ? (int)$_POST['isload'] : 0;
		}

		if ($user_bal < $total && $isload > 0) {
			echo json_encode(['success' => false, 'error' => 'Not enough load.']);
			exit;
		}

		$alert = 'No more free meal.';
		if ($total > 0) {
			$alert = '';
		}

		if ($meal === 'breakfast') {
			$sqlx = "SELECT * FROM `transactions` where rfid_no = '" . mysqli_real_escape_string($conn, $rfid_no) . "' and active >= 1 and dttm >= (NOW() - INTERVAL 12 HOUR) and product_id in (1)";
			$resultx = $conn->query($sqlx);
			if ($resultx->num_rows <= 0) {
				$stmtx = $conn->prepare("INSERT INTO transactions (product_id, product_name, price, qty, amount, user) VALUES ('1', 'BREAKFAST', '0', '1', '0', ?)");
				$stmtx->bind_param("s", $user);
				$stmtx->execute();
				$alert = '';
			}
		} elseif ($meal === 'lunch') {
			$sqlx = "SELECT * FROM `transactions` where rfid_no = '" . mysqli_real_escape_string($conn, $rfid_no) . "' and active >= 1 and dttm >= (NOW() - INTERVAL 12 HOUR) and product_id in (2)";
			$resultx = $conn->query($sqlx);
			if ($resultx->num_rows <= 0) {
				$stmtx = $conn->prepare("INSERT INTO transactions (product_id, product_name, price, qty, amount, user) VALUES ('2', 'LUNCH', '0', '1', '0', ?)");
				$stmtx->bind_param("s", $user);
				$stmtx->execute();
				$alert = '';
			}
		} else {
			$sqlx = "SELECT * FROM `transactions` where rfid_no = '" . mysqli_real_escape_string($conn, $rfid_no) . "' and active >= 1 and dttm >= (NOW() - INTERVAL 12 HOUR) and product_id in (1)";
			$resultx = $conn->query($sqlx);
			if ($resultx->num_rows <= 0) {
				$stmtx = $conn->prepare("INSERT INTO transactions (product_id, product_name, price, qty, amount, user) VALUES ('1', 'BREAKFAST', '0', '1', '0', ?)");
				$stmtx->bind_param("s", $user);
				$stmtx->execute();
				$alert = '';
			} else {
				$sqlx = "SELECT * FROM `transactions` where rfid_no = '" . mysqli_real_escape_string($conn, $rfid_no) . "' and active >= 1 and dttm >= (NOW() - INTERVAL 12 HOUR) and product_id in (2)";
				$resultx = $conn->query($sqlx);
				if ($resultx->num_rows <= 0) {
					$stmtx = $conn->prepare("INSERT INTO transactions (product_id, product_name, price, qty, amount, user) VALUES ('2', 'LUNCH', '0', '1', '0', ?)");
					$stmtx->bind_param("s", $user);
					$stmtx->execute();
					$alert = '';
				}
			}
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
		$txStmt->bind_param("isssss", $isload, $_SESSION['receipt']['orno'], $receipt_name, $rfid_no, $user, $user_alt);
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

		$redirect = $print === 1
			? 'cashier?86d178a053b97f10a65771b2c1ff9621=' . urlencode($rfid_no) . '&print=' . urlencode($_SESSION['receipt']['orno'])
			: 'cashier';

		unset($_SESSION['receipt']);
		session_write_close();

		echo json_encode([
			'success' => true,
			'redirect' => $redirect,
			'warning' => $alert,
		]);
		break;

	default:
		echo json_encode(['success' => false, 'error' => 'Unknown action']);
}
