<?php
$t1 = microtime(true);
$ctx = stream_context_create(array('http'=>array('timeout' => 1,)));
for ($i=0; $i < 500; $i++) { 
	$s = file_get_contents("https://it.bidoo.com/data.php?ALL=885176$i&LISTID=0", false, $ctx);
	echo $s;
	echo "\n";
}
$t2 = microtime(true);

echo ($t2-$t1). "\n";
?>