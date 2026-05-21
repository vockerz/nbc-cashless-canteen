<?php

$return_url = 'void_transaction';
if (isset($_GET['return']) && $_GET['return'] != '') {
	$candidate_return = urldecode($_GET['return']);
	if (strpos($candidate_return, 'void_transaction') !== false) {
		$return_url = $candidate_return;
	}
}
$return_url_js = json_encode($return_url);

if(isset($_GET['id'])){
	if(empty($_GET['id']) && empty($_GET['rfid'])){
		echo '<script type = "text/javascript">window.location.replace(' . $return_url_js . ');</script>';
	}else{
		$id_hash = $_GET['id'];
		$rfid_hash = $_GET['rfid'];

		$receipt = isset($_GET['receipt']) ? $_GET['receipt'] : '';
		$rfid_no = isset($_GET['card']) ? $_GET['card'] : '';
		$has_fast_params = ($receipt !== '' && $rfid_no !== '');
		if ($has_fast_params) {
			if (md5($receipt . 'void') !== $id_hash || md5($rfid_no . 'void') !== $rfid_hash) {
				echo '<script type = "text/javascript">window.location.replace(' . $return_url_js . ');</script>';
				exit;
			}
		} else {
			$stmt = $conn->prepare("SELECT receipt, rfid_no FROM transactions WHERE md5(concat(receipt, 'void')) = ? AND md5(concat(rfid_no, 'void')) = ? AND active = 2 LIMIT 1");
			$stmt->bind_param("ss", $id_hash, $rfid_hash);
			if($stmt->execute() === TRUE){
				$result = $stmt->get_result();
				$row = $result->fetch_assoc();
				if (!$row) {
					echo '<script type = "text/javascript">window.location.replace(' . $return_url_js . ');</script>';
					exit;
				}

				$receipt = $row['receipt'];
				$rfid_no = $row['rfid_no'];
			} else {
				echo '<script type = "text/javascript">window.location.replace(' . $return_url_js . ');</script>';
				exit;
			}
		}

			$conn->begin_transaction();
			$ok = true;

			$stmt_update_trans = $conn->prepare("UPDATE transactions SET active = 3 WHERE receipt = ? AND rfid_no = ? AND active = 2");
			$stmt_update_trans->bind_param("ss", $receipt, $rfid_no);
			$ok = $ok && $stmt_update_trans->execute() === TRUE;
			if ($ok && $stmt_update_trans->affected_rows > 0) {
				$stmt_update_stock = $conn->prepare("UPDATE products p JOIN (SELECT product_id, SUM(qty) AS qty_total FROM transactions WHERE receipt = ? AND rfid_no = ? AND active = 3 GROUP BY product_id) t ON t.product_id = p.product_id SET p.stock = p.stock + t.qty_total");
				$stmt_update_stock->bind_param("ss", $receipt, $rfid_no);
				$ok = $ok && $stmt_update_stock->execute() === TRUE;

				$stmt_update_balance = $conn->prepare("UPDATE members m JOIN (SELECT rfid_no, SUM(amount) AS amount_total FROM transactions WHERE receipt = ? AND rfid_no = ? AND active = 3 AND isload = 1 GROUP BY rfid_no) t ON t.rfid_no = m.rfid_no SET m.balance = m.balance + t.amount_total");
				$stmt_update_balance->bind_param("ss", $receipt, $rfid_no);
				$ok = $ok && $stmt_update_balance->execute() === TRUE;
			} else {
				$ok = false;
			}

			if ($ok) {
				$conn->commit();
			} else {
				$conn->rollback();
			}

		echo '<script type = "text/javascript">window.location.replace(' . $return_url_js . ');</script>';
	}
}