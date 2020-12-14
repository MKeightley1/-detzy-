<?php	
	
	if((int)$_GET['data'][0]){
		echo json_encode($_GET);
	}else{
		echo json_encode($_POST);
	}
	