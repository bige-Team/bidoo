<!DOCTYPE html>
<html>
<head>
	<title>bid</title>
</head>
<body>

	<form action="" method="POST">
		<button type="submit" name="btnOk">INIZIO</button>
		<button type="submit" name="btn1">STOP</button>
	</form>
	<?php
	include_once('utils.php');
	set_time_limit(0);	//rimuove il tempo massimo per l'esecuzione di uno script php
	$links = [];
	if(isset($_REQUEST['btnOk'])){
		$str = file_get_contents("https://it.bidoo.com");

		$temp = explode("pic_prd", $str);
		for ($i=0; $i <count($temp) ; $i++) {
			$start = strlen($temp[$i])-70;
			$temp[$i] = substr($temp[$i], $start);
		}
		for ($i=0; $i <count($temp) ; $i++) {
			$temp1[$i] = explode("href='auction.php?a=", $temp[$i]);
		}
		for ($i=0; $i <count($temp) ; $i++) {
			$temp2[$i] = explode("'", $temp1[$i][1]);
			$links[$i] = $temp2[$i][0];
		}
		//print_r($links);
		//echo "<br><br>";

		//genera l'array con gli id dei prodotti => ids
		for ($i=0; $i <count($temp) ; $i++) {
			$temp3 = explode("_", $links[$i]);
			$ids[$i] = $temp3[count($temp3)-1];
		}

		//----------------------------------------------------------
		//links[] e ids[] contengono nome e id delle aste
		do {
			for ($i=0; $i < count($ids); $i++) { 
				$s = file_get_contents('https://it.bidoo.com/data.php?ALL='.$ids[$i].'&LISTID=0');	//stringa del file php
				$new = generaArray($s, $links[$i]);
				if(!is_null($new)){
					create_table($links[$i]);
					insert_array($links[$i], $new);
					//$auc = array_merge($auc, $new);
				}

				/*
				foreach ($new as $value) {
					echo $value."\n";
				}*/
				//print_r($auc);
				
			}
			//sleep(10);
			
		}while(!isset($_REQUEST['STOP']));

		if(isset($_REQUEST['STOP'])) {
			//print_r($auc);
			echo "<h1>ASTA CONCLUSA</h1>";
		}
		
	}

	/*	@return array[] 
	 *	(da finire)
	 *	funzione che passato l'array complessivo, la stringa contenente il pezzo da aggiungere ed un nome crea un file con i dati
	*/	
	function generaArray($s, $name) {	//ANALIZZO IL FILE PHP
		$pezzi = explode("|", $s);	//contiene tutte le info di ogni puntatore
		$arr = scaricaArray($name);

		//1571240953*[8266194;ON;1571241000;1;;,]()		asta che deve ancora iniziare
		if(strpos($s, 'ON') == true) {
			//se l'asta è in corso
			if(count($pezzi) > 1) {	//se non c'è almeno una puntata allora l'asta deve ancora iniziare e non salvo nulla

				$primoPezzo = explode(",", $pezzi[0]);
				//1 e 2 = manuale, 3 e 4 = auto
			
				$primaRiga1 = explode(";", $primoPezzo[0]);
				$primaRiga2 = explode(";", $primoPezzo[1]);

				//prendo l'ultimo utente che ha puntato
				$puntate = $primaRiga2[0];
				$nome = $primaRiga2[1];
				$time = $primaRiga2[2];
				$tipo = $primaRiga2[3];

				$primo = $puntate.';'.$nome.';'.$time.';'.$tipo;
				$pezzi[0] = $primo;	//array con le info delle puntate dell'asta
				$pezzi[count($pezzi)-1] = substr($pezzi[count($pezzi)-1], 0, -3);
				//ribalto l'array
				
				//ora su $pezzi ho un'array di stringhe, una stringa è una puntata formattata: puntate_totali;nome;timestamp;tipo_puntata

				if(!is_null($pezzi)){
					$pezzi = array_reverse($pezzi);
					if(!is_null($arr)){
						$elems = array_diff($pezzi, $arr);
						return $elems;
					}
					else
						return $pezzi;
				}
				return null;
			}
			else {
				//l'asta deve ancora iniziare
				return null;
			}
		}
		else if(strpos($s, 'OFF') == true) {
			//echo 'asta finita';
			$fine = explode(';', $s);

			$puntate = $fine[3];
			$nome = $fine[4];
			$time = $fine[2];
			$tipo = $fine[5];

			$primo = $puntate.';'.$nome.';'.$time.';'.$tipo;
			$last = 'NULL;finita;0;0';

			insert_line($name, $primo);
			//insert_line($name, $last);

			return null;
		}
		else {
			//asta in stop, non faccio nulla
			return null;
		}
		return null;
	}

	function scaricaArray($name) {
		$arr = last_10($name);
		
		if($arr != null){
			$arr = $arr->fetch_all();
			foreach ($arr as $value) {
				$puntate = $value[0];
				$nome = $value[1];
				$time = $value[2];
				$tipo = $value[3];
				$elem = $puntate.';'.$nome.';'.$time.';'.$tipo;
				$arr1[] = $elem;
			}
			return $arr1;
		}
		return null;
	}
?>
</body>
</html>

<!--
SELECT COUNT(n_puntate) FROM 250_puntate_8411549

SELECT COUNT(n_puntate) AS prese, (
	SELECT n_puntate
	FROM kit_airpods_8436771
	ORDER BY n_puntate desc
	LIMIT 1
)-(
	SELECT n_puntate
	FROM kit_airpods_8436771
	ORDER BY n_puntate
	LIMIT 1
) - COUNT(n_puntate) + 1 as perdita, (
	SELECT n_puntate
	FROM kit_airpods_8436771
	ORDER BY n_puntate desc
	LIMIT 1
)-(
	SELECT n_puntate
	FROM kit_airpods_8436771
	ORDER BY n_puntate
	LIMIT 1
) + 1 as totale
FROM kit_airpods_8436771

-->