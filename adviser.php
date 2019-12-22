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
	$users = query_to_bidoo_stats("SELECT DISTINCT a.id_utente FROM $auction AS a");
	$users = $users->fetch_all();
	print_r($users);
	foreach ($users as $key => $value)
	{

	}

	//Controlli nella tabella user_ranking
}
?>