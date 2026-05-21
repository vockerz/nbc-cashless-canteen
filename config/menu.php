<aside id="left-panel" class="left-panel">
	<nav class="navbar navbar-expand-sm navbar-default">
		<div class="navbar-header">
			<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#main-menu" aria-controls="main-menu" aria-expanded="false" aria-label="Toggle navigation">
				<i class="fa fa-bars"></i>
			</button>
			<a class="navbar-brand" href="./"><img style = "padding: 3px;" class="align-content" src="images/logo.jpg" height="90px" width="90px" alt=""></a>
		</div>
		<div id="main-menu" class="main-menu collapse navbar-collapse">
			<ul class="nav navbar-nav">
				<li class="active">
					<a href="./"> <i class="menu-icon fa fa-dashboard"></i>Dashboard </a>
				</li>
				<h3 class="menu-title">Transactions</h3><!-- /.menu-title -->
				<?php	if($access->level >= 3){	?>
				<li class="menu-item-has-children dropdown <?php if(isset($_GET['module']) && ($_GET['module'] <> 'main' && $_GET['module'] <> 'reports' && $_GET['module'] <> 'certifications' && $_GET['module'] <> 'admin' && $_GET['module'] <> 'accounts')){ echo 'open show'; } ?>">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="<?php if(isset($_GET['module']) && ($_GET['module'] <> 'reports' && $_GET['module'] <> 'main' && $_GET['module'] <> 'certifications' && $_GET['module'] <> 'admin' && $_GET['module'] <> 'accounts')){ echo 'true';}else{echo 'false';}?>"> <i class="menu-icon fa fa-file-text"></i>Module</a>
					<ul class="sub-menu children dropdown-menu <?php if(isset($_GET['module']) && ($_GET['module'] <> 'reports' && $_GET['module'] <> 'main' && $_GET['module'] <> 'certifications' && $_GET['module'] <> 'admin' && $_GET['module'] <> 'accounts')){ echo 'show'; } ?>">
						<li><i class="fa fa-file-text"></i><a href="cashier">Cashier</a></li>
						<li><i class="fa fa-file-text"></i><a href="addtl_only">Additional Only</a></li>
						<li><i class="fa fa-file-text"></i><a href="void_transaction">Void Transaction</a></li>
					</ul>
				</li>
				<?php  }  ?>
				<li class="menu-item-has-children dropdown <?php if(isset($_GET['module']) && $_GET['module'] == 'reports'){ echo 'open show'; } ?>">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="<?php if(isset($_GET['module']) && $_GET['module'] == 'reports'){ echo 'true';}else{echo 'false';}?>"> <i class="menu-icon fa fa-folder"></i>Report</a>
					<ul class="sub-menu children dropdown-menu <?php if(isset($_GET['module']) && $_GET['module'] == 'reports'){ echo 'show'; } ?>">
						<?php	if($access->level >= 1){	?>
							<li><i class="fa fa-file"></i><a href="reports/package_rep">Free Meal Trans. Report</a></li>
							<li><i class="fa fa-file"></i><a href="reports/transaction_rep">Addt'l Meal Trans. Report</a></li>
							<li><i class="fa fa-file"></i><a href="reports/transactions">Transaction Report</a></li>
							<li><i class="fa fa-file"></i><a href="reports/transaction_rep_load">Load Transaction Report</a></li>
							<li><i class="fa fa-file"></i><a href="reports/transaction_rep_agency">Meal Trans. Report (Agency)</a></li>
							<li><i class="fa fa-file"></i><a href="reports/load_history">Loading History</a></li>
						<?php	}	?>
					</ul>
				</li>
				<?php	if($access->level != 2){	?>
				<li class="menu-item-has-children dropdown <?php if(isset($_GET['module']) && ($_GET['module'] == 'admin' || $_GET['module'] == 'accounts')){ echo 'open show'; } ?>">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="<?php if(isset($_GET['module']) && $_GET['module'] <> 'main' && ($_GET['module'] == 'accounts' || $_GET['module'] == 'admin')){ echo 'true';}else{echo 'false';}?>"> <i class="menu-icon fa fa-cogs"></i>Administration</a>
					<ul class="sub-menu children dropdown-menu <?php if(isset($_GET['module']) && ($_GET['module'] == 'admin' || $_GET['module'] == 'accounts')){ echo 'show'; } ?>">									
						<?php	if($access->level >= 3){	?>
							<li><i class="fa fa-apple"></i><a href="admin/products">Products</a></li>
							<li><i class="fa fa-dropbox"></i><a href="admin/deliveries">Deliveries</a></li>
						<?php	}	?>
						<?php	if($access->level <= 1 || $access->level > 50){	?>
							<li><i class="menu-icon fa fa-users"></i><a href="accounts/emp">Membership</a></li>
						<?php	}	?>
						<?php	if($access->level > 50){	?>
							<li><i class="menu-icon fa fa-file"></i><a href="max_credit">Max Credit</a></li>
							<li><i class="menu-icon fa fa-user"></i><a href="accounts">Accounts</a></li>
						<?php	}	?>
					</ul>
				</li>
				<?php	}	?>
			</ul>
		</div><!-- /.navbar-collapse -->
	</nav>
</aside><!-- /#left-panel -->