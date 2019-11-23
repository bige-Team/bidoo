<?php
$opts = array('http'=>array('timeout'=>1,));
$ctx = stream_context_create($opts);
for ($i=1; $i < 1000; $i++) 
{ 
	$s = file_get_contents("https://it.bidoo.com/data.php?ALL=$i&LISTID=0",false, $ctx);
	//$s = fopen("https://it.bidoo.com/data.php?ALL=15&LISTID=0", 'r', false, $ctx);
	if(FALSE === $s)
		;
	else
		echo $i . " " . date("H:i:s") . "\n"; 
}
?>