<?php
include_once "bid_utils.php";

$val = 8714000; //8.714.000
$diff = 10000; //10.000
set_time_limit(0);
for($i = 0; $i < 20; $i++)
{
	$pid = pcntl_fork();
	if($pid == -1)
		die("Error forking...\n");
	elseif($pid == 0){
		execute_code($val, $diff, $i);
		exit();
	}
}

while(pcntl_waitpid(0, $status) != -1);

function execute_code($val, $diff, $x)
{
	echo "Started " . getmypid() . "\n";
	$link = new mysqli("127.0.0.1", "root", "", "bidoo_stats");

	for($i = ($val - ($diff*($x+1))); $i < ($val - ($diff*$x)); $i++)
	{
		$s = @file_get_contents('https://it.bidoo.com/data.php?ALL='.$i.'&LISTID=0', false, get_stream_context(1));
		if(strpos($s, 'OFF') == true) {
			//echo 'asta finita';
			$fine = explode(';', $s);

			$nome = $fine[4];
			if(strlen($nome) > 4) {
				$puntate = $fine[3];
				$time = $fine[2];
				$tipo = $fine[5];

				$primo = $puntate.';'.$nome.';'.$time.';'.$tipo;
				//echo $primo;				
				$link->query("INSERT INTO winners VALUES ($i, '$nome', $time, $puntate, '$tipo')");				
				//echo "[$x]: inserted $nome auction $i\n";
			}
		}
	}	
	$link->close();
}
?>
