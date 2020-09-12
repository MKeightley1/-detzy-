<?php
	
	define("AWIN", 'https://www.awin1.com/cread.php?awinmid=10781&awinaffid=338915&clickref=&ued=');

	function multiCURL($chArray) {
		
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

	function saveToFile($list){
		$file = fopen("EtsyProducts.csv","w");

		foreach ($list as $line) {
		  fputcsv($file, $line);
		}

		fclose($file);
	}

	function uploadFileContents( $file ){
		if ($file["error"] > 0){
			return false;
		}else{
			move_uploaded_file( $file["tmp_name"], "".$file["name"] );	
			 
			$fileContent = file_get_contents("" . $file["name"]);
			$rows = preg_split("/[\s\n]+/", $fileContent);
			
			
			//$rows = explode("\n", $fileContent);
			array_pop($rows);
			return $rows;
		}
	}

	class EtzyLinx{
		private $etzyAd;
		
		private $listOfRegularExpressions = [
			"Name" 			=> '/meta\ property\=\"og\:title\"\ content\=\"(.{1,400})\"\ \/\>/',
			"Regular price" => '/meta\ property\=\"product\:price\:amount\"\ content\=\"([0-9\.]{1,20})\"/',
			"Images" 		=> '/meta\ property\=\"og\:image\"\ content\=\"(.{0,200})\"/',
			"Product Link"	=> '/meta\ property\=\"og\:url\"\ content\=\"(.{1,400})[?]/s',
			"Shop Link"	=> '/meta\ property\=\"etsymarketplace\:shop\"\ content\=\"(.{1,200})\"/',
			"Short description" 	=> '/data\-product\-details\-description\-text\-content.{1,100}\>[\ \s\n]{0,50}(.{0,3000})\<\/p\>.{0,100}wt\-text\-center\-xs/s',
		];
	
		public function __construct(string $content){
			$this->etzyAd = []; // Instantiate stdClass object
			$this->etzyAd['pageContent'] = $content;
			$this->etzyAd['data'] = [];	
		}

		public function collectData(){
			
			$this->etzyAd['data'] = [];		
			foreach ($this->listOfRegularExpressions as $property => $regex){
				preg_match_all($regex, $this->etzyAd['pageContent'], $data);
				
				$propertyValue = '';
				
				if (strpos($property, 'Link') !== false) {
					$this->etzyAd['data'][$property.'-affilate'] = $this->trackedUrl($data[1][0]);
				}
				
				$propertyValue = $data[1][0];
				
				if (strpos($property, 'Short description') !== false) {
					$urlElements = explode("/", $this->etzyAd['data']['Shop Link']);
					
					//print("<pre>".print_r($data,true)."</pre>");
					$propertyValue = "Seller: <a target='_blank' href='{$this->etzyAd['data']['Shop Link-affilate']}'>{$urlElements[count($urlElements)-1]}</a><br>".$data[1][0];
					echo $propertyValue;
				}
				
				
				$this->etzyAd['data'][$property] = $propertyValue;
				
			}
			
			$this->etzyAd['data']["Button text"] = "Buy Now";
			$this->etzyAd['data']["Type"] = "External";
			
		}
		
		private function trackedUrl($url){
			$url = str_replace(":","%3A",$url);
			$url = str_replace("/","%2F",$url);
			return AWIN.$url;
		}

		public function run()
		{
			return $this->etzyAd['data'];
		}
	}


	header('Access-Control-Allow-Origin: *'); 
	ini_set('max_execution_time', 600); 


	$startTime = time();
	$currentDate = date("d-m-Y");

	$newArray = uploadFileContents($_FILES["uploaded"]);
	

	$listOfPageUrlsContent = multiCURL($newArray);


	$fileData = [
		['Name', 'Regular price', 'Images','External URL','Product Link','Shop Link Affilate','Shop Link', 'Short description', 'Button text', 'Type']
	];

	for($i=0; $i<count($listOfPageUrlsContent); $i++){
		$obj = new EtzyLinx($listOfPageUrlsContent[$i]);
		$obj->collectData();
		$result = $obj->run();
		$fileData[] = $result;
	}
		
	saveToFile($fileData);


	//print("<pre>".print_r($newArray,true)."</pre>");




	echo "<p><a href='EtsyProducts.csv'>Download CSV file</a></p>";

	echo "ContentPages:".count($listOfPageUrlsContent).'<br>';

	$endTime = time();
	
	$mins = ($endTime - $startTime)/60;
	$seconds = ($endTime - $startTime);
	echo "Time taken: ".$seconds."secs : ".count($listOfPageUrlsContent)."links";
	
