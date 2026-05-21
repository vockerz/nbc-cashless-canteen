<?php
	$butname = "Print & Save";
	$onclick = "";
	$transact = "";
	$total_amount = "0";
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
												<input <?php if(isset($_GET['86d178a053b97f10a65771b2c1ff9621']) && $_GET['86d178a053b97f10a65771b2c1ff9621'] != ""){ echo ' value = "' . $_GET['86d178a053b97f10a65771b2c1ff9621'] . '" readonly '; } ?> <?php if(!isset($_GET['86d178a053b97f10a65771b2c1ff9621']) || (isset($_GET['86d178a053b97f10a65771b2c1ff9621']) && $_GET['86d178a053b97f10a65771b2c1ff9621'] == '') ){ echo 'autofocus';}?> autocomplete = "off" placeholder = "Tap Card....." name="86d178a053b97f10a65771b2c1ff9621" type="text" class="input-sm form-control-sm form-control" aria-required="true" aria-invalid="false">
											</div>
										</div>
										<?php
											if(!isset($_GET[''])){
												$stmtx = $conn->prepare("UPDATE transactions SET active = 0 WHERE product_id in (1,2) and rfid_no IS NULL and active = 1");
												$stmtx->execute();
												$_SESSION['total'] = 0;
											}
											$stmt = $conn->prepare("SELECT * FROM members WHERE rfid_no = ?");
											$stmt->bind_param("s", $_GET['86d178a053b97f10a65771b2c1ff9621']);								
											if($stmt->execute() === TRUE){
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
													<table class="table">
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
																			$stmt2->bind_param("s", $_GET['86d178a053b97f10a65771b2c1ff9621']);	
																			$stmt2->execute();

																			$result2 = $stmt2->get_result();
																			$row2 = $result2->fetch_object();
																			$maxCredit = $row->max_credit > 0 ? $row->max_credit : $maxCredit;
																			$maxCredit = str_replace(",", "", $maxCredit);
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
										<?php	} else{ if(isset($_GET['86d178a053b97f10a65771b2c1ff9621'])){ alert("addtl_only", "No record found"); }	} }	?>
									</div>							
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
														if(isset($_GET['print'])){
															$stmt = $conn->prepare("SELECT product_id, product_name, price, sum(qty) as xqty, sum(amount) as xamount, member_name, dttm, receipt FROM transactions WHERE user = ? and active = 2 and receipt = ? GROUP BY product_id");
															$stmt->bind_param("ss", $_SESSION['nameinsta'], $_GET['print']);		
														}else{
															$stmt = $conn->prepare("SELECT product_id, product_name, price, sum(qty) as xqty, sum(amount) as xamount, member_name, dttm, receipt FROM transactions WHERE user = ? and active = 1 GROUP BY product_id");
															$stmt->bind_param("s", $_SESSION['nameinsta']);														
														}
														if($stmt->execute() === TRUE){
															$total_qty = "0";
															$result = $stmt->get_result();
															if($result->num_rows > 0){
																while($row = $result->fetch_object()){
																		$name = $row->member_name;
																		$date = ddate($row->dttm);
																		$receipt = $row->receipt;
																	
																	echo '<tr>';
																		echo '<td><a href = "addtl_only?del='.$row->product_id.'&product_id='.$row->product_id.'&86d178a053b97f10a65771b2c1ff9621='.$_GET['86d178a053b97f10a65771b2c1ff9621'].'&barcode=a&qty=1&transact=&checkout=" class = "btn btn-sm btn-danger"><i class="fa fa-times-circle"></i><span></a></td>';
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
								<a href = "addtl_only?86d178a053b97f10a65771b2c1ff9621=<?php echo $_GET['86d178a053b97f10a65771b2c1ff9621'];?>&barcode=&qty=&transact=go&checkout=<?php echo (isset($lunch) && $lunch != '' ? '&lunch=1' : '');?><?php echo (isset($breakfast) && $breakfast != '' ? '&breakfast=1' : '');?><?php echo (isset($_GET['isload']) && $_GET['isload'] == '1' ? '&isload=1' : '');?>" id = "sub_but" <?php if(!isset($_GET['checkout']) || !isset($_GET['86d178a053b97f10a65771b2c1ff9621'])){ ?> style="display: none;" name = "transact" <?php } ?> type="submit" <?php echo $onclick; ?>  class="btn btn-info btn-block btn-sm">
									<i class="fa fa-dot-circle-o"></i>&nbsp;<span>Save</span>
								</a>
								<a href = "addtl_only?86d178a053b97f10a65771b2c1ff9621=<?php echo $_GET['86d178a053b97f10a65771b2c1ff9621'];?>&barcode=&qty=&transact=go&checkout=&print<?php echo (isset($lunch) && $lunch != '' ? '&lunch=1' : '');?><?php echo (isset($breakfast) && $breakfast != '' ? '&breakfast=1' : '');?><?php echo (isset($_GET['isload']) && $_GET['isload'] == '1' ? '&isload=1' : '');?>" id = "sub_but_print" <?php if(!isset($_GET['checkout']) || !isset($_GET['86d178a053b97f10a65771b2c1ff9621'])){ ?> style="display: none;" name = "transact" <?php } ?> type="submit" <?php echo $onclick; ?>  class="btn btn-info btn-block btn-sm">
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
		if($maxCredit < ($row2->amt+$_SESSION['total']) && !$isload) {
			alert("addtl_only?86d178a053b97f10a65771b2c1ff9621=".$_GET['86d178a053b97f10a65771b2c1ff9621']."&barcode=&qty=1&transact=&checkout=", "You will exceed to allowable amount: " . number_format(str_replace(",", "", $maxCredit), 2)) ;
			exit; 
		}
		//$stmt = $conn->prepare("UPDATE products AS a INNER JOIN transactions AS b ON a.product_id = b.product_id SET a.stock = (a.stock - b.qty) WHERE b.user = ? and state = 1");
		//$stmt->bind_param("s", $_SESSION['nameinsta']);
		//if($stmt->execute() === TRUE){
			//$stmt = $conn->prepare("UPDATE transactions SET active = 2 WHERE user = ? AND active = 1");
			$stmt = $conn->prepare("UPDATE products AS a INNER JOIN transactions AS b ON a.product_id = b.product_id SET b.isload = ?, b.receipt = ?, b.member_name = ?, b.rfid_no = ?, b.active = 2, a.stock = (a.stock - b.qty) WHERE b.user = ? and active = 1");
			$stmt->bind_param("issss", $isload, $_SESSION['receipt']['orno'], $_SESSION['receipt']['name'], $_SESSION['receipt']['rfid_no'], $_SESSION['nameinsta']);
			if($stmt->execute() === TRUE){				
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
	if(isset($_GET['barcode']) && isset($_GET['qty'])){
		$stmt = $conn->prepare("SELECT * FROM products WHERE barcode = ?");
		$stmt->bind_param("s", $_GET['barcode']);
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
					if($maxCredit < $row2->amt+($amount+$_SESSION['total']) && !isset($_GET['isload'])) {
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
							$stmt = $conn->prepare("INSERT INTO transactions (product_id, product_name, price, qty, amount, user) VALUES (?, ?, ?, ?, ?, ?)");
							$stmt->bind_param("ssssss", $row->product_id, $row->name, $row->price, $_GET['qty'], $amount, $_SESSION['nameinsta']);
						}
						if($stmt->execute() === TRUE){
							alert("addtl_only?86d178a053b97f10a65771b2c1ff9621=".$_GET['86d178a053b97f10a65771b2c1ff9621']."&barcode=a&qty=1&transact=&checkout=".$checkbox.(isset($_GET['additional']) && $_GET['additional'] == 'on' ? '&additional=on' : ''), "");
							echo 1;
						}
					}
				}else{
					alert("addtl_only", "No more stock");
				}
			}
		}
	}
