<?
header("Content-type: image/png");
require('functions.php');
$username = $_GET['username'];
$file = getImgUrl($username);
readfile($file);
exit(0);
?>
