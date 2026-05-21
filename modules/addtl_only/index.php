<?php
	$butname = "Print & Save";
	$onclick = "";
	$transact = "";
	$total_amount = "0";
	$rfid_no = isset($_GET['86d178a053b97f10a65771b2c1ff9621']) ? trim($_GET['86d178a053b97f10a65771b2c1ff9621']) : '';
	$barcode = isset($_GET['barcode']) ? trim($_GET['barcode']) : '';
	$barcode = preg_replace('/^\][A-Za-z][0-9]/', '', $barcode); // strip AIM symbology prefix
	$barcode = preg_replace('/[\x00-\x1F\x7F]/', '', $barcode); // strip scanner control chars
	$barcode = trim($barcode);
	$qty = isset($_GET['qty']) ? trim((string)$_GET['qty']) : '';
	$user = trim((string)($_SESSION['nameinsta'] ?? ''));
	$user_alt = trim((string)($_SESSION['usernameinsta'] ?? ''));
	if ($user === '' && $user_alt !== '') {
		$user = $user_alt;
	}
	if ($user_alt === '' || strcasecmp($user_alt, $user) === 0) {
		$user_alt = $user;
	}
	// Fetch global max_credit from settings (cached in session)
	if (!isset($_SESSION['sys_max_credit'])) {
		$_settingsRow = $conn->query("SELECT max_credit FROM settings LIMIT 1");
		$_settingsObj = $_settingsRow ? $_settingsRow->fetch_object() : null;
		$_SESSION['sys_max_credit'] = $_settingsObj ? (float)($_settingsObj->max_credit ?? 1000) : 1000;
	}
	$maxCredit = $_SESSION['sys_max_credit'];
	$row2 = null; // initialise so barcode block never hits "undefined variable"
?>
<?php if(isset($_GET['print'])){ ?>
<style type="text/css">
	#fees label{
		font-size: 12px !important;
	}
	@page {
			size: Letter portrait;
	}
	label{
		font-size: 19px !important;
	}
	#fees div{		
		margin-top: -10px !important;
	}
	u h4{
		font-size: 22px !important;
	}
	tr td, tr th{
		font-size: 19px !important;
	}
	p{
		position: absolute;
		z-index: 999;
		left: 15%;
		font-size: 19px !important;
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

</style>
<?php } ?>
<div class="breadcrumbs">
	<div class="col-sm-4">
		<div class="page-header float-left">
			<div class="page-title">
				<h1><?php echo $title ;?></h1>
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

<?php
	if(isset($_GET['del']) && isset($_GET['product_id'])){
		$stmt2 = $conn->prepare("DELETE FROM transactions WHERE user = ? and product_id = ? and rfid_no is null and member_name is null and receipt is null and dttm like '" . date("Y-m-d") . "%' ");
		$stmt2->bind_param("si", $_SESSION['nameinsta'], $_GET['product_id']);	
		if( $stmt2->execute() ){
			alert("addtl_only?86d178a053b97f10a65771b2c1ff9621=".$_GET['86d178a053b97f10a65771b2c1ff9621']."&transact=&checkout", "");
		}
	}
?>

