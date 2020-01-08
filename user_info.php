<link rel="stylesheet" type="text/css" href="style.css">
<?php
if(isset($_REQUEST['id_utente']))
{
	include "mysql_utils.php";

	$id_utente = $_REQUEST['id_utente'];
	echo "<h2>$id_utente</h2>";

	$res = query_to_bidoo_stats("SELECT u.puntate_usate, u.aste_partecipate FROM users_ranking AS u WHERE u.id_utente='$id_utente'");
	$res = $res->fetch_all();

	//Take win auctions
	$wins = query_to_bidoo_stats("SELECT w.id_asta, w.id_utente, w.time_stamp, w.n_puntate, w.tipo_puntata FROM winners AS w WHERE w.id_utente='$id_utente'");
	$wins = $wins->fetch_all();

	if(isset($res[0]))
	{
		$puntate_usate = $res[0][0];
		$aste_partecipate = $res[0][1];

		echo "<b>PUNTATE USATE: </b>$puntate_usate<br>";
		echo "<b>ASTE PARTECIPATE: </b>$aste_partecipate<br>";
		echo "<b>MEDIA PUNTATE PER ASTA: </b>" . ($puntate_usate/$aste_partecipate) . "<br>";
		echo "<br><br>";
		echo "<b>ASTE VINTE</b>";
		print_r($wins);
	}
	else
	{
		echo "<b>UTENTE NON ANCORA REGISTRATO</b>";
	}
	
}
else
{
	echo "<script>window.close()</script>";
}
?>