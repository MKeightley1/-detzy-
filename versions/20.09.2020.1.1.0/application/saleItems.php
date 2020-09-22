<?php
	
	define("AWIN", 'https://www.awin1.com/cread.php?awinmid=10781&awinaffid=338915&clickref=&ued=');
	//include_once('traits/session.php');
	//include_once('traits/curl.php');
	
	
	function saveToFile($list){
		$file = fopen("etzyLinx.csv","w");

		foreach ($list as $line) {
		  fputcsv($file, $line);
		}

		fclose($file);
	}
	
	
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
	
	
	abstract class Shop
	{
		//use sessionManager;
		//use curlManager;
		
		public $listOfRegularExpressions = [
			"Name" 			=> '/meta\ property\=\"og\:title\"\ content\=\"(.{1,400})\"\ \/\>/',
			"Regular price" => '/meta\ property\=\"product\:price\:amount\"\ content\=\"([0-9\.]{1,20})\"/',
			"Images" 		=> '/meta\ property\=\"og\:image\"\ content\=\"(.{0,200})\"/',
			"Product Link"	=> '/meta\ property\=\"og\:url\"\ content\=\"(.{1,400})[?]/s',
			"Shop Link"	=> '/meta\ property\=\"etsymarketplace\:shop\"\ content\=\"(.{1,200})\"/',
			"Short description" 	=> '/data\-product\-details\-description\-text\-content.{1,100}\>[\ \s\n]{0,50}(.{0,1000})\<\/p\>/s',
		];
		public $name;
		public $listOfUrls;
		public $pages;
		public $headerSheet = [
			'Name',
			'Category',
			'Regular price',
			'Images',
			'Product Link-affilate',
			'Product Link',
			'Shop Link-affilate',
			'Shop Link',
			'Short description',
		
		];
		public $returnResults;
		public $soldItems;
		
		public function __construct($name) {
			$this->name = $name;
		}
		
		public function getCountUrls(){
			return $this->listOfUrls;
		}
		
		private function readData(){
			 $this->pages = async($this->listOfUrls);
			 $this->regexData();
		}
		
		private function regexData(){
			$this->returnResults = [ $this->headerSheet ];
			
			for($i=0; $i<count($this->pages); $i++){
				$newRowOfData = [];
				foreach ($this->listOfRegularExpressions as $property => $regex){
					
					$propertyValue = '';
					preg_match_all($regex, $this->pages[$i], $data);
					if (strpos($property, 'Link') !== false) {
						$newRowOfData[$property.'-affilate'] = $this->trackedUrl($data[1][0]);
					}
					$propertyValue = $data[1][0];
					if (strpos($property, 'Short description') !== false) {
						$urlElements = explode("/", $newRowOfData['Product Link']);
						$propertyValue = "<br>Seller: <a target='_blank' href='{$newRowOfData['Shop Link-affilate']}'>{$urlElements[count($urlElements)-1]}</a><br><br>".$data[1][0]."Click ‘Buy Now’ to purchase this product on Etsy and support a local artisan! This is an affiliate link, so we receive a small commission from the sale at no extra cost to you.";
					}
					
					$categoriesMatches = [
						'Christmas Tree Decorations' => ['ornament', 'bauble', 'tree'],
						'Christmas Cards' => ['card'],
						'Christmas Gift Packaging' => ['tag', 'box', 'wrapping', 'gift'],
						'Christmas Home Decor' => ['sign', 'garland'],
						'Christmas Baking' => ['cake', 'sprinkle', 'baking'],
						'Christams Stockings & Santa Sacks' => ['stocking','sack']
					];
					
					$newRowOfData[$property] = $propertyValue;
					
					if (strpos($property, 'Name') !== false) {
						$newRowOfData['category'] = '';
						
						$categoryMatchCount = 0;
						foreach ($categoriesMatches as $category => $keywords){
							
							foreach ($keywords as $keyword){
								$findme   = $keyword;
								$pos = strpos(strtolower($data[1][0]), $findme);
								if($pos !== false){
									$newRowOfData['category'] = $category;
									$categoryMatchCount++;
								}
							}
						}
						if($categoryMatchCount>1){
							$newRowOfData['category'] = '';
						}
					}
					
					
					
				}
				
				if(strlen($newRowOfData['Regular price']."")>0){
					$this->returnResults[] = $newRowOfData;
				}else{
					$this->soldItems[] = $this->listOfUrls[$i];
				}
			}	
			saveToFile($this->returnResults);
			
		}
		
		private function trackedUrl($url){
			$url = str_replace(":","%3A",$url);
			$url = str_replace("/","%2F",$url);
			return AWIN.$url;
		}
		
		public function loadShopLinks($file){
			if ($file["error"] > 0){
				return false;
			}else{
				move_uploaded_file( $file["tmp_name"], "".$file["name"] );	
				 
				$fileContent = file_get_contents("" . $file["name"]);
				$rows = preg_split("/[\s\n]+/", $fileContent);
				
				//$rows = explode("\n", $fileContent);
				array_pop($rows);
				$this->listOfUrls = $rows;
			}
			
			$this->readData();
		}
		
		// Common method
		public function getResults() {
			print("<pre>".print_r($this->returnResults,true)."</pre>");
		}
		
		// Common method
		public function getSoldResults() {
			echo "<b>Unavailable products</b>";
			echo "<ul>";
			
			
			foreach($this->soldItems as $item){
				$item2 = substr($item, 0, strpos($item, "?")); 
				$pieces = explode("/", $item2);
				echo "<li><a href='$item' target='__blank'>".end($pieces)."</a></li>";
			}
			echo "<ul>";
		}
	}
	
	class Etzy extends Shop{
		public function startApp(){
			
		}
	}

	header('Access-Control-Allow-Origin: *'); 
	ini_set('max_execution_time', 600); 

	$obj = new Etzy('Etzy');
	$obj->loadShopLinks($_FILES["uploaded"]);
	//$obj->getResults();
	
	$count = count($obj->getCountUrls());
	echo "<br><span style='font-size:100px;'>&#9924;</span><br><a href='etzyLinx.csv'>Download CSV file</a> ($count urls)<br><br><br>";
	
	$obj->getSoldResults();
	