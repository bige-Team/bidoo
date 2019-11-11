<?php
$l = new mysqli("127.0.0.1", "root", "", "bidoo_stats");
$l->query("INSERT INTO auction_tracking (name, analized) VALUES ('carlino', 2)
		   ");
$l->close();
?>