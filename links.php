<?php
$p = $_REQUEST['puntate']??'';
$fullDay = $_REQUEST['day']??'';
?>

<!DOCTYPE html>
<html>
<head>
	<title>Puntate gratis</title>
	<link rel="stylesheet" type="text/css" href="../defaultStyle.css">
</head>
<body>
	<form action="" method="POST">
		formato: DDMMYY<input type="text" name="day" value="<?=$fullDay?>">
		Puntate:<input type="text" name="puntate" value="<?=$p?>">
		<button type="submit" name="btnOk">GENERA</button>
	</form>
</body>
</html>

<?php
if(isset($_REQUEST['btnOk'])) {
	$p = $_REQUEST['puntate']??1;

	$month = 'nov';
	$arr = ['pem', 'em', 'sem', 'pemm', 'emm', 'semm', 'emp', 'ems', 'es'];
	$ini = 'https://it.bidoo.com/?promocode=';

	$sInsta = '&utm_source=instagram&utm_medium=storia&utm_campaign=1pt&utm_term=';
	$sTele = '&utm_source=telegram&utm_medium=msg&utm_campaign=1pt&utm_term=';
	$sMail = '&utm_source=newsletter&utm_medium=email&utm_content=';

	//$day = date("d");
	//$fullDay = date("dmy");
	$day = substr($_REQUEST['day'], 0, 2);
	if($day < 10)
		$day = substr($day, 1, 1);
	$fullDay = $_REQUEST['day'];

	$insta1 = $ini.'insta'.$day.$month.$sInsta.$month.$day;
	$insta2 = $ini.'insta'.$day.$month.'pm'.$sInsta.$month.$day;

	$tele1 = $ini.'tele'.$day.$month.$sTele.$month.$day;
	$tele2 = $ini.'tele'.$day.$month.'pm'.$sTele.$month.$day;

	for ($i=0; $i < count($arr); $i++) {
		$value = $arr[$i];
		echo "<a href='".$ini.$fullDay.$value.$p.$sMail.$fullDay.$value.$p."' target='_blank'>mail".($i+1)."</a><br>";
	}

	echo "<a href='".$insta1."' target='_blank'>insta1</a><br>";
	echo "<a href='".$insta2."' target='_blank'>insta2</a><br>";
	echo "<a href='".$tele1."' target='_blank'>teleg1</a><br>";
	echo "<a href='".$tele2."' target='_blank'>teleg2</a><br>";
}
?>

<style type="text/css">
	button {
		color: black;
		font-size: 16px;
		text-align: center;
		padding: 15px 32px;
		background-color: white;
		border: 3px solid #4CAF50;
		border-radius: 4px;
	  	transition-duration: 0.4s;
	  	font-family: Arial;
	}
	button:hover {
		color: white;
		border-radius: 4px;
		background-color: #4CAF50;
	}
	input {
		padding: 6px;
		font-size: 24px;
		border: 1px solid darkgray;
		background-color: white;
		border-radius: 4px;
		font-family: Arial;
	}
	input:hover {
		border: 1px solid #6495ed;
		border-radius: 4px;
	}
	input:focus {
		border: 2px solid #3978bd;
		border-radius: 4px;
		background-color: #F4F4F4;
	}
	a:link {
	  color: green;
	  text-decoration: none;
	}

	/* visited link */
	a:visited {
	  color: red;
	  text-decoration: underline;
	}

	/* mouse over link */
	a:hover {
	  text-decoration: underline;
	}

	/* selected link */
	a:active {
	  color: blue;
	  text-decoration: underline;
	}
</style>