<?php if(isset($_GET['print'])){ echo ' <div id = "reportg" style="background-color: white;">'; } ?>
<div class="content mt-3">
	<div class="animated fadeIn">
		<div class="row">
			<div class="col-<?php if(isset($_GET['print'])){ echo '12'; }else{ echo 'lg-12'; } ?>">
				<div class="card">
					<form action="" method="get">
						<div class="card-body" id ="tablex">
							<div class="row">
								<?php if(!isset($_GET['print'])){ ?>
									<div class="col-4">
										<u><h4 class="text-left spacing">EMPLOYEE DETAILS</h4></u> 
										<div class="row">
											<div class="col-12">
												<label>RFID No.</label>
												<input id="rfid-input" <?php if($rfid_no !== ''){echo ' value="'.htmlspecialchars($rfid_no,ENT_QUOTES).'" readonly '; }else{ echo 'autofocus'; } ?> autocomplete="off" placeholder="Tap Card....." name="86d178a053b97f10a65771b2c1ff9621" type="text" class="input-sm form-control-sm form-control" aria-required="true" aria-invalid="false">
											</div>
										</div>
										<div id="employee-panel-content">
										<?php
										$_isInitialOpen = ($rfid_no === '' && $barcode === '' && !isset($_GET['checkout']) && !isset($_GET['transact']) && !isset($_GET['del']) && !isset($_GET['print']));
										if($rfid_no === '' && !$_isInitialOpen){
											$stmtx = $conn->prepare("UPDATE transactions SET active = 0 WHERE user = ? and rfid_no IS NULL and active = 1");
											$stmtx->bind_param("s", $_SESSION['nameinsta']);
											$stmtx->execute();
										}
										if($rfid_no === ''){
												$_SESSION['total'] = 0;
											}
											if($rfid_no !== ''){
												$stmt = $conn->prepare("SELECT rfid_no, lname, fname, mname, balance, type, address, department, position, green_mark, max_credit FROM members WHERE rfid_no = ?");
												$stmt->bind_param("s", $rfid_no);
											}
											if($rfid_no !== '' && $stmt->execute() === TRUE){
												$total_qty = "0";
												$result = $stmt->get_result();
												if($result->num_rows > 0){
													if(date("H") <= 13){
														$date = date("Y-m-d", strtotime("-1 day"));
														$hr = date("H:i:s", strtotime("-12 hrs"));
														$date = $date. ' ' . $hr;	
													}else{														
														$date = date("Y-m-d H:i:s", strtotime("-12 hrs"));
													}
													/*}*/
													$row = $result->fetch_object();
													$transact = "go"; 
													$_SESSION['receipt']['rfid_no'] = $row->rfid_no;
													$_SESSION['type'] = $row->type;
													$_SESSION['user_bal'] = $row->balance;
													$_SESSION['receipt']['name'] = $row->lname. ', ' . $row->fname . ', ' . $row->mname;
													$filename = "";
													if(file_exists("images/photos/".$row->rfid_no.".jpg")){
														$filename = "images/photos/".$row->rfid_no.".jpg";
													}
										?>
											<div class="row">
												<div class="col-12"><br>
													<table class="table" <?= $row->green_mark > 0 ? 'style="background-color: #00FF00;"' : '' ?>>
														<tbody>
															<?php if($filename){ ?>
																<tr>
																	<td align = "center" colspan="2"><img height = "200" src="<?php echo $filename;?>"></td>
																</tr>
															<?php } ?>
															<tr>
																<td style="align right">Balance: </td>
																<td><b><?php echo number_format($row->balance);?></b></td>
															</tr>
															<?php
																if($_SESSION['type'] == 'Direct' && $_SESSION['user_bal'] > 0){
																	$checkbox = "";
																	if(isset($_GET['isload'])){
																		$checkbox = "checked";
																	}
																	echo '<tr>';
																		echo '<td>Use Load?</td>';
																		echo '<td><input id = "isload" name = "isload" '.$checkbox.' value = "1" type = "checkbox" class = "form-control-sm form-control"/></td>';
																	echo '</tr>';
																}
															?>
															<tr>
																<td style="align right">Name: </td>
																<td><b><?php echo $row->lname. ', ' . $row->fname . ', ' . $row->mname; ?></b></td>
															</tr>
															<tr>
																<td style="align right">Address: </td>
																<td><b><?php echo $row->address; ?></b></td>
															</tr>
															<tr>
																<td style="align right">Department: </td>
																<td><b><?php echo $row->department; ?></b></td>
															</tr>
															<tr>
																<td style="align right">Position: </td>
																<td><b><?php echo $row->position; ?></b></td>
															</tr>
															<tr>
																<td style="align right">For Deduction: </td>
																<td>
																	<b>
																		<?php
																			if(date("Y-m-d") >= date("Y-m-01") && date("Y-m-d") <= date("Y-m-15")){
																				$date = " and dttm BETWEEN '" .  date("Y-m-01") . " 00:00:00' and '" . date("Y-m-15") . " 23:59:59' ";
																			}elseif(date("Y-m-d") >= date("Y-m-16")){
																				$date = " and dttm BETWEEN '" . date("Y-m-16") . " 00:00:00' and '" . date("Y-m-t") . " 23:59:59' ";
																			}
																			$stmt2 = $conn->prepare("SELECT sum(amount) as amt FROM transactions  WHERE active = 2 and isload = 0 and rfid_no = ?" . $date);
$stmt2->bind_param("s", $rfid_no);
																			$stmt2->execute();

																			$result2 = $stmt2->get_result();
																			$row2 = $result2->fetch_object();
																			$maxCredit = $row->max_credit > 0 ? (float)$row->max_credit : (float)$maxCredit;
																			$_SESSION['max_credit'] = $maxCredit;
																			if ($row2->amt > $maxCredit) {
																				//alert("addtl_only", "Already maxed allowable amount: " . number_format(str_replace(",", "", $maxCredit), 2));
																			}
																			echo str_replace(".00", "", number_format($row2->amt,2)); 

																		?>
																	</b>
																</td>
															</tr>
														</tbody>
													</table>
												</div>
											</div>
										<?php	} else{ if($rfid_no !== ''){ alert("addtl_only", "No record found"); }	} }	?>
								</div><!-- /employee-panel-content -->
									</div><!-- /col-4 -->
								<div class="col-3">
									<u><h4 class="text-left spacing">ITEM DETAILS</h4></u> 
									<div class="row">
										<div class="col-8">
											<label>Enter Barcode</label>
											<input autofocus autocomplete = "off" pattern="[.0-9,]*" placeholder = "Scan Barcode....." name="barcode" type="text" class="input-sm form-control-sm form-control" aria-required="true" aria-invalid="false">											
										</div>
										<div class="col-4">
											<label>Qty</label>
											<input onclick="this.select();" <?php if(isset($_GET['qty'])){ echo ' value = "' . $_GET['qty'] . '"'; }else{ echo ' value = "1" '; } ?> autofocus autocomplete = "off" required pattern="[0-9,]*" placeholder = "Quantity....." name="qty" type="text" class="input-sm form-control-sm form-control" aria-required="true" aria-invalid="false">
										</div>
									</div>
								</div>
								<?php } ?>	
								<div class="col-<?php if(isset($_GET['print'])){ echo 12 ; }else{ echo 5; } ?>">
									<u><h4 class="text-left spacing"><?php if(isset($_GET['print'])){ echo 'Transaction Receipt'; }else{ echo 'CART DETAILS'; } ?></h4></u>
									<?php if(isset($_GET['print'])){ ?>
											<div class="row">
												<div class="col-12">
													<table>
														<tbody>
															<tr align="left">
																<td>Receipt No.:&nbsp;&nbsp;</td>
																<td><b><span id = "receipt"></span></b></td>
															</tr>
															<tr align="left">
																<td>Name:&nbsp;&nbsp;</td>
																<td><span id = "name"></span></td>
															</tr>
															<tr align="left">
																<td>Date:&nbsp;&nbsp;</td>
																<td><span id = "date"></span></td>
															</tr>
														</tbody>
													</table>
												</div>
											</div>
									<?php }	?>
									<div class="row">										
										<div class="col-12">
											<table class="table">
												<thead <?php if(!isset($_GET['print'])){ ?> class="thead-dark" <?php } ?> align="center">
													<tr>
														<th>Act</th>
														<th>Item</th>
														<th>Price</th>
														<th>Qty</th>
														<th>Total</th>
													</tr>
												</thead>
												<tbody id = "cart">
													<?php
														$isInitialOpen = (!isset($_GET['print']) && $rfid_no === '' && $barcode === '' && !isset($_GET['checkout']) && !isset($_GET['transact']) && !isset($_GET['del']));
														if(!$isInitialOpen){
														if(isset($_GET['print'])){
															$stmt = $conn->prepare("SELECT product_id, MAX(product_name) as product_name, MAX(price) as price, sum(qty) as xqty, sum(amount) as xamount, MAX(member_name) as member_name, MAX(dttm) as dttm, MAX(receipt) as receipt FROM transactions WHERE (TRIM(user) = TRIM(?) OR TRIM(user) = TRIM(?)) and active = 2 and receipt = ? GROUP BY product_id");
															$stmt->bind_param("sss", $user, $user_alt, $_GET['print']);		
														}else{
															$stmt = $conn->prepare("SELECT product_id, MAX(product_name) as product_name, MAX(price) as price, sum(qty) as xqty, sum(amount) as xamount, MAX(member_name) as member_name, MAX(dttm) as dttm, MAX(receipt) as receipt FROM transactions WHERE (TRIM(user) = TRIM(?) OR TRIM(user) = TRIM(?)) and active = 1 GROUP BY product_id");
															$stmt->bind_param("ss", $user, $user_alt);													
														}
														$_SESSION['items'] = [];
														$name = '';
														$date = '';
														$receipt = '';
														if($stmt->execute() === TRUE){
															$total_qty = "0";
															$result = $stmt->get_result();
															if($result->num_rows > 0){
																while($row = $result->fetch_object()){
																	$_SESSION['items'][] = $row;
																		$name = $row->member_name;
																		$date = ddate($row->dttm);
																		$receipt = $row->receipt;
																	
																	echo '<tr>';
																		echo '<td><a href="#" data-pid="'.$row->product_id.'" class="btn btn-sm btn-danger del-item"><i class="fa fa-times-circle"></i></a></td>';
																		echo '<td>' . $row->product_name . '</td>';
																		echo '<td>' . str_replace(".00", "", number_format($row->price,2)) . '</td>';
																		echo '<td>' . str_replace(".00", "", number_format($row->xqty,2)) . '</td>';
																		echo '<td>' . number_format($row->xamount,2) . '</td>';
																	echo '</tr>';
																	$total_qty += $row->xqty;
																	$total_amount += $row->xamount;
																}
															$_SESSION['total'] = $total_amount;
													?>
													<tr>
														<td></td>
														<td></td>
														<td><b><i>TOTAL</b></i></td>
														<td><b><i><?php echo str_replace(".00", "", number_format($total_qty,2)) ;?></b></i></td>
														<td><b><i><?php echo number_format($total_amount,2);?></b></i></td>
													</tr>
													<?php	
															}else{
																//if(isset($_GET['checkout'])){
																//	alert("addtl_only","Insert product.");
																//}
															}
															if(isset($_GET['print'])){
																echo '
																	<script type = "text/javascript">
																			$(document).ready(function(){
																				$("#name").text("'.$name.'");  
																				$("#date").text("'.$date.'");  
																				$("#receipt").text("'.$receipt.'");  
																			});
																	</script>';
															}
														}
														}
													?>
												</tbody>
											</table>
											<!--<div class="sufee-alert alert with-close alert-primary alert-dismissible fade show">
												<span class="badge badge-pill badge-primary">Success</span>
												Transaction has been saved.
											</div>-->
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="card-footer" <?php if(isset($_GET['print'])){ $transact = "go"; echo ' style = "display: none"; '; } ?>>
							<a <?php if(!isset($_GET['86d178a053b97f10a65771b2c1ff9621']) || isset($_GET['checkout'])){ ?> style="display: none;" <?php } ?> href="addtl_only?checkout" class="btn btn-info btn-block btn-sm"><i class="fa fa-dot-circle-o"></i> Checkout </a>
							<?php //if($transact == "go"){ ?>								
								<a href = "addtl_only?86d178a053b97f10a65771b2c1ff9621=<?php echo $rfid_no;?>&barcode=&qty=&transact=go&checkout=<?php echo (isset($lunch) && $lunch != '' ? '&lunch=1' : '');?><?php echo (isset($breakfast) && $breakfast != '' ? '&breakfast=1' : '');?><?php echo (isset($_GET['isload']) && $_GET['isload'] == '1' ? '&isload=1' : '');?>" id = "sub_but" onclick="return window.saveAddtl ? window.saveAddtl(false) : true;" <?php if(!isset($_GET['checkout']) || !isset($_GET['86d178a053b97f10a65771b2c1ff9621'])){ ?> style="display: none;" name = "transact" <?php } ?> type="submit" <?php echo $onclick; ?>  class="btn btn-info btn-block btn-sm">
									<i class="fa fa-dot-circle-o"></i>&nbsp;<span>Save</span>
								</a>
								<a href = "addtl_only?86d178a053b97f10a65771b2c1ff9621=<?php echo $rfid_no;?>&barcode=&qty=&transact=go&checkout=&print<?php echo (isset($lunch) && $lunch != '' ? '&lunch=1' : '');?><?php echo (isset($breakfast) && $breakfast != '' ? '&breakfast=1' : '');?><?php echo (isset($_GET['isload']) && $_GET['isload'] == '1' ? '&isload=1' : '');?>" id = "sub_but_print" onclick="return window.saveAddtl ? window.saveAddtl(true) : true;" <?php if(!isset($_GET['checkout']) || !isset($_GET['86d178a053b97f10a65771b2c1ff9621'])){ ?> style="display: none;" name = "transact" <?php } ?> type="submit" <?php echo $onclick; ?>  class="btn btn-info btn-block btn-sm">
									<i class="fa fa-dot-circle-o"></i>&nbsp;<span>Print & Save</span>
								</a>
							<?php //}else{ ?>
								<button style="display: none;" id = "transactt" <?php if($transact != ""){ echo '  name = "transact" '; } if(!isset($_GET['checkout']) || !isset($_GET['86d178a053b97f10a65771b2c1ff9621'])){ ?> style="display: none;" name = "transact" <?php } ?> type="submit" <?php echo $onclick; ?>  class="btn btn-info btn-block btn-sm">
									<i class="fa fa-dot-circle-o"></i>&nbsp;<span><?php echo $butname; ?></span>
								</button>
							<?php
								//}
								//if(isset($_GET['86d178a053b97f10a65771b2c1ff9621'])){
									echo '<input type = "hidden" name = "checkout" value = "1">';
								//}
								if($transact != ""){
									$_SESSION['receipt']['orno'] = rand(0, 999999);
									$_SESSION['receipt']['orno'] = 'R-'.str_pad($_SESSION['receipt']['orno'], 10, 0, STR_PAD_LEFT);
									//echo '<input type = "hidden" name = "print" value = "'.$_SESSION['receipt']['orno'].'">';
								}
							?>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<?php
	if( isset($_GET['transact']) && $_GET['transact'] != "" && $transact == 'go'){
		
		$isload = 0;

		if($_SESSION['total'] > 0){
			$isload = 1;
		}
		if($_SESSION['user_bal'] < $_SESSION['total'] && $_SESSION['type'] == 'Direct'){
			$isload = 0;
		}elseif($_SESSION['user_bal'] >= $_SESSION['total'] && $_SESSION['type'] == 'Direct'){
			if(isset($_GET['isload'])){
				$isload = $_GET['isload'];
			}else{
				$isload = 0;
			}
		}
		if($_SESSION['user_bal'] < $_SESSION['total'] && $isload > 0){
			alert("addtl_only?86d178a053b97f10a65771b2c1ff9621=".$_GET['86d178a053b97f10a65771b2c1ff9621']."&barcode=&qty=1&transact=&checkout=", "Not enough load.");
			exit;
		}
		if($maxCredit < ((float)($row2->amt ?? 0)+$_SESSION['total']) && !$isload) {
			alert("addtl_only?86d178a053b97f10a65771b2c1ff9621=".$_GET['86d178a053b97f10a65771b2c1ff9621']."&barcode=&qty=1&transact=&checkout=", "You will exceed to allowable amount: " . number_format(str_replace(",", "", $maxCredit), 2)) ;
			exit; 
		}
		//$stmt = $conn->prepare("UPDATE products AS a INNER JOIN transactions AS b ON a.product_id = b.product_id SET a.stock = (a.stock - b.qty) WHERE b.user = ? and state = 1");
		//$stmt->bind_param("s", $_SESSION['nameinsta']);
		//if($stmt->execute() === TRUE){
			//$stmt = $conn->prepare("UPDATE transactions SET active = 2 WHERE user = ? AND active = 1");
			// Step 1: mark ALL active=1 transactions as active=2 (no JOIN so all items are included)
			$stmt = $conn->prepare("UPDATE transactions SET isload = ?, receipt = ?, member_name = ?, rfid_no = ?, active = 2 WHERE user = ? AND active = 1");
			$stmt->bind_param("issss", $isload, $_SESSION['receipt']['orno'], $_SESSION['receipt']['name'], $_SESSION['receipt']['rfid_no'], $_SESSION['nameinsta']);
			$stmt->execute();
			// Step 2: decrement stock only for products that exist in the products table
			$stmtStock = $conn->prepare("UPDATE products AS a INNER JOIN transactions AS b ON a.product_id = b.product_id SET a.stock = (a.stock - b.qty) WHERE b.user = ? AND b.active = 2 AND b.receipt = ?");
			$stmtStock->bind_param("ss", $_SESSION['nameinsta'], $_SESSION['receipt']['orno']);
			$stmtStock->execute();
			if(true){				
		      	if($isload == 1){
		      		$stmtx = $conn->prepare("UPDATE members SET balance = (balance - ?) WHERE rfid_no = ?");
			      	$stmtx->bind_param("ss", $_SESSION['total'], $_SESSION['receipt']['rfid_no']);
			      	$stmtx->execute();
				}
				if(isset($_GET['print'])){
					alert("addtl_only?86d178a053b97f10a65771b2c1ff9621=".$_GET['86d178a053b97f10a65771b2c1ff9621']."&print=".$_SESSION['receipt']['orno'], "Save and print");
				}else{
					alert("addtl_only", "Save succesful");
				}
				$text = 'Receipt: ' . $_SESSION['receipt']['orno'] . "\n";
				$text .= 'RFID No.: ' .  $_SESSION['receipt']['rfid_no'] . "\n";
				$text .= 'Total: ' .  $_SESSION['total'] . "\n";
				$text .= 'Transaction:' . print_r($_SESSION['items'], true);
				$filePath = generateTextFile($text, $_SESSION['receipt']['orno']);
				$_SESSION['total'] = 0;
				unset($_SESSION['receipt']);
			}
		//}
	}
	if(isset($_GET['print'])){
		echo '
		<script type = "text/javascript"> 
			window.onload = function() {						 
				setTimeout(function() { window.print(); window.location.href = "addtl_only"; }, 500); 
			};
		</script>';
		echo ' </div'; 
	}	
