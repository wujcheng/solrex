<?php
$username = "username";  // Feedburner account name.
$expire_time = 3600;     // Expire time(in second, 3600s = 1 hour).

$fb_url = "feeds.feedburner.com";
$gif_path = "/~fc/".$username."?bg=99CCFF&fg=444444&anim=0";
$localfile = "fb_".$username.".gif";

if(!function_exists('httpSocketConnection')){
  function httpSocketConnection($host, $method, $path, $data)
  {
    $method = strtoupper($method);
    if ($method == "GET") {
      $path.= '?'.$data;
    }
    $filePointer = fsockopen($host, 80, $errorNumber, $errorString);
    if (!$filePointer) {
      return false;
    }
    $requestHeader = $method." ".$path."  HTTP/1.1\r\n";
    $requestHeader.= "Host: ".$host."\r\n";
    $requestHeader.= "User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1) Gecko/20061010 Firefox/2.0\r\n";
    $requestHeader.= "Content-Type: application/x-www-form-urlencoded\r\n";
    if ($method == "POST") {
      $requestHeader.= "Content-Length: ".strlen($data)."\r\n";
    }
    $requestHeader.= "Connection: close\r\n\r\n";
    if ($method == "POST") {
      $requestHeader.= $data;
    }           
    fwrite($filePointer, $requestHeader);
    $responseHeader = '';
    $responseContent = '';
    do {
      $responseHeader.= fread($filePointer, 1);
    }
    while (!preg_match('/\\r\\n\\r\\n$/', $responseHeader));
    if (!strstr($responseHeader, "Transfer-Encoding: chunked")) {
      while (!feof($filePointer)) {
        $responseContent.= fgets($filePointer, 128);
      }
    } else {
      while ($chunk_length = hexdec(fgets($filePointer))) {
        $responseContentChunk = '';
        $read_length = 0;
        while ($read_length < $chunk_length) {
          $responseContentChunk .= fread($filePointer, $chunk_length - $read_length);
          $read_length = strlen($responseContentChunk);
        }
        $responseContent.= $responseContentChunk;
        fgets($filePointer);
      }
    }
    return chop($responseContent);
  }
}

function get_fbcount($host, $path, $file)
{
  $content = httpSocketConnection($host, 'GET', $path, NULL);
  $fp = fopen( $file,"w" );
  fwrite($fp, $content);
  fclose($fp);
}

if (file_exists($localfile)) {
  $last_modified = filemtime($localfile);
  if ( date('U') - $last_modified > $expire_time) {
    get_fbcount($fb_url, $gif_path, $localfile);
  }
} else {
  get_fbcount($fb_url, $gif_path, $localfile);
}
Header("Location: $localfile");
?>
