
<?php

$pid = pcntl_fork();
//$pid1 = pcntl_fork();

if($pid === -1)
{
	die('could not fork');
}
elseif ($pid === 0)
{
	// we are the parent
	pcntl_wait($status); //Protect against Zombie children
}
else
{
	// we are the child
	$i = 0;
	echo "\nMy pid = " . getmypid() . "\n";
	while($i<100)
	{
		/*
		$link = new mysqli("localhost", "root", "", "bidoo");
		$link->query("INSERT INTO test (name, t) VALUES ('$pid', CURRENT_TIME)");
		$link->close();
		$i++;*/
		echo $pid . ' - ' . $i . "\n";
		$i++;

		sleep(1);
	}
	
	pcntl_wait($status);
}
/*
if($pid1 === -1)
{
	die('could not fork');
}
elseif ($pid1 === 0)
{
	// we are the parent
	pcntl_wait($status); //Protect against Zombie children
}
else
{
	// we are the child
	$i = 0;
	while($i<100)
	{
		
		$link = new mysqli("localhost", "root", "", "bidoo");
		$link->query("INSERT INTO test (name, t) VALUES ('$pid', CURRENT_TIME)");
		$link->close();
		$i++;
		echo $pid1 . ' - ' . $i . "\n";
		$i++;
		sleep(1);
	}
	
	pcntl_wait($status);
}
*/
?>
