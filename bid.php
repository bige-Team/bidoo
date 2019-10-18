<!DOCTYPE html>
<html>
<head>
	<title>bid</title>
</head>
<body>

	<form action="" method="POST">
		<button type="submit" name="btnOk">invia</button>
	</form>
	<?php
	include_once "utils.php";

	set_time_limit(0);
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
			print_r($temp1[$i][1]);
			$links[$i] = $temp2[$i][0];
		}
		//print_r($links);
		//echo "<br><br>";
		//genera l'array con gli id dei prodotti => ids
		for ($i=0; $i <count($temp) ; $i++) {
			$temp3 = explode("_", $links[$i]);
			$ids[$i] = $temp3[count($temp3)-1];
		}
		//print_r($ids);
		//echo "<br><br>$s";

		
		//for ($i=0; $i <count($ids) ; $i++) { 
			
			
		//	generaFile($s, $links[$i]);
			//echo "\n".$links[$i];
		//}
		$i = 14;
		//Crea tavola nel database
		create_table($links[$i]);

		$auc = ["NULL;NULL;NULL;NULL"];
		do {
			$s = file_get_contents('https://it.bidoo.com/data.php?ALL='.$ids[$i].'&LISTID=0');	//stringa del file php
			$auc = generaArray($auc, $s, $links[$i]);
			echo "<br>";
			//Inserimento nel database
			for($j = 0; $j < count($auc) - 1; $j++)
			{
				insert_line($links[$i], $auc[$j]);
			}
			//insert_array($links[$i], $auc);

			print_r($auc);
			sleep(10);
		}while($auc != null);

		if($auc == null) {
			echo "<h1>ASTA CONCLUSA</h1>";
		}
		
	}

	/*	@return array[] 
	 *	(da finire)
	 *	funzione che passato il link ed il nome restituisce un array che alla pos 0 ha il nome del prodotto, nelle altre posizioni contiene lo storico delle puntate
	*/	
	function generaArray($arr, $s, $name) {	//ANALIZZO IL FILE PHP
		$pezzi = explode("|", $s);	//contiene tutte le info di ogni puntatore

		//1571240953*[8266194;ON;1571241000;1;;,]()		asta che deve ancora iniziare
		if(count($pezzi) > 1) {	//se non c'è almeno una puntata allora l'asta deve ancora iniziare e non salvo nulla
			$primoPezzo = explode(",", $pezzi[0]);
			//1 e 2 = manuale, 3 = auto
			
			$primaRiga1 = explode(";", $primoPezzo[0]);
			$primaRiga2 = explode(";", $primoPezzo[1]);

			if($primaRiga1[1] != 'OFF' && $primaRiga1[1] != 'STOP') {
				//prendo l'ultimo utente che ha puntato
				$puntate = $primaRiga2[0];
				$nome = $primaRiga2[1];
				$time = $primaRiga2[2];
				$tipo = $primaRiga2[3];

				$primo = $puntate.';'.$nome.';'.$time.';'.$tipo;
				$pezzi[0] = $primo;	//array con le info delle puntate dell'asta
				$pezzi[count($pezzi)-1] = substr($pezzi[count($pezzi)-1], 0, -3);

				return array_unique(array_merge($pezzi, $arr));
				//print_r($pezzi);
				/*
				$finale = implode("\n", $pezzi);	//stringa contenente i dati dell'asta
				$finale .= "\n";
				file_put_contents('data/'.$name.'.txt', $finale, FILE_APPEND | LOCK_EX);
				*/
			}
			else {
				file_put_contents('data/'.$name.'txt', $arr);
				return null;
			}
		}
	}
	?>
</body>
</html>

<script type="text/javascript">
	/*
	var links = [];
	//funzione che ricava i link delle aste
	function getLinks() {
		var todo = document.getElementsByClassName("pic_prd");

		for(var i=0; i<todo.length; i++){
			links.push(todo[i].href);
		}
	}
	*/
</script>