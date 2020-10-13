<?php

	define("LOGFILE", "log.csv");
	
	//startScript
	$startTimestamp = strtotime("now");
	
	$devMode = false;
	$traceLog = [];
	$printResults = false;
	$disableEmail = false;
	
	if(isset($_GET['devMode'])){
		$devMode = true;
	}
	if(isset($_GET['disabelEmail'])){
		$disableEmail = true;
	}
	if(isset($_GET['print'])){
		$printResults = true;
	}
	
	//retrieveUrlsFromDb
	$urls = ['https://www.etsy.com/au/listing/253784594/set-of-4-australian-golden-wattle'];
	$traceLog[] = [strtotime("now")-$startTimestamp,'getDBUrls'];
	
	//receive address Urls & their status
	$returnedUrls = ['https://www.etsy.com/au/listing/253784594/set-of-4-australian-golden-wattle'=>'soldout'];
	$traceLog[] = [strtotime("now")-$startTimestamp,'getExternalUrls'];
	
	

	function saveFile($list = []){
		$file = fopen(LOGFILE, "w");

		foreach ($list as $line) {
		  fputcsv($file, $line);
		}
		fclose($file);
	}
	function loadFile(){
		$result = [];
		if (($handle = fopen(LOGFILE, "r")) !== FALSE) {
			while (($row = fgetcsv($handle, 0, ",")) !== FALSE) {
				$result[] = $row;
			}
			fclose($handle);
		}
		return $result;
	}
	
	
	
	
	

	
	

	//create new log
	$newLog = [
		$startTimestamp,
		strtotime("now")-$startTimestamp,
		count($urls), count($returnedUrls)
	];
	$results = loadFile();
	array_unshift($results , $newLog);
	$results = array_slice($results, 0, 5, true);
	
	saveFile($results);

	if($devMode){
		print("<pre>".print_r($traceLog,true)."</pre>");
	}

	if($printResults){
		print("<pre>".print_r($returnedUrls,true)."</pre>");
	}
	if(!$disableEmail){
		//sendEmail
		$msg = "First line of text\nSecond line of text";
		mail("maurice.keightley@gmail.com","Xmas Shop",$msg);
	}
	
	
	
	
	
	
	
	
	