<link rel="stylesheet" type="text/css" href="style.css">
<form method="get">
	<input type="text" name="auction" placeholder="Auction Name">
	<button name="btnOK">Ok</button>
</form>

<?php
include_once "mysql_utils.php";

if(isset($_REQUEST['btnOK']))
{
	$auction = $_REQUEST['auction'];
	$parts = explode('_', $auction);
	if(ctype_digit($parts[0]))//Check if a string contains numbers
	{
		//Case 1000_Puntate...
		echo "<a href='product_stats.php?product=$parts[1]&value=$parts[0]&btnOK=' target='_blank'>$auction</a>";
	}
	else
	{
		//Case Buono_Amazon_100...
		echo "<a href='product_stats.php?product=$parts[1]&value=$parts[2]&btnOK=' target='_blank'>$auction</a>";
	}

	//Utenti che partecipano
	$users = query("SELECT * FROM (SELECT a.id_utente, COUNT(*) AS puntate_usate, DATE_FORMAT(MAX(TIMESTAMPADD(HOUR, 1, FROM_UNIXTIME(a.time_stamp))), '%H:%i:%s') AS ultima_puntata, a.tipo_puntata FROM $auction AS a GROUP BY a.id_utente) AS t ORDER BY t.puntate_usate DESC");
	$users = $users->fetch_all();
	echo "<table>";
	echo "<tr>";
	echo "<td><b>ID UTENTE</b></td>";
	echo "<td><b>PUNTATE USATE</b></td>";
	echo "<td><b>ULTIMA PUNTATA</b></td>";
	echo "<td><b>TIPO PUNTATA</b></td>";
	echo "</tr>";
	foreach ($users as $key => $value)
	{
		$id_utente = $value[0];
		$puntate_usate = $value[1];
		$ultima_puntata = $value[2];
		$tipo_puntata = $value[3];
		echo "<tr>";
		echo "<td><a href='user_info.php?id_utente=$id_utente' target='_blank'>$id_utente</a></td>";
		echo "<td>$puntate_usate</td>";
		$d1 = date_create($ultima_puntata);
		$d2 = date_create("00:00:30");
		date_add($d1, date_interval_create_from_date_string("2 minutes"));
		$current = date("H:i:s");
		if(strtotime($d1->format("H:i:s")) > strtotime($current))
			echo "<td><mark>$ultima_puntata</mark></td>";
		else
			echo "<td>$ultima_puntata</td>";
		
		echo "<td>$tipo_puntata</td>";
		echo "</tr>";
	}
	echo "</table>";

	
}
?>