<?php
//sql functions
function connect() {
	//$link = new mysqli("sql7.freemysqlhosting.net", "sql7308522", "bCQvsAUzMS", "sql7308522");
	//$link = new mysqli("localhost", "root", "Rt9du2pg", "bidoo");
	$link = new mysqli("localhost", "root", "", "bidoo");
	if (mysqli_connect_errno()) 
	{
		printf("linkect failed: %s\n", mysqli_connect_error());
		return;
	}
	return $link;
}
function query($query) {
	$link = connect();
	$result = $link->query($query);
	$link->close();
	return $result;
}
function create_table($name) {
	query("CREATE TABLE if not exists " . $name . " (
		id_utente VARCHAR(15),
		time_stamp INT,
		n_puntate INT PRIMARY KEY,
		tipo_puntata char(1)
	);");
	//$l = new mysqli("localhost", "root", "Rt9du2pg", "bidoo_stats");
	//$l->query("INSERT INTO auction_tracking (name) VALUES ($name)");
	//$l->close();
}

function last_10($table)
{
	return query("SELECT * FROM " . $table . " ORDER BY n_puntate DESC LIMIT 10");
}
function insert_line($table, $string)
{
	$parts = explode(';', $string);
	$n_puntate = $parts[0];
	$id_utente = $parts[1];
	$time_stamp = $parts[2];
	$tipo_puntata = $parts[3];
	query("INSERT INTO " . $table . "(id_utente, time_stamp, n_puntate, tipo_puntata) VALUES ('" .$id_utente. "', " .$time_stamp. ", " .$n_puntate. ", '" .$tipo_puntata. "')");
}

function insert_array($table, $arr)
{
	create_table($table);
	for($i = 0; $i < count($arr) - 1; $i++)
		insert_line($table, $arr[$i]);	
}

/* stato asta:
0 = da assegnare
1 = assegnata
2 = finita
*/
function insert_auc($id, $name) {
	query("INSERT INTO aucs VALUES (" .$id. ", '" .$name. "', 0)");
}
function assign_auc($id) {
	query("UPDATE aucs SET stato = '1' WHERE id = " .$id);
}
function end_auc($id) {
	query("UPDATE aucs SET stato = '2' WHERE id = " .$id);
}
function get_state($id) {
	$aucs = query("SELECT id, nome FROM aucs WHERE stato = 2 AND id = ".$id);
	$aucs = $aucs->fetch_all();

	$aucs1 = null;
	for ($i=0; $i <count($aucs) ; $i++) {
		$aucs1[$aucs[$i][0]] = $aucs[$i][1];
	}
	return $aucs1;
}

//modifica lo stato, prende le aste
function getAucs($qta) {
	$aucs = query("SELECT id, nome FROM aucs WHERE stato = 0 ORDER BY id LIMIT " .$qta);
	$aucs = $aucs->fetch_all();

	$aucs1 = null;
	for ($i=0; $i <count($aucs) ; $i++) {
		$aucs1[$aucs[$i][0]] = $aucs[$i][1];
		assign_auc($aucs[$i][0]);		
	}
	return $aucs1;
}

//php functions
function generaArray($key, $ids) {	//ANALIZZO IL FILE PHP
	$opts = array('http'=>array('timeout'=>1,));
	$ctx = stream_context_create($opts);

	$s = @file_get_contents('https://it.bidoo.com/data.php?ALL='.$key.'&LISTID=0', false, $ctx);
	$pezzi = explode("|", $s);
	$arr = scaricaArray($ids[$key]);	//prendo dal database le ultime 10 puntate dell'asta

	//1571240953*[8266194;ON;1571241000;1;;,]()		asta che deve ancora iniziare
	if(strpos($s, 'ON') == true) {
		//se l'asta è in corso
		if(count($pezzi) > 1) {	//se non c'è almeno una puntata allora l'asta deve ancora iniziare e non salvo nulla

			$primoPezzo = explode(",", $pezzi[0]);
			$primaRiga2 = explode(";", $primoPezzo[1]);

			//prendo l'ultimo utente che ha puntato
			$puntate = $primaRiga2[0];
			$nome = $primaRiga2[1];
			$time = $primaRiga2[2];
			$tipo = $primaRiga2[3];	//1 e 2 = manuale, 3 e 4 = auto

			$primo = $puntate.';'.$nome.';'.$time.';'.$tipo;
			$pezzi[0] = $primo;	//array con le info delle puntate dell'asta
			$pezzi[count($pezzi)-1] = substr($pezzi[count($pezzi)-1], 0, -3);	//prendo la substring togliendo ]()
			//ribalto l'array
			
			//ora su $pezzi ho un'array di stringhe, una stringa è una puntata formattata: puntate_totali;nome;timestamp;tipo_puntata
			if(!is_null($pezzi)){	//se l'array non è vuoto
				$pezzi = array_reverse($pezzi);	//lo rovescio
				if(!is_null($arr)){	//se la tabella di quell'asta non è vuota
					$elems = array_diff($pezzi, $arr);
					return $elems;
				}
				else
					return $pezzi;
			}
			return null;
		}
		else {
			//l'asta deve ancora iniziare
			return null;
		}
	}
	else if(strpos($s, 'OFF') == true) {
		//echo 'asta finita';
		$fine = explode(';', $s);

		$puntate = $fine[3];
		$nome = $fine[4];
		$time = $fine[2];
		$tipo = $fine[5];

		$primo = $puntate.';'.$nome.';'.$time.';'.$tipo;

		create_table($ids[$key]);
		insert_line($ids[$key], $primo);
		//aggiorno l'asta con id $key
		end_auc($key);

		return null;
	}
	else {
		//asta in stop, non faccio nulla
		return null;
	}
}
function scaricaArray($name) {
	$arr = last_10($name);	//prendo i dati delle ultime 10 puntate dal database
	
	if($arr != null){
		$arr = $arr->fetch_all();	//trasformo i dati per poterli leggere in un array di array
		if(count($arr) != 0) {

			foreach ($arr as $value) {
				$puntate = $value[0];
				$nome = $value[1];
				$time = $value[2];
				$tipo = $value[3];
				$elem = $puntate.';'.$nome.';'.$time.';'.$tipo;
				$arr1[] = $elem;
			}
			return $arr1;
		}
	}
	return null;
}

?>