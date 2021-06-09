<?php	

	class Application {
		
		private $s;
		private $f;
		private $e;
		private $l;
		private $u;
		private $r;
		private $rs;
		
		function __construct($i, $u, $r) {
			$this->s = date_timestamp_get(date_create());
			$this->f = $this->go();
			$this->e = date_timestamp_get(date_create())-$startTime;	
			$this->l = [];
			$this->u = $u;
			$this->r = $r;
			$this->c = $c;
		}
		
		function getResults(){
			$record = [
				"s" => $this->s,
				"f" => $this->f,
				"e" => $this->e,
				"l" => $this->l,
				"u" => $this->u,
				"r" => $this->r,
				"c" => $this->c,
			];	
		}
		
		// run
		function go(){
			$pCs = asyncURLReader($this->l);
			$sumCount = count($pCs);
			
			$results = [];
			for($i=0;$i<$sumCount;$i++){
			foreach ($regexList as $ref => $regex){
				preg_match_all($regex, $pageContents[$i], $regexData);
				if($regexData[1] && $regexData[1]!== NULL){	
					if(!isset($results[$ref])){
						$results[$ref] = [];
					}
					$results[$ref][] = $listOfUrls[$i];
				}
			}
			$this->rs = $results
		}
		
			
			
			
		}
		
		// general functions
		function asyncURLReader($lU) {
		
		$multiCurl = [];
		$result = [];
	
		$mh = curl_multi_init();
		foreach ($lU as $i => $u) {
		  $multiCurl[$i] = curl_init();
		  curl_setopt($multiCurl[$i], CURLOPT_URL,$u);
		  curl_setopt($multiCurl[$i], CURLOPT_HEADER,0);
		  curl_setopt($multiCurl[$i], CURLOPT_RETURNTRANSFER,1);
		  curl_multi_add_handle($mh, $multiCurl[$i]);
		}
		$index=null;
		do {
		  curl_multi_exec($mh,$index);
		} while($index > 0);
	
		foreach($multiCurl as $k => $ch) {
		  $result[$k] = curl_multi_getcontent($ch);

		  curl_multi_remove_handle($mh, $ch);
		}
		curl_multi_close($mh);
		
		return $result;
	}
		
	}

	$urls = ["https://www.etsy.com/au/listing/734847058/australian-native-wreath-pink-burgundy?ref=shop_home_active_17&fbclid=IwAR3_JK79wsFB-NbNGvcne11OdRbri-5hhp3oRKMfQ-TwrV6j2EBIoYF3w8A?"];
	$regex = [
	'break' 	=> '/(This\ shop\ is\ taking\ a\ short\ break)/',
	'sold'	=> '/(Sorry\,\ this\ item\ is\ sold\ ou)t/',
	'unavailable' => '/(Sorry\,\ this\ item\ is\ unavailable)/'
	];	
	$new = new $application('',$urls, $regex );












	function asyncURLReader($listOfUrls) {
		
		$multiCurl = [];
		$result = [];
	
		$mh = curl_multi_init();
		foreach ($listOfUrls as $i => $url) {
		  $multiCurl[$i] = curl_init();
		  curl_setopt($multiCurl[$i], CURLOPT_URL,$url);
		  curl_setopt($multiCurl[$i], CURLOPT_HEADER,0);
		  curl_setopt($multiCurl[$i], CURLOPT_RETURNTRANSFER,1);
		  curl_multi_add_handle($mh, $multiCurl[$i]);
		}
		$index=null;
		do {
		  curl_multi_exec($mh,$index);
		} while($index > 0);
	
		foreach($multiCurl as $k => $ch) {
		  $result[$k] = curl_multi_getcontent($ch);

		  curl_multi_remove_handle($mh, $ch);
		}
		curl_multi_close($mh);
		
		return $result;
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
		
		return $result;
	}	
	
	function recievePostData(){
		// Takes raw data from the request
		$postDataJson = file_get_contents('php://input');

		// Converts it into a PHP object
		$data = json_decode($postDataJson);
	
		$_SESSION['data']['post'] = $data;
		
		return true;
	}
	
	function transformer(){
		$listOfUrls = $_SESSION['data']['post']['urls'];
		
		$regexList = $_SESSION['data']['post']['regex'];
		// seek url page contents
		$pageContents = asyncURLReader($listOfUrls);
		
		// for each page contents retrieved
		$sumCount = count($pageContents);
		
		$results = [];
		for($i=0;$i<$sumCount;$i++){
			// for each regex filter identified
			foreach ($regexList as $ref => $regex){
				preg_match_all($regex, $pageContents[$i], $regexData);
		
				if($regexData[1] && $regexData[1]!== NULL){
					
					if(!isset($results[$ref])){
						$results[$ref] = [];
					}
					
					$results[$ref][] = $listOfUrls[$i];
				}
				
			}
		}
		
		$_SESSION['data']['response']['count']=$sumCount;
		$_SESSION['data']['response']['urls']=$results;
		return true;
	}
	
	
	$_SESSION['data']['post']['regex']=json_decode($_POST['regex']);
	$_SESSION['data']['post']['urls']=json_decode($_POST['urls']);
	
	
	// Takes raw data from the request
	//$postDataJson = file_get_contents('php://input');

	//$_SESSION['data']['post'] = $postDataJson;

	//acknowledge php input
//	gateway( 'recievePostData' );	
	
	//call async curl reader
	gateway( 'transformer' );
	
	
	/*
	echo '<pre>';
		print_r($_SESSION);
	echo '</pre>';
*/


	$response['data']['session']=$_SESSION;


	echo json_encode($_SESSION);
?>