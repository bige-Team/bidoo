<?php
/*
puntate usate
username
id prodotto
tipo puntata 1-2 = manuale 3 = auto
*/

function connect()
{
	$link = new mysqli("sql7.freemysqlhosting.net", "sql7308522", "bCQvsAUzMS", "sql7308522");
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

function write_db($file)
{
	$tmp_file = file($file);
	for($i = 0; $i < count($tmp_file); $i++)
	{
		$parts = explode(';', $tmp_file[$i]);
		$n_puntate = $parts[0];
		$id_utente = $parts[1];
		$id_utente = $parts[1];
		$id_utente = $parts[1];
	}
}

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