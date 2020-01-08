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
	$wins = query_to_bidoo_stats("SELECT w.id_asta, w.time_stamp, w.n_puntate, w.tipo_puntata FROM winners AS w WHERE w.id_utente='$id_utente'");
	$wins = $wins->fetch_all();

	if(isset($res[0]))
	{
		$puntate_usate = $res[0][0];
		$aste_partecipate = $res[0][1];

		echo "<b>PUNTATE USATE: </b>$puntate_usate<br>";
		echo "<b>ASTE PARTECIPATE: </b>$aste_partecipate<br>";
		echo "<b>MEDIA PUNTATE PER ASTA: </b>" . ($puntate_usate/$aste_partecipate) . "<br>";
		echo "<br><br>";
		if($isset($wins[0]))
		{
			echo "<b>ASTE VINTE</b><br>";
			echo "<table>";
			echo "<tr>";
			echo "<td>ID ASTA</td>";
			echo "<td>TIMESTAMP</td>";
			echo "<td>NUMERO PUNTATE</td>";
			echo "<td>TIPO PUNTATA</td>";
			echo "</tr>";
			foreach ($wins as $key => $value) 
			{
				$id_asta = $value[0];
				$time_stamp = $value[1];
				$n_puntate = $value[2];
				$tipo_puntata = $value[3];
				echo "<tr>";
				echo "<td><a href='https://it.bidoo.com/auction.php?a=$id_asta' target='_blank'>$id_asta</a></td>";
				echo "<td>$time_stamp</td>";
				echo "<td>$n_puntate</td>";
				echo "<td>$tipo_puntata</td>";
				echo "</tr>";
			}
			echo "</table>";
		}
		else
		echo "<b>NESSUNA ASTA VINTA</b><br>";			
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