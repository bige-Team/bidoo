<?php
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

//Make the parent wait all the childs
while(pcntl_waitpid(0, $status) != -1);

# Execute more code here

function execute_code($msg)
{
	for($j=0; $j<10;$j++)
	{
		echo "[$msg]: printing $j - . " . round(microtime(true)*1000) . "\n";
		sleep(rand(1,4));
	}
	echo "terminated $msg\n";
	//$sleep_time = rand(4, 11);
	//sleep($sleep_time);
	
}
?>