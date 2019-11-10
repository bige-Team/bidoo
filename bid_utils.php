<?php
function array_byte_size($arr)
{
	$tot = 0;
	foreach ($arr as $key => $value) 
	{
		$tot += strlen($value);
	}
	return $tot;
}

//old getIds()
function get_auctions() 
{
	$str = file_get_contents("https://it.bidoo.com");

	$temp = explode("pic_prd", $str);
	for ($i=0; $i <count($temp) ; $i++) 
	{
		$start = strlen($temp[$i])-80;
		$temp[$i] = substr($temp[$i], $start);

		$temp1[$i] = explode("href='auction.php?a=", $temp[$i]);

		$temp2[$i] = explode("'", $temp1[$i][1]);
		$links[$i] = $temp2[$i][0];

		$temp3 = explode("_", $links[$i]);
		$ids[$temp3[count($temp3)-1]] = $links[$i];
	}
	array_pop($ids);	//rimuovo l'ultimo elemento che è vuoto

	return $ids;
}
?>