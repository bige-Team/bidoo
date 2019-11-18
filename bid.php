	<?php
	// include_once('utils.php');
start();
	function start()
	{
		set_time_limit(0);
		$ids = array();
		$toRemove = array();

		$ids = getBids();	//metodo che genera l'array con le aste da bidoo
/*
		foreach ($ids as $key => $value) {
			insert_elem('rimuovi', $value);
		}
*/
		//----------------------------------------------------------
		//ids[] contiene: id => nome
		do {
			$hour = date("H");
			$min = date("i");

			if(!($hour >= 0 && $hour < 12)) {	//se non siamo nell'orario di stop

				foreach ($ids as $key => $value) {
					$s = file_get_contents('https://it.bidoo.com/data.php?ALL='.$key.'&LISTID=0');	//stringa del file php
					echo $s . "<br>"	;
					$new = generaArray($s, $key, $ids);

					if(!is_null($new)){		//se ci sono aggiornamenti da fare
						create_table($value);
						insert_array($value, $new);
					}
				}

				foreach ($toRemove as $value) {
					echo "rimossa l'asta " . $ids[$value];
					unset($ids[$value]);	//rimuove l'asta dall'array delle aste
				}
				$toRemove = [];	//resetto l'array con gli elementi da rimuovere
			}
		}while(true/*!isset($_REQUEST['STOP'])*/);
/*
		if(isset($_REQUEST['STOP'])) {
			echo "<h1>ASTA CONCLUSA</h1>";
		}
		*/
	}
	/*
	set_time_limit(0);	//rimuove il tempo massimo per l'esecuzione di uno script php
	$ids = array();
	$toRemove = array();

	if(isset($_REQUEST['btnOk'])){

		$ids = getBids();	//metodo che genera l'arraycon le aste da bidoo

		foreach ($ids as $key => $value) {
			insert_elem('rimuovi', $value);
		}

		//----------------------------------------------------------
		//ids[] contiene: id => nome
		do {
			$hour = date("H");
			$min = date("i");

			if(!($hour >= 0 && $hour < 12)) {	//se non siamo nell'orario di stop

				foreach ($ids as $key => $value) {
					$s = file_get_contents('https://it.bidoo.com/data.php?ALL='.$key.'&LISTID=0');	//stringa del file php
					$new = generaArray($s, $key, $ids);

					if(!is_null($new)){		//se ci sono aggiornamenti da fare
						create_table($value);
						insert_array($value, $new);
					}
				}

				foreach ($toRemove as $value) {
					echo "rimossa l'asta " . $ids[$value];
					unset($ids[$value]);	//rimuove l'asta dall'array delle aste
				}
				$toRemove = [];	//resetto l'array con gli elementi da rimuovere
			}
		}while(!isset($_REQUEST['STOP']));

		if(isset($_REQUEST['STOP'])) {
			echo "<h1>ASTA CONCLUSA</h1>";
		}
		
	}
*/
	function getBids() {
		$str = file_get_contents("https://it.bidoo.com");

		$temp = explode("pic_prd", $str);
		for ($i=0; $i <count($temp) ; $i++) {
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

	/*	
	 *	passo: la stringa contenente il pezzo da aggiungere, chiave prodotto e l'array complessivo
	 *	
	 *	@return array[] 
	*/	
	function generaArray($s, $key, $ids) {	//ANALIZZO IL FILE PHP
		$pezzi = explode("|", $s);
		$arr = scaricaArray($ids[$key]);	//prendo dal database le ultime 10 puntate dell'asta

		//1571240953*[8266194;ON;1571241000;1;;,]()		asta che deve ancora iniziare
		if(strpos($s, 'ON') == true) {
			//se l'asta è in corso
			if(count($pezzi) > 1) {	//se non c'è almeno una puntata allora l'asta deve ancora iniziare e non salvo nulla

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
				if(!is_null($pezzi)){	//se l'array non è vuoto
					$pezzi = array_reverse($pezzi);	//lo rovescio
					if(!is_null($arr)){	//se la tabella di quell'asta non è vuota
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
			$last = '0;FINITO;0;F';

			insert_line($ids[$key], $primo);
			insert_line($ids[$key], $last);
			delete_line('rimuovi', $ids[$key]);
			$GLOBALS['toRemove'][] = $key;	//aggiungo all'array degli elementi da rimuovere l'id dell'asta finita (la key)

			return null;
		}
		else {
			//asta in stop, non faccio nulla
			return null;
		}
	}

	function scaricaArray($name) {
		$arr = last_10($name);	//prendo i dati delle ultime 10 puntate dal database
		
		if($arr != null){
			$arr = $arr->fetch_all();	//trasformo i dati per poterli leggere in un array di array

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