<?php

/*
    Last Edited: 24 July, 2022
 */

ini_set('max_execution_time', 600); //600 seconds
header('Access-Control-Allow-Origin: *');

run();

function run(){
		if (isset($_GET['test']) && $_GET['test'] == '1') {
		$postData = [
			'checkOnly' => isset($_GET['checkOnly']) && $_GET['checkOnly'] == '1',
			'async' => isset($_GET['async']) && $_GET['async'] == '1',
			'u' => [
				'http://www.example.net'
			],
			'r' => [
				'Sold' => "/(domain is for use in illustrative examples in documents)/",
			],
		];
		echo json_encode(controller($postData));
	} else {
		$data = file_get_contents('php://input');
		$data = json_decode($data, true);
		
		$postData = [
			'checkOnly' => false,
			'async' => false,
			'u' => $data['u'],
			'r' => $data['r'],
		];
		echo json_encode(controller($postData));
	}
}

function controller($postData = []){
    $urls = $postData['u'];
    $regexRules =  $postData['r'];
    $checkOnly = $postData['checkOnly'];
    $contents = $postData['async']? multi_thread_request($urls) : single_thread_request($urls);

    foreach ($contents as $url => $content){
        $updatedValue = [];
        foreach ($regexRules as $key => $regex){
            $matches = false;
            preg_match_all($regex, $content, $data);
            if($data[1]){
                if($checkOnly){
                    $matches = true;
                }else{
                    $matches = [];
                    foreach ($data[1] as $matchValue ){
                        $matches[] = $matchValue;
                    }
                }
            }
            $updatedValue[] = [$key => $matches];
        }
        $contents[$url] = $updatedValue;
    }
    return $contents;
}

function multi_thread_request($nodes){
    $res = [];
    $mh = curl_multi_init();
    $curl_array = array();
    foreach($nodes as $i => $url)
    {
        $curl_array[$i] = curl_init($url);
        curl_setopt($curl_array[$i], CURLOPT_RETURNTRANSFER, true);
        curl_multi_add_handle($mh, $curl_array[$i]);
    }
    $running = NULL;
    do {
        usleep(10000);
        curl_multi_exec($mh,$running);
    } while($running > 0);

    foreach($nodes as $i => $url)
    {
        $res[$url] = curl_multi_getcontent($curl_array[$i]);
    }

    foreach($nodes as $i => $url){
        curl_multi_remove_handle($mh, $curl_array[$i]);
    }
    curl_multi_close($mh);
    return $res;
}

function single_thread_request($nodes){
    $res = [];
    foreach($nodes as $url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        $res[$url] = $output;
    }
    return $res;
}


