	<?php	
	if(isset($_GET['date_fr'])){
		include('config/conf.php');
		session_start();
	    $date_fr = mysqli_real_escape_string($conn, report_date($_GET['date_fr']));
	    $date_to = mysqli_real_escape_string($conn, report_date($_GET['date_to']));
        // The function header by sending raw excel
		header("Content-type: application/vnd-ms-excel");
	}
?>
<?php
	if (isset($_GET['view_delivery'])) { 
		// Defines the name of the export file "codelution-export.xls"
		header("Content-Disposition: attachment; filename=Deliveries Report (".date("Y-m-d h:i A") .").xls");
		header("Pragma: no-cache"); 
		header("Expires: 0");
?>
<style>
	.table .thead-dark th {
		color: #fff;
		background-color: #212529 !important;
		border-color: #32383e;
	}
</style>
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
<?php
	}
?>
<?php
    if(isset($_GET['trans_export']) && isset($_SESSION['insta_acc'])){
    	$shift_fr = " 05:00:00"; $shift_to = " 04:59:59";
		if(isset($_GET['shift']) && $_GET['shift'] != ""){
			if($_GET['shift'] == 'Day'){
				$shift_fr = " 05:00:00";
				$shift_to = " 16:59:59";
			}else{
				$shift_fr = " 17:00:00";
				$shift_to = " 04:59:59";
				if($_GET['date_fr'] == $_GET['date_to']){
					$shift_to = " 23:59:59";
				}
			}							
		}else{
		//	$_GET['date_to'] = date("Y-m-d", strtotime("+1day", strtotime($_GET['date_to'])));
		}
		if(isset($_GET['agency'])){
			$prod = "SELECT * FROM transactions as a LEFT JOIN members as b on a.rfid_no = b.rfid_no WHERE b.type = 'Agency' and a.isload = 1 and a.dttm BETWEEN '" . $date_fr . $shift_fr . "' and '" . $date_to . $shift_to . "' and  a.product_id NOT IN (1,2)  and a.active = 2 GROUP BY a.transaction_id";
    		$agency = "[ AGENCY ]";
    	}else{
			$prod = "SELECT * FROM transactions as a LEFT JOIN members as b on a.rfid_no = b.rfid_no WHERE a.isload = 0 and a.dttm BETWEEN '" . $date_fr . $shift_fr . "' and '" . $date_to . $shift_to . "' and  a.product_id NOT IN (1,2)  and a.active = 2 GROUP BY a.transaction_id";
    		$agency = "Addt'l";
    	}
    	if(isset($_GET['load_rep'])){
			$prod = "SELECT * FROM transactions as a LEFT JOIN members as b on a.rfid_no = b.rfid_no WHERE b.type = 'Direct' and a.isload = 1 and a.dttm BETWEEN '" . $date_fr . $shift_fr . "' and '" . $date_to . $shift_to . "' and  a.product_id NOT IN (1,2)  and a.active = 2 GROUP BY a.transaction_id";
    		$agency = "LOAD";
    	}
    	// Defines the name of the export file "codelution-export.xls"
		header("Content-Disposition: attachment; filename=".$agency." Transactions Report Meal (".date("Y-m-d h:i A") .".xls");
		header("Pragma: no-cache"); 
		header("Expires: 0");
    	$prod = $conn->query($prod);
		if($prod->num_rows > 0){
?>	
		<div align="center" id = "head">
			<b style="font-size: 18px;">NBC (Philippines) Car Technology</b><br>
			<b style="font-size: 14px;">Lot 9-B FPIP II Special Economic Zone</b><br>
			<b style="font-size: 14px;">Sto. Tomas, Batangas 4234</b><br><br>
		</div>
		<br>
		<div class="card">			
			<div class="card-header">
				<h4 class="text-CENTER spacing"><center><b><?php echo $agency; ?> TRANSACTIONS REPORT From <?php echo ddate($_GET['date_fr']) . ' - ' . ddate($_GET['date_to']); ?></b></center></h4>
			</div>
			<div class="card-body">
				<table class="table" border="1px solid black">
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
			</div>
		</div>
<?php }  } ?>

