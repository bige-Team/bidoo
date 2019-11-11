<?php
declare(ticks = 1);
pcntl_signal(SIGINT, "end_of_all");
sleep(100);	

function end_of_all($sig)
{
	echo 'Exiting with signal: ' . $sig;
  exit(1);
}
?>