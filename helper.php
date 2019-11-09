<?php
include_once "utils.php";


	$table_names = get_table_names();
	$link = con();
	$q = "";
	for ($i=0; $i < count($table_names); $i++) 
	{
		$q .= "INSERT INTO auction_tracking (name) VALUES ('$table_names[$i]');";
	}
	echo $q;
	$link->close();
	

function con()
{
	$link = new mysqli("localhost", "root", "Rt9du2pg", "bidoo_stats");
	return $link;
}
?>