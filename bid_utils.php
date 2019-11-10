<?php
function array_byte_size($arr)
{
	$tot = 0;
	foreach ($arr as $key => $value) 
	{
		$tot += strlen($value)*8;
	}
	return $tot;
}
?>