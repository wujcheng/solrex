<?
function getCounter($username) {
  require_once('Snoopy.class.php');
  $url   = 'http://api.douban.com/people/'.$username.'/friends?max-results=0';
  $snoop = new Snoopy;
  $snoop->agent = 'Douyou Counter http://solrex.cn/douyou/';
  $snoop->fetch($url);

  if(strpos($snoop->response_code, '200')){
    if (eregi('totalResults>([0-9]+)<', $snoop->results, $args)) {
      return $args[1];
    } else {
      return 'NaN';
    }
  } else {
    return 'Err';
  }
}

function updateImg($username, $dc_str) {
  $im     = imagecreatefrompng("templates/douyou.png");
  $fcolor = imagecolorallocate($im, 0x42, 0x42, 0x42);
  $px     = 43 - 6 * strlen($dc_str);
  imagestring($im, 2, $px, 2, $dc_str, $fcolor);
  imagepng($im, "dc_img/".$username.".png");
  imagedestroy($im);
}

function updateCounter($username) {
  $counter  = getCounter($username);
  if ($counter == 'NaN' || $counter == 'Err') {
    return $counter;
  }
  updateImg($username, $counter);
  return TRUE;
}

function getImgUrl($username) {
  $username = strtolower($username);
  $file = 'dc_img/'.$username.'.png';
  if (!file_exists($file)) {
    $ret = updateCounter($username);
    if ($ret == 'NaN' || $ret == 'Err') {
      return 'templates/'.$ret.'.png';
    }
  } else {
    $last_modified = filemtime($file);
    if ( date('U') - $last_modified > 86400) {
      updateCounter($username);
    }
  }
  return $file;
}
?>
