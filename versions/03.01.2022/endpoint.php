<?php	

	// MOE Developer - 2022-JAN-02

	class Application {
		
		private $st;
		private $f;
		private $e;
		private $u;
		private $r;
		private $rs = [];
		private $c = 0;
		
		function __construct($i, $u, $r, $m) {
			
			$this->st = date_timestamp_get(date_create());
			$this->u = $u;
			$this->r = $r;
			$this->m = $m;
			$this->f = $this->go();
			$this->du = date_timestamp_get(date_create())-$this->st;	
		}
		
		function getResults(){
			return [
				"st" => $this->st,
				"du" => $this->du,
				"c" => $this->c,
				"rs" => $this->rs,
			];	
		}
		
		// run
		function go(){
			if( count($this->u) ){
				$results = [];
				for($s=0;$s<count($this->u);$s++){
					$pC = file_get_contents($this->u[$s]);
	
					$result = [];
					foreach ($this->r as $ref => $regex){
						preg_match_all($regex, $pC, $regexData);
						
						if($regexData[1] && $regexData[1]!== NULL){	
							if($this->m === 0){
								$result[] = $ref; 
							}else{
								$matches = [];
								for($d=0;$d<count($regexData[1]); $d++){
									$matches[] = $regexData[1][$d];
								}
								$result[] = [$ref=>$matches]; 
							}
						}
					}
					$ele = explode("/", $this->u[$s]);
					$results[$ele[count($ele)-1]] = $result;
				}
				
				$this->c = count($results);
				$this->rs = $results;

			}
		}
		
	}

		
	$test = isset($_GET['test'])? $_GET['test'] : 0 ;

	if($test){
		$data = json_decode(file_get_contents('php://input'));
	}
	
	$u = $data['u'];
	$r = $data['r'];
	$m = $data['m'];
	$new = new Application('',$data['u'], $data['r'], $data['m']);

	echo json_encode($new->getResults());

