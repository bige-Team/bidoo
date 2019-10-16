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

function insert_file($file)
{
	$tmp_file = file($file);
	for($i = 0; $i < count($tmp_file); $i++)
	{
		$parts = explode(';', $tmp_file[$i]);
		$n_puntate = $parts[0];
		$id_utente = $parts[1];
		$id_prodotto = $parts[2];
		$tipo_puntata = $parts[3];
		query("INSERT INTO bidoo_data (id_utente, id_prodotto, n_puntate, tipo_puntata) VALUES (\"" . $id_utente . "\", \"" . $id_prodotto . "\", " . $n_puntate . ", " . $tipo_puntata . ")");
	}
}

function insert_line($string)
{
	$parts = explode(';', $string);
	$n_puntate = $parts[0];
	$id_utente = $parts[1];
	$id_prodotto = $parts[2];
	$tipo_puntata = $parts[3];
	query("INSERT INTO bidoo_data (id_utente, id_prodotto, n_puntate, tipo_puntata) VALUES (\"" . $id_utente . "\", \"" . $id_prodotto . "\", " . $n_puntate . ", " . $tipo_puntata . ")");
}

function select_row($row_name)
{
	return query("SELECT " . $row_name . " FROM bidoo_data");
}

function create_html_table()
{
	echo "<table>";
	echo "<tr>";
	echo "<td>ID UTENTE</td>";
	echo "<td>ID PRODOTTO</td>";
	echo "<td>N PUNTATE</td>";
	echo "<td>TIPO PUNTATA</td>";
	echo "</tr>";
	$id_utente = select_row("id_utente");
	$id_prodotto = select_row("id_prodotto");
	$n_puntate = select_row("n_puntate");
	$tipo_puntata = select_row("tipo_puntata");

	if($id_utente->num_rows > 0 && $id_prodotto->num_rows > 0 && $n_puntate->num_rows > 0 && $tipo_puntata->num_rows > 0)
	{
		$row1 = $id_utente->fetch_all();
		$row2 = $id_prodotto->fetch_all();
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

create_html_table();
/*
insert_line("231;giannimario;193028374;3");
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