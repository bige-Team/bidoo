<form method="post">
	<input type="text" name="auction" placeholder="Auction Name">
	<button name="btnOK">Ok</button>
</form>

<?php
include_once "mysql_utils.php";

if(isset($_REQUEST['btnOK']))
{
	$auction = $_REQUEST['auction'];
	echo "<b>$auction</b><br><br>";

	//Utenti che partecipano
	$users = query("SELECT * FROM (SELECT a.id_utente, COUNT(*) AS puntate_usate FROM $auction AS a GROUP BY a.id_utente) AS t ORDER BY t.puntate_usate DESC ");
	$users = $users->fetch_all();
	echo "<table>";
	echo "<tr>";
	echo "<td><b>ID UTENTE</b></td>";
	echo "<td><b>PUNTATE USATE</b></td>";
	echo "</tr>";
	foreach ($users as $key => $value)
	{
		$id_utente = $value[0];
		$puntate_usate = $value[1];
		echo "<tr>";
		echo "<td><a href='user_info.php?id_utente=$id_utente' target='_blank'>$id_utente</a></td>";
		echo "<td>$puntate_usate</td>";
		echo "</tr>";
	}
	echo "</table>";

	//Controlli nella tabella user_ranking
}
?>