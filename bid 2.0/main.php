<?php
include_once('utils.php');
set_time_limit(0);	//rimuove il tempo massimo per l'esecuzione di uno script php

while (true) {
	$hour = date('H')+1;
	if($hour <= 23 && $hour > 12) {
		$ids = getBids();	//metodo che genera l'array con le aste da bidoo
		//ids[] contiene: id => nome
		//inserisco le aste nel database
		foreach ($ids as $key => $value) {
			insert_auc($key, $value);
		}
		sleep(120);
	}
	else {
		sleep(600);
	}
}

function getBids() {
	$opts = array('http'=>array('timeout'=>5,));
	$ctx = stream_context_create($opts);

	$str = file_get_contents("https://it.bidoo.com", false, $ctx);

	$temp = explode("pic_prd", $str);
	for ($i=0; $i <count($temp) ; $i++) {
		$start = strlen($temp[$i])-90;
		$temp[$i] = substr($temp[$i], $start);

		$temp1[$i] = explode("href='auction.php?a=", $temp[$i]);

		if(isset($temp1[$i][1])){
			$temp2[$i] = explode("'", $temp1[$i][1]);
			$links[$i] = $temp2[$i][0];

			$temp3 = explode("_", $links[$i]);
			$ids[$temp3[count($temp3)-1]] = $links[$i];
		}
		
	}

	return $ids;
}