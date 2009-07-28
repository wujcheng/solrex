<?
require('functions.php');
echo date("Y-m-d, H:m:s");
echo '<br>';
if ($imgdir = @opendir('dc_img/')) {
  while ($file = readdir($imgdir)) {
    if ($file == '.' || $file == '..'){
      continue;
    }
    $basename = basename($file, '.png');
    echo 'Updating '.$basename.'<br>';
    updateCounter($basename);
  }
}
echo date('Y-m-d, H:m:s');
?>
