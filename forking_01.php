<?php
$link = new mysqli("127.0.0.1", "root", "", "bidoo");
  if (mysqli_connect_errno()) 
  {
    printf("linkect failed: %s\n", mysqli_connect_error());
    return;
  }
$link->query("INSERT INTO test (name, t) VALUES ('gio', '23:3:3')");
?>