?>
<?php
	if($barcode !== '' && $barcode !== 'a' && isset($_GET['qty'])){
		$stmt = $conn->prepare("SELECT product_id, name, price, stock FROM products WHERE barcode = ?");
		$stmt->bind_param("s", $barcode);
		if($stmt->execute() === TRUE){
			$result = $stmt->get_result();
			if($result->num_rows > 0){
				$row = $result->fetch_object();
				if ($_GET['barcode'] == '48031837') {
					$_GET['qty'] = 1;
					$stmtRestrict = $conn->prepare("SELECT * FROM transactions WHERE product_id = ? and rfid_no = ?");
					$stmtRestrict->bind_param("ss", $row->product_id, $_GET['86d178a053b97f10a65771b2c1ff9621']);
					if ($stmtRestrict->execute() === TRUE) {
						$resultRestrict = $stmtRestrict->get_result();
						if ($resultRestrict->num_rows > 0) {
							$rowRestrict = $resultRestrict->fetch_object();
							if ($rowRestrict->active == 2) {
								alert("addtl_only?86d178a053b97f10a65771b2c1ff9621=".$_GET['86d178a053b97f10a65771b2c1ff9621']."&barcode=&qty=1&transact=&checkout=", "Employee already availed one <br>" . $row->name);
								exit;
							}
						}
					} 
				}
				if($row->stock > 0){
					$amount = ( str_replace(",", "", $_GET['qty']) * str_replace(",", "", $row->price) );
					$stmt = $conn->prepare("SELECT * FROM transactions WHERE product_id = ? and user = ? and active = 1");
					$stmt->bind_param("ss", $row->product_id, $_SESSION['nameinsta']);
					if($_SESSION['user_bal'] < ($amount+$_SESSION['total']) && $_SESSION['type'] == 'Agency'){
						alert("addtl_only?86d178a053b97f10a65771b2c1ff9621=".$_GET['86d178a053b97f10a65771b2c1ff9621']."&barcode=&qty=1&transact=&checkout=", "Not enough load.");
						exit;
					}
					if($maxCredit < (float)($row2->amt ?? 0)+($amount+$_SESSION['total']) && !isset($_GET['isload'])) {
						alert("addtl_only?86d178a053b97f10a65771b2c1ff9621=".$_GET['86d178a053b97f10a65771b2c1ff9621']."&barcode=&qty=1&transact=&checkout=", "You will exceed to allowable amount: " . number_format(str_replace(",", "", $maxCredit), 2)) ;
						exit;
					}
					$checkbox = "";
					if(isset($_GET['isload'])){
						$checkbox = "&isload=1";
					}
					if($stmt->execute() === TRUE){
						$result = $stmt->get_result();
						if($result->num_rows > 0){
							//$row2 = $result->fetch_object();
							//if($row->stock < ($_GET['qty']+$row2->qty)){
							//	alert("addtl_only", $row->name . " has " . $row->stock . " stock/s only.");
							//	exit;
							//}
							if ($_GET['barcode'] == '48031837') {
								alert("addtl_only?86d178a053b97f10a65771b2c1ff9621=".$_GET['86d178a053b97f10a65771b2c1ff9621']."&barcode=&qty=1&transact=&checkout=", "Only one " . $row->name . " can be purchased.");
								exit;
							}
							$stmt = $conn->prepare("UPDATE transactions SET qty = (qty + ?), amount = (amount + ?) WHERE product_id = ? AND user = ? and active = 1");
							$stmt->bind_param("ssss", $_GET['qty'], $amount, $row->product_id, $_SESSION['nameinsta']);
						}else{
							//if($row->stock < ($_GET['qty'])){
							//	alert("addtl_only", $row->name . " has " . $row->stock . " stock/s only.");
							//	exit;
							//}
							$stmt = $conn->prepare("INSERT INTO transactions (product_id, product_name, price, qty, amount, user, active, isload) VALUES (?, ?, ?, ?, ?, ?, '1', 0)");
							$stmt->bind_param("ssssss", $row->product_id, $row->name, $row->price, $_GET['qty'], $amount, $_SESSION['nameinsta']);
						}
						if($stmt->execute() === TRUE){
							alert("addtl_only?86d178a053b97f10a65771b2c1ff9621=".$_GET['86d178a053b97f10a65771b2c1ff9621']."&barcode=a&qty=1&transact=&checkout=".$checkbox.(isset($_GET['additional']) && $_GET['additional'] == 'on' ? '&additional=on' : ''), "");
							echo 1;
						}else{
							alert("addtl_only", "Barcode write error: " . $stmt->error);
						}
					}else{
						alert("addtl_only", "Barcode cart read error: " . $stmt->error);
					}
				}else{
					alert("addtl_only", "No more stock");
				}
			}else{
				alert("addtl_only", "Product not found: " . htmlspecialchars($barcode, ENT_QUOTES));
			}
		}else{
			alert("addtl_only", "Barcode lookup error: " . $stmt->error);
		}
	}
