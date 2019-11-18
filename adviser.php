<?php
$name = explode('=', $_SERVER['QUERY_STRING'])[1];
echo "$name";
?>