<?
	$asta = $_REQUEST['asta']??8695000;
?>
<!DOCTYPE html>
<html>
<head>
	<title>TUTTO</title>
</head>
<body>
	<form action="" method="POST">
		<input type="text" name="asta">
		<button type="submit" name="btnOk">INIZIO</button>
	</form>
</body>
</html>

<?php
include_once('utils.php');
set_time_limit(0);

if(isset($_REQUEST['btnOk'])){
	$a = $_REQUEST['asta']??8695000;
	for ($i=$a; $i > $a-1000; $i--) {
		$s = file_get_contents('https://it.bidoo.com/data.php?ALL='.$i.'&LISTID=0');

		if(strpos($s, 'OFF') == true) {
			echo 'asta finita';
			$fine = explode(';', $s);

			$nome = $fine[4];
			if(strlen($nome) > 4) {
				$puntate = $fine[3];
				$time = $fine[2];
				$tipo = $fine[5];

				$primo = $puntate.';'.$nome.';'.$time.';'.$tipo;
				echo $primo;

				insert_line('winners', $primo);
			}
		}
	}
}
?>