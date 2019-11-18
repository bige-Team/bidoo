<form method="POST">
	<input type="text" name="id_utente" placeholder="id utente">
	<button name="btnOK">OK</button>
	<br><br>
	<button name="fill">Fill stats table</button> 
	<button name="empty">Empty stats table</button>
	<button name="dropBidoo">Drop bidoo</button>
	<button name="createBidoo">Create bidoo</button>
</form>

<?php
include_once "mysql_utils.php";

if(isset($_POST['btnOK']))
{
	$id_utente = $_POST['id_utente'];
	$table_names = get_table_names();
	$n_puntate = 0;
	$n_tabelle = 0;

	for ($i=0; $i < count($table_names); $i++) 
	{ 
		$res = query("SELECT COUNT(n_puntate) FROM " . $table_names[$i] . " WHERE id_utente='" . $id_utente . "'");
		$res = $res->fetch_all()[0][0];
		if($res > 0)
		{
			$n_puntate += $res;
			$n_tabelle++;
		}
	}
	echo "<b>" . $id_utente . "</b> con <b>" . $n_puntate . "</b> puntate in <b> " . $n_tabelle . "</b> tabella/e";
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

//TODO: CONTROLLARE CHE L'ASTA SIA FINITA E AGGIORNARE LA TABELLA auction_tracking
function update_user_rank()
{	
	$table_names = get_table_names();
	for ($i=0; $i < count($table_names); $i++) 
	{
		$res = query("SELECT DISTINCT id_utente FROM $table_names[$i]");
		$res = $res->fetch_all();
		for ($j=0; $j < count($res); $j++)
		{ 
			$arr[$table_names[$i]][$j] = $res[$j][0];
		}
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
	$link = new mysqli("localhost", "root", "", "bidoo_stats");
	foreach ($user_infos as $key => $value) 
	{
		
		$link->query("INSERT INTO users_ranking (id_utente, puntate_usate, aste_partecipate) 
					 VALUES ('$key', $value[puntate_usate], ". count($value['aste_partecipate']) . ") 
					 ON DUPLICATE KEY UPDATE puntate_usate = puntate_usate + $value[puntate_usate], 
					 aste_partecipate = aste_partecipate + ". count($value['aste_partecipate']));
		
		//echo $key . " => " . $value['puntate_usate'] . " - " . count($value['aste_partecipate']) . "<br>";
	}
	$link->close();
	


	/*
	//Generazione query
	$table_names = get_table_names();
	$get_names_query = "SELECT DISTINCT id_utente FROM (";
	for ($i=0; $i < count($table_names); $i++) 
	{
		$get_names_query .= "SELECT id_utente FROM bidoo." . $table_names[$i] . " ";
		if($i != count($table_names)-1)
			$get_names_query .= " UNION ";
	}
	$get_names_query .= ") as id_utente";

	//SELECT di tutti gli utenti e INSERT nella table users_ranking
	$all_users = query($get_names_query);
	$all_users = $all_users->fetch_all(); //tutti gli utenti
	$link = new mysqli("localhost", "root", "Rt9du2pg", "bidoo_stats");
	for ($i=0; $i < count($all_users); $i++)
	{ 
		$id_utente = $all_users[$i][0];
		$link->query("INSERT INTO users_ranking (id_utente) VALUES ('" . $id_utente . "')");
	}
	$link->close();
	*/

	/*
		+-------------------+
		|	DA OTTIMIZZARE	|
		+-------------------+
	*/

	/*
	$query = "SELECT SUM(t) FROM (";
	for ($i=0; $i < count($all_users); $i++) 
	{
		for ($j=0; $j < count($table_names); $j++) 
		{
			$user = $all_users[$i][0];
			$query .= "SELECT COUNT(id_utente) AS " . $user . " FROM " . $table_names[$j] . " WHERE id_utente='" . $user . "'";
			if($i != count($all_users)-1)
				$query .= " UNION ";
		}
	}
	$query .= ") AS t";
	
	$result = query($query);

	$result = $result->fetch_all();
	print_r($result);
	*/

	/*
	$link = connect();
	for ($i=0; $i < count($table_names); $i++) 
	{
		for ($j=0; $j < count($all_users); $j++) 
		{
			$res = $link->query("SELECT COUNT(id_utente) FROM " . $table_names[$i] . " WHERE id_utente='" . $all_users[$j][0] . "'");
			$res = $res->fetch_all();
			//echo $all_users[$j][0] . " -> " . $res[0][0] . "<br>";
			if($res[0][0] != 0)
			{
				query_to_bidoo_stats("UPDATE users_ranking SET puntate_usate = (SELECT puntate_usate (SELECT * FROM users_ranking) as t WHERE id_utente='" . $all_users[$j][0] . "') + " . $res[0][0] . " WHERE id_utente='" . $all_users[$j][0] . "'");
			}
		}
	}
	$link->close();
	*/

}
?>