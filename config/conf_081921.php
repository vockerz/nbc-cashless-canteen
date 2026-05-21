<?php
	$host = "localhost";
	$uname = "root";
	$pword = "";
	$db = "nbc";
	
	$conn = new mysqli($host, $uname, $pword, $db);
	$pagename = "nbc";
	if($conn->connect_error){
		die("Connection error:". $conn->connect_error);
	}
	mysqli_query($conn, "SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'");
		
	//if(isset($_GET['action']) && $_GET['action'] == 'print'){
	//	require('fpdf.php');
	//}
	function savelogs($transaction, $transdetails){
		$host = "localhost";
		$uname = "root";
		$pword = "";
		$db = "building";
		$conn = mysqli_connect($host, $uname, $pword, $db);
		if (mysqli_connect_errno()){
			die ('Unable to connect to database '. mysqli_connect_error());
		}
		$pcname = gethostname();		
		$username = $_SESSION['usernameinsta'];
		$realname = $_SESSION['nameinsta'];
		$sqllogs = "insert into audit_trail
						(username,realname,transaction,datetrans,transdetail,pcname) 
						values
						('$username','$realname','$transaction',now(),'$transdetails','$pcname')";			
		$result = mysqli_query($conn, $sqllogs);
	}
	function alert($link, $msg) {
		if($msg <> ""){
			echo '<script type = "text/javascript">
						$(document).ready(function(){
							Swal.fire({
							  position: "center",
							  icon: "warning",
							  title: "'.$msg.'",
							  showConfirmButton: false,
							  timer: '.(isset($_SESSION['sleep']) && $_SESSION['sleep'] > 0 ? $_SESSION['sleep']*1000 : 1250).'
							}).then(function() {
							    window.location = "'.$link.'";
							});
						}); </script>';
		}else{
			echo '<script type = "text/javascript">window.location.replace("'.$link.'");</script>';
		}
	}
	function check_empty($check){
		$count = 0;
		foreach($check as $x => $tag) {
			if($x != 'cname' || $x != 'sanitary_no' || $tag != ' '){
				if($tag == ''){
					$count += 1;
					echo $x .'<br>';
				}
			}
		}
		return $count;
	}

	function stat_label($status){
		switch ($status) {
			case '1':
				$label = 'ForRelease';
				break;

			case '2':
				$label = 'Released';
				break;

			default:
				$label = 'Pending';
				break;
		}
		return $label;
	}

	function queryx($check, $type){
		$tags = "";
		foreach($check as $x => $tag) {
			if($tags == ""){
				$tags = $x;
			}else{
				if($type == 'edit'){
					echo $x . ' = ?,';
				}else{
					echo $x . ', ';
				}
			}
		}
	}
	function param_x($check){
		foreach($check as $x => $tag) {
			echo '$_POST["' . $x . '"], ';
		}
	}

	function check_echo($check){
		foreach($check as $x => $tag) {
			echo $x . ' = ' . $tag . '<br>';
		}
	}

	function ddate($date) {
		return date("M j, Y", strtotime($date));
	}
	function ddatet($date) {
		return date("M j, Y h:i A", strtotime($date));
	}
	function ddatex($date) {
		return date("M Y", strtotime($date));
	}
	function random_string($length) {
			$key = '';
			$keys = array_merge(range(0, 9));
			$keys2 = array_merge(range('A', 'Z'));
			for ($i = 0; $i < $length; $i++) {
					$key .= $keys[array_rand($keys)];
					if($i %3 == true){
				 		$key .= $keys2[array_rand($keys2)];
					}
			}
			return $key;
	}
	function tonum($str){
		if($str == ""){
			return 0;
		}
		$strx = str_replace(",", "", $str);
		return $strx;
	}

	class Field_calculate {
			const PATTERN = '/(?:\-?\d+(?:\.?\d+)?[\+\-\*\/])+\-?\d+(?:\.?\d+)?/';

			const PARENTHESIS_DEPTH = 10;

			public function calculate($input){
					if(strpos($input, '+') != null || strpos($input, '-') != null || strpos($input, '/') != null || strpos($input, '*') != null){
							//	Remove white spaces and invalid math chars
							$input = str_replace(',', '.', $input);
							$input = preg_replace('[^0-9\.\+\-\*\/\(\)]', '', $input);

							//	Calculate each of the parenthesis from the top
							$i = 0;
							while(strpos($input, '(') || strpos($input, ')')){
								$input = preg_replace_callback('/\(([^\(\)]+)\)/', 'self::callback', $input);
								$i++;
								if($i > self::PARENTHESIS_DEPTH){
										break;
								}
							}

							//	Calculate the result
							if(preg_match(self::PATTERN, $input, $match)){
								return $this->compute($match[0]);
							}
							// To handle the special case of expressions surrounded by global parenthesis like "(1+1)"
							if(is_numeric($input)){
									return $input;
							}

							return 0;
					}

					return $input;
			}

			private function compute($input){
					$compute = create_function('', 'return '.$input.';');

					return 0 + $compute();
			}

			private function callback($input){
					if(is_numeric($input[1])){
							return $input[1];
					}
					elseif(preg_match(self::PATTERN, $input[1], $match)){
							return $this->compute($match[0]);
					}

					return 0;
			}
	}
	$Cal = new Field_calculate();
?>
