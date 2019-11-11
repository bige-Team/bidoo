<?php
function connect()
{
	$link = new mysqli("127.0.0.1", "root", "", "bidoo");
	//$link = new mysqli("localhost", "root", "Rt9du2pg", "bidoo");
	//$link = new mysqli("localhost", "root", "", "bidoo");
	if (mysqli_connect_errno()) 
	{
		printf("linkect failed: %s\n", mysqli_connect_error());
		return;
	}
	return $link;
}
//time_stamp -> INT tipo_puntata -> char(1) id_utente -> VARCHAR(15)
function create_table($name)
{
	query("CREATE TABLE if not exists " . $name . " (
		id_utente VARCHAR(15),
		time_stamp INT,
		n_puntate INT PRIMARY KEY,
		tipo_puntata char(1)
	);");
	$l = new mysqli("127.0.0.1", "root", "", "bidoo_stats");
	$l->query("INSERT INTO auction_tracking (name) VALUES ($name)");
	$l->close();
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
	query("INSERT INTO " . $table . "(id_utente, time_stamp, n_puntate, tipo_puntata) VALUES (\"" .$id_utente. "\", " .$time_stamp. ", " .$n_puntate. ", \"" .$tipo_puntata. "\")");
}

function insert_array($table, $arr)
{
	for($i = 0; $i < count($arr) - 1; $i++)
		insert_line($table, $arr[$i]);	
}

function select_row($table, $row_name)
{
	return query("SELECT " . $row_name . " FROM " . $table);
}

function last_10($table)
{
	return query("SELECT * FROM " . $table . " ORDER BY n_puntate DESC LIMIT 10");
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
	//DIFFERENZA TEMPO
	/*
	$res = query("SELECT id_utente, time_stamp FROM " . $table . " ORDER BY time_stamp ASC");
	$time = [];
	$i = 0;
	foreach ($res->fetch_all() as $key => $value)
	{
		$time[$i++] = $value[1];
	}
	print_r($time);
	echo "<br>";
	for ($i=count($time)-1; $i >= 0; $i--)
	{
		if($i > 0)
			$time[$i] = $time[$i] - $time[$i-1];
	}
	print_r($time);
	*/
}




//create_table("ciao");
/*
insert_line("ciao", "231;giannimario;193028374;3");
insert_line("324;ivan;193028374;1");
insert_line("45;osanna;193028389;1");
insert_line("65;rinogino;193028390;3");
*/

/*
PRELEVAMENTO DI DATI DAL DATABASE
$res = query("SELECT id_utente FROM bidoo_data");
if($res->num_rows > 0)
{
	while($row = $res->fetch_assoc())
	{
		echo "id = " . $row["id_utente"];
	}
}
else
	echo "0 results";

INSERIMENTO NEL DATABASE DI DATI
$res = query("INSERT INTO bidoo_data (id_utente) VALUES ('giovanni')");
*/
?>