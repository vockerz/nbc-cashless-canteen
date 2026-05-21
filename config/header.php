<?php date_default_timezone_set("Asia/Manila"); ?>
<?php ini_set('max_execution_time', 600); ?>
<?php if(isset($_GET['view'])){ $_GET['view'] = mysqli_real_escape_string($conn,$_GET['view']); } ?>
<?php ini_set('default_charset', 'utf-8'); ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title> [ <?php if(isset($title)){echo $title; }?> ] NBC Cashless System </title>
		<base href="<?php echo "http://$_SERVER[HTTP_HOST]"."/".$pagename;?>/"/>
		<meta charset="utf-8">
  		<meta name="viewport" content="width=device-width, initial-scale=1">		
		<!-- Latest compiled and minified CSS 
		<link rel="stylesheet" href="css/bootstrap-theme.min.css">
		<link rel="stylesheet" href="css/bootstrap.min.css">-->
		
		<!-- jQuery library -->
		<script src="js/jquery.min.js"></script>
		<script type="text/javascript" src="js/js.js"></script>
		<script src="js/js2.js"></script>
		<script src="js/sweetalert2@10.js"></script>
		<script src="js/jquery-3.3.1.js"></script>
		<!--<script src="js/jquery.dataTables.min.js"></script>-->
		
		<!-- Latest compiled JavaScript -->
		<script src="js/bootstrap.min.js"></script>
		<link rel="stylesheet" href="css/chosen.min.css">
		<?php //if(isset($_SESSION['insta_acc']) && $access->level > 50){ ?>
			<link rel="stylesheet" href="assets/css/normalize.css">
		    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
		    <link rel="stylesheet" href="assets/css/font-awesome.min.css">
		    <link rel="stylesheet" href="assets/css/themify-icons.css">
		    <link rel="stylesheet" href="assets/css/flag-icon.min.css">
		    <link rel="stylesheet" href="assets/css/cs-skin-elastic.css">
		    <!-- <link rel="stylesheet" href="assets/css/bootstrap-select.less"> -->
		    <link rel="stylesheet" href="assets/scss/style.css">
		    <link href="assets/css/lib/vector-map/jqvmap.min.css" rel="stylesheet">
		<?php 	//}	?>		
		<link rel="stylesheet" href="css/css.css">
		<link href='assets/fonts/opensans/css.css' rel='stylesheet' type='text/css'>
		<!--<link rel="stylesheet" href="css/jquery.dataTables.min.css.css?<?php echo rand(1,100);?>">-->
		<style type="text/css">
			div.checkbox .form-check-label{
			  font-size: 13px !important;
			}
		</style>
	</head>
	<body <?php if (!isset($_SESSION['insta_acc'])){ echo ' class = "bg-dark" '; } ?> <?php if(isset($_GET['print'])){ echo ' style="background-color: white;" '; } ?> >
