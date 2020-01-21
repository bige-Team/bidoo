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
	if(strtolower($product) == "puntate")
	{
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
	}
	else
	{
		$res = $l->query("SELECT
					    `a`.`name` AS `name`,
					    `a`.`id` AS `id`,
					    `a`.`terminated` AS `terminated`
					from
					    `auction_tracking` `a`
					where
					    ((`a`.`name` like '%$product%')
					    and (`a`.`name` like '%\_$value\_%')
					    and (`a`.`terminated` = 1))");
	}
	$l->close();
	$auction_names = $res->fetch_all();

	$l = connect();
	$all_prices = array();
	$asta_max = "";
	$i_max = 0;
	$asta_min = "";
	$i_min = 0;
	foreach ($auction_names as $key => $val)
	{
		$table_name = $val[0];
		$is_terminated = $val[2];
		if($is_terminated == TRUE)
		{
			$res = $l->query("SELECT n_puntate FROM $table_name ORDER BY n_puntate DESC LIMIT 1");
			$res = $res->fetch_all();
			$all_prices[] =  $res[0][0];
			if($res[0][0] >= $all_prices[$i_max])
			{
				$i_max = count($all_prices)-1;
				$asta_max = $table_name;
			}
			if($res[0][0] <= $all_prices[$i_min])
			{
				$i_min = count($all_prices)-1;
				$asta_min = $table_name;
			}
		}
	}
	$l->close();

	if(count($all_prices) != 0)
	{
		echo "<br><b>PRODOTTO $product $value VALUTATO SU " . count($all_prices) . " ASTE</b><br>";
		$avg_price = 0;
		foreach ($all_prices as $val)
		{
			if($val != max($all_prices) && $val != min($all_prices))
				$avg_price += $val;
		}
		$avg_price = ($avg_price/count($all_prices))/100;
		echo "<br>";
		echo "<b>PREZZO MEDIO: </b>". round($avg_price, 2) . " EURO<br>";
		echo "<b>PREZZO MASSIMO: </b>". (max($all_prices)/100) . " EURO <a href='https://it.bidoo.com/auction.php?a=$asta_max' target='blank'>$asta_max</a><br>";
		echo "<b>PREZZO MINIMO: </b>". (min($all_prices)/100) . " EURO <a href='https://it.bidoo.com/auction.php?a=$asta_min' target='blank'>$asta_min</a><br>";
		
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
						HOUR(TIMESTAMPADD(HOUR, 1, FROM_UNIXTIME(time_stamp))) AS time_hour
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
			$puntate_per_hour[$key] = round($value/count($all_prices));
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