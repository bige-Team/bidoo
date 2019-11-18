<?php
include_once "bid_utils.php";
include_once "mysql_utils.php";
set_time_limit(0);
//Creating shm segment
$shm_key = ftok(__FILE__, 'b');//Generete a hex value
$shm_id = shmop_open($shm_key, "c", 0644, 1);//Create the shm space
shmop_write($shm_id, 1, 0);//Locking
shmop_close($shm_id);

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
		child_loop($i);
		exit();
	}
}
#Parent code
parent_loop();


function parent_loop()
{
	$shm_key = ftok(__FILE__, 'b');
	$shm_id = shmop_open($shm_key, "w", 0, 0);
	shmop_write($shm_id, 1, 0);#Locking
	echo "[parent]: gathering auctions...\n";
	$auctions = get_and_insert_auctions();
	shmop_write($shm_id, 0, 0);#Unlocking
	shmop_close($shm_id);

	while(true)
	{
		sleep(300);//5 Minutes
		echo "[parent]: gathering auctions...\n";
		#TODO: manage $auctions == null -> auctions in pause
		$shm_key = ftok(__FILE__, 'b');
		$shm_id = shmop_open($shm_key, "w", 0, 0);
		shmop_write($shm_id, 1, 0);//Locking
		$auctions = get_and_insert_auctions();
		shmop_write($shm_id, 0, 0);//Unlocking
		shmop_close($shm_id);
	}
}

function child_loop($iteration)
{
	echo "Started thread " . getmypid() . "\n";
	$max_auctions = 10;
	$auctions_count = 0;
	if($auctions_count < $max_auctions)#Need more auctions
	{
		//Read if the database is being updated
		$shm_key = ftok(__FILE__, 'b');
		$shm_id = shmop_open($shm_key, "w", 0, 0);
		$updating_db_lock = shmop_read($shm_id, 0, 1);
		shmop_close($shm_id);
		//If it is, just wait in the loop
		while($updating_db_lock != 0)
		{
			echo "[" . getmypid() . "]: Sleeping, database being updated\n";
			usleep(rand(300000,600000));#300-600 ms
			$shm_key = ftok(__FILE__, 'b');
			$shm_id = shmop_open($shm_key, "w", 0, 0);
			$updating_db_lock = shmop_read($shm_id, 0, 1);
			shmop_close($shm_id);
		}
		sleep($iteration*2);
		$auction_needed = $max_auctions - $auctions_count;
		echo "[" . getmypid() . "]: Retriving auctions to analize, need $auction_needed\n";
		$res = query_to_bidoo_stats("SELECT a.name, a.id FROM auction_tracking as a WHERE a.assigned=0 AND a.terminated=0 ORDER BY a.name LIMIT $auction_needed");
		$res = $res->fetch_all();

		if(count($res) == 0)#No auctions aviable
		{
			echo "[" . getmypid() . "]: No free auctions, waiting...\n";
			sleep(30);#Sleep 30 seconds
		}
		else#Some auctions aviable
		{
			$l = connect_to_stats();
			for($i = 0; $i < count($res); $i++)
			{
				$current = $res[$i][0];
				$l->query("UPDATE auction_tracking SET auction_tracking.assigned=1 WHERE auction_tracking.name='$current'");
			}
			$l->close();

			for($i = 0; $i < count($res); $i++)
			{
				$name = $res[$i][0];
				$state = create_table($name);
				echo "[" . getmypid() . "]: Creating table for $name with result $state\n";
			}

			$auctions_count += count($res);
			echo "[" . getmypid() . "]: Starting analizing $auctions_count auctions\n";
			analize_auctions($res, $auctions_count, $max_auctions);
			echo "[" . getmypid() . "]: Breaked, sleeping";
			sleep(10);
		}
	}
		
	shmop_delete($shm_id);
}
?>