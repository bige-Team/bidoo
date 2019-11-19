<?php
include_once "mysql_utils.php";
$name = explode('=', $_SERVER['QUERY_STRING'])[1];
echo "<a href='https://it.bidoo.com/auction.php?a=$name'>$name</a><br>";
create_html_table($name);
?>