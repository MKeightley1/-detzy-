<?php	

	$regex = [
      'break' 	=> '/(This\ shop\ is\ taking\ a\ short\ break)/',
      'sold'	=> '/(Sorry\,\ this\ item\ is\ sold\ out)/',
      'unavailable' => '/(Sorry\,\ this\ item\ is\ unavailable)/'
    ];
	
	$postDataData =  json_encode([[
'meta_value'=>'https://www.awin1.com/cread.php?awinmid=10781&awinaffid=338915&clickref=&ued=https%3A%2F%2Fwww.etsy.com%2Fau%2Flisting%2F744470061%2Fquokkan-around-the-christmas-tree',
'post_title'=>'Quokka'n Around the Christmas Tree Greeting Card',
'post_status'=>'publish',
'guid'=>'https://aussiemadechristmas.com.au/product/quokkan-around-the-christmas-tree/'
]]);


	//collect GET data
	$flagRegister = $_GET['register'];
	//collect php post data
	if((int)$flagRegister[0]){
		$postDataData = $_POST;
	}
	
	$_SESSION['data']['post']=json_decode($postDataData);
	echo json_encode($_SESSION);
	









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
	
	
//	$_SESSION['data']['post']['regex']=json_decode($_POST['regex']);
//	$_SESSION['data']['post']['urls']=json_decode($_POST['urls']);
	
	
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


//	$response['data']['session']=$_SESSION;


//	echo json_encode($_SESSION);
?>