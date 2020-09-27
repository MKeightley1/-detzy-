<?php


	$testData = [
		"https://www.etsy.com/au/listing/734847058/australian-native-wreath-pink-burgundy?ref=shop_home_active_17&fbclid=IwAR3_JK79wsFB-NbNGvcne11OdRbri-5hhp3oRKMfQ-TwrV6j2EBIoYF3w8A",
		"https://www.etsy.com/au/listing/526065852/merry-and-bright-wood-christmas-xmas?ga_order=most_relevant&ga_search_type=all&ga_view_type=gallery&ga_search_query=xmas&ref=sr_gallery-5-43&organic_search_click=1",
		"https://www.etsy.com/au/listing/734847058/australian-native-wreath-pink-burgundy?ref=shop_home_active_17&fbclid=IwAR3_JK79wsFB-NbNGvcne11OdRbri-5hhp3oRKMfQ-TwrV6j2EBIoYF3w8A?",
	];


	
	function async($chArray) {
	
	$multiCurl = array();
	$result = array();
	
	$mh = curl_multi_init();
	foreach ($chArray as $i => $url) {
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
	
	
	/*
	function arrayTrans($n)
{
    return ([$n]);
}
*/




	function saveLog($links){
		
		
		//$b = array_map('arrayTrans', $fileContents);
		//print_r($b);
		
		//var_dump($links);
		saveFile("log.csv","w", $links);	
	}
	function saveFile($filename, $method, $list = NULL){
		
		$file = fopen($filename, $method);

		foreach ($list as $line) {
		  fputcsv($file, $line);
		}

		fclose($file);
	}
	
	
	class linxChecker{
		private $links;
		private $linkCheckerRegex = [
			'break' 	=> '/(This\ shop\ is\ taking\ a\ short\ break)/',
			'sold'	=> '/(Sorry\,\ this\ item\ is\ sold\ out)/'
		];
		private $results;
		
		public function __construct(array $links){
			$this->links = $links; // Instantiate stdClass object
			$this->getLinkContents();
		}
		private function getLinkContents(){
			
			$listOfPageUrlsContent = async($this->links);
			
			$sumCount = count($listOfPageUrlsContent);
			for($i=0;$i<$sumCount;$i++){
				foreach ($this->linkCheckerRegex as $ref => $regex){
					preg_match_all($regex, $listOfPageUrlsContent[$i], $data);
					
					if(isset($data[1][0])&&$data[1][0]!=''){
						$newArray = [
							$this->links[$i] ,
							$ref,
							gmdate(DATE_RFC822)
						];
						
						$this->results[]= $newArray;
					}
				}
			}
		}
		public function getResults(){
			return $this->results;
		}
	}
	

	//receive input
	$demoMode = false;
	if(isset($_GET['test'])){
		$demoMode = true;
	}
	
	$listOfLinks = [];
	if($demoMode || !isset($_POST["links"])){
		$listOfLinks = $testData;
	}else{
		$listOfLinks = $_POST["links"];
	}
	
	//var_dump($listOfLinks);
	$new = new linxChecker($listOfLinks);
	
	
	saveLog($new->getResults());
	echo json_encode($new->getResults());

	