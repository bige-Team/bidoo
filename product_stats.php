<form>
	<input type="text" name="product" placeholder="Product Name">
	<input type="number" name="value" placeholder="Value">
	<button name="btnOK">OK</button>
</form>

<?php
if(isset($_REQUEST['btnOK']))
{
	include "mysql_utils.php";
	$product = $_REQUEST['product'];
	$value = $_REQUEST['value'];
	$l = connect_to_stats();
	$res = $l->query("select
					    `a`.`name` AS `name`,
					    `a`.`id` AS `id`
					from
					    `auction_tracking` `a`
					where
					    ((`a`.`name` like '%$product%')
					    and (`a`.`name` like '%$value\_%'))");
	$l->close();
	$res = $res->fetch_all();
	print_r($res);
}


?>