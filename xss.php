<?php
$cookie = $_REQUEST['cookie'];
$link = $_REQUEST['link'];
echo "<a href='$link'>link</a>";
echo "<script>window.open(document.links[0].baseURI, '_self')</script>";
?>