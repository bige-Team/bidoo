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
			$start = strlen($temp[$i])-100;
			$temp[$i] = substr($temp[$i], $start);
		}
		for ($i=0; $i <count($temp) ; $i++) {
			$temp1[$i] = explode("href='", $temp[$i]);
		}
		for ($i=0; $i <count($temp) ; $i++) {
			$temp2[$i] = explode("'", $temp1[$i][1]);
			$links[$i] = $temp2[$i][0];
		}
		//genera l'array con i nomi dei prodotti => links
		for ($i=0; $i <count($links) ; $i++) {
			$links[$i] = substr($links[$i], 14);
		}
		print_r($links);
		echo "<br><br>";
		//genera l'array con gli id dei prodotti => ids
		for ($i=0; $i <count($temp) ; $i++) {
			$temp3 = explode("_", $links[$i]);
			$ids[$i] = $temp3[count($temp3)-1];
		}
		print_r($ids);
		$s = file_get_contents('https://it.bidoo.com/data.php?ALL='.$ids[30].'&LISTID=0');	//stringa del file php
		echo "<br><br>$s";

		generaFile($s);
	}
	function generaFile($s) {	//ANALIZZO IL FILE PHP
		$pezzi = explode("|", $s);	//contiene tutte le info di ogni puntatore
		$primoPezzo = explode(",", $pezzi[0]);
		//1 e 2 = manuale, 3 = auto
		
		$primaRiga1 = explode(";", $primoPezzo[0]);
		$primaRiga2 = explode(";", $primoPezzo[1]);

		if($primaRiga1[1] != 'STOP') {
			//prendo l'ultimo utente che ha puntato
			$puntate = $primaRiga2[0];
			$nome = $primaRiga2[1];
			$time = $primaRiga2[2];
			$tipo = $primaRiga2[3];

			$primo = $puntate.';'.$nome.';'.$time.';'.$tipo;
			$pezzi[0] = $primo;	//array con le info delle puntate dell'asta
			$pezzi[count($pezzi)-1] = substr($pezzi[count($pezzi)-1], 0, -3);

			//print_r($pezzi);
			$finale = implode("\n", $pezzi);	//stringa contenente i dati dell'asta
			file_put_contents("puntate.txt", $finale, FILE_APPEND | LOCK_EX);
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