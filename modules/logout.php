<?php
	session_destroy();
?>	
	<script type="text/javascript"> 
		window.location.replace('<?php echo "$_SERVER[REQUEST_URI]";?>');		
	</script>	
	