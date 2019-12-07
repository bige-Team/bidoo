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
				    `a`.`id` AS `id`,
				    `a`.`analized` AS `analized`,
				    `a`.`assigned` AS `assigned`,
				    `a`.`terminated` AS `terminated`
				from
				    `bidoo_stats`.`auction_tracking` `a`
				where
				    (`a`.`name` like concat((select `p`.`products` from `bidoo_stats`.`v_products` `p` where ((`p`.`products` like '%$product%') and (`p`.`products` like '%\_$value\_%'))), '%'))");
	$l->close();
	$res = $res->fetch_all();
	print_r($res);
}


?>