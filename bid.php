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
		//print_r($ids);
		//echo "<br><br>$s";

		/*
		for ($i=0; $i <count($ids) ; $i++) { 
			$s = file_get_contents('https://it.bidoo.com/data.php?ALL='.$ids[$i].'&LISTID=0');	//stringa del file php
			
			generaFile($s, $links[$i]);
			//echo "\n".$links[$i];
		}
		*/

		
	}

	/*	@return array[]
	 *	(da finire)
	 *	funzione che passato il link ed il nome restituisce un array che alla pos 0 ha il nome del prodotto, nelle altre posizioni contiene lo storico delle puntate
	*/

	function checkAndWrite($id, $arr) {
		$s = file_get_contents('https://it.bidoo.com/data.php?ALL='.$id.'&LISTID=0');

		$arr1 = generaFile($s, 'prova.txt');
		$fin = array_unique(array_merge($arr, $arr1));

		sleep(5);
		checkAndWrite($id, $fin);
	}

	function generaFile($s, $name) {	//ANALIZZO IL FILE PHP
		$pezzi = explode("|", $s);	//contiene tutte le info di ogni puntatore

		//1571240953*[8266194;ON;1571241000;1;;,]()		asta che deve ancora iniziare
		if(count($pezzi) > 1) {	//se non c'è almeno una puntata allora l'asta deve ancora iniziare e non salvo nulla
			$primoPezzo = explode(",", $pezzi[0]);
			//1 e 2 = manuale, 3 = auto
			
			$primaRiga1 = explode(";", $primoPezzo[0]);
			$primaRiga2 = explode(";", $primoPezzo[1]);

			if($primaRiga1[1] == 'OFF') {	//asta conclusa
				file_put_contents($name, $fin);
			}

			if($primaRiga1[1] != 'STOP') {
				//prendo l'ultimo utente che ha puntato
				$puntate = $primaRiga2[0];
				$nome = $primaRiga2[1];
				$time = $primaRiga2[2];
				$tipo = $primaRiga2[3];

				$primo = $puntate.';'.$nome.';'.$time.';'.$tipo;
				$pezzi[0] = $primo;	//array con le info delle puntate dell'asta
				$pezzi[count($pezzi)-1] = substr($pezzi[count($pezzi)-1], 0, -3);

				return $pezzi;
				//print_r($pezzi);
				//finale = implode("\n", $pezzi);	//stringa contenente i dati dell'asta
				//$finale .= "\n";
				//file_put_contents('data/'.$name.'.txt', $finale, FILE_APPEND | LOCK_EX);
			}
		}
	}
		//1571146529*[8257613; ON; 1571146539; 182; johnathan90; 3		 182; johnathan90; 1571146529; 3
		//|181;franco196;1571146527;1
		//|180;johnathan90;1571146517;3
		//|179;stefanovianelli;1571146516;1
		//|178;johnathan90;1571146507;3|177;franco196;1571146506;1|176;johnathan90;1571146497;3|175;franco196;1571146496;1|174;johnathan90;1571146486;3|173;franco196;1571146485;1]()
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