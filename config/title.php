<?php
	$page = array(
					"cashier"					=>			"Cashier",
					"meal_only"					=>			"Cashier [ Meal Only ]",
					"addtl_only"				=>			"Cashier [ Additionals Only ]",
					"reports"					=>			"Reports",
					"package_rep"				=>			"Free Meal Transaction Report",
					"transaction_rep"			=>			"Additional Meal Transaction Report",
					"accounts"					=>			"Accounts",
					"adduser"					=>			"New user",
					"emp"						=>			"Employees",
					"load_import"				=>			"Load Import",
					"products"					=>			"Products",
					"deliveries"				=>			"Deliveries",
					"print"						=>			"Print",
					"transaction_rep_agency"	=>			"Report - Agency",
					"transaction_rep_load"		=>			"Report - Load Transaction",
					"load_history"				=>			"Report - Loading History",
					"reports"					=>			"Reports",
					"max_credit"				=>			"Max Credit Settings",
					"void_transaction"			=>			"Void Transaction",
				);
	if( isset($_GET['module']) && $_GET['module'] != "" && (!isset($_GET['action']) || (isset($_GET['action']) && $_GET['action']) == '') ) {
		$_GET['module'] = str_replace("/", "", $_GET['module']);
	}
	foreach($page as $x => $tag) {
	    if((isset($_GET['module']) && $_GET['module'] == $x) && (isset($_GET['action']) && $_GET['action'] != "")){
			$title = $tag . ' - ' . ucwords($_GET['action']);
			$xx = $tag;
	   	}elseif(isset($_GET['action']) && $_GET['action'] == $x){
			$title = $tag;
	    }elseif(isset($_GET['module']) && $_GET['module'] == $x){
			$title = $tag;
	    }elseif(isset($_SESSION['insta_acc']) && isset($_GET['module']) != $x){
			$title = "Dashboard";
		}elseif(!isset($_SESSION['insta_acc'])){
			$title = "Login Page";
		}
	}

	if (!isset($title)){
		$title = 'Error: 404';
	}
	$title2 = "";
	if(isset($_GET['view'])){
		$title2 = ' [ View ]';
	//	echo '<script type = "text/javascript">$(\'button[name = "b_submit"]\').remove(); {</script>';
	}
	$butname = " Submit ";
	if(isset($_GET['edit'])){
		$title2 = ' [ Edit ]';
		$butname = " Update ";
	}
?>