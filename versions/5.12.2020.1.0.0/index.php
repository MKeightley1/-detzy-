<?php	
	$regex = [
      'break' 	=> '/(This\ shop\ is\ taking\ a\ short\ break)/',
      'sold'	=> '/(Sorry\,\ this\ item\ is\ sold\ out)/',
      'unavailable' => '/(Sorry\,\ this\ item\ is\ unavailable)/'
    ];
	
	$postDataData =  json_encode([[
		'meta_value'=>'https://www.awin1.com/cread.php?awinmid=10781&awinaffid=338915&clickref=&ued=https%3A%2F%2Fwww.etsy.com%2Fau%2Flisting%2F744470061%2Fquokkan-around-the-christmas-tree',
		'post_title'=>'Quokkan Around the Christmas Tree Greeting Card',
		'post_status'=>'publish',
		'guid'=>'https://aussiemadechristmas.com.au/product/quokkan-around-the-christmas-tree/'
	]]);


	//collect GET data
	$flagRegister = isset($_GET['register'])?$_GET['register'] : '0';
	//collect php post data
	if((int)$flagRegister[0]){
		$postDataData = json_decode($_POST);
	}
	$postDataData = deConverter($postDataData);
	
	
	$_SESSION['data']['post']=json_decode($postDataData);
	
	
	
	
	
	
	echo json_encode($_SESSION);
	


	function deConverter($data){
		foreach($data as $key => $value){
			$tempString = $result['meta_value'];
			$tempString = str_replace("%2F","/",$tempString);
			$tempString = str_replace("%3A",":",$tempString);	
			$tempString = explode("=", $tempString)[4];
			
			$data[$key]['ownUrl'] = tempString);
		}
		return $data;
	}
	
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
	
?>