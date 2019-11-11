<?php
include_once "bid_utils.php";
include_once "mysql_utils.php";
set_time_limit(0);

//Get auctions
$auctions = get_auctions();

$l = new mysqli("127.0.0.1", "root", "", "bidoo_stats");
foreach ($auctions as $key => $value) 
{
	//$l->query("TRUNCATE TABLE auction_traking");
	$l->query("INSERT INTO auction_tracking (name) VALUES ('$value')");
}
//print_r($auctions);
foreach ($auctions as $key => $value) {
	//echo "$value status: " . auction_check_status($value) . "\n";
	$s = file_get_contents("https://it.bidoo.com/data.php?ALL=$value&LISTID=0");
	//echo "$value -> " . strpos($s, 'ON') . "\n";
	echo $s . "\n";
}
$l->close();

//print_r($auctions);
/*
#CREATING SHARED MEMORY SEGMENT
$a = array_to_string($auctions, '|');
$shm_key = ftok(__FILE__, 'g');//Generete a hex value
$shm_id = shmop_open($shm_key, "c", 0644, strlen($a));//Create the shm space
shmop_write($shm_id, $a, 0);//Write in the string

//$data = shmop_read($shm_id, 0, shmop_size($shm_id));

shmop_delete($shm_id);
*/
#PARENT CODE
//TODO: get the lock on the array
//$tmp_auctions = get_auctions();
//$new_auctions = array_diff($auctions, $tmp_auctions);
//TODO: give these auction to the childrens
#PARENT CODE

#CHILD CODE
//TODO: get lock on the shm containing the auctions to start monitoring
#CHILD CODE

/*
for($i = 0; $i < 5; $i++)
{	
	$pid = pcntl_fork();
	if($pid == -1)
		die("Error forking...\n");
	elseif($pid == 0){
		execute_code($i);
		exit();
	}
	else
	{
		#Parent code
		#TODO: update auction_traking every t seconds
		while(true)
		{
			sleep(10);

		}
	}
}

while(pcntl_waitpid(0, $status) != -1);
*/
function execute_code($pc, $range)
{

}
?>