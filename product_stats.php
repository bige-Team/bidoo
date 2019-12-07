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
					    `a`.`terminated` AS `terminated`
					from
					    `auction_tracking` `a`
					where
					    ((`a`.`name` like '%$product%')
					    and (`a`.`name` like '%$value\_%'))");
	$l->close();
	$res = $res->fetch_all();

	$l = connect();
	$all_prices = array();
	$all_timestamp = array();
	foreach ($res as $key => $val)
	{
		$table_name = $val[0];
		$is_terminated = $val[2];
		if($is_terminated == TRUE)
		{
			$res = $l->query("SELECT n_puntate, time_stamp FROM $table_name ORDER BY n_puntate DESC LIMIT 1");
			$res = $res->fetch_all();
			$all_prices[] = $res[0][0];
			$all_timestamp[] = $res[0][1];
		}
	}
	$l->close();
	echo "<br><b>PRODOTTO $product $value</b><br>";
	$avg_price = 0;
	foreach ($all_prices as $val)
	{
		$avg_price += $val;
	}
	$avg_price = ($avg_price/count($all_prices))/100;
	echo "<br><b>PREZZO MEDIO: </b>". round($avg_price, 2) . " EURO<br>";

	$avg_timestamp = 0;
	foreach ($all_timestamp as $val)
	{
		$avg_timestamp += $val;
	}
	$avg_timestamp /= count($all_timestamp);
	echo "<b>ORA MEDIA PER: </b>". date("H:i:s", $avg_timestamp) . "<br>";
}
?>