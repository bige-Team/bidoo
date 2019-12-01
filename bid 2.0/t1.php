<?php
include_once('utils.php');
set_time_limit(0);

//$aucs = array();
$nAucs = 8;
$aucs = getAucs($nAucs);

while (true) {
	$hour = date('H')+1;
	if($hour <= 23 && $hour > 12 && !is_null($aucs)) {

		//controllo che le aste correnti siano ancora valide
		foreach ($aucs as $key => $value) {
			$ended_auc = get_state($key);
			if(!is_null($ended_auc)) {		//se viene restituita l'asta analizzata significa che è finita e la rimuovo
				unset($aucs[$key]);
			}
		}
		
		$index = $nAucs-count($aucs);	//numero di aste che posso ancora assegnare al processo

		if($index > 0) {	//ho meno aste di quelle che mi aspetto, quindi aggiorno la lista delle aste
			$newAucs = getAucs($index);
			if(!is_null($newAucs)){
				$aucs = $aucs + $newAucs;
			}
		}

		//se ho delle aste le analizzo
		foreach ($aucs as $key => $value) {
			$new = generaArray($key, $aucs);

			if(!is_null($new)){		//se ci sono aggiornamenti da fare
				insert_array($value, $new);
			}
		}
		sleep(1);
	}
}
?>