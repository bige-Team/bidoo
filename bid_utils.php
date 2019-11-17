<?php
/*
* Get the auctions from it.bidoo.com
* $ids[n_auction] => [auction_name] 
*/
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

/*
* Converts an array into a string using $separator
*/
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

/*
* Converts a string to an array using $separator
*/
function string_to_array($string, $separator)
{
	return explode($separator, $string);
}

/*
* Checks if the auction is terminated, if so update the table auction_tracking
*/
function check_auctions_status()
{
	$res = query_to_bidoo_stats("SELECT a.id from auction_tracking as a where a.assigned=1");
	$res = $res->fetch_all();#$res[0] => Array([0] => id);
	foreach ($res as $key => $value) 
	{
		$s = file_get_contents("https://it.bidoo.com/data.php?ALL=$value[0]&LISTID=0");
		if(strpos($s, 'OFF') == true)
		{
			query_to_bidoo_stats("UPDATE auction_tracking as a SET a.terminated = 1 WHERE a.name='$value'");
		}
	}
}

/*
* Get and insert auctions into the table bidoo_stats
*/
function get_and_insert_auctions()
{
	$hour = date("H");
	if(!($hour >= 0 && $hour < 12))
	{
		//Auctions running
		$auctions = get_auctions();
		$l = connect_to_stats();
		foreach ($auctions as $key => $value) 
			$l->query("INSERT INTO auction_tracking VALUES ('$value', $key, 0, 0, 0)");
		$l->close();
		return $auctions;	
	}
	else
	{
		//Auctions in pause
		return null;
	}
}

/*
* $auctions [$i]=>0->[name],1->[id]
*/
function analize_auctions($auctions, &$auctions_count)
{
	$res = "";
	do
	{
		for ($i=0; $i < count($auctions); $i++) 
		{ 
			$name = $auctions[$i][0];
			$id = $auctions[$i][1];
			$s = file_get_contents("https://it.bidoo.com/data.php?ALL=$id&LISTID=0", false, get_stream_context(1));
			$res = generaArray($s, $id, $name, $auctions);

			if(!is_null($res) && is_array($res))
			{
				#Everything ok
				insert_array($name, $res);
			}
			elseif(!is_array($res) && $res != "NOT_STARTED_YET")
			{
				#Auction closed
				unset($auctions[$res]);#!! NOT WORKING
				#GET NEW AUCTION:
				/*
					Check if db is not being updated
					Get the needed auctions
				*/
				$auctions_count--;
				echo "[" . getmypid() . "]: Array count: " . count($auctions) . ", num: $auctions_count\n";
			}
			if($res  == "NOT_STARTED_YET")
			{
				#TODO: put auction aside and pick it up after 5 sec
				sleep(1);
			}
		}
	}while($res != "BREAK");
	echo "[" . getmypid() . "]: Breaked\n";
}

/*
* Analizes the php file
*/
function generaArray($s, $key, $name, $ids)
{	
	$pezzi = explode("|", $s);
	$arr = scaricaArray($name);	//prendo dal database le ultime 10 puntate dell'asta

	//1571240953*[8266194;ON;1571241000;1;;,]()		asta che deve ancora iniziare
	if(strpos($s, 'ON') == true) 
	{
		//se l'asta è in corso
		if(count($pezzi) > 1) 
		{
			//se non c'è almeno una puntata allora l'asta deve ancora iniziare e non salvo nulla
			$primoPezzo = explode(",", $pezzi[0]);
			$primaRiga2 = explode(";", $primoPezzo[1]);

			//prendo l'ultimo utente che ha puntato
			$puntate = $primaRiga2[0];
			$nome = $primaRiga2[1];
			$time = $primaRiga2[2];
			$tipo = $primaRiga2[3];	//1 e 2 = manuale, 3 e 4 = auto

			$primo = $puntate.';'.$nome.';'.$time.';'.$tipo;
			$pezzi[0] = $primo;	//array con le info delle puntate dell'asta
			$pezzi[count($pezzi)-1] = substr($pezzi[count($pezzi)-1], 0, -3);	//prendo la substring togliendo ]()
			//ribalto l'array
			
			//ora su $pezzi ho un'array di stringhe, una stringa è una puntata formattata: puntate_totali;nome;timestamp;tipo_puntata
			if(!is_null($pezzi))
			{	//se l'array non è vuoto
				$pezzi = array_reverse($pezzi);	//lo rovescio
				if(!is_null($arr))
				{	//se la tabella di quell'asta non è vuota
					$elems = array_diff($pezzi, $arr);
					return $elems;
				}
				else
					return $pezzi;
			}
			return null;
		}
		else 
		{
			//l'asta deve ancora iniziare
			echo "[" . getmypid() . "]: Auction '$name' not started yet\n";
			return "NOT_STARTED_YET";
		}
	}
	else if(strpos($s, 'OFF') == true) 
	{
		#AUCTION CLOSED
		//TODO: decrease count of $auctions_count
		echo "[" . getmypid() . "]: Auction '$name' closed\n";
		query_to_bidoo_stats("UPDATE auction_tracking as a SET a.terminated=1 WHERE a.name='$name'");
		return $name;
	}
	else 
	{
		#Auction in pause
		return null;
	}
}

function scaricaArray($name) 
{
	$arr = last_10($name);	//prendo i dati delle ultime 10 puntate dal database
	$res = array();
	if($arr != false)
	{
		$arr = $arr->fetch_all();	//trasformo i dati per poterli leggere in un array di array
		$i = 0;
		foreach ($arr as $value)
		{
			$puntate = $value[0];
			$nome = $value[1];
			$time = $value[2];
			$tipo = $value[3];
			$elem = $puntate.';'.$nome.';'.$time.';'.$tipo;
			$res[$i] = $elem;
			$i++;
		}
		return $res;
	}
	return null;
}

/*
* Pass it to a file_get_contents to set the timeout timer to $timer
*/
function get_stream_context($timer)
{
	return $ctx = stream_context_create(array('http'=>array('timeout' => $timer,)));
}
?>