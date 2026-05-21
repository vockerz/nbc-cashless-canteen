<?php
	if($access->level < 1){
		alert("/nbc", "No access");
	}
?>
<style type="text/css">
	@media print {
		body{
			background-color: white;
		}
		#reportg, #reportg * {
			visibility: visible;
		}
		#fees label{
			font-size: 12px !important;
		}
		label{
			font-size: 12px !important;
		}
		#fees div{		
			margin-top: -10px !important;
		}
		p{
			position: absolute;
			z-index: 999;
			left: 2%;
			font-size: 12px !important;
			top: 0%;
			color: black !important;
		}
		#imgx, #pbot{
			position: absolute;
			z-index: 999;
			right: 0%;
			font-size: 12px !important;
			bottom: 0%;
			color: black !important;
		}
		table th, table td{
			font-size: 10px !important;
			padding: 3px !important;
		}
		#header{
			font-size: 12px !important;
		}
		.card{
			width: 99%;
		}
		#head{
			margin-top: -140px;
		}
	}
</style>
<div class="breadcrumbs">
	<div class="col-sm-4">
		<div class="page-header float-left">
			<div class="page-title">
				<h1><?php echo $xx . $title2; ?></h1>
			</div>
		</div>
	</div>
	<div class="col-sm-8">
		<div class="page-header float-right">
			<div class="page-title">
				<ol class="breadcrumb text-right">
					<li><a href="./">Dashboard</a></li>
					<li><a href="<?php echo $_GET['module'];?>"><?php echo $xx; ?></a></li>
					<li class="active"><?php echo $title;?></li>
				</ol>
			</div>
		</div>
	</div>
