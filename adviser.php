<?php
include_once "mysql_utils.php";
$name = explode('=', $_SERVER['QUERY_STRING'])[1];
echo "$name";
create_html_table();
?>