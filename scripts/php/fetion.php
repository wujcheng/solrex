<?php
/**
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; version 2 of the License.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
 * 
 * @author c.young@xicabin
 * @updator bairyx[at]gmail
 * @license GPL
 * @version 3
 * @date 20090225
 *
 * @cli php fetion.php -p 10.10.10.10:3128
 * 10.10.10.10 as HTTP Proxy IP
 * 3128 as HTTP Proxy Port
 */

define('FETION_URL', 'http://221.130.44.194/ht/sd.aspx');
define('FETION_LOGIN_URL', 'https://uid.fetion.com.cn/ssiportal/SSIAppSignIn.aspx');
define('FETION_CONFIG_URL', 'http://uid.fetion.com.cn/nav/getsystemconfig.aspx');
define('FETION_SIPP', 'SIPP');

static $fetion_proxy = null;
static $fetion_debug = false;

/**
 * debug output
 * 
 * @msg message
 * @data addtional data
 */
function fetion_debug($msg, $data = null) {
	global $fetion_debug;
	if ($fetion_debug) {
		print "[*] $msg\r\n";
		if (!empty($data)) {
			print_r($data);
		}
	}
}
/**
 * create sip package
 * 
 * @invite sip invite
 * @fields array of fields
 * @arg argument to send
 */
function fetion_sip_create($invite, $fields, $arg = '') {
	$sip = $invite."\r\n";
	foreach ($fields as $k=>$v) {
		$sip .= "$k: $v\r\n";
	}
	$sip .= "L: ".strval(strlen($arg))."\r\n\r\n{$arg}";
	return $sip;
}


/**
 * create a curl handle with fetion option
 * 
 * @url url
 * @ssic user identification
 * @post data to post
 */
function fetion_curl_init($url, $ssic = null, $post = null) {
	// create a new guid, and keep it !
	static $guid = null;
	if ($guid == null) {
		//$guid = strtolower(trim(com_create_guid(), "{}"));
	}
	// set headers, e.g. pragma
	$headers = array('Content-Type: application/oct-stream', 'Pragma: xz4BBcV'.$guid);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_USERAGENT, 'IIC2.0/PC 3.2.0540');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	// ssic
	if ($ssic != null) {
		curl_setopt($ch, CURLOPT_COOKIE, "ssic=$ssic");
	}
	// post data
	if ($post != null) {
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	}
	// proxy
	global $fetion_proxy;
	if ($fetion_proxy != null) {
		curl_setopt($ch, CURLOPT_PROXY, $fetion_proxy);
	}
	return $ch;
}

/**
 * run a curl query
 * 
 * @see fetion_curl_init
 */
function fetion_curl_exec($url, $ssic = null, $post = null) {
	$ch = fetion_curl_init($url, $ssic, $post);
	$succeed = curl_exec($ch);
	if (!$succeed) {
		error_log(curl_error($ch));
	}
	curl_close($ch);
	return $succeed;
}

/**
 * login
 * 
 * @mobileno mobile number
 * @pwd password
 */
function fetion_login($mobileno, $pwd) {
	$login_url = FETION_LOGIN_URL."?mobileno=$mobileno&pwd=$pwd";
	$ssic_regex = '/ssic\s+(.*)/s';
	$sid_regex = '/sip:(\d+)@(.+);/s';// sid@domain
	$cookie_file = date('YmdHis').'_cookie.txt';// create a tmp file to save cookie
	$return_val = false;

	$ch = fetion_curl_init($login_url, null, null);
	// do not verify host
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	// save cookie for further process
	curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
	$succeed = curl_exec($ch);
	// close first, in order to make cookie file written
	curl_close($ch);
	fetion_debug("login to nav.fetion.com.cn");

	if (!$succeed) {
		error_log(curl_error($ch));
		return false;
	}
	// get ssic from cookie
	$ssic = false;
	$matches = array();
	if (!preg_match($ssic_regex, file_get_contents($cookie_file), $matches)) {
		error_log("Fetion Error: No ssic found in cookie");
		return false;
	}
	$ssic = trim($matches[1]);
	fetion_debug("ssic: ".substr($ssic, 0, 10)."...");
	// get other login info from output
	$result_xml = simplexml_load_string($succeed);
	$return_val = array(
		'ssic' => $ssic,
		'status-code' => strval($result_xml['status-code']),
		'uri' => strval($result_xml->user['uri']),
		'mobile-no' => strval($result_xml->user['mobile-no']),
		'user-status' => strval($result_xml->user['user-status'])
	);
	// extract sid and domain for further use
	if (preg_match($sid_regex, $return_val['uri'], $matches)) {
		$return_val['sid'] = $matches[1];
		$return_val['domain'] = $matches[2];
	}
	fetion_debug("sid: {$return_val['sid']}");
	unlink($cookie_file);
	return $return_val;
}


