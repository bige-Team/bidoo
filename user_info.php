<link rel="stylesheet" type="text/css" href="style.css">
<?php
if(isset($_REQUEST['id_utente']))
{
	include "mysql_utils.php";

	$id_utente = $_REQUEST['id_utente'];
	echo "<h3>$id_utente</h3>";
	
}
else
{
	echo "<script>window.close()</script>";
}
?>