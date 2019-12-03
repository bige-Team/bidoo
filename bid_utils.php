<?php
/*
* Get the auctions from it.bidoo.com
* $auctions[n_auction] => [auction_name] 
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
	return $ids;
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
* Get and insert auctions into the table bidoo_stats
*/
function get_and_insert_auctions()
{
	$hour = date("H")+1;
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
function analize_auctions($auctions, $auctions_count, $max_auctions)
{
	$res = "";
	$pos_to_delete = array();
	$hour = date("H")+1;
	do
	{
		if(!($hour >= 0 && $hour < 12))
		{
			for ($i=0; $i < count($auctions); $i++) 
			{ 
				$name = $auctions[$i][0];
				$id = $auctions[$i][1];
				$s = @file_get_contents("https://it.bidoo.com/data.php?ALL=$id&LISTID=0", false, get_stream_context(1));//Set the timeout timer to 1, @ -> suppress warning
				

				$res = analize_page($s, $name);

				if(!is_null($res) && is_array($res))
				{
					#Everything ok
					insert_array($name, $res);
				}
				elseif(!is_array($res) && $res == "CLOSED")
				{
					#Auction closed
					echo "[" . getmypid() . "]: Auction $name closed" . date("H:i:s") . "\n";
					$pos_to_delete[] = $i;				
				}
			}
			//Remove the closed auctions from the array
			for($i = 0; $i < count($pos_to_delete); $i++)
			{
				unset($auctions[$pos_to_delete[$i]]);
				$auctions_count--;
			}
			$auctions_temp = null;
			$auctions_temp = array();
			foreach ($auctions as $key => $value)
			{
				$auctions_temp[] = $value;
			}
			$auctions = $auctions_temp;
			$pos_to_delete = null;
			$pos_to_delete = array();

			//Get new auctions
			$needed_auctions = $max_auctions - $auctions_count;
			//if($needed_auctions < $max_auctions)
			//{
				$new_auctions = query_to_bidoo_stats("SELECT a.name, a.id FROM auction_tracking as a WHERE a.assigned=0 AND a.terminated=0 ORDER BY a.name LIMIT $needed_auctions");
				$new_auctions = $new_auctions->fetch_all();
				$l = connect_to_stats();
				for($i = 0; $i < count($new_auctions); $i++)
				{
					$current = $new_auctions[$i][0];
					$l->query("UPDATE auction_tracking SET auction_tracking.assigned=1 WHERE auction_tracking.name='$current'");
				}
				$l->close();
				echo "[" . getmypid() . "]: Analizing $auctions_count, need $needed_auctions, receiving " . count($new_auctions) . " - " . date("H:i:s") . "\n";
				foreach ($new_auctions as $key => $value) 
				{
					$state = create_table($value[0]);
					echo "[" . getmypid() . "]: Creating table for $value[0] with result $state - " . date("H:i:s") . "\n";
					$auctions[] = $value;
				}
			//}
			//else
			//{
				//#Need no more auctions
				//echo "[" . getmypid() . "]: Auction count $auctions_count - " . date("H:i:s") . "\n";

			//}
		}
		else
		{
			//echo "[" . getmygid() . "]: Auction in pause, sleeping...\n";
			sleep(600);#Sleep 10 minutes
		}	
		$hour = date("H")+1;	
	}while(true);
}

/*
* Analizes the php file
*/
function analize_page($s, $name)
{	
	$pezzi = explode("|", $s);
	$arr = get_last_10($name);	//prendo dal database le ultime 10 puntate dell'asta

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
			return null;#Auction not started
		}
		else 
		{
			//l'asta deve ancora iniziare
			//echo "[" . getmypid() . "]: Auction '$name' not started yet\n";
			return "NOT_STARTED_YET";
		}
	}
	else if(strpos($s, 'OFF') == true) 
	{
		#AUCTION CLOSED
		$last = explode(';', $s);

		$puntate = $last[3];
		$nome = $last[4];
		$time = $last[2];
		$tipo = $last[5];

		$winner = $puntate.';'.$nome.';'.$time.';'.$tipo;
		insert_line($name, $winner);

		query_to_bidoo_stats("UPDATE auction_tracking as a SET a.terminated=1, a.assigned=0 WHERE a.name='$name'");
		return "CLOSED";
	}
	else 
	{
		#Auction in pause
		return null;
	}
}

function get_last_10($name) 
{
	$arr = last_10($name);	//prendo i dati delle ultime 10 puntate dal database
	$new_array = array();
	if($arr != false)
	{
		$arr = $arr->fetch_all();	//trasformo i dati per poterli leggere in un array di array
		foreach ($arr as $value)
		{
			$n_puntate = $value[0];
			$id_utente = $value[1];
			$time_stamp = $value[2];
			$tipo_puntata = $value[3];
			$elem = $n_puntate.';'.$id_utente.';'.$time_stamp.';'.$tipo_puntata;
			$new_array[] = $elem;
		}
		return $new_array;
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