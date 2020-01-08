<?php
include "mysql_utils.php";

$product = $_REQUEST['product'];
$value = $_REQUEST['value'];

$l = connect_to_stats();
if(strtolower($product) == "puntate")
{
	$res = $l->query("SELECT
				    `a`.`name` AS `name`,
				    `a`.`id` AS `id`
				from
				    `auction_tracking` `a`
				where
				    ((`a`.`name` like '%$product%')
				    and (`a`.`name` like '%$value\_%')
				    and (`a`.`assigned` = 1))");
}
else
{
	$res = $l->query("SELECT
				    `a`.`name` AS `name`,
				    `a`.`id` AS `id`
				from
				    `auction_tracking` `a`
				where
				    ((`a`.`name` like '%$product%')
				    and (`a`.`name` like '%\_$value\_%')
				    and (`a`.`assigned` = 1))");
}
$l->close();
$auction_names = $res->fetch_all();

foreach ($auction_names as $key => $value) 
{
	$name = $value[0];
	echo "<a href='https://it.bidoo.com/auction.php?a=$name' target='_blank'>$name</a>";
}

?>