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

<?php if (isset($_GET['print'])) {
	echo ' <div id = "reportg" style="background-color: white;">';
} ?>
<div class="content mt-3">
	<div class="animated fadeIn">
		<div class="row">
			<div class="col-<?php if (isset($_GET['print'])) {
								echo '12';
							} else {
								echo 'lg-12';
							} ?>">
				<div class="card">
					<form action="" method="get">
						<div class="card-body" id="tablex">
							<div class="row">
								<div class="col-6">
									<label>Max Credit</label>
									<input class="form-control form-control-md" type="text" placeholder="Enter max credit...." pattern="[.0-9,]*" name="max_credit" value="<?php echo $maxCredit; ?>" />
								</div>
								<div class="col-6">
									<label>Action</label>
									<button name="transact" type="submit" class="btn btn-info btn-block btn-md">
										<i class="fa fa-dot-circle-o"></i> <span>Update</span>
									</button>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<?php
if (isset($_GET['transact'])) {
	$stmt = $conn->prepare("UPDATE settings SET max_credit = ?");
	$stmt->bind_param("s", $_GET['max_credit']);
	if ($stmt->execute() === TRUE) {
		alert("max_credit", "Update successful.");
	}
}
?>