<?php
include_once "mysql_utils.php";
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
		<!--<input type="text" name="tableName" placeholder="Database Name">
		<button name="btnOK">OK</button><br>-->
		<?php
		$names = get_table_names();
		foreach ($names as $key => $value)
		{
			echo "<a href='adviser.php?name=$value' target='blank'>$value</a><br>";
		}
		?>
	</form>
</body>
</html>