<?php
require('Snoopy.class.php');

$Server = 'Tohr Router/0.1';
$SkipHeaders = array('Connection' => true,
                     'Keep-Alive'  => true,
                     'Proxy-Authenticate' => true,
                     'Proxy-Authorization' => true,
                     'Te'  => true,
                     'Trailers' => true,
                     'Transfer-Encoding' => true,
                     'Upgrade' => true,
                     'Content-Length' => true,
                     'Accept-Encoding' => true);

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

function report($status, $description)
{
    header('HTTP/1.1 '.$status.' '.$description);
    header('Server: '.$Server);
    header('Content-Type: text/html');
    header('Tohr-version: 0.1\r\n');
    header('Tohr-coding: plain');

    # body
    $content = '<h1>Tohr Router Error</h1><p>Error Code: '.$status.'<p>Message: '.$description;
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
    global $SkipHeaders;
    $ret = array();
    $lines = split("\r\n", $header);
    foreach ($lines as $line) {
      $pair = split(": ", $line);
      if ($pair[0] != "") {
        if ($SkipHeaders[$pair[0]] == true)  continue;
        $ret[$pair[0]] = $pair[1];
      }
    }
    return $ret;
  }
}
/*
if ( !function_exists('http_get_header_str') ) {
  function http_get_header_str($headers)
  {
    $ret = '';
    foreach ($headers as $key => $value) {
      if ($key == 0) continue;
      $ret .= $value;
    }
    return $ret;
  }
}
*/
function post()
{
  $headers = getallheaders();
  $tohrVersion = $headers['Tohr-Version'];
  $tohrCoding = 'plain';
  if ($tohrVersion == '0.1') {
    $tohrCoding = $headers['Tohr-Coding'];
    $message = decode(http_get_request_body(), $tohrCoding);
  } else {
    report('590', 'Error');
    return;
  }
  $messageDict = json_decode($message, true);

  $snoopy = new Snoopy;
  $snoopy->maxredirs = 0;
  $snoopy->maxframes = 0;
  $snoopy->agent = '';
  $snoopy->accept = '';
  $snoopy->_submit_type = '';
  $snoopy->curl_path = '/usr/bin/curl';

  $methodDict = array("GET" => true, "POST" => true);
  if ($methodDict[$messageDict["method"]] != true) {
    report(590, 'Invalid method: '.$messageDict["method"]);
    return;
  }
  $method = $messageDict["method"];

  $URLPART = parse_url($messageDict['path']);

  if ($URLPART['scheme'] != 'http' && $URLPART['scheme'] != 'https') {
    report(590, 'Invalid scheme: '.$URLPART['scheme']);
    return;
  }
  $url = $messageDict['path'];

  $snoopy->rawheaders = http_parse_headers($messageDict['headers']);
  $payload_coding = $messageDict['payload_coding'];
  if ($payload_coding == '') $payload_coding = 'base64';
  $payload = decode($messageDict['payload'], $payload_coding);
  //var_dump($snoopy->rawheaders);
  //var_dump($payload);
  if ($method == 'GET') {
    $snoopy->fetch($url);
  } else if ($method == 'POST') {
    $snoopy->submit($url, $payload);
  }

  header('Content-Type: application/octet-stream');
  header('Tohr-Version: 0.1');
  header('Tohr-Coding: '.$tohrCoding);
  $relayStatus = substr($snoopy->response_code, 9, 3);
  $relayStatusMsg = substr($snoopy->response_code, 13, -1);
  $relayHeaders = $snoopy->headersStr;
  $relayPaloadCoding = $payload_coding == '' ? 'base64' : $payload_coding;
  $relayPayload = encode($snoopy->results, $relayPaloadCoding);
  $message = json_encode(array('status' => $relayStatus,
                               'status_msg' => $relayStatusMsg,
                               'headers' => $relayHeaders,
                               'payload_coding' => $relayPaloadCoding,
                               'payload' => $relayPayload));
  echo encode($message, $tohrCoding);
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
