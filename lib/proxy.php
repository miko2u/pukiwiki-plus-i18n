<?php
// $Id: proxy.php,v 2.1.10 2006/08/11 01:03:29 miko Exp $
// Copyright (C)
//   2005-2006 PukiWiki Plus! Team
//   2003-2005 PukiWiki Developers Team
// License: GPL v2 or (at your option) any later version
//
// HTTP-Proxy related functions

// Max number of 'track' redirection message with 301 or 302 response
define('PKWK_HTTP_REQUEST_URL_REDIRECT_MAX', 2);
define('PKWK_HTTP_REQUEST_TIMEOUT', 8);
define('PKWK_HTTP_CONNECT_TIMEOUT', 2);
define('PKWK_HTTP_VERSION', '1.1');
define('PKWK_HTTP_CLIENT', 'PukiWiki/1.4');

/*
 * is_requestable($uri)
 */
function is_requestable($uri)
{
	global $script;

	$arr1 = parse_url($script);
	$arr2 = parse_url($uri);
	$arr1['port']  = isset($arr1['port'])  ? $arr1['port'] : 80;
	$arr2['port']  = isset($arr2['port'])  ? $arr2['port'] : 80;
	$arr1['path']  = isset($arr1['path'])  ? dirname($arr1['path'] . 'dummy') : '/';
	$arr2['path']  = isset($arr2['path'])  ? dirname($arr2['path'] . 'dummy') : '/';

	if ($arr1['scheme'] != $arr2['scheme'] ||
		$arr1['host'] != $arr2['host'] ||
		$arr1['port'] != $arr2['port'] ||
		$arr1['path'] != $arr2['path'])
		return TRUE;

	return FALSE;
}

