<?php

$var = 'one';
$pid = pcntl_fork();

if($pid === -1)
{
	exit();
}elseif ($pid === 0)
{
	echo $var;
	$var = 'two';
}else{
	echo $var;
	$var = 'three';

	pcntl_wait($status);
}

?>