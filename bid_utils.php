<?php
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
		if($i != count($temp)-1)
		{
			$temp2[$i] = explode("'", $temp1[$i][1]);
			$links[$i] = $temp2[$i][0];
			$temp3 = explode("_", $links[$i]);
			$ids[$temp3[count($temp3)-1]] = $links[$i];
		}
	}
	//array_pop($ids);	//rimuovo l'ultimo elemento che è vuoto
	return $ids;
}

function loop(&$ids, &$toRemove)
{
	do
	{
		$hour = date("H");
		$min = date("i");

		if(!($hour >= 0 && $hour < 12)) //se non siamo nell'orario di stop
		{	
			foreach ($ids as $key => $value) 
			{
				$s = file_get_contents('https://it.bidoo.com/data.php?ALL='.$key.'&LISTID=0');	//stringa del file php
				$new = generaArray($s, $key, $ids);

				if(!is_null($new))
				{		//se ci sono aggiornamenti da fare
					create_table($value);
					insert_array($value, $new);
				}
			}

			foreach ($toRemove as $value)
			{
				echo "rimossa l'asta " . $ids[$value];
				unset($ids[$value]);	//rimuove l'asta dall'array delle aste
			}
			$toRemove = [];	//resetto l'array con gli elementi da rimuovere
		}
	}while(true/*!isset($_REQUEST['STOP'])*/);
}

function array_to_string($array, $separator)
{
	$s = "";
	foreach ($array as $key => $value) 
	{
		$s .= $value . $separator;
	}
	$s = substr($s, 0, -1);//Remove last char
	return $s;
}

function string_to_array($string, $separator)
{
	return explode($separator, $string);
}

function auction_check_status($auction)
{
	$s = file_get_contents("https://it.bidoo.com/data.php?ALL=$auction&LISTID=0");
	if(strpos($s, 'ON') == true)
		return 1;
	elseif(strpos($s, 'OFF') == true)
		return 0;
	else 
		return null;
}
?>