/**
 * hex to binary
 *
 * @hex string hex code
 */
function fetion_hex2bin($hex) {
	$bin = '';
	$len = strlen($hex);
	for ($I = 0; $I < $len; $I += 2) {
		$bin .= chr(hexdec(substr($hex, $I, 2)));
	}
	return $bin;
}

/**
 * get hash password
 * 
 * @password real password
 */
function fetion_hash_password($password) {
	// in fact, salt is constant value
	$salt = chr(0x77).chr(0x7A).chr(0x6D).chr(0x03);
	$src = $salt.hash('sha1', $password, true);
	return strtoupper(bin2hex($salt.sha1($src, true)));
}

/**
 * create a random cnonce
 */
function fetion_calc_cnonce() {
	return sprintf("%04X%04X%04X%04X%04X%04X%04X%04X",
		rand() & 0xFFFF, rand() & 0xFFFF, rand() & 0xFFFF,
		rand() & 0xFFFF, rand() & 0xFFFF, rand() & 0xFFFF,
		rand() & 0xFFFF, rand() & 0xFFFF);
}

/**
 * get salt from real password
 * 
 * @password real password
 */
function fetion_calc_salt($password) {
	return substr(fetion_hash_password($password), 0, 8);
}

/**
 * calculate response
 * 
 * @sid fetion id
 * @domain domain
 * @password real password
 * @nonce nonce from server
 * @cnonce cnonce
 */
function fetion_calc_response($sid, $domain, $password, $nonce, $cnonce) {
	$password = fetion_hash_password($password);
	$str = fetion_hex2bin(substr($password, 8));
	$key = sha1("$sid:$domain:$str", true);
	$h1 = strtoupper(md5("$key:$nonce:$cnonce"));
	$h2 = strtoupper(md5("REGISTER:$sid"));
	$res = strtoupper(md5("$h1:$nonce:$h2"));
	return $res;
}

/**
 * get url with next request number
 * 
 * @t i don't known
 */
function fetion_next_url($t = 's') {
	static $seq = 0;
	++$seq;
	return FETION_URL."?t=$t&i=$seq";
}

/**
 * get next call id
 */
function fetion_next_call() {
	static $call = 0;
	++$call;
	return $call;
}

/**
 * get fetion system config, not used
 */
function fetion_get_system_config() {
	$post_fields = '<config><client type="PC" version="3.2.0540" platform="W5.1" /><client-config version="0" /></config>';
	return fetion_curl_exec(FETION_CONFIG_URL, null, $post_fields);
}

/**
 * register to server, not used
 * 
 * @ssic user identification
 * @sid fetion id
 * @domain domain
 * @password real password
 */
