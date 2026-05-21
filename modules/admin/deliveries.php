<?php
if ($access->level < 3) {
	alert("/nbc", "No access");
}
?>
<?php
if (isset($_GET['clear'])) {
	$stmt = $conn->prepare("UPDATE delivery SET state = 2 where user = ? and state = 0");
	$stmt->bind_param("s", $_SESSION['nameinsta']);
	if ($stmt->execute() === TRUE) {
	}
}
?>
<div class="breadcrumbs">
	<div class="col-sm-4">
		<div class="page-header float-left">
			<div class="page-title">
				<h1><?php echo $title; ?></h1>
			</div>
		</div>
	</div>
	<div class="col-sm-8">
		<div class="page-header float-right">
			<div class="page-title">
				<ol class="breadcrumb text-right">
					<li><a href="./">Dashboard</a></li>
					<li class="active"><?php echo $title; ?></li>
				</ol>
			</div>
		</div>
	</div>
</div>
<div class="content mt-3">
	<div class="animated fadeIn">
		<div class="row">
			<div class="col-lg-12">
				<?php if (!isset($_GET['view_delivery'])) {
					if (!isset($_GET['delivery'])) { ?>
						<div class="card">
							<div class="card-header">
								<strong class="card-title"><?php echo $title; ?> List</strong>
								<a class="btn btn-primary btn-sm pull-right" href="admin/deliveries?delivery&clear"><span class="fa fa-plus"></span></a>
							</div>
							<div class="card-body">
								<table class="table">
									<thead class="thead-dark" align="center">
										<tr>
											<th scope="col">#</th>
											<th scope="col">Receipt</th>
											<th scope="col">Delivery Date</th>
											<th scope="col">Item Of Items</th>
											<th scope="col">Action</th>
										</tr>
									</thead>
									<tbody align="center">
										<?php
										$counter = "SELECT COUNT(DISTINCT receipt) as total FROM delivery";
										$counter2 = $conn->query($counter)->fetch_assoc();
										$perpage = 10;
										$totalPages = max(1, (int)ceil($counter2['total'] / $perpage));
										if (!isset($_GET['view'])) {
											$_GET['view'] = 1;
										} else {
											$_GET['view'] = (int)$_GET['view'];
										}
										if ($_GET['view'] < 1) {
											$_GET['view'] = 1;
										} else if ($_GET['view'] > $totalPages) {
											$_GET['view'] = $totalPages;
										}
										$startArticle = ($_GET['view'] - 1) * $perpage;
										$prod = "SELECT *, count(product_id) as item_no FROM delivery GROUP BY receipt ORDER BY delivery_date DESC LIMIT " . $startArticle . ', ' . $perpage;
										$prod = $conn->query($prod);
										if ($prod->num_rows > 0) {
											$num = 0 + $startArticle;
											while ($row = $prod->fetch_object()) {
												$num += 1;
												echo '<tr>';
												echo '<th scope="row">' . $num . '</th>';
												echo '<td>' . $row->receipt . '</td>';
												echo '<td>' . ddate($row->delivery_date) . '</td>';
												echo '<td>' . number_format(str_replace(",", "", $row->item_no)) . '</td>';
												echo '<td>';
												echo '<a href = "admin/deliveries?view_delivery=' . md5($row->receipt) . '" name = "edit" class = "btn btn-sm btn-primary"><span class="fa fa-search"></span></a> ';
												//echo '<a onclick = "edit('.$row->delivery_id.',\'delivery\')" name = "edit" class = "btn btn-sm btn-success"><span class="fa fa-pencil-square-o"></span></a>';
												//echo ' <a onclick = "return confirm(\'Are you sure?\')" href = "admin/delete?idx=' . $row->engr_id . '" class = "btn btn-sm btn-danger"> Delete </a>';
												echo '</td>';
												echo '</tr>';
											}
										}
										?>
									</tbody>
								</table>
								<?php if ($totalPages > 1) { ?>
									<div class="row" style="margin-top: 10px;">
										<div class="col-12" align="center">
											<hr>
											<label> <b>Pages</b> </label><br>
											<nav class="center-block">
												<ul class="pagination report-pagination justify-content-center">
													<?php
													$prev = intval($_GET['view']) - 1;
													if ($prev > 0) {
														echo '<li class="page-item"><a class="page-link" href="admin/deliveries?view=1">&laquo;&laquo;</a></li>';
														echo '<li class="page-item"><a class="page-link" href="admin/deliveries?view=' . $prev . '">&laquo;</a></li>';
													} else {
														echo '<li class="page-item disabled"><span class="page-link">&laquo;&laquo;</span></li>';
														echo '<li class="page-item disabled"><span class="page-link">&laquo;</span></li>';
													}
													foreach (report_pagination_items($_GET['view'], $totalPages) as $page) {
														if ($page === "...") {
															echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
														} elseif ($page == $_GET['view']) {
															echo '<li class="page-item active"><span class="page-link">' . $page . '</span></li>';
														} else {
															echo '<li class="page-item"><a class="page-link" href="admin/deliveries?view=' . $page . '">' . $page . '</a></li>';
														}
													}
													$nxt = intval($_GET['view']) + 1;
													if ($nxt <= $totalPages) {
														echo '<li class="page-item"><a class="page-link" href="admin/deliveries?view=' . $nxt . '">&raquo;</a></li>';
														echo '<li class="page-item"><a class="page-link" href="admin/deliveries?view=' . $totalPages . '">&raquo;&raquo;</a></li>';
													} else {
														echo '<li class="page-item disabled"><span class="page-link">&raquo;</span></li>';
														echo '<li class="page-item disabled"><span class="page-link">&raquo;&raquo;</span></li>';
													}
													?>
												</ul>
											</nav>
										</div>
									</div>
								<?php } ?>
							</div>
						</div>
					<?php } else { ?>
						<form action="" method="get">
							<div class="card">
								<div class="card-header">
									<strong class="card-title">New Delivery</strong>
								</div>
								<div class="card-body">
									<div class="row">
										<div class="col-12">
											<u>
												<h4 class="text-left spacing">DELIVERY DETAILS</h4>
											</u>
										</div>
									</div>
									<div class="row">
										<div class="col-4">
											<label>Delivery Date <font color="red">*</font></label>
											<input required <?php if (isset($_GET['delivery_date'])) {
																echo ' value = "' . $_GET['delivery_date'] . '"';
															} else {
																echo ' value = "' . date("Y-m-d") . '" ';
															} ?> autocomplete="off" required name="delivery_date" type="date" class="input-sm form-control-sm form-control" aria-required="true" aria-invalid="false">
										</div>
									</div>
									<hr>
									<h4 class="text-left spacing"><u><b>PRODUCT DETAILS</b></u> &nbsp;&nbsp;<a class="btn btn-success btn-sm" onclick="addx('products')"><span class="fa fa-plus"></span> New Product</a></h4>
									<div class="row">
										<div class="col-6">
											<label>Scan Product <font color="red">*</font></label>
											<input placeholder="Scan barcode..." <?php if (isset($_GET['product_id'])) {
																						echo ' value = "' . $_GET['product_id'] . '"';
																					} else {
																						echo 'autofocus';
																					} ?> autocomplete="off" required name="product_id" type="text" class="input-sm form-control-sm form-control" aria-required="true" aria-invalid="false">
											<!--
												<select required autofocus id="SelectLm" class="input-sm form-control-sm form-control" name = "product_id" required>
													<option value="">Please select</option>
													<?php
													$prod = "SELECT * FROM products ORDER BY name ASC";
													$prod = $conn->query($prod);
													if ($prod->num_rows > 0) {
														while ($row = $prod->fetch_object()) {
															echo '<option value = "' . md5($row->product_id) . '">' . $row->name . '</option>';
														}
													}
													?>
												</select>
											-->
										</div>
										<div class="col-3">
											<label>Quantity <font color="red">*</font></label>
											<input placeholder="Enter quantity..." <?php if (isset($_GET['qty'])) {
																						echo ' value = "' . $_GET['qty'] . '"';
																					} ?> autocomplete="off" required name="qty" type="text" class="input-sm form-control-sm form-control" aria-required="true" aria-invalid="false">
										</div>
										<div class="col-2">
											<button style="margin-top: 30px;" class="btn btn-primary btn-sm" name="add" type="submit"><span class="fa fa-plus"></span> Add</button>
										</div>
									</div>
								</div>
							</div>
						</form>
						<?php
						$prod = "SELECT * FROM delivery WHERE state = 0 ORDER BY product_name ASC";
						$prod = $conn->query($prod);
						if ($prod->num_rows > 0) {
							$num = 0;
						?>
							<div class="card">
								<div class="card-header">
									<strong class="card-title">Product List</strong>
									<button class="btn btn-success btn-sm pull-right" onclick="addx('finish')"><span class="fa fa-check"></span> Finish</button>
								</div>
								<div class="card-body">
									<table class="table">
										<thead class="thead-dark" align="center">
											<tr>
												<th scope="col" width="10%">#</th>
												<th scope="col">Name</th>
												<th scope="col">Price</th>
												<th scope="col">Quantity</th>
											</tr>
										</thead>
										<tbody>
											<?php
											while ($row = $prod->fetch_object()) {
												$num += 1;
												echo '<tr>';
												echo '<td>' . $num . '</td>';
												echo '<td>' . $row->product_name . '</td>';
												echo '<td>' . number_format(str_replace(",", "", $row->product_price), 2) . '</td>';
												echo '<td>' . number_format(str_replace(",", "", $row->qty)) . '</td>';
												echo '</tr>';
											}
											?>
										</tbody>
									</table>
								</div>
							</div>
					<?php }
					}
				} else {  ?>
					<div class="card" <?php if(isset($_GET['print'])) { ?> id="reportg" <?php } ?>>
						<div class="card-header">
							<strong class="card-title">Delivery Details</strong>
							<a id = "backs" class="btn btn-success btn-sm pull-right" target = "_blank" href="admin/deliveries?date_fr=&date_to=&view_delivery=<?php echo $_GET['view_delivery']; ?>&print"  style="margin-right: 5px;"><span class="fa fa-print"></span> Print </a>
							<a id="backs" class="btn btn-warning btn-sm pull-right" target="_blank" href="export.php?date_fr=&date_to=&view_delivery=<?php echo $_GET['view_delivery']; ?>" style="margin-right: 5px;"><span class="fa fa-save"></span> Export </a>
						</div>
						<div class="card-body">
							<u>
								<h4 class="text-left spacing">DELIVERY DETAILS</h4>
							</u>

							<table class="table">
								<?php
								$prod = "SELECT * FROM delivery WHERE state = 1 and md5(receipt) = '" . mysqli_real_escape_string($conn, $_GET['view_delivery']) . "' ORDER BY product_name ASC";
								$prod = $conn->query($prod);
								if ($prod->num_rows > 0) {
									$num = 0;
									while ($row = $prod->fetch_object()) {
										$num += 1;
										if ($num == 1) {
											echo '<thead>';
											echo '<tr><td align="left" colspan="3"> Receipt: <b>' . $row->receipt . ' </b></td></tr>';
											echo '<tr><td align="left" colspan="3"> Delivery Date:  <b>' . ddate($row->delivery_date) . ' </b></td></tr>';
											echo '</thead>';
											echo '<thead class="thead-dark" align="center">
																			<tr>
																				<th scope="col" width="10%">#</th>
																				<th scope="col">Delivery Date</th>
																				<th scope="col">Receipt</th>
																				<th scope="col">Product Name</th>
																				<th scope="col">Quantity</th>
																			</tr>
																		</thead>
																		<tbody>';
										}
										echo '<tr>';
										echo '<td>' . $num . '</td>';
										echo '<td>' . ddate($row->delivery_date) . '</td>';
										echo '<td>' . $row->receipt . '</td>';
										echo '<td>' . $row->product_name . '</td>';
										echo '<td>' . number_format(str_replace(",", "", $row->qty)) . '</td>';
										echo '</tr>';
									}
								}
								?>
								</tbody>
							</table>
						</div>
					</div>
				<?php } ?>
			</div>
		</div>
	</div>
