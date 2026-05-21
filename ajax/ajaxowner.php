<?php
include '../config/conf.php';
session_start();
if (!isset($_GET['module'])) {
	echo '<script type = "text/javascript">window.location.replace("/nbc");</script>';
}
$access = "SELECT * FROM user where account_id = '$_SESSION[insta_acc]'";
if ($conn->query($access)->num_rows <= 0) {
	$_GET['module'] = 'logout';
}
$access = $conn->query($access)->fetch_object();
?>
<?php
if (isset($_GET['add'])) {
	if (isset($_GET['form'])) {
		switch ($_GET['form']) {
			case 'finish':
?>
				<form action="" method="post">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="modalLabel">Finish Delivery</h5>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body">
							<form action="" method="post">
								<div class="form-group">
									<label>Delivery Date <font color="red"> * </font></label>
									<input readonly type="date" value="<?php echo date("Y-m-d"); ?>" autocomplete="off" name="dt" class="form-control  form-control-sm" required placeholder="Enter product name." />
								</div>
								<div class="form-group">
									<label>Delivery Receipt <font color="red"> * </font></label>
									<input readonly type="text" value="<?php $rand = rand(0, 999999);
																		$rand = 'DR-' . str_pad($rand, 10, 0, STR_PAD_LEFT);
																		echo $rand; ?>" autocomplete="off" name="receipt" class="form-control  form-control-sm" required placeholder="Enter product name." />
								</div>
							</form>
						</div>
						<div class="modal-footer">
							<button type="submit" class="btn btn-primary" name="finish">Confirm</button>
							<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
						</div>
					</div>
				</form>
			<?php
				break;

			case 'delivery':
			?>
				<form action="" method="post">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="modalLabel">New Delivery</h5>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body">
							<form action="" method="post">
								<div class="form-group">
									<label>Delivery Date <font color="red"> * </font></label>
									<input type="date" value="<?php echo date("Y-m-d"); ?>" autocomplete="off" name="name" class="form-control  form-control-sm" required placeholder="Enter product name." />
								</div>
								<div class="form-group">
									<label>Product Price <font color="red"> * </font></label>
									<input type="text" autocomplete="off" name="price" class="form-control  form-control-sm" required placeholder="Enter product price." />
								</div>
								<div class="form-group">
									<label>Barcode Number <font color="red"> * </font></label>
									<input type="text" autocomplete="off" name="barcode" class="form-control  form-control-sm" required placeholder="Enter barcode number." />
								</div>
							</form>
						</div>
						<div class="modal-footer">
							<button type="submit" class="btn btn-primary" name="add">Add</button>
							<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
						</div>
					</div>
				</form>
			<?php
				break;

			case 'products':
			?>
				<form action="" method="post">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="modalLabel">New Product</h5>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body">
							<form action="" method="post">
								<div class="form-group">
									<label>Product Name <font color="red"> * </font></label>
									<input type="text" autocomplete="off" name="name" class="form-control  form-control-sm" required placeholder="Enter product name." />
								</div>
								<div class="form-group">
									<label>Product Price <font color="red"> * </font></label>
									<input type="text" autocomplete="off" name="price" class="form-control  form-control-sm" required placeholder="Enter product price." />
								</div>
								<div class="form-group">
									<label>Barcode Number <a class="btn btn-sm btn-success" onclick="document.getElementById('barcode').value = getRndInteger(0,99999999).padStart(10, '0')"><span class="fa fa-barcode"></span> Generate </a></label>
									<input type="text" id="barcode" autocomplete="off" name="barcode" class="form-control  form-control-sm" required placeholder="Enter barcode number." />
								</div>
							</form>
						</div>
						<div class="modal-footer">
							<button type="submit" class="btn btn-primary" name="add">Add</button>
							<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
						</div>
					</div>
				</form>
			<?php
				break;
			case 'engr':
			?>
				<form action="" method="post" enctype="multipart/form-data">>
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="modalLabel">New Employee</h5>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body">
							<form action="" method="post">
								<div class="form-group">
									<label for="category" class="control-label mb-1">Employee No.<font color="red"> * </font></label>
									<input type="text" autocomplete="off" name="emp_no" class="form-control  form-control-sm" required placeholder="Enter no." />
								</div>
								<div class="form-group">
									<label>First Name <font color="red"> * </font></label>
									<input type="text" autocomplete="off" name="fname" class="form-control  form-control-sm" required placeholder="Enter first name." />
								</div>
								<div class="form-group">
									<label>Middle Name <font color="red"> * </font></label>
									<input type="text" autocomplete="off" name="mname" class="form-control  form-control-sm" required placeholder="Enter middle name." />
								</div>
								<div class="form-group">
									<label>Last Name <font color="red"> * </font></label>
									<input type="text" autocomplete="off" name="lname" class="form-control  form-control-sm" required placeholder="Enter last name." />
								</div>
								<div class="form-group">
									<label for="category" class="control-label mb-1">Address<font color="red"> * </font></label>
									<textarea oninput="this.value=this.value.toUpperCase()" name="address" rows="2" placeholder="Address..." class="form-control form-control-sm"></textarea>
								</div>
								<div class="form-group">
									<label for="category" class="control-label mb-1">Department<font color="red"> * </font></label>
									<input type="text" autocomplete="off" name="department" class="form-control  form-control-sm" required placeholder="Enter last name." />
								</div>
								<div class="form-group">
									<label for="category" class="control-label mb-1">Section<font color="red"> * </font></label>
									<input type="text" autocomplete="off" name="section" class="form-control  form-control-sm" required placeholder="Enter last name." />
								</div>
								<div class="form-group">
									<label for="category" class="control-label mb-1">Position<font color="red"> * </font></label>
									<input type="text" autocomplete="off" name="position" class="form-control  form-control-sm" required placeholder="Enter last name." />
								</div>
								<div class="form-group">
									<label for="category" class="control-label mb-1">Employee Type<font color="red"> * </font></label>
									<select required autofocus id="SelectLm" class="input-sm form-control-sm form-control" name="type" required>
										<?php if ($access->level > 0) {	?>
											<option value="">Please select</option>
											<option value="Direct">Direct</option>
										<?php	}	?>
										<option value="Agency">Agency</option>
									</select>
								</div>
								<div class="form-group">
									<label for="category" class="control-label mb-1">Green Mark<font color="red"> * </font></label>
									<select required autofocus id="SelectLm" class="input-sm form-control-sm form-control" name="green_mark" required>
										<option value="">Please select</option>
										<option value="1">Yes</option>
										<option value="0">No</option>
									</select>
								</div>
								<div class="form-group">
									<label for="category" class="control-label mb-1">Max Credit<font color="red"> * </font></label>
									<input pattern="[.0-9,]*" type="text" autocomplete="off" name="max_credit" class="form-control  form-control-sm" required placeholder="Enter max credit." />
								</div>
								<div class="form-group">
									<label for="category" class="control-label mb-1">RFID No.<font color="red"> * </font></label>
									<input type="text" autocomplete="off" name="rfid_no" class="form-control  form-control-sm" required placeholder="Enter rfid no." />
								</div>
								<div class="form-group">
									<label for="category" class="control-label mb-1">QR Code.</label>
									<input type="text" autocomplete="off" name="qr_code" class="form-control  form-control-sm" placeholder="Enter QR. Code" />
								</div>
								<div class="form-group">
									<input class="form-control" type="file" name="image" id="image" accept="image/*">
								</div>
							</form>
						</div>
						<div class="modal-footer">
							<button type="submit" class="btn btn-primary" name="add">Add</button>
							<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
						</div>
					</div>
				</form>
<?php
				break;

			default:
				break;
		}
	}
}
?>


