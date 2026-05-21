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
				<form action="" method="get">
					<div class="card">
						<div class="card-header">
							<strong class="card-title"><?php echo $title; ?></strong>
						</div>
						<div class="card-body">
							<div class="row">
								<div class="col-6">
									<label>Date From</label>
									<input required <?php if(isset($_GET['date_fr'])){ echo ' value = "' . $_GET['date_fr'] . '" '; } ?> autocomplete = "off" name = "date_fr" type = "date" class = "input-sm form-control-sm form-control" aria-required = "true" aria-invalid = "false">
								</div>
								<div class="col-6">
									<label>Date To</label>
									<input required <?php if(isset($_GET['date_to'])){ echo ' value = "' . $_GET['date_to'] . '" '; } ?> autocomplete = "off" name = "date_to" type = "date" class = "input-sm form-control-sm form-control" aria-required = "true" aria-invalid = "false">
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
					if(isset($_GET['date_fr']) && isset($_GET['date_to'])){						
						$prod = "SELECT * FROM loads AS a WHERE a.dttm BETWEEN '" . mysqli_real_escape_string($conn, $_GET['date_fr']) . " 00:00:00' and '" . mysqli_real_escape_string($conn, $_GET['date_to']) . " 23:59:59' ";
						$prod = $conn->query($prod);
						if($prod->num_rows > 0){
							$close = "window.close();";				?>
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
								<h4 class="text-CENTER spacing"><center><b>LOADING HISTORY REPORT From <?php echo ddate($_GET['date_fr']) . ' - ' . ddate($_GET['date_to']); ?></b></center></h4>
								<a id = "backs" class="btn btn-success btn-sm pull-right" target = "_blank" href="reports/load_history?print&date_fr=<?php echo $_GET['date_fr'];?>&date_to=<?php echo $_GET['date_to'];?>"  style="margin-right: 5px;"><span class="fa fa-print"></span> Print </a>
							</div>
							<div class="card-body">
								<table class="table">
									<thead  <?php if(!isset($_GET['print'])){ ?> class="thead-dark" <?php } ?>>
										<tr>
											<th scope="col">Member Name</th>
											<th scope="col">Load</th>
											<th scope="col">Running Balance</th>
											<th scope="col">Date Time</th>
											<th scope="col">User</th>
										</tr>
									</thead>
									<tbody>
										<?php
											$total = 0;
											while ($row = $prod->fetch_object()) {
												echo '<tr>';
													echo '<td>' . $row->member_name . '</td>';
													echo '<td>' . $row->loads . '</td>';
													echo '<td>' . $row->run_bal . '</td>';
													echo '<td>' . date("m/d/Y h:i A", strtotime($row->dttm)) . '</td>';
													echo '<td>' . $row->user . '</td>';
												echo '</tr>';
											}
										?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				<?php }else{ echo '<h4 align = "center"><i><b> No record found </i></b></h4>'; } }?>
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