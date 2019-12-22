<form method="post">
	<input type="text" name="auction" placeholder="Auction Name">
	<button name="btnOK">Ok</button>
</form>

<?php
include_once "mysql_utils.php";

if(isset($_REQUEST['btnOK']))
{
	$auction = $_REQUEST['auction'];
	echo "<b>$auction</b>";

	//Utenti che partecipano
	$users = query("SELECT a.id_utente, COUNT(*) AS puntate_usate FROM $auction AS a GROUP BY a.id_utente");
	$users = $users->fetch_all();
	print_r($users);
	foreach ($users as $key => $value)
	{

	}

	//Controlli nella tabella user_ranking
}
?>