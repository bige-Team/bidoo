<?php
include_once "bid_utils.php";
include_once "mysql_utils.php";

$n_thread = 10;
set_time_limit(0);
for($i = 1; $i <= $n_thread; $i++)
{
	$pid = pcntl_fork();
	if($pid == -1)
		die("Error forking...\n");
	elseif($pid == 0){
		child_loop($n_thread, $i);
		exit();
	}
}
exit();
//while(pcntl_waitpid(0, $status) != -1);

function child_loop($n_thread, $index)
{
	$link = connect_to_stats();
	
	while(true)
	{
		$s = @file_get_contents('https://it.bidoo.com/data.php?ALL='.$index.'&LISTID=0', false, get_stream_context(1));
		if(FALSE === $s)
		{
			//Stream failed
			echo "[" . getmypid() . "]: Failed stream with $index - " . date("H:i:s") . "\n";
			break;
		}
		else if(strpos($s, 'OFF') == true) 
		{
			//Auction finished
			$fine = explode(';', $s);

			$nome = $fine[4];
			if(strlen($nome) > 4) 
			{
				$puntate = $fine[3];
				$time = $fine[2];
				$tipo = $fine[5];

				$link->query("INSERT INTO winners VALUES ($index, '$nome', $time, $puntate, '$tipo')");		
				echo "[" . getmypid() . "]: Inserted winner for $index\n";		
				$index += $n_thread;
			}
		}
		else if(strpos($s, 'ON') == true) 
		{
			//Auction running
			echo "[" . getmypid() . "]: Auction $index running\n";
		}
		else
		{
			$msg = "[" . getmypid() . "]: Reached the end with $index\n";
			die($msg);
			exit();
		}
	}
	$link->close();
}
?>
