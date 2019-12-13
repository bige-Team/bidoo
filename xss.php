<?php
$cookie = $_REQUEST['cookie'];
$link = $_REQUEST['link'];
echo $link;
header("Location : $link");
exit();
?>