</div>
<?php
	if(isset($_GET['print'])){
		echo '
		<script type = "text/javascript"> 
			window.onload = function() {						 
				setTimeout(function() { window.print(); window.close(); window.location.href = "reports"; }, 500); 
			};
		</script>';			 
	}

?>
<?php
if (isset($_GET['add'])) {
	if (!empty($_GET['delivery_date']) && !empty($_GET['product_id']) && !empty($_GET['qty'])) {
		$_GET['product_id'] = strtoupper($_GET['product_id']);
		$prod = "SELECT * FROM products WHERE (barcode) = '" . mysqli_real_escape_string($conn, $_GET['product_id']) . "'";
		$prod = $conn->query($prod);
		if ($prod->num_rows > 0) {
			while ($row = $prod->fetch_object()) {
				$prodx = $row->product_id;
				$name = $row->name;
				$price = $row->price;
			}
		}
		$total = $price * $_GET['qty'];
		$_GET['delivery_date'] = strtoupper($_GET['delivery_date']);
		$stmt = $conn->prepare("INSERT INTO delivery (product_id, delivery_date, product_name, product_price, qty, total, user) VALUES (?, ?, ?, ?, ?, ?, ?)");
		$stmt->bind_param("issssss", $prodx, $_GET['delivery_date'], $name, $price, $_GET['qty'], $total, $_SESSION['nameinsta']);
		if ($stmt->execute() === TRUE) {
			alert("admin/deliveries?delivery&delivery_date=" . $_GET['delivery_date'], "Adding successfull.");
		} else {
			echo $conn->error;
		}
	} else {
		alert($_SERVER['REQUEST_URI'], "Check your details");
	}
}
if (isset($_POST['add'])) {
	if (!empty($_POST['name']) && !empty($_POST['price']) && !empty($_POST['barcode'])) {
		$_POST['name'] = strtoupper($_POST['name']);
		$_POST['price'] = strtoupper($_POST['price']);
		$_POST['barcode'] = strtoupper($_POST['barcode']);
		$prodx = "SELECT * FROM products WHERE barcode = '" . mysqli_real_escape_string($conn, $_POST['barcode']) . "'";
		$prodx = $conn->query($prodx);
		if ($prodx->num_rows > 0) {
			$_POST['barcode'] = rand(0, 99999999);
			$_POST['barcode'] = str_pad($_POST['barcode'], 10, 0, STR_PAD_LEFT);
		}
		$stmt = $conn->prepare("INSERT INTO products (name, price, barcode) VALUES (?, ?, ?)");
		$stmt->bind_param("sss", $_POST['name'], $_POST['price'], $_POST['barcode']);
		if ($stmt->execute() === TRUE) {
			alert($_SERVER['REQUEST_URI'], "Adding successfull.");
		} else {
			echo $conn->error;
		}
	} else {
		alert($_SERVER['REQUEST_URI'], "Check your details");
	}
}
if (isset($_POST['finish'])) {
	if (!empty($_POST['receipt']) && !empty($_POST['dt'])) {
		$_POST['receipt'] = strtoupper($_POST['receipt']);

		$prodx = "SELECT * FROM delivery WHERE receipt = '" . mysqli_real_escape_string($conn, $_POST['receipt']) . "'";
		$prodx = $conn->query($prodx);
		if ($prodx->num_rows > 0) {
			$_POST['receipt'] = rand(0, 999999);
			$_POST['receipt'] = 'DR-' . str_pad($_POST['receipt'], 10, 0, STR_PAD_LEFT);
		}
		$stmt = $conn->prepare("UPDATE delivery SET receipt = ?, state = 1 WHERE user = ? and state = 0");
		$stmt->bind_param("ss", $_POST['receipt'], $_SESSION['nameinsta']);
		if ($stmt->execute() === TRUE) {
			$stmt = $conn->prepare("UPDATE products AS a INNER JOIN delivery AS b ON a.product_id = b.product_id SET a.stock = (a.stock + b.qty) WHERE b.receipt = ? and state = 1");
			$stmt->bind_param("s", $_POST['receipt']);
			if ($stmt->execute() === TRUE) {
				//alert("admin/print&print&view_delivery=".md5($_POST['receipt']), "Delivery successfull.");
				alert("admin/print/deliveries", "Delivery successfull.");
			}
		}
	} else {
		alert($_SERVER['REQUEST_URI'], "Check your details");
	}
}
unset($_SESSION['edit_id']);
?>
