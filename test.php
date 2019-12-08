<?php
include "mysql_utils.php";
$l = connect_to_stats();
$res = $l->query("SELECT
				    `a`.`name` AS `name`,
				    `a`.`id` AS `id`,
				    `a`.`terminated` AS `terminated`
				from
				    `auction_tracking` `a`
				where
				    ((`a`.`name` like '%Amazon%')
				    and (`a`.`name` like '%10\_%')
				    and (`a`.`terminated` = 1))");
$l->close();
$auction_names = $res->fetch_all();



$puntate_per_hour = array();
$l = connect();
foreach  ($auction_names as $key => $val)
{
	$table_name = $val[0];
	$res = $l->query("SELECT 
			count(*) AS tot_puntate,
			t.time_hour
		FROM(SELECT
				n_puntate,
				HOUR(FROM_UNIXTIME(time_stamp)) AS time_hour
			FROM $table_name) AS t
		GROUP BY t.time_hour");
	$res = $res->fetch_all();
	echo "<prev>";
	print_r($res);
	echo "</prev>";
	/*
	for ($j=11; $j < 24; $j++) 
	{
		$puntate_per_hour[$j] = $res[0][$j];
	}
	*/
}
$l->close();
?>