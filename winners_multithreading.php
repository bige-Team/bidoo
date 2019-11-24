<?php
include_once "mysql_utils.php";

$n_thread = 10;
$opts = array('http'=>array('timeout'=>1,));
$ctx = stream_context_create($opts);
set_time_limit(0);
for($i = 1; $i <= $n_thread; $i++)
{
	$pid = pcntl_fork();
	if($pid == -1)
		die("Error forking...\n");
	elseif($pid == 0){
		child_loop($n_thread, $i, $ctx);
		exit();
	}
}
//echo "[PARENT]: Exiting\n";

while(pcntl_waitpid(0, $status) != -1);
exit();

function child_loop($n_thread, $index, $ctx)
{
	set_time_limit(0);
	//echo "Started " . getmypid() . "\n";
	while(true)
	{
		$s = @file_get_contents("https://it.bidoo.com/data.php?ALL=$index&LISTID=0", false, $ctx);
		if(FALSE === $s)
		{
			//Stream failed
			//echo "[" . getmypid() . "]: Failed stream with $index - " . date("H:i:s") . "\n";
			continue;
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

				$link = connect_to_stats();
				$res = $link->query("INSERT INTO winners VALUES ($index, '$nome', $time, $puntate, '$tipo')");
				$link->close();
				//echo "[" . getmypid() . "]: Inserted winner for $index with result $res - " . date("H:i:s") . "\n";		
			}
		}
		else if(strpos($s, 'ON') == true) 
		{
			//Auction running
			//echo "[" . getmypid() . "]: Auction $index running\n";
		}
		else
		{
			//echo "[" . getmypid() . "]: Reached the end with $index\n";
			//exit();
		}
		$index += $n_thread;
	}
	echo "Definitely an error\n";
}
?>
