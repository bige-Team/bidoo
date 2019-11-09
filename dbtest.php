<?php
function connect()
{
	$link = new mysqli("sql7.freemysqlhosting.net", "sql7308522", "bCQvsAUzMS", "sql7308522");
	//$link = new mysqli("localhost", "root", "Rt9du2pg", "bidoo");
	//$link = new mysqli("localhost", "root", "", "bidoo");
	if (mysqli_connect_errno()) 
	{
		printf("linkect failed: %s\n", mysqli_connect_error());
		return;
	}
	return $link;
}

function query($query)
{
	$link = connect();
	$result = $link->query($query);
	$link->close();
	return $result;
}

function insert_line($table, $string)
{
	$parts = explode(';', $string);
	$n_puntate = $parts[0];
	$id_utente = $parts[1];
	$time_stamp = $parts[2];
	$tipo_puntata = $parts[3];
	echo $table . "<br>";
	print_r($parts);
	query("INSERT INTO $table VALUES ('$id_utente', $time_stamp, $n_puntate, '$tipo_puntata')");
	echo "<br> INSERT INTO $table VALUES ('$id_utente', $time_stamp, $n_puntate, '$tipo_puntata')";
}

//insert_line("winners", "mario;431;23;3");
//query("INSERT INTO winners VALUES ('gino', 34, 6, '2')");
insert_line("winners", "80;paolo;321;2");
?>