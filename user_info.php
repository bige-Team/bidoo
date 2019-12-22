<?php
if(isset($_REQUEST['id_utente']))
{
	$id_utente = $_REQUEST['id_utente'];
}
else
{
	echo "<script>window.close()</script>";
}
?>