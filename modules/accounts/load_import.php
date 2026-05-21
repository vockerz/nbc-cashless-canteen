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
						<h5 class="card-title">Choose CSV File</h5>
					</div>
					<div class="card-body">
						<form method="POST" action="" enctype="multipart/form-data" align="center">
							<div class="row">
								<div class="col-md-12" align="center">
									<input class="form form-control input-sm" type="file" accept=".csv" name="uploadedFile" required />
								</div>
							</div>
							<div class="row">
								<div class="col-md-12">
									<hr>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12"  align="center">
									<button type="submit" onclick = "return confirm('Are you sure?');" class="btn btn-sm btn-success center-block" name="upploadBtn"> Upload Excel </button>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php
if(isset($_POST['upploadBtn'])){
		$fileTmpPath = $_FILES['uploadedFile']['tmp_name'];
		$txt_file    = file_get_contents($fileTmpPath );
		$lines = array_map("rtrim", explode("\n", str_replace('"', '', $txt_file)));
		$count = 0;
		foreach($lines as &$e){
			$ex = explode(",", $e);
			if($e == 0){
				continue;
			}
			$ex0 = $ex[0];
			$stmt = $conn->prepare("UPDATE members SET balance = (balance + ?)	where emp_no = ?");
			$stmt->bind_param("si", $ex[1], $ex[0]);
			if($stmt->execute() === TRUE){
				$query = "SELECT * FROM `members` WHERE emp_no = '" . mysqli_real_escape_string($conn, $ex[0]) . "' ";
				if($conn->query($query)){
					$data = $conn->query($query)->fetch_object();
					$name = $data->lname . ', ' . $data->fname;
					$stmt = $conn->prepare("INSERT INTO loads (member_id, member_name, loads, user, run_bal) VALUES (?, ?, ?, ?, ?)");
					$stmt->bind_param("sssss", $data->member_id, $name, $ex[1], $_SESSION['nameinsta'], $data->balance);
					$stmt->execute();
					alert($_SERVER['REQUEST_URI'], "Load successfull.");
				}else{
				//	echo $conn->error;
				}
			}
		}
	}
?>