<?php
    if(isset($_GET['trans2_export']) && isset($_SESSION['insta_acc'])){
		// Defines the name of the export file "codelution-export.xls"
		header("Content-Disposition: attachment; filename=Transaction Report (".date("Y-m-d h:i A") .".xls");
		header("Pragma: no-cache"); 
		header("Expires: 0");
		$where = "";
		$shift_fr = " 05:00:00"; $shift_to = " 04:59:59";
		if(isset($_GET['shift']) && $_GET['shift'] != ""){
			if($_GET['shift'] == 'Day'){
				$shift_fr = " 05:00:00";
				$shift_to = " 16:59:59";
			}else{
				$shift_fr = " 17:00:00";
				$shift_to = " 04:59:59";
				if($_GET['date_fr'] == $_GET['date_to']){
					$shift_to = " 23:59:59";
				}
			}							
		}else{
		//	$_GET['date_to'] = date("Y-m-d", strtotime("+1day", strtotime($_GET['date_to'])));
		}
		if(isset($_GET['product']) && $_GET['product'] != ""){
			$where = " and product_id in (" . mysqli_real_escape_string($conn, $_GET['product']) . ") ";
		}
		$prod = "SELECT * FROM transactions as a left join members as b ON a.rfid_no = b.rfid_no WHERE a.dttm BETWEEN '" . $date_fr . $shift_fr . "' and '" . $date_to . $shift_to . "' and a.active = 2 " . $where . " GROUP BY a.transaction_id  ORDER BY a.dttm";
		$prod = $conn->query($prod);
		if($prod->num_rows > 0){
?>	
		<div align="center" id = "head">
			<b style="font-size: 18px;">NBC (Philippines) Car Technology</b><br>
			<b style="font-size: 14px;">Lot 9-B FPIP II Special Economic Zone</b><br>
			<b style="font-size: 14px;">Sto. Tomas, Batangas 4234</b><br><br>
		</div>
		<br>
		<div class="card">			
			<div class="card-header">
				<h4 class="text-CENTER spacing"><center><b>TRANSACTIONS REPORT From <?php echo ddate($_GET['date_fr']) . ' - ' . ddate($_GET['date_to']); ?></b></center></h4>
			</div>
			<div class="card-body">
				<table class="table" border = "1px solid black;">
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
			</div>
		</div>
<?php }  } ?>

<?php
    if(isset($_GET['free_export']) && isset($_SESSION['insta_acc'])){
		// Defines the name of the export file "codelution-export.xls"
		header("Content-Disposition: attachment; filename=Free Meal Report (".date("Y-m-d h:i A") .".xls");
		header("Pragma: no-cache"); 
		header("Expires: 0");
		$shift_fr = " 05:00:00"; $shift_to = " 04:59:59";
		if(isset($_GET['shift']) && $_GET['shift'] != ""){
			if($_GET['shift'] == 'Day'){
				$shift_fr = " 05:00:00";
				$shift_to = " 16:59:59";
			}else{
				$shift_fr = " 17:00:00";
				$shift_to = " 04:59:59";
				if($_GET['date_fr'] == $_GET['date_to']){
					$shift_to = " 23:59:59";
				}
			}							
		}else{
		//	$_GET['date_to'] = date("Y-m-d", strtotime("+1day", strtotime($_GET['date_to'])));
		}
		$prod = "SELECT * FROM transactions as a LEFT JOIN members as b on a.rfid_no = b.rfid_no  WHERE a.dttm  BETWEEN '" . $date_fr . $shift_fr . "' and '" . $date_to . $shift_to . "' and a.product_id in (1,2) and a.active = 2  GROUP BY a.transaction_id";
		$prod = $conn->query($prod);
		if($prod->num_rows > 0){
?>
		
		<div class="card">			
			<div class="card-header">
					<div align="center" id = "head">
					<b style="font-size: 18px;">NBC (Philippines) Car Technology</b><br>
					<b style="font-size: 14px;">Lot 9-B FPIP II Special Economic Zone</b><br>
					<b style="font-size: 14px;">Sto. Tomas, Batangas 4234</b><br><br>
				</div>
				<br>
				<h4 class="text-CENTER spacing"><center><b>FREE MEAL TRANSACTIONS REPORT From <?php echo ddate($_GET['date_fr']) . ' - ' . ddate($_GET['date_to']); ?></b></center></h4>
			</div>
			<div class="card-body">
				<table class="table" border="1px solid black">
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
			</div>
		</div>
		<?php }  } ?>

<?php
	echo '
		<script type = "text/javascript"> 
			window.onload = function() {						 
				setTimeout(function() { alert("Exported");  window.close(); window.location.href = "reports"; }, 500); 
			};
		</script>';	
?>
