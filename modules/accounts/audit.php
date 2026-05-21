<?php
	if($access->level <= 1){
		echo '<script type = "text/javascript">alert("Restricted.");window.location.replace("/'.$pagename.'");</script>';
	}
?>
<div class="container-fluid" style="margin: 0 20px 0 20px; overflow-x:auto;" id="bgx">
	<div class="row">
		<div class="col-xs-12" align="center">
			<h4> Aduit Trail </h4>
			<hr>
		</div>
	</div>
	<table class="table">
		<thead>
			<tr>
				<th style="text-align: center;" width="15%"><i><u> Username </u></i></th>
				<th style="text-align: center;" width="20%"><i><u> Name </u></i></th>
				<th style="text-align: center;" width="15%"><i><u> Transaction </u></i></th>
				<th style="text-align: center;" width="15%"><i><u> Date of Transaction </u></i></th>
				<th style="text-align: center;" width="35%"><i><u> Details </u></i></th>
			</tr>
		</thead>
		<tbody>
	<?php
		$counter = "SELECT count(*) as total from audit_trail";
		$counter2 = $conn->query($counter)->fetch_assoc();
		$perpage = 20;
		$totalPages = ceil($counter2['total'] / $perpage);
		if(!isset($_GET['view'])){
		    $_GET['view'] = 0;
		}else{
		    $_GET['view'] = (int)$_GET['view'];
		}
		if($_GET['view'] < 1){
		    $_GET['view'] = 1;
		}else if($_GET['view'] > $totalPages){
		    $_GET['view'] = $totalPages;
		}
		$startArticle = ($_GET['view'] - 1) * $perpage;
		$stmtx = "SELECT * FROM audit_trail ORDER BY datetrans DESC LIMIT " . $startArticle . ', ' . $perpage;
		$resultx = $conn->query($stmtx);		
		if($resultx->num_rows > 0){
			while ($row = $resultx->fetch_assoc()) {
	?>
			<tr>
				<td style="text-align: center;"><p><?php echo $row['username']; ?></p></td>
				<td style="text-align: center;"><p><?php echo $row['realname']; ?></p></td>
				<td style="text-align: center;"><p><?php echo $row['transaction']; ?></p></td>
				<td style="text-align: center;"><p><?php echo date("M j, Y h:i:s A", strtotime($row['datetrans'])); ?></p></td>
				<td style="text-align: center;"><p><?php echo $row['transdetail']; ?></p></td>
				<!--<div class="col-xs-1">
					<?php echo $row['pcname']; ?>
				</div>-->
			</tr>
	<?php
			}
		}

	?>
		</tbody>
	</table>
	<div class="row">
		<div class="col-xs-12" align="center">
			<hr>
			<!--<label>Records <?php $startArticlex = $startArticle + 1; $perpagex = $perpage * $_GET['view']; if($perpagex > $counter2['total']){ $perpagex = $counter2['total'];} echo $startArticlex . ' - ' . $perpagex ?> </label><br>-->
			<label> Pages </label><br>
			<?php
				$prev = intval($_GET['view'])-1;					
				if($prev > 0){ echo '<a data-toggle="tooltip" title="Previous" class = "btn btn-default btn-sm" style = "margin: 5px;" href="sys/audit/' . $prev . '"> < </a>'; }
				foreach(range(1, $totalPages) as $page){
				    if($page == $_GET['view']){
				        echo '<b><span class="currentpage" style = "margin: 5px;">' . $page . '</span></b>';
				    }else if($page == 1 || $page == $totalPages || ($page >= $_GET['view'] - 2 && $page <= $_GET['view'] + 2)){
				    	if($page == 0){
				    		continue;
				    	}
				        echo '<a class = "btn btn-default btn-sm" data-toggle="tooltip" title="Page ' . $page . '" style = "margin: 5px;" href="sys/audit/' . $page . '">' . $page . '</a>';
				    }
				}
				$nxt = intval($_GET['view'])+1;
				if($nxt <= $totalPages){ echo '<a class = "btn btn-default btn-sm" data-toggle="tooltip" title="Next" style = "margin: 5px;" href="sys/audit/' . $nxt . '"> > </a>'; }
				
			?>
		</div>
	</div>
</div>