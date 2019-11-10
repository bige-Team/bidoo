<?php
$arr = [];
$arr_lock = 0; //1 - locked; 0 - free
for ($i=0; $i < 50; $i++)
{ 
	$arr[$i] = $i;
}
//print_r($arr);
#CREATING SHARED MEMORY
$shm_id = shmop_open(0xff3, "c", 0644, 1);//8 -> size(bytes), c -> create shared memory
shmop_write($shm_id, $arr_lock, 0);

$pid = pcntl_fork();
if($pid == -1)
	die("Error forking...\n");
elseif($pid == 0) //PID 0 quando il figlio arriva a questo punto del codice
{
	while(true)
	{
		array_updater($arr, $arr_lock);
	}
}
else //Codice del processso padre
{
	while(true)
	{
		$a_lock = shmop_read($shm_id, 0, 1);//Read all the shm
		if($a_lock == 0)
		{
			echo "[" .getmypid() . "]: I have access to the array\n";
		}
		else
			echo "[" .getmypid() . "]: Array locked\n";
		usleep(1000000);
	}
}

while(pcntl_waitpid(0, $status) != -1);

function array_updater(&$array)
{
	#ACCESS TO THE SHARED MEMORY
	$shm_id = shmop_open(0xff3, "w", 0, 0);//w -> read&write
	$arr_lock = shmop_write($shm_id, 1, 0);//write 'true' in the shm

	echo "[" . getmypid() . "]: Locking array\n";
	#SEZIONE CRITICA
	echo "[" . getmypid() . "]: Modifying...\n";
	//echo "[" . getmypid() . "](sub-pc): Lock -> $lock\n";
	for ($i=0; $i < 5; $i++) 
	{ 
		//echo "[" . getmypid() . "]: Inserting $i\n";
		usleep(1000000);//1 sec
	}
	#SEZIONE CRITICA
	echo "[" . getmypid() . "]: Unlocking array\n";
	#WRITE IN THE SHARED MEMORY
	shmop_write($shm_id, 0, 0);//write 'false' in the shm

	echo "[" . getmypid() . "]: Sleeping...\n";
	sleep(5);
}
?>

<?php 
/*
$t1 = micr0time(true);
$s1 = file_get_contents("https://it.bidoo.com/auction.php?a=Buono_Carrefour_10_e_20P_8723399");
$s2 = file_get_contents("https://it.bidoo.com/auction.php?a=Buono_Amazon_70_e_140P_8721960");
$s3 = file_get_contents("https://it.bidoo.com/auction.php?a=Buono_Amazon_30_e_75P_8722401");
$s4 = file_get_contents("https://it.bidoo.com/auction.php?a=Buono_Amazon_5_e_10P_8716283");
$s5 = file_get_contents("https://it.bidoo.com/auction.php?a=Buono_Carburante_10_e_24P_8723177");
echo "Done in " . (microtime(true)-$t1)*1000;
*/
?>