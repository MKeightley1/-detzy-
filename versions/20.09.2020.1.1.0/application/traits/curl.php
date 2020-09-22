<?php
	trait curlManager 
	{
		public function sync($fetchUrl) 
		{
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$fetchUrl);
		curl_setopt($ch, CURLOPT_HEADER,0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		
		
		$data = curl_exec($ch);
		return $data;
		$retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if (in_array($retcode, array('200', '304'))) {
			return $data;
		} else{
			return null;
		}
	}
	
		public function async($chArray) 
		{
		
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
		
		public function local()
		{
			
		}
			
	}