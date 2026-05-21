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
				<div class="card">
					<div class="card-header">
						<strong class="card-title"><?php echo $title; ?> List</strong>
						<div class="pull-right">
							<?php if ($access->emp_add) { ?> <button class="btn btn-primary btn-sm " onclick="addx('engr')"><span class="fa fa-plus"></span></button> <?php } ?>
							<a class="btn btn-success btn-sm" href="accounts/load_import"><span class="fa fa-download"></span> Import Load</a>
						</div>
						<hr>
						<form action="" method="get">
							<div class="form-inline">
								<label>Search: </label>&nbsp;&nbsp; <input autocomplete="off" name="search" <?php if (isset($_GET['search'])) {
																												echo " value = '" . $_GET['search'] . "' ";
																											} ?> style="min-width: 350px;" type="text" class="form-control form-control-sm input-sm" placeholder="Search employee.">
								&nbsp;
								<?php
								if (isset($_GET['view']) && $_GET['view'] != '') {
									echo '<input type = "hidden" value = "' . $_GET['view'] . '" name = "view">';
								}
								?>
								<button type="submit" class="btn btn-primary btn-sm">
									<i class="fa fa-search"></i>&nbsp;<span></span>
								</button>
							</div>
						</form>
					</div>
					<div class="card-body">
						<table class="table">
							<thead class="thead-dark" align="center">
								<tr>
									<th scope="col">#</th>
									<th scope="col">Name</th>
									<th scope="col">Department</th>
									<th scope="col">Position</th>
									<th scope="col">Type</th>
									<th scope="col">Action</th>
								</tr>
							</thead>
							<tbody align="center">
								<?php
								$where = " WHERE 1=1 ";
								if (isset($_GET['search'])) {
									$where .= " and ( emp_no like '%" . mysqli_real_escape_string($conn, $_GET['search']) . "%' or fname like '%" . mysqli_real_escape_string($conn, $_GET['search']) . "%' or lname like '%" . mysqli_real_escape_string($conn, $_GET['search']) . "%' or mname like '%" . mysqli_real_escape_string($conn, $_GET['search']) . "%')";
								}
								if ($access->level == 0) {
									$where .= " and type = 'Agency' ";
								}
								$counter = "SELECT count(*) as total FROM members " . $where;
								$counter2 = $conn->query($counter)->fetch_assoc();
								$perpage = 10;
								$totalPages = ceil($counter2['total'] / $perpage);
								if (!isset($_GET['view'])) {
									$_GET['view'] = 0;
								} else {
									$_GET['view'] = (int)$_GET['view'];
								}
								if ($_GET['view'] < 1) {
									$_GET['view'] = 1;
								} else if ($_GET['view'] > $totalPages) {
									$_GET['view'] = $totalPages;
								}
								$startArticle = ($_GET['view'] - 1) * $perpage;
								$prod = "SELECT * FROM members " . $where . " ORDER BY type DESC, lname asc LIMIT " . $startArticle . ', ' . $perpage;
								$prod = $conn->query($prod);
								if ($prod->num_rows > 0) {
									$num = 0 + $startArticle;
									while ($row = $prod->fetch_object()) {
										$num += 1;
										$name = $row->lname . ', ' . $row->fname . ' ' . $row->mname;
										if ($row->lname == ' ') {
											$name = $row->fname;
										}
										if ($row->fname == ' ') {
											$name = $row->lname;
										}
										echo '<tr>';
										echo '<th scope="row">' . $num . '</th>';
										echo '<td>' . $name . '</td>';
										echo '<td>' . $row->department . '</td>';
										echo '<td>' . $row->position . '</td>';
										echo '<td>' . $row->type . '</td>';
										echo '<td>';
										if ($access->emp_edit) {
											echo '<a onclick = "edit(' . $row->member_id . ',\'engr\')" name = "edit" class = "btn btn-sm btn-success"><span class="fa fa-edit"></span></a>&nbsp;';
										}
										echo '<a style = "color: white; font-weight: bold;" class="btn btn-warning btn-sm" onclick = "edit(\'' . $row->member_id . '\',\'load\')"><span>&#x20B1</span></a>';
										if ($access->emp_delete) {
											echo	' <a onclick = "return confirm(\'Are you sure?\');" href = "accounts/emp?delete=' . $row->member_id . '" class = "btn btn-danger btn-sm" data-toggle="tooltip" title="Delete User"><span class = "fa fa-trash"><span></a>';
										}
										//echo ' <a onclick = "return confirm(\'Are you sure?\')" href = "admin/delete?idx=' . $row->engr_id . '" class = "btn btn-sm btn-danger"> Delete </a>';
										echo '</td>';
										echo '</tr>';
									}
								}
								?>
							</tbody>
						</table>
						<div class="row" style="margin-top: 10px;">
							<div class="col-12" align="center">
								<hr>
								<!--<label>Records <?php $startArticlex = $startArticle + 1;
													$perpagex = $perpage * $_GET['view'];
													if ($perpagex > $counter2['total']) {
														$perpagex = $counter2['total'];
													}
													echo $startArticlex . ' - ' . $perpagex ?> </label><br>-->
								<label> <b>Pages</b> </label><br>
								<nav class="center-block">
									<ul class="pagination justify-content-center">
										<?php
										$search = "";
										if (isset($_GET['search'])) {
											$search = '&search=' . $_GET['search'];
										}
										$prev = intval($_GET['view']) - 1;
										if ($prev > 0) {
											echo '	<li class="page-item">
											<a class="page-link" data-toggle="tooltip" data-placement="top" title="Previous" aria-label="Previous" href="accounts/emp?view=' . $prev . $search . '"><span aria-hidden="true">&laquo;</span><span class="sr-only">Previous</span></a></li>';
										}
										foreach (range(1, $totalPages) as $page) {
											if ($page == $_GET['view']) {
												echo	'<li class="page-item active"><span class="page-link">' . $page . '</span></li>';
											} else if ($page == 1 || $page == $totalPages || ($page >= $_GET['view'] - 2 && $page <= $_GET['view'] + 2)) {
												if ($page == 0) {
													continue;
												}
												echo	'<li class="page-item"><a class = "page-link" data-toggle="tooltip" data-placement="top" title="Page ' . $page . '" href="accounts/emp?view=' . $page . $search . '">' . $page . '</a></li>';
											}
										}
										$nxt = intval($_GET['view']) + 1;
										if ($nxt <= $totalPages) {
											echo	'<li class="page-item"><a class="page-link" data-toggle="tooltip" data-placement="top" title="Next" aria-label="Next" href="accounts/emp?view=' . $nxt . $search . '"><span aria-hidden="true">&raquo;</span><span class="sr-only">Next</span></a></li>';
										}
										?>
									</ul>
								</nav>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php
