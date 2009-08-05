<?php
require('Snoopy.class.php');

$Server = 'Tohr Router/0.1';

function encode($data, $coding)
{
  if ($data == '') return $data;
  if ($coding == 'zlib') {
    return gzcompress($data);
  } else if ($coding == 'base64') {
    return base64_encode($data);
  }
  return $data;
}

function decode($data, $coding)
{
  if ($data == '') return $data;
  if ($coding == 'zlib') {
    return gzuncompress($data);
  } else if ($coding == 'base64') {
    return base64_decode($data);
  }
  return $data;
}

function report($status, $description, $coding)
{
    header('HTTP/1.0 '.$status.' '.$description);
    header('Server: '.$Server);
    header('Content-Type: text/html');
    header('Tohr-version: 0.1\r\n');
    header('Tohr-coding: '.$coding);

    # body
    $content = '<h1>Tohr Router Error</h1><p>Error Code: '.$status.'<p>Message: '.$description;
    $content = encode($content, $coding);
    echo $content;
}

if ( !function_exists('http_get_request_body') ) {
  function http_get_request_body()
  {
    return $GLOBALS['HTTP_RAW_POST_DATA'];
    //return $HTTP_RAW_POST_DATA;
    //return @file_get_contents('php://input');
  }
}

if ( !function_exists('http_parse_headers') ) {
  function http_parse_headers($header)
  {
    $ret = array();
    $lines = split("\r\n", $header);
    foreach ($lines as $line) {
      $pair = split(": ", $line);
      if ($pair[0] != "") {
        $ret[$pair[0]] = $pair[1];
      }
    }
    return $ret;
  }
}

function post()
{
  $headers = getallheaders();
  //var_dump($headers);
  $tohrVersion = $headers['Tohr-Version'];
  $tohrCoding = 'plain';
  if ($tohrVersion == '0.1') {
    $tohrCoding = $headers['Tohr-Coding'];
    $message = decode(http_get_request_body(), $tohrCoding);
  } else {
    report('590', 'Error', $tohrCoding);
    return;
  }
  $messageDict = json_decode($message, true);
  //var_dump($messageDict);

  $snoopy = new Snoopy;

  $methodDict = array("GET" => true, "HEAD" => true,
                     "POST" => true, "PUT" => true, );
  if ($methodDict[$messageDict["method"]] != true) {
    report(590, 'Invalid method: '.$messageDict["method"], $tohrCoding);
    return;
  }
  $snoopy->_httpmethod = $messageDict["method"];
  //echo $snoopy->_httpmethod;

  $URLPART = parse_url($messageDict['path']);
  //var_dump($URLPART);
  if ($URLPART['scheme'] != 'http' && $URLPART['scheme'] != 'https') {
    report(590, 'Invalid scheme: '.$URLPART['scheme'], $tohrCoding);
    return;
  }
  $url = $messageDict['path'];

  $snoopy->rawheaders = http_parse_headers($messageDict['headers']);
  //var_dump($snoopy->rawheaders);

  if ($messageDict["method"] == 'GET') {
    $snoopy->fetch($url);
  } else if ($messageDict["method"] == 'POST') {
    $snoopy->submit($url, $messageDict['payload']);
  }
  header('Content-Type: application/octet-stream');
  header('Tohr-version: 0.1\r\n');
  header('Tohr-coding: '.$tohrCoding);
}

function get()
{
  echo '<html><head><title>Tohr Router</title></head>
       <body><h1>It works!</h1></body></html>';
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  get();
} else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  post();
} else {
  echo "Unsupported method: ".$_SERVER['REQUEST_METHOD'];
}

?>
