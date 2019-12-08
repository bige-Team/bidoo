<link rel="stylesheet" type="text/css" href="style.css">
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
	$res = $l->query("SELECT
					    `a`.`name` AS `name`,
					    `a`.`id` AS `id`,
					    `a`.`terminated` AS `terminated`
					from
					    `auction_tracking` `a`
					where
					    ((`a`.`name` like '%$product%')
					    and (`a`.`name` like '%$value\_%')
					    and (`a`.`terminated` = 1))");
	$l->close();
	$auction_names = $res->fetch_all();

	$l = connect();
	$all_prices = array();
	foreach ($auction_names as $key => $val)
	{
		$table_name = $val[0];
		$is_terminated = $val[2];
		if($is_terminated == TRUE)
		{
			$res = $l->query("SELECT n_puntate FROM $table_name ORDER BY n_puntate DESC LIMIT 1");
			$res = $res->fetch_all();
			$all_prices[] =  $res[0][0];
		}
	}
	$l->close();

	if(count($all_prices) != 0)
	{
		echo "<br><b>PRODOTTO $product $value VALUTATO SU " . count($all_prices) . " ASTE</b><br>";
		$avg_price = 0;
		foreach ($all_prices as $val)
		{
			$avg_price += $val;
		}
		$avg_price = ($avg_price/count($all_prices))/100;
		echo "<br><b>PREZZO MEDIO: </b>". round($avg_price, 2) . " EURO";
		echo "<br><b>PREZZO MASSIMO: </b>". (max($all_prices)/100) . " EURO";
		echo "<br><b>PREZZO MINIMO: </b>". (min($all_prices)/100) . " EURO<br>";
		
		/*
			Range di ore conto per tutte le aste quante puntate sono state usate in quel range di tempo
		*/
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

			foreach ($res as $key => $value)
			{
				$n_puntate = $value[0];
				$time_hour = $value[1];
				if(!isset($puntate_per_hour[$time_hour]))
					$puntate_per_hour[$time_hour] = $n_puntate; 
				else
					$puntate_per_hour[$time_hour] += $n_puntate; 
			}
		}
		$l->close();
		foreach ($puntate_per_hour as $key => $value)
		{
			$puntate_per_hour[$key] = round($value/count($puntate_per_hour));
		}
		ksort($puntate_per_hour); #Array ( [11] => 502 [12] => 282...)
		echo "<br>";
		echo "<b>PUNTATE MEDIE USATE PER ORA</b><br>";
		foreach ($puntate_per_hour as $key => $value) 
		{
			echo "<b>ORE $key-". ($key+1) . ": </b>$value<br>";
		}
	}
	else
		echo "<br><b>PRODOTTO NON TROVATO!</b><br>";	
}
?>