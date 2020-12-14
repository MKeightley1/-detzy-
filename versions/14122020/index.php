<?php	
	
	if((int)$_GET['data'][1]){
		echo json_encode($_GET['data']);
	}else{
		echo json_encode($_POST['data']);
	}
	