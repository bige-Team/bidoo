<?php
include_once "utils.php";
if(isset($_REQUEST['btnOK']))
{
	$name = $_REQUEST['tableName'];
	create_html_table($name);
}
?>

<!DOCTYPE html>
<html>
<head>
	<title>HTML TABLE</title>
</head>
<body>
	<form>
		<input type="text" name="tableName" placeholder="Database Name">
		<button name="btnOK">OK</button>
	</form>
</body>
</html>