<?php
	if($access->level < 1){
		alert("/nbc", "No access");
	}
?>
<style type="text/css">
	.nbc-void-modal-backdrop {
		position: fixed;
		inset: 0;
		background: rgba(8, 16, 28, 0.55);
		display: none;
		align-items: center;
		justify-content: center;
		z-index: 9999;
		backdrop-filter: blur(2px);
	}
	.nbc-void-modal-backdrop.show {
		display: flex;
	}
	.nbc-void-modal {
		width: 92%;
		max-width: 420px;
		border-radius: 14px;
		background: linear-gradient(145deg, #ffffff, #f6f8fb);
		box-shadow: 0 18px 45px rgba(0, 0, 0, 0.28);
		overflow: hidden;
		animation: voidModalIn 140ms ease-out;
	}
	.nbc-void-modal-head {
		padding: 14px 16px 8px;
		font-size: 17px;
		font-weight: 700;
		color: #1f2d3d;
	}
	.nbc-void-modal-body {
		padding: 0 16px 16px;
		color: #405264;
		font-size: 14px;
	}
	.nbc-void-modal-actions {
		display: flex;
		gap: 10px;
		justify-content: flex-end;
		padding: 12px 16px 16px;
		background: #f2f5f9;
	}
	.nbc-void-btn {
		border: 0;
		border-radius: 8px;
		padding: 8px 14px;
		font-size: 13px;
		font-weight: 600;
		cursor: pointer;
	}
	.nbc-void-btn.cancel {
		background: #d8dee7;
		color: #314154;
	}
	.nbc-void-btn.confirm {
		background: linear-gradient(135deg, #d90429, #ef233c);
		color: #fff;
	}
	@keyframes voidModalIn {
		from {
			opacity: 0;
			transform: translateY(6px) scale(0.98);
		}
		to {
			opacity: 1;
			transform: translateY(0) scale(1);
		}
	}
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
				<h1><?php echo $title2; ?></h1>
			</div>
		</div>
	</div>
	<div class="col-sm-8">
		<div class="page-header float-right">
			<div class="page-title">
				<ol class="breadcrumb text-right">
					<li><a href="./">Dashboard</a></li>
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
				<form action="" method="get">
					<div class="card">
						<div class="card-header">
							<strong class="card-title"><?php echo $title; ?></strong>
						</div>
						<div class="card-body">
							<div class="row">
								<div class="col-4">
									<label>Date From</label>
									<input  <?php if(isset($_GET['date_fr'])){ echo ' value = "' . $_GET['date_fr'] . '" '; } ?> autocomplete = "off" name = "date_fr" type = "date" class = "input-sm form-control-sm form-control" aria-required = "true" aria-invalid = "false">
								</div>
								<div class="col-4">
									<label>Date To</label>
									<input  <?php if(isset($_GET['date_to'])){ echo ' value = "' . $_GET['date_to'] . '" '; } ?> autocomplete = "off" name = "date_to" type = "date" class = "input-sm form-control-sm form-control" aria-required = "true" aria-invalid = "false">
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
							<div class="row">
								<div class="col-12">
									<label>Search by receipt no</label>
									<input placeholder="Enter receipt no"  autocomplete = "off" name = "receipt_no" type = "text" class = "input-sm form-control-sm form-control" aria-required = "true" aria-invalid = "false">
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
					if(isset($_GET['submit'])){
						$date_fr_input = isset($_GET['date_fr']) ? trim($_GET['date_fr']) : "";
						$date_to_input = isset($_GET['date_to']) ? trim($_GET['date_to']) : "";
						$receipt_input = isset($_GET['receipt_no']) ? trim($_GET['receipt_no']) : "";

						if($date_fr_input === "" && $date_to_input === "" && $receipt_input === ""){
							echo '<h4 align = "center"><i><b> Please enter receipt no or date range. </b></i></h4>';
						}else{
							$date_fr = $date_fr_input;
							$date_to = $date_to_input;
							if ($date_fr === "" && $date_to !== "") {
								$date_fr = $date_to;
							}
							if ($date_to === "" && $date_fr !== "") {
								$date_to = $date_fr;
							}
						$shift_fr = " 05:00:00"; $shift_to = " 04:59:59";
						if ($date_fr === "" && $date_to === "") {
							$date_fr = date("Y-m-d");
							$date_to = date("Y-m-d");
						}
						if(isset($_GET['shift']) && $_GET['shift'] != ""){
							if($_GET['shift'] == 'Day'){
								$shift_fr = " 05:00:00";
								$shift_to = " 16:59:59";
							}else{
								$shift_fr = " 17:00:00";
								$shift_to = " 04:59:59";
								if($date_fr == $date_to){
									$shift_to = " 23:59:59";
								}
							}							
						}else{
							$date_to = date("Y-m-d", strtotime("+1day", strtotime($date_to)));
						}
						$where = "a.dttm BETWEEN '" . mysqli_real_escape_string($conn, $date_fr) . $shift_fr . "' and '" . mysqli_real_escape_string($conn, $date_to) . $shift_to . "' and";
						if ($receipt_input != "") {
							$where = "a.receipt = '" . mysqli_real_escape_string($conn, $receipt_input) . "' and";
						}
						$prod = "SELECT * FROM transactions as a LEFT JOIN members as b on a.rfid_no = b.rfid_no WHERE a.isreserve = 0 and ". $where ."  a.product_id NOT IN (1,2)  and a.active = 2";
						$prod = $conn->query($prod);
						if($prod->num_rows > 0){
					?>
					<div <?php if(isset($_GET['print'])){ ?> id="reportg" <?php } ?>>
						<div class="card">
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
											<th scope="col" width="20%">Action</th>
										</tr>
									</thead>
									<tbody>										
										<?php
											$total = 0;
											$last_receipt = "";
											$last_rfid = "";
											$return_url = urlencode($_SERVER['REQUEST_URI']);
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
													echo '<td>'.($last_receipt == $row->receipt && $last_rfid == $row->rfid_no ? '-' : '<a class="btn btn-sm btn-danger void-action-link" data-receipt="'.htmlspecialchars($row->receipt, ENT_QUOTES).'" href="void_transaction/void?id='.md5($row->receipt.'void').'&rfid='.md5($row->rfid_no.'void').'&receipt='.urlencode($row->receipt).'&card='.urlencode($row->rfid_no).'&return='.$return_url.'"> Void </a>') . '</td>';
												echo '</tr>';
												$last_rfid = $row->rfid_no;
												$last_receipt = $row->receipt;
											}
										?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				<?php }else{ echo '<h4 align = "center"><i><b> No record found </i></b></h4>'; } } }?>
			</div>
		</div>
	</div>
</div>

<div id="voidConfirmBackdrop" class="nbc-void-modal-backdrop" aria-hidden="true">
	<div class="nbc-void-modal" role="dialog" aria-modal="true" aria-labelledby="voidConfirmTitle">
		<div class="nbc-void-modal-head" id="voidConfirmTitle">Confirm Void</div>
		<div class="nbc-void-modal-body">
			<div id="voidConfirmText">You are about to void this transaction. Continue?</div>
		</div>
		<div class="nbc-void-modal-actions">
			<button type="button" id="voidCancelBtn" class="nbc-void-btn cancel">Cancel</button>
			<button type="button" id="voidConfirmBtn" class="nbc-void-btn confirm">Void Now</button>
		</div>
	</div>
</div>

<script type="text/javascript">
	(function() {
		var pendingVoidUrl = '';
		var backdrop = document.getElementById('voidConfirmBackdrop');
		var confirmBtn = document.getElementById('voidConfirmBtn');
		var cancelBtn = document.getElementById('voidCancelBtn');
		var confirmText = document.getElementById('voidConfirmText');

		if (!backdrop || !confirmBtn || !cancelBtn || !confirmText) {
			return;
		}

		function closeModal() {
			backdrop.classList.remove('show');
			backdrop.setAttribute('aria-hidden', 'true');
			pendingVoidUrl = '';
			confirmBtn.disabled = false;
			confirmBtn.textContent = 'Void Now';
		}

		document.addEventListener('click', function(e) {
			var link = e.target.closest('.void-action-link');
			if (link) {
				e.preventDefault();
				pendingVoidUrl = link.getAttribute('href') || '';
				var receipt = link.getAttribute('data-receipt') || '';
				if (receipt) {
					confirmText.textContent = 'You are about to void receipt ' + receipt + '. Continue?';
				} else {
					confirmText.textContent = 'You are about to void this transaction. Continue?';
				}
				backdrop.classList.add('show');
				backdrop.setAttribute('aria-hidden', 'false');
			}
			if (e.target === backdrop) {
				closeModal();
			}
		});

		cancelBtn.addEventListener('click', closeModal);

		document.addEventListener('keydown', function(e) {
			if (e.key === 'Escape' && backdrop.classList.contains('show')) {
				closeModal();
			}
		});

		confirmBtn.addEventListener('click', function() {
			if (!pendingVoidUrl) {
				closeModal();
				return;
			}
			confirmBtn.disabled = true;
			confirmBtn.textContent = 'Voiding...';
			window.location.href = pendingVoidUrl;
		});
	})();
</script>
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