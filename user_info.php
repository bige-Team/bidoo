<link rel="stylesheet" type="text/css" href="style.css">
<?php
if(isset($_REQUEST['id_utente']))
{
	include "mysql_utils.php";

	$id_utente = $_REQUEST['id_utente'];
	echo "<h3>$id_utente</h3><br><br>";

	$res = query_to_bidoo_stats("SELECT u.puntate_usate, u.aste_partecipate FROM users_ranking AS u WHERE u.id_utente='$id_utente'");
	$res = $res->fetch_all();
	$puntate_usate = $res[0][0];
	$aste_partecipate = $res[0][1];

	echo "<b>PUNTATE USATE: </b>$puntate_usate<br>";
	echo "<b>ASTE PARTECIPATE: </b>$aste_partecipate";
}
else
{
	echo "<script>window.close()</script>";
}
?>