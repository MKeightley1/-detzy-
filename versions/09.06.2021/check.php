<?php	

	class Application {
		
		private $s;
		private $f;
		private $e;
		private $l;
		private $u;
		private $r;
		private $rs = [];
		private $c = 0;
		
		function __construct($i, $u, $r) {
			
			$this->s = date_timestamp_get(date_create());
		
			$this->l = [];
			$this->u = $u;
			$this->r = $r;
			$this->f = $this->go();
			$this->e = date_timestamp_get(date_create())-$this->s;	
		}
		
		function getResults(){
			return [
				"s" => $this->s,
				"e" => $this->e,
				"c" => $this->c,
				"rs" => $this->rs,
			];	
		}
		
		// run
		function go(){
			if( count($this->u) ){
				$pCs = $this->asyncURLReader($this->u);
			//	$pCs = [file_get_contents($this->u[0])];
			
				$sumCount = count($pCs);
				$results = [];
				for($i=0;$i<$sumCount;$i++){
					$result = [];
					foreach ($this->r as $ref => $regex){
						preg_match_all($regex, $pCs[$i], $regexData);
						
					
						if($regexData[1] && $regexData[1]!== NULL){	
							$result[] = $ref; 
						}
					}
					$ele = explode("/", $this->u[$i]);
					$results[$ele[count($ele)-1]] = $result;
				}
				$this->rs = $results;
				$this->c = $sumCount;
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

		$postDataJson = file_get_contents('php://input');
		$data = json_decode($postDataJson);
		$u = $data->u;
		$r = $data->r;
		$new = new Application('',$u, $r );

		echo json_encode($new->getResults());