?>
<?php if( (isset($_SESSION['type']) && $_SESSION['type'] == 'Direct') && (isset($_SESSION['user_bal']) && $_SESSION['user_bal'] > 0) && !isset($_GET['print'])){ ?>
	<script type="text/javascript">
		$(document).on('click','#isload',function(){	
			if(document.getElementById('isload').checked) {
				$("#sub_but").attr("href", "addtl_only?86d178a053b97f10a65771b2c1ff9621=<?php echo $_GET['86d178a053b97f10a65771b2c1ff9621'];?>&barcode=&qty=&transact=go&checkout=&isload=1<?php echo (isset($lunch) && $lunch != '' ? '&lunch=1' : '');?><?php echo (isset($breakfast) && $breakfast != '' ? '&breakfast=1' : '');?>");
				$("#sub_but_print").attr("href", "addtl_only?86d178a053b97f10a65771b2c1ff9621=<?php echo $_GET['86d178a053b97f10a65771b2c1ff9621'];?>&barcode=&qty=&transact=go&checkout=&isload=1&print<?php echo (isset($lunch) && $lunch != '' ? '&lunch=1' : '');?><?php echo (isset($breakfast) && $breakfast != '' ? '&breakfast=1' : '');?>");
			} else {
				$("#sub_but").attr("href", "addtl_only?86d178a053b97f10a65771b2c1ff9621=<?php echo $_GET['86d178a053b97f10a65771b2c1ff9621'];?>&barcode=&qty=&transact=go&checkout=<?php echo (isset($lunch) && $lunch != '' ? '&lunch=1' : '');?><?php echo (isset($breakfast) && $breakfast != '' ? '&breakfast=1' : '');?>");
				$("#sub_but_print").attr("href", "addtl_only?86d178a053b97f10a65771b2c1ff9621=<?php echo $_GET['86d178a053b97f10a65771b2c1ff9621'];?>&barcode=&qty=&transact=go&checkout=&print<?php echo (isset($lunch) && $lunch != '' ? '&lunch=1' : '');?><?php echo (isset($breakfast) && $breakfast != '' ? '&breakfast=1' : '');?>");
			}
		});
	</script>
<?php } ?>

<?php if(!isset($_GET['print'])){ ?> 
<script type="text/javascript">
	$(document).keydown(function(e){
    	var keycode=e.keyCode;
    	if (keycode == 113){
        	window.open( $('#sub_but', this).attr('href'), "_self" );
     	}
	    if (keycode == 27){
		    if (!$('#additional').is(':checked')) {
		        $("#transactt").attr('value', "");
			    $('#additional').prop('checked', true);
		        $("#addtrig").attr('value', "1");
			}else{
		        $("#transactt").attr('value', "go");
			    $('#additional').prop('checked', false);
		        $("#addtrig").attr('value', "0");
			}
		}
	});
</script>
<?php } ?>
