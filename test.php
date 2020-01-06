<?php
$d1 = date_create("20:49:00"); //Dal DB
$d2 = date_create("00:02:00");
date_add($d1, date_interval_create_from_date_string("2 minutes"));

//$d1 > $current
$current = date("H:i:s");
echo $d1->format("H:i:s") . " " . $current;
if(strtotime($d1->format("H:i:s")) > strtotime($current))
	echo "EVIDENZIATA";
else
	echo "NON EVIDENZIATA";
	
?>