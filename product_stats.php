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
					    a.name,
					    a.id,
					    a.terminated
					FROM
					    auction_tracking AS a
					WHERE
					    ((a.name like %amazon%)
					    AND (a.name like %10\_%))
					    AND a.terminated = 1");
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
	if(count($all_prices) != 0)
	{
		echo "<br><b>PRODOTTO $product $value VALUTATO SU " . count($all_prices) . " ASTE</b><br>";
		$avg_price = 0;
		foreach ($all_prices as $val)
		{
			$avg_price += $val;
		}
		$avg_price = ($avg_price/count($all_prices))/100;
		echo "<br><b>PREZZO MEDIO: </b>". round($avg_price, 2) . " EURO<br>";
		
		$group_by_time = array();
		for($i=12; $i < 24; $i++)
		{ 
			foreach($all_timestamp as $val)
			{
				if(date("H", $val) == $i)
				{
					$group_by_time[$i][] = $val;
				}
			}
		}
		foreach ($group_by_time as $val => $hour)
		{
			if(count($val) > 0)
			{
				echo "<b>ORA " . $hour . ": </b>";
				$avg_per_hour = 0;
				for ($i=0; $i < count($val); $i++)
				{ 
					$avg_per_hour += $val[$i];
				}
				$avg_per_hour /= count($val);
				echo date("H:i:s", $avg_per_hour) . "<br>";
			}
		}
		
	}
	else
		echo "<br><b>PRODOTTO NON TROVATO!</b><br>";	
}
?>