/*
 * http_request($url)
 *     Get / Send data via HTTP request
 * $url     : URI started with http:// (http://user:pass@host:port/path?query)
 * $method  : HTTP method(GET/POST/HEAD/PUT/DELETE/OPTIONS/TRACE/CONNECT/PATCH/LINK/UNLINK)
 *            in additional method(webdav), COPY/MOVE/MKCOL/PROPFIND/PROPPATCH/LOCK/UNLOCK
 * $headers : Additional HTTP headers, ended with "\r\n"
 * $post    : An array of data to send via POST method ('key'=>'value')
 * $redirect_max : Max number of HTTP redirect
 * $content_charset : Content charset. Use '' or CONTENT_CHARSET
*/
function http_request($url, $method = 'GET', $headers = array(), $post = array(),
	$redirect_max = PKWK_HTTP_REQUEST_URL_REDIRECT_MAX, $content_charset = '')
{
	global $use_proxy, $no_proxy, $proxy_host, $proxy_port;
	global $need_proxy_auth, $proxy_auth_user, $proxy_auth_pass;

	$rc  = array();
	$arr = parse_url($url);

	$via_proxy = $use_proxy ? ! in_the_net($no_proxy, $arr['host']) : FALSE;

	$arr['query'] = isset($arr['query']) ? '?' . $arr['query'] : '';
	$arr['port']  = isset($arr['port'])  ? $arr['port'] : 80;

	$url_base = $arr['scheme'] . '://' . $arr['host'] . ':' . $arr['port'];
	$url_path = isset($arr['path']) ? $arr['path'] : '/';
	$url = ($via_proxy ? $url_base : '') . $url_path . $arr['query'];

	// HTTP request method
	$query = $method . ' ' . $url . ' HTTP/' . PKWK_HTTP_VERSION . "\r\n";
	$query .= 'Host: ' . $arr['host'] . "\r\n";
	$query .= 'User-Agent: ' . PKWK_HTTP_CLIENT . "\r\n";
	$query .= 'Connection: close' . "\r\n";

	// Basic-auth for HTTP proxy server
	if ($need_proxy_auth && isset($proxy_auth_user) && isset($proxy_auth_pass))
		$query .= 'Proxy-Authorization: Basic '.
			base64_encode($proxy_auth_user . ':' . $proxy_auth_pass) . "\r\n";

	// (Normal) Basic-auth for remote host
	if (isset($arr['user']) && isset($arr['pass']))
		$query .= 'Authorization: Basic '.
			base64_encode($arr['user'] . ':' . $arr['pass']) . "\r\n";

//@miko added
	// Gzipped encoding
	if (PKWK_HTTP_VERSION == '1.1' && extension_loaded('zlib') && (ini_get('mbstring.func_overload') & 2) == 0) {
		$query .= 'Accept-Encoding: gzip' . "\r\n";
	}

	// Add Headers
	if (is_array($headers)) {
		foreach($headers as $key=>$val)
			$query .= $key . ': ' . $val . "\r\n";
	} else {
		$query .= $headers;
	}
//@miko added

	if (strtoupper($method) == 'POST') {
		// 'application/x-www-form-urlencoded', especially for TrackBack ping
		$POST = array();
//		foreach ($post as $name=>$val) $POST[] = $name . '=' . urlencode($val);
		foreach ($post as $name=>$val) $POST[] = urlencode($name) . '=' . urlencode($val);
		$data = join('&', $POST);

		if (preg_match('/^[a-zA-Z0-9_-]+$/', $content_charset)) {
			// Legacy but simple
			$query .= 'Content-Type: application/x-www-form-urlencoded' . "\r\n";
		} else {
			// With charset (NOTE: Some implementation may hate this)
			$query .= 'Content-Type: application/x-www-form-urlencoded' .
				'; charset=' . strtolower($content_charset) . "\r\n";
		}

		$query .= 'Content-Length: ' . strlen($data) . "\r\n";
		$query .= "\r\n";
		$query .= $data;
//@miko_patched
//@for use propfind, use "Depth:infinity, noroot"
	} elseif (strtoupper($method) == 'PROPFIND') {
		// 'text/xml', especially for svn
		$data = implode('', $post);

		if (preg_match('/^[a-zA-Z0-9_-]+$/', $content_charset)) {
			// Legacy but simple
			$query .= 'Content-Type: text/xml' . "\r\n";
		} else {
			// With charset (NOTE: Some implementation may hate this)
			$query .= 'Content-Type: text/xml' .
				'; charset=' . strtolower($content_charset) . "\r\n";
		}

		$query .= 'Content-Length: ' . strlen($data) . "\r\n";
		$query .= "\r\n";
		$query .= $data;
//@miko_patched
	} else {
		$query .= "\r\n";
	}

	$errno  = 0;
	$errstr = '';
	$fp = fsockopen(
		$via_proxy ? $proxy_host : $arr['host'],
		$via_proxy ? $proxy_port : $arr['port'],
		$errno, $errstr, PKWK_HTTP_CONNECT_TIMEOUT);
	if ($fp === FALSE) {
		return array(
			'query'  => $query, // Query string
			'rc'     => $errno, // Error number
			'header' => '',     // Header
			'data'   => $errstr // Error message
		);
	}
	socket_set_timeout($fp, PKWK_HTTP_REQUEST_TIMEOUT, 0);
	fwrite($fp, $query);

	// Get a Head
	$head = '';
	$status = array();
	while(!feof($fp)) {
		$line = rtrim(fgets($fp, 4096));
		$status = socket_get_status($fp);
		if ($status['timed_out']) break;
		if ($line == '') break;
		if ($head != '') {
			$r = explode(':', $line, 2);
			$response[strtolower(trim($r[0]))] = strtolower(trim($r[1]));
		}
		$head .= $line . "\r\n";
	}

	// Get a Body
	$chunked = isset($response['transfer-encoding']) && ($response['transfer-encoding'] == 'chunked');
	$gzipped = isset($response['content-encoding']) && ($response['content-encoding'] == 'gzip');
	$body = '';
	$length = 0;
	$status = array();
	if (!isset($response['content-length']) || $response['content-length'] != 0) {
		while(!feof($fp)) {
			if ($chunked) {
				$body .= fread_chunked($fp, $length);
			} else {
				$body .= fread($fp, 4096);
			}
			$status = socket_get_status($fp);
			if ($status['timed_out']) break;
		}
	}
//	fputs($fp, $query);
//	$response = '';
//	while (! feof($fp)) $response .= fread($fp, 4096);
	fclose($fp);

//@miko added
	if ($body != '' && $gzipped) {
		$body = gzinflate(substr($body,10));
	}
//@miko added

//	$resp = explode("\r\n\r\n", $response, 2);
//	$rccd = explode(' ', $resp[0], 3); // array('HTTP/1.1', '200', 'OK\r\n...')
	$rccd = explode(' ', $head, 3); // array('HTTP/1.1', '200', 'OK\r\n...')
	$rc   = (integer)$rccd[1];

	switch ($rc) {
	case 301: // Moved Permanently
	case 302: // Moved Temporarily
		$matches = array();
//		if (preg_match('/^Location: (.+)$/m', $resp[0], $matches)
		if (preg_match('/^Location: (.+)$/m', $head, $matches) && --$redirect_max > 0) {
			$url = trim($matches[1]);
			if (! preg_match('/^https?:\//', $url)) {
				// Relative path to Absolute
				if ($url{0} != '/')
					$url = substr($url_path, 0, strrpos($url_path, '/')) . '/' . $url;
				$url = $url_base . $url; // Add sheme, host
			}
			// Redirect
			return http_request($url, $method, $headers, $post, $redirect_max);
		}
	}
	return array(
		'query'  => $query,   // Query String
		'rc'     => $rc,      // Response Code
//		'header' => $resp[0], // Header
//		'data'   => $resp[1]  // Data
		'header' => $head, // Header
		'data'   => $body, // Data
		'timeout' => $status['timed_out'],
	);
}