</div>
<div class="content mt-3">
	<div class="animated fadeIn">
		<div class="row">
			<div class="col-lg-12">
				<form action="reports/package_rep" method="get">
					<div class="card">
						<div class="card-header">
							<strong class="card-title"><?php echo $title; ?></strong>
						</div>
						<div class="card-body">
							<div class="row">
								<div class="col-4">
									<label>Date From</label>
									<input required <?php if(isset($_GET['date_fr'])){ echo ' value = "' . $_GET['date_fr'] . '" '; } ?> autocomplete = "off" name = "date_fr" type = "date" class = "input-sm form-control-sm form-control" aria-required = "true" aria-invalid = "false">
								</div>
								<div class="col-4">
									<label>Date To</label>
									<input required <?php if(isset($_GET['date_to'])){ echo ' value = "' . $_GET['date_to'] . '" '; } ?> autocomplete = "off" name = "date_to" type = "date" class = "input-sm form-control-sm form-control" aria-required = "true" aria-invalid = "false">
								</div>
								<div class="col-4">
									<label>Shift <font color = "red"> * </font></label>
									<select class="form-control form-control-sm input-sm" name = "shift">
										<option value=""> All </option>
										<option <?php if(isset($_GET['shift']) && $_GET['shift'] == 'Day'){ echo ' selected '; } ?> value="Day">Day</option>
										<option <?php if(isset($_GET['shift']) && $_GET['shift'] == 'Night'){ echo ' selected '; } ?> value="Night">Night</option>
									</select>
								</div>
							</div>
						</div>
						<div class="card-footer">
							<button type = "submit" onclick = "return confirm('Are you sure?');" name = "submit" class = "btn btn-info btn-block btn-sm">
								<i class = "fa fa-dot-circle-o"></i>&nbsp;<span>Generate</span>
							</button>
						</div>
					</div>
				</form>
				<?php
					$close = "";
					$totalPages = 1;
					$page = 1;
					$perPage = 50;
					if(isset($_GET['date_fr']) && isset($_GET['date_to'])){
						$date_fr = mysqli_real_escape_string($conn, report_date($_GET['date_fr']));
						$date_to = mysqli_real_escape_string($conn, report_date($_GET['date_to']));
						$shift_fr = " 00:00:00"; $shift_to = " 23:59:59";
						if(isset($_GET['shift']) && $_GET['shift'] != ""){
							if($_GET['shift'] == 'Day'){
								$shift_fr = " 05:00:00";
								$shift_to = " 16:59:59";
							}else{
								$shift_fr = " 17:00:00";
								$shift_to = " 04:59:59";
								$date_to = date("Y-m-d", strtotime("+1 day", strtotime($date_to)));
							}							
						}
						$whereSql = " FROM transactions as a LEFT JOIN members as b on a.rfid_no = b.rfid_no WHERE a.dttm BETWEEN '" . $date_fr . $shift_fr . "' and '" . $date_to . $shift_to . "' and a.product_id in (1,2) and a.active IN (0,2) ";
						$orderSql = " ORDER BY a.dttm DESC, a.transaction_id DESC ";
						if(isset($_GET['print'])){
							$prodQuery = "SELECT *" . $whereSql . $orderSql;
							$prod = $conn->query($prodQuery);
						}else{
							$countQuery = "SELECT COUNT(*) as total" . $whereSql;
							$countRes = $conn->query($countQuery);
							$totalRows = 0;
							if($countRes){
								$countRow = $countRes->fetch_object();
								$totalRows = (int)($countRow->total ?? 0);
							}
							$totalPages = max(1, (int)ceil($totalRows / $perPage));
							$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
							if($page > $totalPages){
								$page = $totalPages;
							}
							$offset = ($page - 1) * $perPage;
							$prodQuery = "SELECT *" . $whereSql . $orderSql . " LIMIT " . intval($offset) . ", " . intval($perPage);
							$prod = $conn->query($prodQuery);
						}
						if($prod->num_rows > 0){
							$close = "window.close();";		
				?>
					<div <?php if(isset($_GET['print'])){ ?> id="reportg" <?php } ?>>
						<img <?php if(isset($_GET['print'])){ ?> style = "position: absolute; margin-top: -140px; margin-left: 100px;" src="<?php echo "http://$_SERVER[HTTP_HOST]/".$pagename;?>/images/logo.jpg?<?php echo rand(1,100).rand(1,1000);?>" height = "100px" width = "100px" <?php } ?>>
						<div align="center" id = "head">
							<b style="font-size: 18px;">NBC (Philippines) Car Technology</b><br>
							<b style="font-size: 14px;">Lot 9-B FPIP II Special Economic Zone</b><br>
							<b style="font-size: 14px;">Sto. Tomas, Batangas 4234</b><br><br>
						</div>
						<br>
						<div class="card">			
							<div class="card-header">
								<h4 class="text-CENTER spacing"><center><b>FREE MEAL TRANSACTIONS REPORT From <?php echo ddate($_GET['date_fr']) . ' ' . $shift_fr . ' - ' . ddate($_GET['date_to']) . ' ' . $shift_to; ?></b></center></h4>
								<a id = "backs" class="btn btn-success btn-sm pull-right" target = "_blank" href="reports/package_rep?print&date_fr=<?php echo $_GET['date_fr'];?>&date_to=<?php echo $_GET['date_to'];?>&free_export&shift=<?php echo $_GET['shift'];?>"  style="margin-right: 5px;"><span class="fa fa-print"></span> Print </a>
								<a id = "backs" class="btn btn-warning btn-sm pull-right" target = "_blank" href="export.php?date_fr=<?php echo $_GET['date_fr'];?>&date_to=<?php echo $_GET['date_to'];?>&free_export&shift=<?php echo $_GET['shift'];?>"  style="margin-right: 5px;"><span class="fa fa-save"></span> Export </a>
							</div>
							<div class="card-body">
								<table class="table">
									<thead  <?php if(!isset($_GET['print'])){ ?> class="thead-dark" <?php } ?>>
										<tr>
											<th scope="col" width="20%">Date</th>
											<th scope="col" width="15%">Receipt</th>
											<th scope="col" width="15%">Emp. No.</th>
											<th scope="col" width="30%">Name</th>
											<th scope="col" width="15%">Emp. Type</th>
											<th scope="col" width="20%">Department</th>
											<th scope="col" width="20%">Type</th>	
											<th scope="col" width="20%">Trans. Type</th>	
											<th scope="col" width="10%">Qty</th>
											<th scope="col" width="20%">Amount</th>
										</tr>
									</thead>
									<tbody>
										<?php
											$total = 0;
											while ($row = $prod->fetch_object()) {
												$trans_type = "";
												if($row->product_id <= 2){
													$trans_type = "FREE MEAL";
												}elseif($row->product_id > 2 && $row->type == 'Direct' && $row->isload == 0){
													$trans_type = "SD";
												}elseif($row->isload == 1 && $row->type == 'Direct'){
													$trans_type = "NBCLoad";
												}elseif($row->isload == 1 && $row->type == 'Agency'){
													$trans_type = "AgencyLoad";
												}
												echo '<tr>';
													echo '<td>' . date("m/d/Y h:i A", strtotime($row->dttm)) . '</td>';
													echo '<td>' . $row->receipt . '</td>';
													echo '<td>' . $row->emp_no . '</td>';
													echo '<td>' . $row->member_name . '</td>';
													echo '<td>' . $row->type . '</td>';
													echo '<td>' . $row->department . '</td>';
													echo '<td>' . $row->product_name . '</td>';
													echo '<td>' . $trans_type . '</td>';
													echo '<td>' . $row->qty . '</td>';
													echo '<td>' . number_format($row->amount,2) . '</td>';
												echo '</tr>';
											}
										?>
									</tbody>
								</table>
								<?php if(!isset($_GET['print']) && $totalPages > 1){
									$shiftVal = isset($_GET['shift']) ? $_GET['shift'] : '';
									$basePageUrl = 'reports/package_rep?date_fr=' . urlencode($_GET['date_fr']) . '&date_to=' . urlencode($_GET['date_to']) . '&shift=' . urlencode($shiftVal) . '&submit=';
									echo '<div class="row" style="margin-top:10px;">';
									echo '<div class="col-12 text-center">';
									echo '<ul class="pagination report-pagination justify-content-center">';
									if($page > 1){
										echo '<li class="page-item"><a class="page-link" href="' . $basePageUrl . '&page=1">&laquo;&laquo;</a></li>';
										echo '<li class="page-item"><a class="page-link" href="' . $basePageUrl . '&page=' . ($page - 1) . '">&laquo;</a></li>';
									}else{
										echo '<li class="page-item disabled"><span class="page-link">&laquo;&laquo;</span></li>';
										echo '<li class="page-item disabled"><span class="page-link">&laquo;</span></li>';
									}
									$startPage = max(1, $page - 2);
									$endPage = min($totalPages, $page + 2);
									for($p = $startPage; $p <= $endPage; $p++){
										if($p == $page){
											echo '<li class="page-item active"><span class="page-link">' . $p . '</span></li>';
										}else{
											echo '<li class="page-item"><a class="page-link" href="' . $basePageUrl . '&page=' . $p . '">' . $p . '</a></li>';
										}
									}
									if($page < $totalPages){
										echo '<li class="page-item"><a class="page-link" href="' . $basePageUrl . '&page=' . ($page + 1) . '">&raquo;</a></li>';
										echo '<li class="page-item"><a class="page-link" href="' . $basePageUrl . '&page=' . $totalPages . '">&raquo;&raquo;</a></li>';
									}else{
										echo '<li class="page-item disabled"><span class="page-link">&raquo;</span></li>';
										echo '<li class="page-item disabled"><span class="page-link">&raquo;&raquo;</span></li>';
									}
									echo '</ul>';
									echo '<small class="report-page-count">Page ' . $page . ' of ' . $totalPages . '</small>';
									echo '</div></div>';
								} ?>
							</div>
						</div>
					</div>
				<?php }else{
						echo '<h4 align = "center"><i><b>No record found for selected date/shift.</b></i></h4>';
				} }
				if(isset($_GET['date_fr']) && isset($_GET['date_to']) && !isset($prod)){
					// form not yet submitted - no action needed
				}
				?>
			</div>
		</div>
	</div>
</div>
<?php
	if(isset($_GET['print'])){
		echo '
		<script type = "text/javascript"> 
			window.onload = function() {						 
				setTimeout(function() { window.print(); '.$close.' window.location.href = "reports"; }, 500); 
			};
		</script>';			 
	}

?>
