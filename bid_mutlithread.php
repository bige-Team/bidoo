<?php
//include "utils.php";
include_once "bid_utils.php";
set_time_limit(0);
//start();

$auctions = array();
$toRemove = array();

$auctions = get_auctions();
print_r($auctions);

#CREATING SHARED MEMORY SEGMENT
$a = "";
foreach ($auctions as $key => $value) 
{
		$a .= $value . "|";
}
$a = substr($a, 0, -1);//Remove last pipe
$shm_key = ftok(__FILE__, 'g');
$shm_id = shmop_open($shm_key, "c", 0644, strlen($a));
shmop_write($shm_id, $a, 0);

#TEST
//$start = $arr_size - strlen(array_pop($auctions))*8;
//echo "start: " . $start . "\n";
$size = shmop_size($shm_id);
$count = strlen(array_pop($auctions));
$start = $size-1-strlen(array_pop($auctions));
echo "size: $size\nstart: $start\ncount: $count\n";
$data = shmop_read($shm_id, 0, $size);
echo "data->" . $data . "\n";
#TEST

shmop_delete($shm_id);

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
}

while(pcntl_waitpid(0, $status) != -1);
*/
function execute_code($pc)
{

}
?>