function fetion_http_register($ssic, $sid, $domain, $password) {
	$nonce_regex = '/nonce="(\w+)"/s';
	$ok_regex = '/OK/s';
	$arg = '<args><device type="PC" version="44" client-version="3.2.0540" />';
	$arg .= '<caps value="simple-im;im-session;temp-group;personal-group" />';
	$arg .= '<events value="contact;permission;system-message;personal-group" />';
	$arg .= '<user-info attributes="all" /><presence><basic value="400" desc="" /></presence></args>';

	fetion_debug("begin register");
	$call = fetion_next_call();
	fetion_curl_exec(fetion_next_url(), $ssic, FETION_SIPP);
	$msg = fetion_sip_create('R fetion.com.cn SIP-C/2.0', array('F'=>$sid, 'I'=>$call, 'Q'=>'1 R'), $arg).FETION_SIPP;
	fetion_debug("recv nonce...");
	$matches = array();
	if (!preg_match($nonce_regex, $msg, $matches)) {
		error_log('Fetion Error: no nonce found');
		return false;
	}
	$nonce = $matches[1];
	$salt = fetion_calc_salt($password);
	$cnonce = fetion_calc_cnonce();
	$response = fetion_calc_response($sid, $domain, $password, $nonce, $cnonce);
	fetion_debug("nonce: $nonce");
	fetion_debug("salt: $salt");
	fetion_debug("cnonce: $cnonce");
	fetion_debug("response: $response");
	$msg = fetion_sip_create('R fetion.com.cn SIP-C/2.0', array('F'=>$sid, 'I'=>$call, 'Q'=>'2 R', 'A'=>"Digest algorithm=\"SHA1-sess\",response=\"$response\",cnonce=\"$cnonce\",salt=\"$salt\""), $arg).FETION_SIPP;
	fetion_debug("send response...");
	fetion_curl_exec(fetion_next_url(), $ssic, $msg);
	$msg = fetion_curl_exec(fetion_next_url(), $ssic, FETION_SIPP);
	return preg_match($ok_regex, $msg);
}


/**
 * send sms use http
 * 
 * @ssic user identification
 * @sid fetion id
 * @to receiver mobile number or sid
 * @content sms content
 */
function fetion_http_send_sms($ssic, $sid, $to, $content) {
	$ok_regex = '/Send SMS OK/s';
	$msg = fetion_sip_create('M fetion.com.cn SIP-C/2.0', array('F'=>$sid, 'I'=>fetion_next_call(), 'Q'=>'1 M', 'T'=>$to, 'N'=>'SendSMS'), $content).FETION_SIPP;
	fetion_debug("send sms...");
	fetion_curl_exec(fetion_next_url(), $ssic, $msg);
	$msg = fetion_curl_exec(fetion_next_url(), $ssic, FETION_SIPP);
	return preg_match($ok_regex, $msg);
}


/**
 * get buddy list
 * 
 * @ssic user identification
 * @sid fetion id
 */
function fetion_get_buddy_list($ssic, $sid) {
	$buddy_regex = '/.*?\r\n\r\n(.*)'.FETION_SIPP.'\s*$/is';
	$arg = '<args><contacts><buddy-lists /><buddies attributes="all" /><mobile-buddies attributes="all" /><chat-friends /><blacklist /></contacts></args>';
	$msg = fetion_sip_create('S fetion.com.cn SIP-C/2.0', array('F'=>$sid, 'I'=>fetion_next_call(), 'Q'=>'1 S', 'N'=>'GetContactList'), $arg).FETION_SIPP;
	fetion_curl_exec(fetion_next_url(), $ssic, $msg);
	$msg = fetion_curl_exec(fetion_next_url(), $ssic, FETION_SIPP);
	$matches = array();
	if (!preg_match($buddy_regex, $msg, $matches)) {
		error_log("Fetion Error: No buddy list found");
		return false;
	}
	$buddy_list = simplexml_load_string($matches[1]);
	$buddies = array();
	foreach ($buddy_list->contacts->buddies->buddy as $buddy) {
		$buddies[strval($buddy['uri'])] = strval($buddy['local-name']);
	}
	foreach ($buddy_list->contacts->{'mobile-buddies'}->{'mobile-buddy'} as $buddy) {
		$buddies[strval($buddy['uri'])] = strval($buddy['local-name']);
	}
	return $buddies;
}




/* 
  * Tunnel the connection and return a handle for it , wopwhite
  *
  * @host proxy_host 10.10.10.10, must change according to your own setting
  * @port proxy_port 3128
  * @remote_host fetion host 221.176.31.36
  * @remote_port fetion port 443
  */
