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
		private $status;
		
		private $listOfRegularExpressions = [
			"Product Link"	=> '/meta\ property\=\"og\:url\"\ content\=\"(.{1,200})[?]/s',
			"Shop Link"	=> '/meta\ property\=\"etsymarketplace\:shop\"\ content\=\"(.{1,200})\"/',
			"Name" 			=> '/meta\ property\=\"og\:title\"\ content\=\"(.{1,400})\"\ \/\>/',
			"Regular price" => '/meta\ property\=\"product\:price\:amount\"\ content\=\"([0-9\.]{1,20})\"/',
			//"Images" 		=> '/meta\ property\=\"og\:image\"\ content\=\"(.{0,200})\"/',
			"Images" 		=> '/data\-src\-zoom\-image\=\"(.{0,400}\.jpg)\"/',
			"Short description" 	=> '/data\-product\-details\-description\-text\-content.{1,100}\>[\ \s\n]{0,50}(.{0,10000})\<\/p\>.{0,100}wt\-text\-center\-xs/s',
			
		];
	
		public function __construct(string $content){
			$this->etzyAd = []; // Instantiate stdClass object
			$this->etzyAd['pageContent'] = $content;
			$this->etzyAd['data'] = [];	
			$this->status = true;
		}

		public function collectData(){
			
			$this->etzyAd['data'] = [];		
			foreach ($this->listOfRegularExpressions as $property => $regex){
				preg_match_all($regex, $this->etzyAd['pageContent'], $data);
				
				$propertyValue = '';
				/*
				if(count($data)==0 && $this->status && (strpos($property, 'Images') !== true)){
					echo $property."::";
					var_dump($data);
					$this->status = false;
				}
				*/
					
				$propertyValue = $data[1][0];
				
				if (strpos($property, 'Short description') !== false) {
					$urlElements = explode("/", $this->etzyAd['data']['Shop Link']);
					$shortDescrition = preg_replace("/(<a.{0,200}\/a>)/", "LINK", $data[1][0]);
					
					$propertyValue = "Seller: <a target='_blank' href='{$this->etzyAd['data']['Shop Link-affilate']}'>{$urlElements[count($urlElements)-1]}</a><br>".$shortDescrition;
				}
				
				if (strpos($property, 'Images') !== false) {
					
					$slicedArray = $data[1];
					if(count($slicedArray)>3){
						$slicedArray =  array_slice($data[1], 0, 3);
					}
					$propertyValue = implode(", ", $slicedArray );
				}
				
				$this->etzyAd['data'][$property] = $propertyValue;
				
				if (strpos($property, 'Shop Link') !== false) {
					$this->etzyAd['data'][$property.'-affilate'] = $this->trackedUrl($data[1][0]);
				}
			}
			
			
			$this->etzyAd['data']['External Url'] = $this->trackedUrl($this->etzyAd['data']['Product Link']);
			$this->etzyAd['data']["Type"] = "External";
			$this->etzyAd['data']["Button text"] = "Buy Now";
			
		}
		
		private function trackedUrl($url){
			$url = str_replace(":","%3A",$url);
			$url = str_replace("/","%2F",$url);
			return AWIN.$url;
		}

		public function getStatus(){
			return $this->status;
		}
		public function run(){
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
		['Product Link', 'Shop Link', 'Shop Link Affiliate', 'Name', 'Regular price', 'Images','Short description', 'External URL', 'Type', 'Button text']
	];


	echo "<b>Sold products</b><br>";
	for($i=0; $i<count($listOfPageUrlsContent); $i++){		
		
		$obj = new EtzyLinx($listOfPageUrlsContent[$i]);
		$obj->collectData();
		$result = $obj->run();
		
		if(strlen($result['Regular price']."")>0){
			$fileData[] = $result;
		}else{
			echo $newArray[$i]."<br>";
			//("<pre>".print_r($result,true)."</pre>");
		}
	}
		
	saveToFile($fileData);

	echo "<p><a href='EtsyProducts.csv'>Download CSV file</a></p>";

	echo "ContentPages:".count($listOfPageUrlsContent).'<br>';

	$endTime = time();
	
	$mins = ($endTime - $startTime)/60;
	$seconds = ($endTime - $startTime);
	echo "Time taken: ".$seconds."secs : ".count($listOfPageUrlsContent)."links";
	
