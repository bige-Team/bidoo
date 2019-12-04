<?php
function connect()
{
	$link = new mysqli("127.0.0.1", "root", "", "bidoo");
	if (mysqli_connect_errno()) 
	{
		printf("linkect failed: %s\n", mysqli_connect_error());
		return null;
	}
	return $link;
}

function connect_to_stats()
{
	$link = new mysqli("127.0.0.1", "root", "", "bidoo_stats");
	if (mysqli_connect_errno()) 
	{
		printf("linkect failed: %s\n", mysqli_connect_error());
		return null;
	}
	return $link;
}

//time_stamp -> INT tipo_puntata -> char(1) id_utente -> VARCHAR(15)
function create_table($name)
{
	$res = query("CREATE TABLE " . $name . " (
		id_utente VARCHAR(15),
		time_stamp INT,
		n_puntate INT PRIMARY KEY,
		tipo_puntata char(1)
	);");# if not exists
	return $res;
}

function query($query)
{
	$link = connect();
	$result = $link->query($query);
	$link->close();
	return $result;
}

function delete_line($table, $string) {
	query("DELETE FROM " . $table . " WHERE name = \"".$string."\"");
}
function insert_elem($table, $string) {
	#TODO: fix VALUES (...) wrong ''
	query("INSERT INTO " . $table . " VALUES (\"".$string."\")");
}

function insert_file($table, $file)
{
	$tmp_file = file($file);
	for($i = 0; $i < count($tmp_file); $i++)
	{
		$parts = explode(';', $tmp_file[$i]);
		$n_puntate = $parts[0];
		$id_utente = $parts[1];
		$time_stamp = $parts[2];
		$tipo_puntata = $parts[3];
		query("INSERT INTO " . $table . " VALUES ('" .$id_utente. "', " .$time_stamp. ", " .$n_puntate. ", '" .$tipo_puntata. "')");
	}
}

function insert_line($table, $string)
{
	$parts = explode(';', $string);
	$n_puntate = $parts[0];
	$id_utente = $parts[1];
	$time_stamp = $parts[2];
	$tipo_puntata = $parts[3];
	query("INSERT INTO $table VALUES ('$id_utente', $time_stamp, $n_puntate, '$tipo_puntata')");
}

function insert_array($table, $arr)
{
	$l = connect();
	for($i = 0; $i < count($arr) - 1; $i++)
	{
		$parts = explode(';', $arr[$i]);
		$n_puntate = $parts[0];
		$id_utente = $parts[1];
		$time_stamp = $parts[2];
		$tipo_puntata = $parts[3];
		query("INSERT INTO $table VALUES ('$id_utente', $time_stamp, $n_puntate, '$tipo_puntata')");
	}
	$l->close();
}

function select_row($table, $row_name)
{
	return query("SELECT " . $row_name . " FROM " . $table . " ORDER BY n_puntate DESC");
}

function last_10($table)
{
	return query("SELECT * FROM " . $table . " ORDER BY n_puntate DESC LIMIT 10");
}

function  query_to_bidoo_stats($query)
{
	$l = new mysqli("127.0.0.1", "root", "", "bidoo_stats");
	$res = $l->query($query);
	$l->close();
	return $res;
}

function create_html_table($table)
{
	echo "<table>";
	echo "<tr>";
	echo "<td>ID UTENTE</td>";
	echo "<td>TIME STAMP</td>";
	echo "<td>N PUNTATE</td>";
	echo "<td>TIPO PUNTATA</td>";
	echo "</tr>";
	$id_utente = select_row($table, "id_utente");
	$time_stamp = select_row($table, "time_stamp");
	$n_puntate = select_row($table, "n_puntate");
	$tipo_puntata = select_row($table, "tipo_puntata");

	if($id_utente->num_rows > 0 && $time_stamp->num_rows > 0 && $n_puntate->num_rows > 0 && $tipo_puntata->num_rows > 0)
	{
		$row1 = $id_utente->fetch_all();
		$row2 = $time_stamp->fetch_all();
		$row3 = $n_puntate->fetch_all();
		$row4 = $tipo_puntata->fetch_all();

		for($i = 0; $i < count($row1); $i++)
		{
			echo "<tr>";
			echo "<td>" . $row1[$i][0] . "</td>";
			echo "<td>" . $row2[$i][0] . "</td>";
			echo "<td>" . $row3[$i][0] . "</td>";
			echo "<td>" . $row4[$i][0] . "</td>";
			echo "</tr>";
		}
	}
	echo "</table>";
}

function empty_table()
{
	query("TRUNCATE TABLE bidoo_data");
}

function get_table_names()
{
	$res = query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE='BASE TABLE' AND TABLE_SCHEMA='bidoo'");
	$i = 0;
	$names = [];
	if ($res->num_rows > 0) 
	    while($row = $res->fetch_assoc()) 
	    {
	    	$names[$i] = $row['TABLE_NAME'];
	    	$i++;
	    }
	return $names;
}

function get_table_names_from_autcion_tracking()
{
	$res = query_to_bidoo_stats("SELECT a.name FROM auction_tracking AS a WHERE a.analized = 0");
	$res = $res->fetch_all();
	$names = array();
	foreach ($res as $key => $value) 
	{
		$names[] = $value[0];
	}
	return $names;
}

function analize_database($table)
{
	$id_utenti = [];
	echo $table . "<br>";
	$res = query("SELECT id_utente, time_stamp FROM " . $table . " ORDER BY id_utente, time_stamp");
	
	foreach ($res->fetch_all() as $key => $value)
	{
		if(!isset($id_utenti[$value[0]]))
			$id_utenti[$value[0]]['n_puntate'] = 1; 
		else
			$id_utenti[$value[0]]['n_puntate'] += 1;

		if(!isset($id_utenti[$value[0]]['time_stamp']))
			$id_utenti[$value[0]]['time_stamp'] = $value[1];
		else
			$id_utenti[$value[0]]['time_stamp'] = ($id_utenti[$value[0]]['time_stamp']-$value[1]);
	}

	echo "<table>";
	echo "<tr>";
	echo "<td>UTENTE</td>";
	echo "<td>PUNTATE USATE</td>";
	echo "<td>TEMPO PUNTATA MEDIO</td>";
	echo "</tr>";

	foreach ($id_utenti as $key => $value) 
	{
		$avg = $id_utenti[$key]['time_stamp']/$id_utenti[$key]['n_puntate'];
		echo "<tr>";
		echo "<td>" . $key . "</td>";
		echo "<td>" . $value['n_puntate'] . "</td>";
		echo "<td>" . $avg . "</td>";
		echo "</tr>";
	}

	echo "</table>";

	print_r($id_utenti);
}
?>