// Read from Chunked Encoding
function fread_chunked($fp, &$length)
{
	if ($length == 0) {
		$line = rtrim(fgets($fp, 4096));
		$matches = array();
		if (preg_match('/^([0-9a-f]+)/i', $line, $matches)) {
			$length = hexdec($matches[1]);
			if ($length == 0) {
				$line = rtrim(fgets($fp, 4096)); // make eof
				return '';
			}
		} else {
			return '';
		}
	}
	$data = fread($fp, $length);
	$length -= strlen($data);
	if ($length == 0) {
		fgets($fp, 4096);	// trailing crlf
	}
	return $data;
}

// Separate IPv4 network-address and its netmask
define('PKWK_CIDR_NETWORK_REGEX', '/^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})(?:\/([0-9.]+))?$/');

// Check if the $host is in the specified network(s)
function in_the_net($networks = array(), $host = '')
{
	if (empty($networks) || $host == '') return FALSE;
	if (! is_array($networks)) $networks = array($networks);

	$matches = array();

	if (preg_match(PKWK_CIDR_NETWORK_REGEX, $host, $matches)) {
		$ip = $matches[1];
	} else {
		$ip = gethostbyname($host); // May heavy
	}
	$l_ip = ip2long($ip);

	foreach ($networks as $network) {
		if (preg_match(PKWK_CIDR_NETWORK_REGEX, $network, $matches) &&
		    is_long($l_ip) && long2ip($l_ip) == $ip) {
			// $host seems valid IPv4 address
			// Sample: '10.0.0.0/8' or '10.0.0.0/255.0.0.0'
			$l_net = ip2long($matches[1]); // '10.0.0.0'
			$mask  = isset($matches[2]) ? $matches[2] : 32; // '8' or '255.0.0.0'
			$mask  = is_numeric($mask) ?
				pow(2, 32) - pow(2, 32 - $mask) : // '8' means '8-bit mask'
				ip2long($mask);                   // '255.0.0.0' (the same)

			if (($l_ip & $mask) == $l_net) return TRUE;
		} else {
			// $host seems not IPv4 address. May be a DNS name like 'foobar.example.com'?
			foreach ($networks as $network)
				if (preg_match('/\.?\b' . preg_quote($network, '/') . '$/', $host))
					return TRUE;
		}
	}

	return FALSE; // Not found
}
?>
