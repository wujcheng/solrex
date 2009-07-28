<?php
$string = "NaN";
$im     = imagecreatefrompng("templates/douyou.png");
$fcolor = imagecolorallocate($im, 0x42, 0x42, 0x42);
$px     = 43 - 6 * strlen($string);
imagestring($im, 2, $px, 2, $string, $fcolor);
imagepng($im, "test.png");
imagedestroy($im);
?>
