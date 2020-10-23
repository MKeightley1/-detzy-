<?php	
	function testFunction(){
		sleep(5);		
		return true;	
	}	
	function sendEmail(){
		ini_set( 'display_errors', 1 );
		error_reporting( E_ALL );
		$from = "test@hostinger-tutorials.com";
		$to = "maurice.keightley@gmail.com";
		$subject = "1122233553666";
		$message = "2PHP mail works just fine666";
		$headers = "From:" . $from;
  		return mail($to,$subject,$message, $headers);	
	}
	function gateway( $functionName ){
		$startTime = date_timestamp_get(date_create());
 		$result = $functionName();
		$endTime = date_timestamp_get(date_create())-$startTime;
 		$record = [
			"timestamp_start" => $startTime,
			"timestamp_end" => $endTime,
			"function" => $functionName,
			"input" => 'testvar',
			"output" => $result		
		];				
		$_SESSION["logHistory"][] = $record;
	}	
	
	gateway( 'sendEmail' );	
	echo '<pre>';
		print_r($_SESSION);
	echo '</pre>';

	var_dump( $_POST );	
	var_dump( $_GET );	
?>