if (isset($_POST['add']) && $access->emp_add == 1) {
	if (!empty($_POST['fname']) && !empty($_POST['mname']) && !empty($_POST['lname']) && !empty($_POST['emp_no']) && !empty($_POST['section'])) {
		$_POST['fname'] = strtoupper($_POST['fname']);
		$_POST['mname'] = strtoupper($_POST['mname']);
		$_POST['lname'] = strtoupper($_POST['lname']);
		$_POST['address'] = strtoupper($_POST['address']);
		$_POST['department'] = strtoupper($_POST['department']);
		$_POST['position'] = strtoupper($_POST['position']);

		$image = $_FILES['image'];
		if (!empty($image['name'])) {
			exit;
			$imageFileType = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
			if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
				alert($_SERVER['REQUEST_URI'], 'Only JPG, JPEG, PNG, and GIF files are allowed.');
				exit;
			}
		}

		$stmt = "SELECT * FROM members where emp_no = '" . mysqli_real_escape_string($conn, $_POST['emp_no']) . "'";
		$stmt = $conn->query($stmt);
		if ($stmt->num_rows > 0) {
			alert($_SERVER['REQUEST_URI'], "Employee already exist");
			exit;
		}

		$stmt = $conn->prepare("INSERT INTO members (max_credit, type, fname, mname, lname, address, department, position, rfid_no, qr_code, emp_no, section) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
		$stmt->bind_param("ssssssssssss", $_POST['max_credit'], $_POST['type'], $_POST['fname'], $_POST['mname'], $_POST['lname'], $_POST['address'], $_POST['department'], $_POST['position'], $_POST['rfid_no'], $_POST['qr_code'], $_POST['emp_no'], $_POST['section']);
		if ($stmt->execute() === TRUE) {
			if (!empty($image)) {
				$targetDir = 'images/photos/';
				if ($image['name'] == $existingImageName) {
					// Delete the existing image.
					unlink($targetDir . $_POST['rfid_no'] . '.jpg');
				}
				rename($image['tmp_name'], $targetDir . $_POST['rfid_no'] . '.jpg');
			}
			alert($_SERVER['REQUEST_URI'], "Adding successfull.");
		} else {
			echo $conn->error;
		}
	} else {
		alert($_SERVER['REQUEST_URI'], "Check your details");
	}
}
if (isset($_POST['load'])) {
	if (!empty($_POST['load_amnt']) && isset($_SESSION['edit_id'])) {
		$_POST['load_amnt'] = strtoupper($_POST['load_amnt']);
		$cur_bal = number_format($_POST['load_amnt'], 2) + number_format($_SESSION['cur_bal'], 2);
		$stmt = $conn->prepare("INSERT INTO loads (member_id, member_name, loads, run_bal, `user`) VALUES (?, ?, ?, ?, ?)");
		$stmt->bind_param("sssss", $_SESSION['edit_id'], $_SESSION['member'], $_POST['load_amnt'], $cur_bal, $_SESSION['nameinsta']);
		if ($stmt->execute() === TRUE) {
			$stmtx = $conn->prepare("UPDATE members SET balance = (balance + ?) WHERE member_id = ?");
			$stmtx->bind_param("si", $_POST['load_amnt'], $_SESSION['edit_id']);
			$stmtx->execute();
			alert($_SERVER['REQUEST_URI'], "Loading successfull.");
		} else {
			echo $conn->error;
		}
	} else {
		alert($_SERVER['REQUEST_URI'], "Check your details");
	}
}
if (isset($_POST['update']) && $access->emp_edit == 1) {
	if (!empty($_POST['fname']) && !empty($_POST['mname']) && !empty($_POST['lname']) && !empty($_POST['emp_no']) && !empty($_POST['section'])) {
		$_POST['fname'] = strtoupper($_POST['fname']);
		$_POST['mname'] = strtoupper($_POST['mname']);
		$_POST['lname'] = strtoupper($_POST['lname']);
		$_POST['address'] = strtoupper($_POST['address']);
		$_POST['department'] = strtoupper($_POST['department']);
		$_POST['position'] = strtoupper($_POST['position']);
		$_POST['max_credit'] = strtoupper($_POST['max_credit']);

		$image = $_FILES['image'];
		if (!empty($image['name'])) {
			$imageFileType = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
			if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
				alert($_SERVER['REQUEST_URI'], 'Only JPG, JPEG, PNG, and GIF files are allowed.');
				exit;
			}
		}

		if (isset($_SESSION['edit_rfid']) && isset($_POST['rfid_no']) && $_SESSION['edit_rfid'] != $_POST['rfid_no']) {
			savelogs("RFID Edit", "From: " . $_SESSION['edit_rfid'] . ' to ' . $_POST['rfid_no']);
			$_POST['rfid_no'] = $_POST['rfid_no'];
		} else {
			$_POST['rfid_no'] = $_SESSION['edit_rfid'];
		}
		if (isset($_SESSION['edit_balance']) && $_SESSION['edit_balance'] != $_POST['balance']) {
			$pcname = gethostname();
			$transaction = "Load Edit";
			$transdetails = "From: " . $_SESSION['edit_balance'] . ' to ' . $_POST['balance'];
			$username = $_SESSION['usernameinsta'];
			$realname = $_SESSION['nameinsta'];
			$stmt = $conn->prepare("insert into audit_trail(username,realname,transaction,datetrans,transdetail,pcname) VALUES (?,?,?,now(),?,?)");
			$stmt->bind_param("sssss", $username, $realname, $transaction, $transdetails, $pcname);
			if ($stmt->execute() === TRUE) {
			} else {
				echo $conn->error;
			}
		}
		$stmt = $conn->prepare("UPDATE members SET max_credit = ?, balance = ?, type = ?, fname = ?, mname = ?, lname = ?, address = ?, department = ?, position = ?, emp_no = ?, section = ?, rfid_no = ?	where member_id = ?");
		$stmt->bind_param("ssssssssssssi", $_POST['max_credit'], $_POST['balance'], $_POST['type'], $_POST['fname'], $_POST['mname'], $_POST['lname'], $_POST['address'], $_POST['department'], $_POST['position'], $_POST['emp_no'], $_POST['section'], $_POST['rfid_no'], $_SESSION['edit_id']);
		if ($stmt->execute() === TRUE) {
			if (!empty($image['name'])) {
				$targetDir = 'images/photos/';
				if ($image['name'] == $existingImageName) {
					// Delete the existing image.
					unlink($targetDir . $_POST['rfid_no'] . '.jpg');
				}
				rename($image['tmp_name'], $targetDir . $_POST['rfid_no'] . '.jpg');
			}
			alert($_SERVER['REQUEST_URI'], "Update successfull.");
		}
	} else {
		alert($_SERVER['REQUEST_URI'], "Check your details");
	}
}
if (isset($_GET['delete']) && $access->emp_delete == 1) {
	$stmt = $conn->prepare("DELETE FROM `members`	where member_id = ?");
	$stmt->bind_param("i", $_GET['delete']);
	if ($stmt->execute() === TRUE) {
		alert('accounts/emp', "Delete successfull.");
	}
}
unset($_SESSION['edit_id']);
?>