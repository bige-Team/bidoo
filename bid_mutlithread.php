<?php
//include "utils.php";
//include "bid.php";

//start();
$t1 = microtime(true);
for($i = 0; $i < 5; $i++)
{	
	$pid = pcntl_fork();
	if($pid == -1)
		die("Error forking...\n");
	elseif($pid == 0){
		execute_code($i);
		exit();
	}
}

while(pcntl_waitpid(0, $status) != -1);
echo "time: " . (microtime(true)-$t1)*1000 . "\n";

function execute_code($pc)
{
	$t1 = microtime(true);
	$s = "";
	switch ($pc) {
		case 0: $s = "https://it.bidoo.com/auction.php?a=Buono_Carrefour_10_e_20P_8723399";
			break;
		case 1: $s = "https://it.bidoo.com/auction.php?a=Buono_Amazon_70_e_140P_8721960";
			break;
		case 2: $s = "https://it.bidoo.com/auction.php?a=Buono_Amazon_30_e_75P_8722401";
			break;
		case 3: $s = "https://it.bidoo.com/auction.php?a=Buono_Amazon_5_e_10P_8716283";
			break;
		case 4: $s = "https://it.bidoo.com/auction.php?a=Buono_Carburante_10_e_24P_8723177";
			break;
	}
	$file = file_get_contents($s);
	echo "[$pc]: Done " . (microtime(true)-$t1)*1000 . "\n";
}

?>