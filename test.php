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
for ($i=0; $i < count($auction_names); $i++)
{ 
	$res = $l->query("SELECT 
			count(*) AS tot_puntate,
			t.time_hour
		FROM(SELECT
				n_puntate,
				HOUR(FROM_UNIXTIME(time_stamp)) AS time_hour
			FROM $auction_names[$i]) AS t
		GROUP BY t.time_hour");
	$res = $res->fetch_all();
	print_r($res);
	for ($j=11; $j < 24; $j++) 
	{
		$puntate_per_hour[$j] = $res[0][$j];
	}
}
$l->close();
?>