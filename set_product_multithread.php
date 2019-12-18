<?php
include_once "mysql_utils.php";
set_time_limit(0);

$n_thread = 20;
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

function child_loop($n_thread, $index)
{
	echo "Started " . getmypid() . "\n";
	while(true)
	{
		$name = get_name($index);
		update_name($name, $index);
		$index += $n_thread;
	}	
}

function get_name($id) 
{
	$opts = array('http'=>array('timeout'=>10));
	$ctx = stream_context_create($opts);

	$str = file_get_contents("https://it.bidoo.com/auction.php?a=$id", false, $ctx);

	$values = explode("media-heading\">", $str);
	if(count($values) > 1) 
	{
		$name = explode('<', $values[1])[0];
		return $name;
	}
	return null;
}
function update_name($name, $id)
{
	query_to_bidoo_stats("UPDATE winners SET nome = '$name' WHERE id_asta = $id");
}