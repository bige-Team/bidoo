<?php
$var = 0;

for($i = 0; $i < 5; $i++)
{	
	$pid = pcntl_fork();
	if($pid == -1)
		die("Error forking...\n");
	elseif($pid == 0){
		execute_code($i, $var);
		exit();
	}
}

while(pcntl_waitpid(0, $status) != -1);
echo "In fine $var";

function execute_code($msg, $var)
{
	echo "[$msg]: Leggo variabile $var\n";
	echo "[$msg]: Scrivo\n";
	$var = $msg;
	echo "[$msg]: Leggo dopo la scrittura $var\n";
}

/*
$link = new mysqli("127.0.0.1", "root", "", "bidoo");
  if (mysqli_connect_errno()) 
  {
    printf("linkect failed: %s\n", mysqli_connect_error());
    return;
  }
$link->query("INSERT INTO test (name, t) VALUES ('gio', '23:3:3')");
*/
?>