<?php
include_once "bid_utils.php";
include_once "mysql_utils.php";
set_time_limit(0);
//Creating shm segment
$updating_db_lock = 1;
$shm_key = ftok(__FILE__, 'b');//Generete a hex value
$shm_id = shmop_open($shm_key, "c", 0644, 1);//Create the shm space
shmop_write($shm_id, $updating_db_lock, 0);//Write in

for($i = 0; $i < 5; $i++)
{
	$hour = date("H");
	while($hour >= 0 && $hour < 12)
	{
		echo "Auctions in pause " . $hour . "\n";
		sleep(600);#Sleep 10 minutes
		$hour = date("H");
	}
	$pid = pcntl_fork();
	if($pid == -1)
		die("Error forking...\n");
	elseif($pid == 0) #Child code
	{
		child_loop();
		exit();
	}
}
#Parent code
parent_loop($shm_id);


function parent_loop($shm_id)
{
	shmop_write($shm_id, 1, 0);#Locking
	$auctions = get_and_insert_auctions();
	shmop_write($shm_id, 0, 0);#Unlocking

	while(true)
	{
		sleep(300);//5 Minutes
		check_auctions_status($auctions);#Check using database?
		#TODO: manage $auctions == null -> auctions in pause
		shmop_write($shm_id, 1, 0);//Locking
		$auctions = get_and_insert_auctions();
		shmop_write($shm_id, 0, 0);//Unlocking
		
	}
}

function child_loop()
{
	usleep(rand(100000,500000));
	echo "Started thread " . getmypid() . "\n";
	$max_auctions = 10;
	$auctions_count = 0;
	while(true)
	{
		if($auctions_count < $max_auctions)
		{
			//Read if the database is being updated
			$shm_key = ftok(__FILE__, 'b');
			$shm_id = shmop_open($shm_key, "w", 0, 0);
			$updating_db_lock = shmop_read($shm_id, 0, 1);
			//If it is, just wait in the loop
			while($updating_db_lock != 0)
			{
				usleep(200000);#200 ms
				$updating_db_lock = shmop_read($shm_id, 0, 1);
				//echo "Child " . getmypid() . " -> locked\n";
			}

			$auction_needed = $max_auctions - $auctions_count;
			$l = new mysqli("127.0.0.1", "root", "", "bidoo_stats");
			$res = $l->query("SELECT a.name, a.id FROM auction_tracking as a WHERE a.assigned=0 AND a.terminated=0 ORDER BY a.name LIMIT $auction_needed");
			$res = $res->fetch_all();
			if(count($res) == 0)#No auctions aviable
			{
				echo "[" . getmypid() . "]: No free auctions, waiting...\n";
				$l->close();
				sleep(30);
			}
			else#Some auctions aviable
			{
				for($i = 0; $i < count($res); $i++)
				{
					$current = $res[$i][0];
					$l->query("UPDATE auction_tracking SET auction_tracking.assigned=1 WHERE auction_tracking.name='$current'");
				}
				$l->close();
				for($i = 0; $i < count($res); $i++)
					create_table($current);

				$auctions_count += count($res);
				echo "[" . getmypid() . "]: Starting analizing\n";
				analize_auctions($res);
			}
		}
		else
		{

		}
		
	}
	
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