?>
<?php if( (isset($_SESSION['type']) && $_SESSION['type'] == 'Direct') && (isset($_SESSION['user_bal']) && $_SESSION['user_bal'] > 0) && !isset($_GET['print'])){ ?>
	<script type="text/javascript">
		$(document).on('click','#isload',function(){	
			if(document.getElementById('isload').checked) {
				$("#sub_but").attr("href", "addtl_only?86d178a053b97f10a65771b2c1ff9621=<?php echo $rfid_no;?>&barcode=&qty=&transact=go&checkout=&isload=1<?php echo (isset($lunch) && $lunch != '' ? '&lunch=1' : '');?><?php echo (isset($breakfast) && $breakfast != '' ? '&breakfast=1' : '');?>");
				$("#sub_but_print").attr("href", "addtl_only?86d178a053b97f10a65771b2c1ff9621=<?php echo $rfid_no;?>&barcode=&qty=&transact=go&checkout=&isload=1&print<?php echo (isset($lunch) && $lunch != '' ? '&lunch=1' : '');?><?php echo (isset($breakfast) && $breakfast != '' ? '&breakfast=1' : '');?>");
			} else {
				$("#sub_but").attr("href", "addtl_only?86d178a053b97f10a65771b2c1ff9621=<?php echo $rfid_no;?>&barcode=&qty=&transact=go&checkout=<?php echo (isset($lunch) && $lunch != '' ? '&lunch=1' : '');?><?php echo (isset($breakfast) && $breakfast != '' ? '&breakfast=1' : '');?>");
				$("#sub_but_print").attr("href", "addtl_only?86d178a053b97f10a65771b2c1ff9621=<?php echo $rfid_no;?>&barcode=&qty=&transact=go&checkout=&print<?php echo (isset($lunch) && $lunch != '' ? '&lunch=1' : '');?><?php echo (isset($breakfast) && $breakfast != '' ? '&breakfast=1' : '');?>");
			}
		});
	</script>
<?php } ?>

