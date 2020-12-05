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
		$postDataData = $_POST;
	}
	
	$_SESSION['data']['post']=json_decode($postDataData);
	echo json_encode($_SESSION);
	



?>