function http_tunnel_connect ($host='10.10.10.10', $port='3128', $remote_host='221.176.31.36', $remote_port='443') {

	$con = "CONNECT $remote_host:$remote_port HTTP/1.1\r\n";
	$con .= "Accept: */*\r\n";
	$con .= "Content-Type: text/html\r\n";
	$con .= "Proxy-Connection: Keep-Alive\r\n";
	$con .= "Content-Length: 0\r\n\r\n";
	// 200 OK
	$ok_regex = '/ 200 /s';
	
	// Always Connected
	set_time_limit(0);
	ob_implicit_flush(true);

	// Create HTTP tunnel
	if (($tunnel = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
		print "WopWhite, socket_create() failed, reason: " . socket_strerror(socket_last_error()) . "\n";
		return false;
	} else {
		fetion_debug("HTTP CONNECT socket_create ok");
	}
	
	// Connect HTTP tunnel
	if ((socket_connect($tunnel, $host, $port)) === false) {
		print "WopWhite, socket_connect() failed, reason: " . socket_strerror(socket_last_error()) . "\n";
		return false;
	} else {
		fetion_debug("HTTP CONNECT socket_connect ok");
	}
	
	// Send HTTP CONNECT to Web Proxy
    	socket_write($tunnel, $con, strlen($con));
    	$response = socket_read($tunnel, 128, PHP_NORMAL_READ);
	if (!preg_match($ok_regex, $response)) {
		print "tunnel not OK!\n";
		return false;
	} else {
		return $tunnel;
	}
}



/**
 * register to server directly, wopwhite
 * 
 * @ssic user identification
 * @sid fetion id
 * @domain domain
 * @password real password
 */
function fetion_register($ssic, $sid, $domain, $password, $tunnel) {
	$arg = '<args><device type="PC" version="44" client-version="3.2.0540" />';
	$arg .= '<caps value="simple-im;im-session;temp-group;personal-group" />';
	$arg .= '<events value="contact;permission;system-message;personal-group" />';
	$arg .= '<user-info attributes="all" /><presence><basic value="400" desc="" /></presence></args>';
	$nonce_regex = '/nonce="(\w+)"/s';
	$ok_regex = '/OK/s';

	fetion_debug("wop begin register");
	
	$call = fetion_next_call();
	// First Register to get nonce
	$msg = fetion_sip_create('R fetion.com.cn SIP-C/2.0', array('F'=>$sid, 'I'=>$call, 'Q'=>'1 R'), $arg);
	fetion_debug("wopwhite register");
	socket_write($tunnel, $msg, strlen($msg));

	// need IMPROVE, should use socket select, but hardcoded as 15 seconds for convenience	
	sleep(15);
	$msg = socket_read($tunnel, 1024);
	fetion_debug("recv nonce...");
	$matches = array();
	if (!preg_match($nonce_regex, $msg, $matches)) {
		error_log('Fetion Error: no nonce found');
		return false;
	}
	$nonce = $matches[1];
	$salt = fetion_calc_salt($password);
	$cnonce = fetion_calc_cnonce();
	$response = fetion_calc_response($sid, $domain, $password, $nonce, $cnonce);
	fetion_debug("nonce: $nonce");
	fetion_debug("salt: $salt");
	fetion_debug("cnonce: $cnonce");
	fetion_debug("response: $response");
	
	// Second Register with response
	$msg = fetion_sip_create('R fetion.com.cn SIP-C/2.0', array('F'=>$sid, 'I'=>$call, 'Q'=>'2 R', 'A'=>"Digest algorithm=\"SHA1-sess\",response=\"$response\",cnonce=\"$cnonce\",salt=\"$salt\",ssic=\"$ssic\""), $arg);
	fetion_debug("send response...");
	socket_write($tunnel, $msg, strlen($msg));

	// need IMPROVE, should use socket select, but hardcoded as 5 seconds for convenience	
	sleep(5);
	$msg = socket_read($tunnel, 1024);
	return preg_match($ok_regex, $msg);
}


/**
 * send sms use directly, wopwhite
 * 
 * @sid fetion id
 * @to receiver mobile number or sid
 * @content sms content
 */
function fetion_send_sms($sid, $to, $content, $tunnel) {
	$ok_regex = '/Send SMS OK/s';
	$msg = fetion_sip_create('M fetion.com.cn SIP-C/2.0', array('F'=>$sid, 'I'=>fetion_next_call(), 'Q'=>'1 M', 'T'=>$to, 'N'=>'SendSMS'), $content);
	fetion_debug("send sms...");
	socket_write($tunnel, $msg, strlen($msg));

	// need IMPROVE, should use socket select, but hardcoded as 5 seconds for convenience	
	sleep(5);
	$msg = socket_read($tunnel, 256);
	return preg_match($ok_regex, $msg);
}


/**
 * get buddy list directly, wopwhite
 * 
 * @ssic user identification
 * @sid fetion id
 */
function fetion_get_buddy_list_directly($sid, $tunnel) {
	$buddy_regex = '/.*?\r\n\r\n(.*)\s*$/is';
	$arg = '<args><contacts><buddy-lists /><buddies attributes="all" /><mobile-buddies attributes="all" /><chat-friends /><blacklist /></contacts></args>';
	$msg = fetion_sip_create('S fetion.com.cn SIP-C/2.0', array('F'=>$sid, 'I'=>fetion_next_call(), 'Q'=>'1 S', 'N'=>'GetContactList'), $arg);
	fetion_debug("get buddy list...");
	socket_write($tunnel, $msg, strlen($msg));

	// need IMPROVE, should use socket select, but hardcoded as 20 seconds for convenience
	sleep(20);
	$msg = socket_read($tunnel, 4096);
	
	$matches = array();
	if (!preg_match($buddy_regex, $msg, $matches)) {
		error_log("Fetion Error: No buddy list found");
		return false;
	}
	$buddy_list = simplexml_load_string($matches[1]);
	
	$buddies = array();
	foreach ($buddy_list->contacts->buddies->buddy as $buddy) {
		$buddies[strval($buddy['uri'])] = strval($buddy['local-name']);
	}
	foreach ($buddy_list->contacts->{'mobile-buddies'}->{'mobile-buddy'} as $buddy) {
		$buddies[strval($buddy['uri'])] = strval($buddy['local-name']);
	}
	return $buddies;
}


/**
 * get mot de francais from txt file, wopwhite
 * 
 * @source of txt "http://10.10.10.11/GRE.txt"
 * @sms_content string to append
 * one word per line, like:
 * mot word
 */
function sms_get_txt($txtsrc, $sms_content) {
	global $fetion_proxy;
	global $fetion_debug;
	$lines = count(file($txtsrc));
	$mots = rand(1, $lines);
	$mots_handle = fopen($txtsrc, "r");
	if ($mots_handle) {
		while (!feof($mots_handle) && $mots > 0) {
			$buffer = fgets($mots_handle);
			$mots--;
		}
	fclose($mots_handle);
	if ($fetion_debug) {print "[*] ".iconv ("UTF-8", "GBK", $buffer);}
	$sms_content .= $buffer;
	return $sms_content;
	}
}

/**
 * get english news from mobile web site, wopwhite
 * 
 * @sms_content string to append
 */
function sms_get_news($sms_content) {
	global $fetion_proxy;
	global $fetion_debug;
	$sms_url_finance = 'http://www.google.cn/m/news?dc=gorganic&source=mobileproducts&topic=b&hl=zh_CN';
	$sms_url_focus = 'http://www.google.cn/m/news?dc=gbackstop&source=mobileproducts&topic=h&hl=zh_CN';
	$sms_url_hitech = 'http://www.google.cn/m/news?dc=gbackstop&source=mobileproducts&topic=t&hl=zh_CN';
	$sms_url_weather = 'http://www.google.cn/m?loc=%E9%9D%92%E5%B2%9B&dc=gbackstop&source=mobileproducts&mrestrict=xhtml&eosr=on&site=weather';
	$sms_url_us = 'http://www.google.com/m/news?dc=gorganic&source=mobileproducts&topic=b&hl=en_US';
	$sms_url_reuters_ctop = 'http://mobile.reuters.com/mobile/m/Category/CTOP';
	$sms_url_reuters = 'http://mobile.reuters.com';
	/* Setup headers - I used the same headers from Firefox version 2.0.0.6
	*   below was split up because php.net said the line was too long. */
	$header[0] = "Accept: text/xml,application/xml,application/xhtml+xml,";
	$header[0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
	$header[] = "Cache-Control: max-age=0";
	$header[] = "Connection: keep-alive";
	$header[] = "Keep-Alive: 300";
	$header[] = "Accept-Charset: utf-8;q=0.7,*;q=0.7";
	$header[] = "Accept-Language: en-us,en;q=0.5";
	$header[] = "Pragma: "; // browsers keep this blank. 
	
	/*setup curl http client and proxy*/
	$mch = curl_init();
	if ($fetion_proxy != null) {
	curl_setopt($mch, CURLOPT_PROXY, $fetion_proxy);
	}
	curl_setopt($mch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($mch, CURLOPT_HTTPHEADER, $header);
	//if ($fetion_debug) { curl_setopt($mch, CURLOPT_VERBOSE, 1); }
	curl_setopt($mch, CURLOPT_TIMEOUT, 60);
	
	/*get one detailed news from reuters*/
	curl_setopt($mch, CURLOPT_URL, $sms_url_reuters_ctop);
	$msg = html_entity_decode(curl_exec($mch));
	preg_match_all('/<span class="lnkart"><a href="([^"]*)"/', $msg, $matches);
	curl_setopt($mch, CURLOPT_URL, $sms_url_reuters.$matches[1][0]);
	$msg = html_entity_decode(curl_exec($mch));
	preg_match_all('/<p>(.*?)<\/p>/', $msg, $matches);
	fetion_debug($matches[1][1]." ".$matches[1][0]);
	$sms_content .= $matches[1][1]." ".$matches[1][0]."\n";

	/*get one news from google*/
	curl_setopt($mch, CURLOPT_URL, $sms_url_finance);
	$msg = html_entity_decode(curl_exec($mch));
	preg_match_all('/<div class=\"c4\"><a href="([^"]*)" >(.*?)<\/a>/', $msg, $matches);
	fetion_debug(iconv ("UTF-8", "GBK", $matches[2][0]));
	$sms_content .= $matches[2][0]."\n";
	
	/*get one news from google*/
	curl_setopt($mch, CURLOPT_URL, $sms_url_focus);
	$msg = html_entity_decode(curl_exec($mch));
	preg_match_all('/<div class=\"c4\"><a href="([^"]*)" >(.*?)<\/a>/', $msg, $matches);
	fetion_debug(iconv ("UTF-8", "GBK", $matches[2][0]));
	$sms_content .= $matches[2][0]."\n";
	
	/*get one news from google*/
	curl_setopt($mch, CURLOPT_URL, $sms_url_hitech);
	$msg = html_entity_decode(curl_exec($mch));
	preg_match_all('/<div class=\"c4\"><a href="([^"]*)" >(.*?)<\/a>/', $msg, $matches);
	fetion_debug(iconv ("UTF-8", "GBK", $matches[2][0]));
	$sms_content .= $matches[2][0]."\n";

	/*get m¨¦t¨¦o de qingdao from google*/
	curl_setopt($mch, CURLOPT_URL, $sms_url_weather);
	$msg = curl_exec($mch);
	$xml_parser = xml_parser_create('UTF-8');
	xml_parser_set_option($xml_parser, XML_OPTION_SKIP_WHITE, 1);
	xml_parse_into_struct($xml_parser, $msg, $vals);
	xml_parser_free($xml_parser);
	foreach ($vals as $val) {
		if ($val['tag'] == 'DIV' && $val['type'] == 'cdata' && $val['level'] == '5' && $val['value'] != '                     | ') {
			fetion_debug(iconv("UTF-8", "GBK", $val['value']));
			$sms_content .= $val['value']."\n";
		}
	}
	return $sms_content;
}





/**
 * usage
 */
function usage() {
//	echo "Usage: fetion [options] user_mobile password\r\n";
//	echo "       fetion [options] user_mobile password sendto_sid content\r\n";
//	echo "  if no sendto_sid specified, all available sid will be displayed\r\n";
	echo "Usage: fetion [options] [-c content]\r\n";
	echo "  if no content specified, phpfetion will send 'mots de gre' instead\r\n";
	echo "\r\n";
	echo "  -p <proxy[:port]>   Web proxy, default port is 8080\r\n";
	echo "  -a                  Send to all buddies\r\n";
	echo "  -d                  Debug output\r\n";
	echo "e.g.  php fetion.php -p 10.10.10.10:3128 -d -a -c hello,abc\r\n";
}



/**
 * main
 * 
 * @args command line args
 */
function main($argc, $argv) {
	global $fetion_proxy;
	global $fetion_debug;

	$user_mobile = null;
	$password = null;
	$sendto_sid = null;
	$content = null;
	$send_all = false;
	$proxy_host = null;
	$proxy_port = null;
	// caution, need to change to your own fetion number
	$mobiledn = 'FIXME';
	$passwd = 'FIXME';
	$cmcc_fetion_ip = '221.176.31.36';
	$cmcc_fetion_pt = '443';


	if ($argc < 2) {
		usage();
		return 1;
	}
	for ($I = 1; $I < $argc; ++$I) {
		if ($argv[$I] == '-p') {
			$fetion_proxy = $argv[++$I];
			list($proxy_host, $proxy_port) = split(":", $fetion_proxy, 2);
			if ($proxy_host === null) { $proxy_host='10.10.10.10'; }
			if ($proxy_port === null) { $proxy_port='8080'; }
		} else if ($argv[$I] == '-d') {
			$fetion_debug = true;
		} else if ($argv[$I] == '-a') {
			$send_all = true;
		} else if ($argv[$I] == '-c') {
			$content = $argv[++$I];
		}
/*		} else {
			$user_mobile = $argv[$I++];
			$password = $argv[$I++];
			if (isset($argv[$I])) {
				$sendto_sid = $argv[$I++];
				$content = $argv[$I];
			}
			break;
		}
*/
	}

	if ($content === null) {
		$content = "\n";
		$content = sms_get_txt("http://10.10.10.11/GRE.txt", $content);
		$content = sms_get_txt("http://10.10.10.11/GRE1.txt", $content);
		//$content = sms_get_news($content);
	} else {
		$content = iconv("GBK","UTF-8",$content);
	}

	/*wopwhite login fetion and get ssic and sid*/
	$login_info = fetion_login($mobiledn, $passwd);
	if ($login_info === false) {
		print "[*] login failed\r\n";
		return 1;
	}
	$ssic = $login_info['ssic'];
	$sid = $login_info['sid'];
	$domain = $login_info['domain'];
	
	if ($fetion_debug) {
		print "[*] https get successful\r\n";
		print "[*] ssic=$ssic\n";
		print "[*] sid=$sid\n";
		print "[*] domain=$domain\n";
	}
	
	if ($fetion_proxy != null) {
		$http_tunnel = http_tunnel_connect($proxy_host, $proxy_port, $cmcc_fetion_ip, $cmcc_fetion_pt);
		if ($http_tunnel === false) {
			print "[*] http_tunnel failed\r\n";
			return 1;
		}
		$ok = fetion_register($ssic, $sid, $domain, $passwd, $http_tunnel);
		if ($ok === false) {
			print "[*] register failed\r\n";
			return 1;
		}
		fetion_debug("register successful");

		// send sms to wopwhite himself	
		// caution, need to change fetion id to your own number
		$ok = fetion_send_sms($sid, 'sip:FIXME@fetion.com.cn;p=0000', $content, $http_tunnel);
		if ($fetion_debug) { print "[*] send sms ".strval($ok ? 'successful' : 'failed')."\r\n"; }

		if ($send_all === true) {
			$buddies = fetion_get_buddy_list_directly($sid, $http_tunnel);
			if ($buddies === false) {
				print "[*] get buddy list failed\r\n";
			} else {
				if ($fetion_debug) { print "[buddy_sid]                                   [name]\r\n"; }
				foreach ($buddies as $buddy_sid=>$name) {
					if ($fetion_debug) { printf("  %35s => %s\r\n", $buddy_sid, $name); }
					$ok = fetion_send_sms($sid, $buddy_sid, $content, $http_tunnel);
					if ($fetion_debug) { print "[*] send sms ".strval($ok ? 'successful' : 'failed')."\r\n"; }
				}
			}
		}
	}
}

main($argc, $argv);
?>
