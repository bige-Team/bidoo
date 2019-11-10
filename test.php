<?php
$lock = false;
/*
$t1 = microtime(true);
$s1 = file_get_contents("https://it.bidoo.com/auction.php?a=Buono_Carrefour_10_e_20P_8723399");
$s2 = file_get_contents("https://it.bidoo.com/auction.php?a=Buono_Amazon_70_e_140P_8721960");
$s3 = file_get_contents("https://it.bidoo.com/auction.php?a=Buono_Amazon_30_e_75P_8722401");
$s4 = file_get_contents("https://it.bidoo.com/auction.php?a=Buono_Amazon_5_e_10P_8716283");
$s5 = file_get_contents("https://it.bidoo.com/auction.php?a=Buono_Carburante_10_e_24P_8723177");
echo "Done in " . (microtime(true)-$t1)*1000;
*/
echo "Before all: " . $lock . "\n";
testAndLock($lock);

//Sezione critica
echo "Before setting lock:  ".  $lock . "\n";
$lock = false;
echo "After setting lock:  ".  $lock . "\n";
echo $lock . "\n";
sleep(1);



function testAndLock(&$bool)
{
	echo "In the function: " . $bool . "\n";
	if($bool)
		return false;
	else
		$bool = true;
	return true;
}
?>