<?php
if (isset($_GET['val']) && isset($_GET['edit'])) {
	if (isset($_GET['form'])) {
		$_GET['val'] = mysqli_real_escape_string($conn, $_GET['val']);
		switch ($_GET['form']) {
			case 'engr':
				if ($access->level > 0) {
					$stmt = "SELECT * FROM members where member_id = '" . $_GET['val'] . "'";
				} else {
					$stmt = "SELECT * FROM members where member_id = '" . $_GET['val'] . "' and type = 'Agency'";
				}
				$stmt = $conn->query($stmt);
				if ($stmt->num_rows > 0) {
					$res = $stmt->fetch_object();
					$_SESSION['edit_id'] = $res->member_id;
?>
					<form action="" method="post" enctype="multipart/form-data">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title" id="modalLabel">Edit Employee</h5>
								<button type="button" class="close" data-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body">
								<form action="" method="post">
									<div class="form-group">
										<label for="category" class="control-label mb-1">Employee No.<font color="red"> * </font></label>
										<input <?php if ($res->emp_no <> "") {
													echo ' value = "' . $res->emp_no . '"';
												} ?> type="text" autocomplete="off" name="emp_no" class="form-control  form-control-sm" required placeholder="Enter no." />
									</div>
									<div class="form-group">
										<label>First Name <font color="red"> * </font></label>
										<input <?php if ($res->fname <> "") {
													echo ' value = "' . $res->fname . '"';
												} ?> type="text" autocomplete="off" name="fname" class="form-control  form-control-sm" required placeholder="Enter first name." />
									</div>
									<div class="form-group">
										<label>Middle Name <font color="red"> * </font></label>
										<input <?php if ($res->mname <> "") {
													echo ' value = "' . $res->mname . '"';
												} ?> type="text" autocomplete="off" name="mname" class="form-control  form-control-sm" required placeholder="Enter middle name." />
									</div>
									<div class="form-group">
										<label>Last Name <font color="red"> * </font></label>
										<input <?php if ($res->lname <> "") {
													echo ' value = "' . $res->lname . '"';
												} ?> type="text" autocomplete="off" name="lname" class="form-control  form-control-sm" required placeholder="Enter last name." />
									</div>
									<div class="form-group">
										<label for="category" class="control-label mb-1">Address<font color="red"> * </font></label>
										<textarea oninput="this.value=this.value.toUpperCase()" name="address" rows="2" placeholder="Address..." class="form-control form-control-sm"><?php if ($res->address <> "") {
																																															echo $res->address;
																																														} ?></textarea>
									</div>
									<div class="form-group">
										<label for="category" class="control-label mb-1">Department<font color="red"> * </font></label>
										<input <?php if ($res->department <> "") {
													echo ' value = "' . $res->department . '"';
												} ?> type="text" autocomplete="off" name="department" class="form-control  form-control-sm" required placeholder="Enter department." />
									</div>
									<div class="form-group">
										<label for="category" class="control-label mb-1">Section<font color="red"> * </font></label>
										<input <?php if ($res->section <> "") {
													echo ' value = "' . $res->section . '"';
												} ?> type="text" autocomplete="off" name="section" class="form-control  form-control-sm" required placeholder="Enter section." />
									</div>
									<div class="form-group">
										<label for="category" class="control-label mb-1">Position<font color="red"> * </font></label>
										<input <?php if ($res->position <> "") {
													echo ' value = "' . $res->position . '"';
												} ?> type="text" autocomplete="off" name="position" class="form-control  form-control-sm" required placeholder="Enter position." />
									</div>
									<div class="form-group">
										<label for="category" class="control-label mb-1">RFID No.<font color="red"> * </font></label>
										<input <?php $_SESSION['edit_rfid'] = $res->rfid_no;
												if ($res->rfid_no <> "") {
													echo ' value = "' . $res->rfid_no . '"';
												} ?> type="text" autocomplete="off" name="rfid_no" class="form-control  form-control-sm" required placeholder="Enter rfid no.." />
									</div>
									<div class="form-group">
										<label for="category" class="control-label mb-1">Load Balance<font color="red"> * </font></label>
										<input <?php $_SESSION['edit_balance'] = $res->balance;
												if ($res->balance <> "") {
													echo ' value = "' . $res->balance . '"';
												} ?> type="text" autocomplete="off" name="balance" class="form-control  form-control-sm" required placeholder="Enter load." />
									</div>
									<div class="form-group">
										<label for="category" class="control-label mb-1">Max Credit<font color="red"> * </font></label>
										<input <?php $_SESSION['edit_max_credit'] = $res->max_credit ?? '';
												if ($res->max_credit <> "") {
													echo ' value = "' . $res->max_credit . '"';
												} ?> type="text" autocomplete="off" name="max_credit" class="form-control  form-control-sm" required placeholder="Enter max credit." />
									</div>
									<div class="form-group">
										<label for="category" class="control-label mb-1">Employee Type<font color="red"> * </font></label>
										<select required autofocus id="SelectLm" class="input-sm form-control-sm form-control" name="type" required>
											<option value="">Please select</option>
											<option <?php if ($res->type == "Direct") {
														echo ' selected ';
													} ?> value="Direct">Direct</option>
											<option <?php if ($res->type == "Agency") {
														echo ' selected ';
													} ?> value="Agency">Agency</option>
										</select>
									</div>
									<div class="form-group">
									<label for="category" class="control-label mb-1">Green Mark<font color="red"> * </font></label>
									<select required autofocus id="SelectLm" class="input-sm form-control-sm form-control" name="green_mark" required>
										<option value="">Please select</option>
										<option <?php if ($res->green_mark == 1) { echo ' selected '; } ?> value="1">Yes</option>
										<option <?php if ($res->green_mark == 0) { echo ' selected '; } ?> value="0">No</option>
									</select>
								</div>
									<div class="form-group">
										<input class="form-control" type="file" name="image" id="image" accept="image/*">
									</div>
									<?php
									if (file_exists("../images/photos/" . $res->rfid_no . ".jpg")) {
										$filename = "images/photos/" . $res->rfid_no . ".jpg";
									?>
										<div class="form-group" align="center">
											<img height="200" src="<?php echo $filename; ?>">
										</div>
									<?php } ?>
								</form>
							</div>
							<div class="modal-footer">
								<button type="submit" class="btn btn-primary" name="update">Update</button>
								<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
							</div>
						</div>
					</form>
				<?php
				}
				break;
			case 'products':
				$stmt = "SELECT * FROM products where product_id = '" . $_GET['val'] . "'";
				$stmt = $conn->query($stmt);
				if ($stmt->num_rows > 0) {
					$res = $stmt->fetch_object();
					$_SESSION['edit_id'] = $res->product_id;
				?>
					<form action="" method="post">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title" id="modalLabel">Edit Product</h5>
								<button type="button" class="close" data-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body">
								<form action="" method="post">
									<div class="form-group">
										<label>Product Name <font color="red"> * </font></label>
										<input <?php if ($res->name <> "") {
													echo ' value = "' . $res->name . '"';
												} ?> type="text" autocomplete="off" name="name" class="form-control  form-control-sm" required placeholder="Enter product name." />
									</div>
									<div class="form-group">
										<label>Product Price <font color="red"> * </font></label>
										<input pattern="[.0-9,]*" <?php if ($res->price <> "") {
																		echo ' value = "' . str_replace(".00", "", $res->price) . '"';
																	} ?> type="text" autocomplete="off" name="price" class="form-control  form-control-sm" required placeholder="Enter product price." />
									</div>
									<div class="form-group">
										<label>Barcode Number</label>
										<input readonly <?php if ($res->barcode <> "") {
															echo ' value = "' . $res->barcode . '"';
														} ?> type="text" autocomplete="off" name="barcode" class="form-control  form-control-sm" required placeholder="Enter barcode number." />
									</div>
								</form>
							</div>
							<div class="modal-footer">
								<button type="submit" class="btn btn-primary" name="update">Update</button>
								<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
							</div>
						</div>
					</form>
				<?php
				}
				break;

			case 'load':
				if ($access->level > 0) {
					$stmt = "SELECT * FROM members where member_id = '" . mysqli_real_escape_string($conn, $_GET['val']) . "'";
				} else {
					$stmt = "SELECT * FROM members where member_id = '" . mysqli_real_escape_string($conn, $_GET['val']) . "' and type = 'Agency'";
				}
				$stmt = $conn->query($stmt);
				if ($stmt->num_rows > 0) {
					$res = $stmt->fetch_object();
					$name = $res->lname . ', ' . $res->fname . ' ' . $res->mname;
					$_SESSION['edit_id'] = $res->member_id;
					$_SESSION['cur_bal'] = $res->balance;
					$_SESSION['member'] = $name;
				?>
					<form action="" method="post">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title" id="modalLabel">Load</h5>
								<button type="button" class="close" data-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body">
								<form action="" method="post">
									<div class="form-group">
										<label>Employee Name <font color="red"> * </font></label>
										<input readonly <?php if ($name <> "") {
															echo ' value = "' . $name . '"';
														} ?> type="text" autocomplete="off" name="name" class="form-control  form-control-sm" required />
									</div>
									<div class="form-group">
										<label>Current Balance <font color="red"> * </font></label>
										<input readonly <?php if ($res->balance <> "") {
															echo ' value = "' . $res->balance . '"';
														} ?> type="text" autocomplete="off" name="balance" class="form-control  form-control-sm" required />
									</div>
									<div class="form-group">
										<label>Load Amount</label>
										<input pattern="[.0-9,]*" type="text" autocomplete="off" name="load_amnt" class="form-control  form-control-sm" required placeholder="Enter load amount." />
									</div>
								</form>
							</div>
							<div class="modal-footer">
								<button type="submit" class="btn btn-primary" name="load">Update</button>
								<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
							</div>
						</div>
					</form>
<?php
				}
				break;

			default:
				# code...
				break;
		}
	}
}
?>