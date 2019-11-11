<?php
include_once "bid_utils.php";
include_once "mysql_utils.php";
set_time_limit(0);
//Creating shm segment
$updating_db_lock = 0;
$shm_key = ftok(__FILE__, 'a');//Generete a hex value
$shm_id = shmop_open($shm_key, "c", 0644, 1);//Create the shm space
shmop_write($shm_id, $updating_db_lock, 0);//Write in


$pid = pcntl_fork();
if($pid == -1)
	die("Error forking...\n");
elseif($pid == 0) #Child code
{
	child_loop();
}
else #Parent code
{
	parent_loop();
}

function parent_loop()
{
	shmop_write($shm_id, 1, 0);//Locking
	$auctions = get_and_insert_auctions();
	shmop_write($shm_id, 0, 0);//Unlocking

	while(true)
	{
		sleep(300);//5 Minutes
		check_auctions_status($auctions);#Check using database?
		shmop_write($shm_id, 1, 0);//Locking
		$auctions = get_and_insert_auctions();
		shmop_write($shm_id, 0, 0);//Unlocking
	}
}

function child_loop()
{
	$shm_key = ftok(__FILE__, 'a');
	$shm_id = shmop_open(shm_key, "w", 0, 0);
	$updating_db_lock = shmop_read($shm_id, 0, 1);

	while($updating_db_lock == 1);

	$max_auctions = 10;
	$l = new mysqli("127.0.0.1", "root", "", "bidoo_stats");
		$res = $l->query("SELECT a.name FROM auction_traking as a WHERE a.assigned=0 ORDER BY a.name LIMIT $max_auctions");
		print_r($res->fetch_all());
	$l->close();
	shmop_close($shm_id);
}
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