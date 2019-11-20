<?php
for($i = 0; $i < 5; $i++)
{
	$pid = pcntl_fork();
	if($pid == 0)
	{
		$s = getmypid() . " ". date("H:i:s") . "\r";
		file_put_contents("logs/" . getmypid() . ".txt", $s, FILE_APPEND | LOCK_EX);
		exit();
	}
}

?>