<?php if(!isset($_GET['print'])){ ?>
<script type="text/javascript">
(function(){
	var currentRfid = '<?php echo htmlspecialchars($rfid_no, ENT_QUOTES); ?>';
	var isSaving = false;
	var isResolvingRfid = false;
	var pendingBarcodeScan = null;
	var barcodeInputTimer = null;
	var rfidInputTimer = null;
	var scanDebounceMs = 300;
	var rfidDebounceMs = 150;
	var barcodeRequestInFlight = false;
	var queuedBarcodeScans = [];
	var barcodeProductMap = {};
	var lastAcceptedScanAtByBarcode = {};
	var duplicateGuardMs = 800;
	var textboxScanStopperMs = 600;
	var lastTextboxScanKey = '';
	var lastTextboxScanAt = 0;
	var suppressBarcodeInputFallbackUntil = 0;
	var barcodeEnterSuppressMs = 400;
	var scanIndicatorTimer = null;
	var activeBarcodeRequestKey = '';
	var lastScanErrorKey = '';
	var lastScanErrorAt = 0;
	var scanErrorSuppressMs = 3000;
	var failedScanCooldownMs = 3000;
	var failedScanUntilByKey = {};
	var lastRfidLookup = '';
	var scannerBuffer = '';
	var scannerLastTs = 0;

	// ── Helpers ──────────────────────────────────────────────────────────
	function fmtNum(n) {
		var v = parseFloat(n) || 0;
		return (v % 1 === 0 ? parseInt(v) : v.toFixed(2)).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
	}
	function fmtMoney(n) {
		return parseFloat(n).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
	}

	function parseCellNumber(text) {
		return parseFloat(String(text || '').replace(/,/g, '')) || 0;
	}

	function optimisticIncrementCartByPid(pid, qtyDelta) {
		var targetPid = String(pid || '');
		var inc = parseFloat(qtyDelta) || 0;
		if (targetPid === '' || inc <= 0) {
			return false;
		}

		var found = false;
		var unitPrice = 0;
		$('#cart tr').each(function() {
			var $row = $(this);
			var $del = $row.find('a.del-item');
			if ($del.length === 0) {
				return;
			}
			if (String($del.data('pid')) !== targetPid) {
				return;
			}
			var $cells = $row.find('td');
			if ($cells.length < 5) {
				return;
			}
			unitPrice = parseCellNumber($cells.eq(2).text());
			var currentQty = parseCellNumber($cells.eq(3).text());
			var newQty = currentQty + inc;
			$cells.eq(3).text(fmtNum(newQty));
			$cells.eq(4).text(fmtMoney(newQty * unitPrice));
			found = true;
		});

		if (!found) {
			return false;
		}

		var $rows = $('#cart tr');
		var $totalRow = $rows.last();
		var $totalCells = $totalRow.find('td');
		if ($totalCells.length >= 5 && /TOTAL/i.test($totalCells.eq(2).text())) {
			var totalQty = parseCellNumber($totalCells.eq(3).text()) + inc;
			var totalAmt = parseCellNumber($totalCells.eq(4).text()) + (unitPrice * inc);
			$totalCells.eq(3).text(fmtNum(totalQty));
			$totalCells.eq(4).text(fmtMoney(totalAmt));
		}
		return true;
	}

	function normalizeBarcodeInput(raw) {
		var v = $.trim(raw || '');
		// Remove AIM symbology prefix like ]C1, ]E0, ]Q3, etc.
		v = v.replace(/^\][A-Za-z][0-9]/, '');
		return v;
	}

	function makeScanKey(barcodeVal, qtyVal) {
		return normalizeBarcodeInput(barcodeVal) + '|' + (parseInt(qtyVal, 10) || 1);
	}

	function shouldQueueBarcodeScan(barcodeVal, qtyVal) {
		var key = makeScanKey(barcodeVal, qtyVal);
		if (activeBarcodeRequestKey === key) {
			return false;
		}
		if (queuedBarcodeScans.length > 0) {
			var lastQueued = queuedBarcodeScans[queuedBarcodeScans.length - 1];
			if (makeScanKey(lastQueued.barcode, lastQueued.qty) === key) {
				return false;
			}
		}
		return true;
	}

	function shouldSuppressScanError(msg, barcodeVal) {
		var key = normalizeBarcodeInput(barcodeVal) + '|' + String(msg || '');
		var now = Date.now();
		if (key === lastScanErrorKey && (now - lastScanErrorAt) < scanErrorSuppressMs) {
			return true;
		}
		lastScanErrorKey = key;
		lastScanErrorAt = now;
		return false;
	}

	function markFailedScan(barcodeVal, qtyVal) {
		failedScanUntilByKey[makeScanKey(barcodeVal, qtyVal)] = Date.now() + failedScanCooldownMs;
	}

	function clearFailedScan(barcodeVal, qtyVal) {
		delete failedScanUntilByKey[makeScanKey(barcodeVal, qtyVal)];
	}

	function shouldSuppressFailedScanRetry(barcodeVal, qtyVal) {
		var key = makeScanKey(barcodeVal, qtyVal);
		var untilTs = failedScanUntilByKey[key] || 0;
		if (untilTs <= 0) {
			return false;
		}
		if (Date.now() < untilTs) {
			return true;
		}
		delete failedScanUntilByKey[key];
		return false;
	}

	function shouldDropDuplicateScan(barcodeVal, qtyVal) {
		var key = barcodeVal + '|' + (parseInt(qtyVal, 10) || 1);
		var now = Date.now();
		var last = lastAcceptedScanAtByBarcode[key] || 0;
		if ((now - last) < duplicateGuardMs) {
			return true;
		}
		lastAcceptedScanAtByBarcode[key] = now;
		return false;
	}

	function shouldBlockTextboxDuplicate(barcodeVal, qtyVal) {
		var key = barcodeVal + '|' + (parseInt(qtyVal, 10) || 1);
		var now = Date.now();
		if (key === lastTextboxScanKey && (now - lastTextboxScanAt) < textboxScanStopperMs) {
			return true;
		}
		lastTextboxScanKey = key;
		lastTextboxScanAt = now;
		return false;
	}

	function showScanIndicator(state) {
		return;
	}

	function refreshCartFromServer() {
		xpost({action: 'cart'}, function(res) {
			if (res && res.success) {
				renderCart(res);
			}
		});
	}

	function legacyBarcodeFallback(barcodeVal, qty, isload) {
		if (!currentRfid) {
			return;
		}
		var isloadPart = isload ? '&isload=1' : '';
		window.location.href = 'addtl_only?86d178a053b97f10a65771b2c1ff9621=' + encodeURIComponent(currentRfid)
			+ '&barcode=' + encodeURIComponent(barcodeVal)
			+ '&qty=' + encodeURIComponent(qty || 1)
			+ '&transact=&checkout=' + isloadPart;
	}

	// ── Render employee panel from AJAX response ──────────────────────────
	function renderEmployeePanel(data) {
		var bg = data.green_mark > 0 ? ' style="background-color:#00FF00;"' : '';
		var html = '<div class="row"><div class="col-12"><br>';
		html += '<table class="table"' + bg + '><tbody>';
		if (data.has_photo) {
			html += '<tr><td align="center" colspan="2"><img height="200" src="' + data.photo + '"></td></tr>';
		}
		html += '<tr><td>Balance: </td><td><b>' + data.balance + '</b></td></tr>';
		if (data.type === 'Direct' && data.user_bal > 0) {
			html += '<tr><td>Use Load?</td><td><input id="isload" name="isload" value="1" type="checkbox" class="form-control-sm form-control"/></td></tr>';
		}
		html += '<tr><td>Name: </td><td><b>'       + data.name       + '</b></td></tr>';
		html += '<tr><td>Address: </td><td><b>'    + data.address    + '</b></td></tr>';
		html += '<tr><td>Department: </td><td><b>' + data.department + '</b></td></tr>';
		html += '<tr><td>Position: </td><td><b>'   + data.position   + '</b></td></tr>';
		html += '<tr><td>For Deduction: </td><td><b><span id="deduction-val">' + (data.for_deduction || '0') + '</span></b></td></tr>';
		html += '</tbody></table></div></div>';
		$('#employee-panel-content').html(html);

		currentRfid = data.rfid_no;
		var rfidEnc = encodeURIComponent(data.rfid_no);
		$('#sub_but').attr('href', 'addtl_only?86d178a053b97f10a65771b2c1ff9621=' + rfidEnc + '&barcode=&qty=&transact=go&checkout=');
		$('#sub_but_print').attr('href', 'addtl_only?86d178a053b97f10a65771b2c1ff9621=' + rfidEnc + '&barcode=&qty=&transact=go&checkout=&print');
		$('#sub_but, #sub_but_print').show();

		$('#rfid-input').prop('readonly', true);
		$('input[name="barcode"]').val('').focus();
	}

	function submitBarcode(barcodeVal, qty, isload) {
		barcodeVal = normalizeBarcodeInput(barcodeVal);
		if (barcodeVal === '') {
			return;
		}
		if (shouldSuppressFailedScanRetry(barcodeVal, qty)) {
			showScanIndicator('blocked');
			$('input[name="barcode"]').val('').focus();
			return;
		}
		if (shouldDropDuplicateScan(barcodeVal, qty)) {
			showScanIndicator('blocked');
			$('input[name="barcode"]').val('').focus();
			return;
		}
		if (barcodeRequestInFlight) {
			if (shouldQueueBarcodeScan(barcodeVal, qty)) {
				queuedBarcodeScans.push({ barcode: barcodeVal, qty: qty, isload: isload });
				showScanIndicator('ok');
				if (barcodeProductMap[barcodeVal]) {
					optimisticIncrementCartByPid(barcodeProductMap[barcodeVal], qty);
				}
			} else {
				showScanIndicator('blocked');
			}
			$('input[name="barcode"]').val('').focus();
			return;
		}

		barcodeRequestInFlight = true;
		activeBarcodeRequestKey = makeScanKey(barcodeVal, qty);
		var $inp = $('input[name="barcode"]');
		var finalized = false;
		function finalizeScan() {
			if (finalized) {
				return;
			}
			finalized = true;
			barcodeRequestInFlight = false;
			activeBarcodeRequestKey = '';
			if (queuedBarcodeScans.length > 0) {
				var nextScan = queuedBarcodeScans.shift();
				submitBarcode(nextScan.barcode, nextScan.qty, nextScan.isload);
			}
		}

		$inp.val('').focus();
		showScanIndicator('ok');
		if (barcodeProductMap[barcodeVal]) {
			optimisticIncrementCartByPid(barcodeProductMap[barcodeVal], qty);
		}
		xpost({action:'barcode', barcode:barcodeVal, qty:qty, isload:isload}, function(res) {
			if (res && res.success) {
				clearFailedScan(barcodeVal, qty);
				if (res.scanned_barcode && res.affected_product_id) {
					barcodeProductMap[normalizeBarcodeInput(res.scanned_barcode)] = String(res.affected_product_id);
				}
				if ($.isArray(res.items) && res.items.length > 0) {
					renderCart(res);
					$inp.focus();
					finalizeScan();
					return;
				}
				xpost({action:'cart'}, function(cartRes) {
					if (cartRes && cartRes.success && $.isArray(cartRes.items) && cartRes.items.length > 0) {
						renderCart(cartRes);
						$inp.focus();
						finalizeScan();
						return;
					}
					$inp.focus();
					finalizeScan();
					legacyBarcodeFallback(barcodeVal, qty, isload);
				}, function() {
					$inp.focus();
					finalizeScan();
					legacyBarcodeFallback(barcodeVal, qty, isload);
				});
				return;
			}
			var msg = (res && res.error) ? res.error : 'Unknown error';
			if (res && res._debug) {
				console.warn('[barcode not found] sent:', barcodeVal, '| server raw hex:', res._debug.raw, '| clean:', res._debug.clean, '| digits:', res._debug.digits);
			}
			markFailedScan(barcodeVal, qty);
			refreshCartFromServer();
			showScanIndicator('error');
			if (!shouldSuppressScanError(msg, barcodeVal)) {
				Swal.fire({position:'center', icon:'warning', title: msg,
					html: (res && res._debug && res._debug.clean) ? '<small style="color:#666">Barcode received: <b>' + res._debug.clean + '</b></small>' : '',
					showConfirmButton:false, timer:2500});
			}
			$inp.focus();
			finalizeScan();
		}, function() {
			refreshCartFromServer();
			showScanIndicator('error');
			$inp.focus();
			finalizeScan();
			legacyBarcodeFallback(barcodeVal, qty, isload);
		});
	}

	// ── Render cart table from AJAX response ──────────────────────────────
	function renderCart(data, allowEmpty) {
		allowEmpty = !!allowEmpty;
		if (!data || !data.items) {
			return;
		}
		if (!allowEmpty && data.items.length === 0 && $('#cart tr').length > 0) {
			return;
		}
		var html = '';
		$.each(data.items, function(i, item) {
			// Support both field name conventions:
			// SQL aliases (xqty / xamount) and plain names (qty / amount)
			var qty    = item.xqty    !== undefined ? item.xqty    : item.qty;
			var amount = item.xamount !== undefined ? item.xamount : item.amount;
			html += '<tr>';
			html += '<td><a href="#" data-pid="' + item.product_id + '" class="btn btn-sm btn-danger del-item"><i class="fa fa-times-circle"></i></a></td>';
			html += '<td>' + item.product_name + '</td>';
			html += '<td>' + fmtNum(item.price) + '</td>';
			html += '<td>' + fmtNum(qty)         + '</td>';
			html += '<td>' + fmtMoney(amount)    + '</td>';
			html += '</tr>';
		});
		if (data.items.length > 0) {
			var totalQty    = data.total_qty    !== undefined ? data.total_qty    : data.totalQty;
			var totalAmount = data.total_amount !== undefined ? data.total_amount : data.totalAmount;
			html += '<tr><td></td><td></td><td><b><i>TOTAL</i></b></td>';
			html += '<td><b><i>' + fmtNum(totalQty)      + '</i></b></td>';
			html += '<td><b><i>' + fmtMoney(totalAmount) + '</i></b></td></tr>';
		}
		$('#cart').html(html);
	}

	function resetTransactionUi() {
		currentRfid = '';
		isSaving = false;
		$('#sub_but i, #sub_but_print i').removeClass('fa-spinner fa-spin').addClass('fa-dot-circle-o');
		$('#sub_but span').text('Save');
		$('#sub_but_print span').text('Print & Save');
		$('#sub_but, #sub_but_print').removeClass('disabled').css('pointer-events', '').css('opacity', '').hide();
		$('#rfid-input').prop('readonly', false).val('').focus();
		$('input[name="barcode"]').val('');
		$('input[name="qty"]').val('1');
		$('#cart').html('');
		$('#employee-panel-content').html('');
	}

	// ── Fast POST helper (native fetch, no jQuery overhead) ──────────────
	function xpost(data, onSuccess, onFail) {
		var controller = new AbortController();
		var timeoutId = setTimeout(function(){ controller.abort(); }, 15000);
		fetch('ajax/addtl_ajax.php', {
			method: 'POST',
			headers: {'Content-Type': 'application/x-www-form-urlencoded'},
			body: new URLSearchParams(data),
			credentials: 'same-origin',
			signal: controller.signal
		})
		.then(function(r){
			clearTimeout(timeoutId);
			return r.text();
		})
		.then(function(text){
			try {
				onSuccess(JSON.parse(text));
				return;
			} catch (e1) {
				// Some PHP environments prepend warnings/notices before JSON.
				var start = text.indexOf('{');
				var end = text.lastIndexOf('}');
				if (start !== -1 && end > start) {
					var candidate = text.substring(start, end + 1);
					onSuccess(JSON.parse(candidate));
					return;
				}
				throw e1;
			}
		})
		.catch(function(err){
			clearTimeout(timeoutId);
			(onFail || function(){})(err);
		});
	}

	// ── Delete cart item ──────────────────────────────────────────────────
	$(document).on('click', '.del-item', function(e) {
		e.preventDefault();
		var pid = $(this).data('pid');
		xpost({action: 'delete', product_id: pid}, function(res) {
			if (res.success) renderCart(res, true);
		});
	});

	// ── RFID scan ─────────────────────────────────────────────────────────
	function resolveRfid(rfidVal) {
		rfidVal = $.trim(rfidVal || '');
		if (rfidVal === '' || (currentRfid !== '' && rfidVal === currentRfid)) {
			return;
		}
		if (isResolvingRfid && lastRfidLookup === rfidVal) {
			return;
		}
		isResolvingRfid = true;
		lastRfidLookup = rfidVal;
		pendingBarcodeScan = null;
		$('input[name="barcode"]').focus();
		var $inp = $('#rfid-input').prop('readonly', true);
		xpost({action: 'rfid', rfid: rfidVal}, function(res) {
			isResolvingRfid = false;
			if (res.success) {
				renderEmployeePanel(res);
				if (pendingBarcodeScan) {
					var queuedScan = pendingBarcodeScan;
					pendingBarcodeScan = null;
					submitBarcode(queuedScan.barcode, queuedScan.qty, queuedScan.isload);
				}
			} else {
				Swal.fire({position:'center', icon:'warning', title: res.error, showConfirmButton:false, timer:1500});
				$inp.prop('readonly', false).val('').focus();
			}
		}, function() {
			isResolvingRfid = false;
			Swal.fire({position:'center', icon:'error', title:'Connection error', showConfirmButton:false, timer:1500});
			$inp.prop('readonly', false).focus();
		});
	}

	$(document).on('keydown', '#rfid-input', function(e) {
		if (e.keyCode !== 13) return;
		e.preventDefault();
		if (rfidInputTimer) {
			clearTimeout(rfidInputTimer);
			rfidInputTimer = null;
		}
		resolveRfid($(this).val());
	});

	$(document).on('input', '#rfid-input', function() {
		var $field = $(this);
		var rfidVal = $.trim($field.val());
		if (rfidVal.length < 6) {
			return;
		}
		if (rfidInputTimer) {
			clearTimeout(rfidInputTimer);
		}
		rfidInputTimer = setTimeout(function() {
			rfidInputTimer = null;
			resolveRfid($field.val());
		}, rfidDebounceMs);
	});

	// ── Barcode scan ──────────────────────────────────────────────────────
	$(document).on('keydown', 'input[name="barcode"]', function(e) {
		if (barcodeInputTimer) {
			clearTimeout(barcodeInputTimer);
			barcodeInputTimer = null;
		}
		if (e.keyCode !== 13) return;
		e.preventDefault();
		var barcodeVal = normalizeBarcodeInput($(this).val());
		if (barcodeVal === '') return;
		suppressBarcodeInputFallbackUntil = Date.now() + barcodeEnterSuppressMs;
		var qty    = parseInt($('input[name="qty"]').val()) || 1;
		var isload = $('#isload').is(':checked') ? 1 : 0;
		if (shouldBlockTextboxDuplicate(barcodeVal, qty)) {
			showScanIndicator('blocked');
			$(this).val('');
			return;
		}
		if (currentRfid === '') {
			if (isResolvingRfid) {
				pendingBarcodeScan = {barcode: barcodeVal, qty: qty, isload: isload};
				$(this).val('');
				return;
			}
			Swal.fire({position:'center', icon:'warning', title:'Scan RFID first.', showConfirmButton:false, timer:1200});
			return;
		}
		submitBarcode(barcodeVal, qty, isload);
	});

	// Global fallback for wedge scanners when focus/input handlers miss events.
	$(document).on('keydown', function(e) {
		var now = Date.now();
		if (now - scannerLastTs > 90) {
			scannerBuffer = '';
		}
		scannerLastTs = now;

		if (e.key === 'Enter') {
			var $t = $(e.target);
			if ($t.is('input[name="barcode"], #rfid-input, input[name="qty"], textarea, [contenteditable="true"]')) {
				scannerBuffer = '';
				return;
			}
			if (scannerBuffer.length >= 6 && currentRfid !== '') {
				var normalized = normalizeBarcodeInput(scannerBuffer);
				if (normalized.length >= 6) {
					e.preventDefault();
					var qty = parseInt($('input[name="qty"]').val()) || 1;
					var isload = $('#isload').is(':checked') ? 1 : 0;
					submitBarcode(normalized, qty, isload);
				}
			}
			scannerBuffer = '';
			return;
		}

		if (e.key && e.key.length === 1) {
			scannerBuffer += e.key;
			if (scannerBuffer.length > 64) {
				scannerBuffer = scannerBuffer.slice(-64);
			}
		}
	});

	// Fallback for scanners that don't send Enter suffix.
	$(document).on('input', 'input[name="barcode"]', function() {
		var $field = $(this);
		var raw = $.trim($field.val());
		if (raw.length < 1) {
			return;
		}
		if (Date.now() < suppressBarcodeInputFallbackUntil) {
			return;
		}
		if (barcodeInputTimer) {
			clearTimeout(barcodeInputTimer);
		}
		barcodeInputTimer = setTimeout(function() {
			barcodeInputTimer = null;
			var barcodeVal = normalizeBarcodeInput($field.val());
			if (barcodeVal === '') {
				return;
			}
			var qty = parseInt($('input[name="qty"]').val()) || 1;
			var isload = $('#isload').is(':checked') ? 1 : 0;
			if (shouldBlockTextboxDuplicate(barcodeVal, qty)) {
				showScanIndicator('blocked');
				$field.val('');
				return;
			}
			if (currentRfid === '') {
				if (isResolvingRfid) {
					pendingBarcodeScan = {barcode: barcodeVal, qty: qty, isload: isload};
					$field.val('');
					return;
				}
				return;
			}
			submitBarcode(barcodeVal, qty, isload);
		}, scanDebounceMs);
	});

	// ── Update Save hrefs when Use Load checkbox changes ──────────────────
	$(document).on('change', '#isload', function() {
		var rfidEnc    = encodeURIComponent(currentRfid);
		var isloadPart = $(this).is(':checked') ? '&isload=1' : '';
		$('#sub_but').attr('href', 'addtl_only?86d178a053b97f10a65771b2c1ff9621=' + rfidEnc + '&barcode=&qty=&transact=go&checkout=' + isloadPart);
		$('#sub_but_print').attr('href', 'addtl_only?86d178a053b97f10a65771b2c1ff9621=' + rfidEnc + '&barcode=&qty=&transact=go&checkout=&print' + isloadPart);
	});

	window.saveAddtl = function(printFlag) {
		if (isSaving) {
			return false;
		}
		if (!currentRfid) {
			Swal.fire({position:'center', icon:'warning', title:'No employee selected.', showConfirmButton:false, timer:1200, timerProgressBar:true, width:'360px'});
			return false;
		}
		isSaving = true;
		$('#sub_but, #sub_but_print').addClass('disabled').css('pointer-events', 'none').css('opacity', '0.7');
		$('#sub_but i, #sub_but_print i').removeClass('fa-dot-circle-o').addClass('fa-spinner fa-spin');
		$('#sub_but span').text('Saving...');
		$('#sub_but_print span').text('Saving...');
		xpost({
			action: 'save',
			print: printFlag ? 1 : 0,
			isload: $('#isload').is(':checked') ? 1 : 0
		}, function(res) {
			if (!res.success) {
				isSaving = false;
				$('#sub_but, #sub_but_print').removeClass('disabled').css('pointer-events', '').css('opacity', '');
				$('#sub_but i, #sub_but_print i').removeClass('fa-spinner fa-spin').addClass('fa-dot-circle-o');
				$('#sub_but span').text('Save');
				$('#sub_but_print span').text('Print & Save');
				Swal.fire({position:'center', icon:'warning', title: (res.error || 'Unable to save transaction.'), showConfirmButton:false, timer:1500, timerProgressBar:true, width:'360px'});
				return;
			}
			if (!printFlag) {
				resetTransactionUi();
				return;
			}
			window.location.href = res.redirect;
		}, function() {
			isSaving = false;
			$('#sub_but, #sub_but_print').removeClass('disabled').css('pointer-events', '').css('opacity', '');
			$('#sub_but i, #sub_but_print i').removeClass('fa-spinner fa-spin').addClass('fa-dot-circle-o');
			$('#sub_but span').text('Save');
			$('#sub_but_print span').text('Print & Save');
			Swal.fire({position:'center', icon:'error', title:'Connection timeout, retrying legacy save...', showConfirmButton:false, timer:1500, timerProgressBar:true, width:'360px'});
			var fallbackUrl = printFlag ? $('#sub_but_print').attr('href') : $('#sub_but').attr('href');
			if (fallbackUrl) {
				window.location.href = fallbackUrl;
			}
		});
		return false;
	};

	$(document).on('click', '#sub_but', function(e) {
		e.preventDefault();
		window.saveAddtl(false);
	});

	$(document).on('click', '#sub_but_print', function(e) {
		e.preventDefault();
		window.saveAddtl(true);
	});

	// ── F2 key shortcut = Save ────────────────────────────────────────────
	$(document).keydown(function(e) {
		if (e.keyCode === 113) {
			window.saveAddtl(false);
		}
	});

	// Prevent accidental form submit
	$('form').on('submit', function(e) { e.preventDefault(); });

	if (currentRfid !== '' && $('#cart tr').length === 0) {
		xpost({action: 'cart'}, function(res) {
			if (res && res.success) {
				renderCart(res);
			}
		});
	}
}());
</script>
<?php } ?>