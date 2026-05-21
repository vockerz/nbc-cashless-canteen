<style type="text/css">
	#bar_g{
		border: 1px black solid;
		border-radius: 5px;
		margin-bottom: 20px;
		padding: 5px !important;
	}
</style>
<div class="breadcrumbs">
	<div class="col-sm-4">
		<div class="page-header float-left">
			<div class="page-title">
				<h1>Dashboard</h1>
			</div>
		</div>
	</div>
	<div class="col-sm-8">
		<div class="page-header float-right">
			<div class="page-title">
				<ol class="breadcrumb text-right">
					<li class="active">Dashboard</li>
				</ol>
			</div>
		</div>
	</div>
</div>
<?php
	$transactions_exists = false;
	$table_check = $conn->query("SHOW TABLES LIKE 'transactions'");
	if($table_check && $table_check->num_rows > 0){
		$transactions_exists = true;
	}

	$profit = (object) array('tot_fee' => 0, 'cnt' => 0);
	if($transactions_exists){
		$profit_query = 'SELECT sum(replace(a.amount,",","")) as tot_fee, count(receipt) as cnt FROM transactions AS a WHERE active = 2';
		$profit_result = $conn->query($profit_query);
		if($profit_result){
			$profit = $profit_result->fetch_object();
		}
	}
	$profit2 = 'SELECT count(*) AS cnt FROM members';
	$profit2 = $conn->query($profit2)->fetch_object();
?>
<div class="content mt-3">
	<div class="col-xl-4 col-lg-6">
		<div class="card">
			<div class="card-body">
				<div class="stat-widget-one">
					<div class="stat-icon dib"><i class="ti ti- text-success border-success" style='font-family: "Open Sans","Helvetica Neue","Montserrat",Helvetica,Arial,sans-serif !important;padding-left: 20px; padding-right: 20px; font-weight: 530;'>&#x20B1</i></div>
					<div class="stat-content dib">
						<div class="stat-text">Total Amount Paid</div>
						<div class="stat-digit"><?php echo str_replace('.00', '', number_format($profit->tot_fee,2)); ?></div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-xl-4 col-lg-6">
		<div class="card">
			<div class="card-body">
				<div class="stat-widget-one">
					<div class="stat-icon dib"><i class="ti ti-file text-primary border-primary"></i></div>
					<div class="stat-content dib">
						<div class="stat-text">Total Cashless Transactions</div>
						<div class="stat-digit"><?php echo number_format($profit->cnt); ?></div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-xl-4 col-lg-6">
		<div class="card">
			<div class="card-body">
                <div class="stat-widget-one">
                    <div class="stat-icon dib"><i class="ti-user text-primary border-primary"></i></div>
                    <div class="stat-content dib">
                        <div class="stat-text">Total Employees</div>
                        <div class="stat-digit"><?php echo number_format($profit2->cnt); ?></div>
                    </div>
                </div>
            </div>
		</div>
	</div>
	<div class="col-lg-12" >
		<div class="card">
			<div class="card-body" style="height: 315px;">
				<canvas id="Chart"></canvas>
			</div>
			<div class="card-footer">					
				<progress id="animationProgress" max="1" value="0" style="width: 100%"></progress>
			</div>
		</div>
	</div>
</div><!-- /#right-panel -->
<script src="js/Chart.bundle.min.js"></script>
<script type="text/javascript">
	var progress = document.getElementById('animationProgress');
	var ctx = document.getElementById( "Chart" );
	var color = Chart.helpers.color;
	window.chartColors = {
		red: 	'rgb(255, 99, 132)',
		orange: 'rgb(255, 159, 64)',
		yellow: 'rgb(255, 205, 86)',
		green: 	'rgb(75, 192, 192)',
		blue: 	'rgb(54, 162, 235)',
		purple: 'rgb(153, 102, 255)',
		grey: 	'rgb(201, 203, 207)'
	};
	var myChart = new Chart( ctx, {
		type: 'line',
		data: {
			labels: [ 
				<?php 
					for ($i = 1; $i <= date("t"); $i++) {
					  echo $i;
					  if($i < date("t")){
					  	echo ',';
					  }
					}

				?>
			],
			datasets: 
				[
					{
						label: "Amount Paid",
						backgroundColor: window.chartColors.red,
						borderColor: window.chartColors.red,
						fill: false,
						data: 
							[
								<?php
									$arr = array();
									$arr_c = array();
									$t =  date("t");
									for ($i = 1; $i <= date("t"); $i++) {
									  $arr[$i] = 0;
									  $arr_c[$i] = 0;
									}
									if($transactions_exists){
										$profit = 'SELECT count(receipt) as count,day(dttm) as for_m, sum(replace(a.amount,",","")) as tot_fee,group_concat(a.rfid_no separator ", ") FROM transactions as a where MONTH(dttm) = MONTH(CURDATE()) and receipt is not null GROUP BY YEAR(dttm),day(dttm) ORDER BY month(dttm) ASC';
										$profit = $conn->query($profit);
										if($profit && $profit->num_rows > 0){
											while ($row = $profit->fetch_object()) {
												$arr[$row->for_m] = str_replace(",", "", number_format($row->tot_fee,2));
												$arr_c[$row->for_m] = str_replace(",", "", number_format($row->count,2));
											}
										}
									}
									foreach ($arr as $key => $value) {
											echo $value . ', ';
									}									 
								?> 
							],
					},
					{
						label: "Transaction Count",
						backgroundColor: window.chartColors.blue,
						borderColor: window.chartColors.blue,
						fill: false,
						data: 
							[
								<?php
									foreach ($arr_c as $key => $value) {
											echo $value . ', ';
									}									 
								?> 
							],
					}

				]
		},
		options: {
			responsive: true, 
			title: {
					display: true,
					text: 'Transaction Graph (<?php echo date("F");?>)'
				},
			animation: {
					duration: 2000,
					onProgress: function(animation) {
						progress.value = animation.currentStep / animation.numSteps;
					},
					onComplete: function() {
						window.setTimeout(function() {
							progress.value = 0;
						}, 2000);
					}
				},
			maintainAspectRatio: false,
			tooltips: {
					mode: 'index',
					intersect: false,
					callbacks: {
	                	label: function(tooltipItem, data) {
	                		var dataLabel = data.datasets[tooltipItem.datasetIndex].label;
	                		var money_label = "";
	                		if(dataLabel == 'Amount Paid'){
	                			var money_label = "₱ ";
	                		}
	                		var value = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index].toLocaleString();
	                		if(parseInt(value) >= 1000){
								return ' ' + data.datasets[tooltipItem.datasetIndex].label +': ' + money_label + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
							} else {
								return ' ' + data.datasets[tooltipItem.datasetIndex].label +': ' + money_label + value;
							}
	                	}
	               }
	           },
			scales: {
				xAxes: [{
					display: true,
					scaleLabel: {
						display: true,
						labelString: 'Day'
					}
				}],
				yAxes: [{
					display: true,
						scaleLabel: {
						display: true,
						labelString: 'Value'
					},
					ticks: {
						beginAtZero:true,
						callback: function(value, index, values) {
							if(parseInt(value) >= 1000){
								return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
							} else {
								return value;
							}
						}
					}
				}]
			},
		}
	});
</script>