<form method="POST">
	<input type="text" name="id_utente" placeholder="id utente">
	<button name="btnOK">OK</button>
	<br><br>
	<button name="fill">Fill stats table</button> 
	<button name="empty">Empty stats table</button>
	<!--<button name="dropBidoo">Drop bidoo</button>
	<button name="createBidoo">Create bidoo</button>-->
</form>

<?php
include_once "mysql_utils.php";

if(isset($_POST['btnOK']))
{
	$id_utente = $_POST['id_utente'];
	$table_names = get_table_names();
	$n_puntate = 0;
	$n_tabelle = 0;
	$tables = ""; #Nomi aste partecipate

	for ($i=0; $i < count($table_names); $i++) 
	{ 
		$res = query("SELECT COUNT(n_puntate) FROM " . $table_names[$i] . " WHERE id_utente='$id_utente'");
		$res = $res->fetch_all()[0][0];
		if($res > 0)
		{
			$n_puntate += $res;
			$n_tabelle++;
			$tables .= $table_names[$i] . " ";
		}
	}
	echo "<b>" . $id_utente . "</b> con <b>" . $n_puntate . "</b> puntate in <b> " . $n_tabelle . "</b> tabella/e";
	$user_tables_name = explode(' ', $tables);
	array_pop($user_tables_name);
	for ($i=0; $i < count($user_tables_name); $i++) 
	{ 
		echo "<a href='adviser.php?name=$user_tables_name[$i]' target='blank'>$user_tables_name[$i]</a><br>";
	}
}

if(isset($_POST['fill']))
{
	update_user_rank();
	echo "Filled table 'users_ranking'";
}

if(isset($_POST['empty']))
{
	query_to_bidoo_stats("TRUNCATE TABLE users_ranking");
	echo "TRUNCATE on table 'users_ranking'";
}

if(isset($_POST['dropBidoo']))
{
	query("DROP DATABASE bidoo");
	echo "DROP on database bidoo";
}

if(isset($_POST['createBidoo']))
{
	query_to_bidoo_stats("CREATE DATABASE bidoo");
	echo "CREATE on database bidoo";
}

function update_user_rank()
{	
	$table_names = get_table_names_from_autcion_tracking();
	for ($i=0; $i < count($table_names); $i++) 
	{
		$res = query("SELECT DISTINCT id_utente FROM $table_names[$i]");
		$res = $res->fetch_all();
		for ($j=0; $j < count($res); $j++)
		{ 
			$arr[$table_names[$i]][$j] = $res[$j][0];
		}
	}

	//Aggiorno il auction_tracking
	for ($i=0; $i < count($table_names); $i++) 
	{ 
		query_to_bidoo_stats("UPDATE auction_tracking SET analized = 1 WHERE name = '$table_names[$i]'");
	}

	//prende i dati delle puntate usate e delle aste partecipate
	$link = connect();
	foreach ($arr as $key => $value) 
	{
		for ($i=0; $i < count($value); $i++) 
		{ 
			$res = $link->query("SELECT COUNT(*) FROM $key WHERE id_utente='$value[$i]'");
			$res = $res->fetch_all();
			if($res[0][0] != 0)
			{
				if(!isset($user_infos[$value[$i]]['puntate_usate']))
				{
					$user_infos[$value[$i]]['puntate_usate'] = $res[0][0];
					$user_infos[$value[$i]]['aste_partecipate'][0] = $key;
				}
				else
				{
					$user_infos[$value[$i]]['puntate_usate'] += $res[0][0];
					$user_infos[$value[$i]]['aste_partecipate'][count($user_infos[$value[$i]]['aste_partecipate'])] = $key;
				}
			}
		}
	}
	$link->close();

	//print_r($user_infos);

	//li scrivo nella tabella user_ranking
	$link = connect_to_stats();
	foreach ($user_infos as $key => $value) 
	{
		$link->query("INSERT INTO users_ranking (id_utente, puntate_usate, aste_partecipate) 
					 VALUES ('$key', $value[puntate_usate], ". count($value['aste_partecipate']) . ") 
					 ON DUPLICATE KEY UPDATE puntate_usate = puntate_usate + $value[puntate_usate], 
					 aste_partecipate = aste_partecipate + ". count($value['aste_partecipate']));
		//echo $key . " => " . $value['puntate_usate'] . " - " . count($value['aste_partecipate']) . "<br>";
	}
	$link->close();
}
?>