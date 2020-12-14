<?php	
	
	if((int)$_GET['data'][1]){
		echo json_encode($_GET);
	}else{
		echo json_encode($_POST);
	}
	