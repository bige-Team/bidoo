<?php
$time = microtime(true);
echo ($time / 1000000) . "\n";
while(($time/1000000) < 10)
{
	$time = microtime(true);
	echo "$time\n";
}



