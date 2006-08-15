<?php
// PukiWiki Plus! - Yet another WikiWikiWeb clone.
// $Id: funcplus.php,v 0.1.14 2006/08/15 01:10:00 miko Exp $
// Copyright (C)
//   2005-2006 PukiWiki Plus! Team
// License: GPL v2 or (at your option) any later version
//
// Plus! extension function(s)

defined('FUNC_SPAMLOG')   or define('FUNC_SPAMLOG', FALSE);
defined('FUNC_BLACKLIST') or define('FUNC_BLACKLIST', FALSE);
defined('FUNC_SPAMREGEX') or define('FUNC_SPAMREGEX', '#(?:a\s+href=|\[/link\]|\[/url\])#i');
defined('FUNC_SPAMCOUNT') or define('FUNC_SPAMCOUNT', 3);

// same as 'basename' for page
function basepagename($str)
{
	return mb_basename($str);
}

// multibyte supported 'basename' function
function mb_basename($str)
{
	return preg_replace('#^.*/#', '', $str);
}

// SPAM check
function is_spampost($array, $count=0)
{
	global $vars;

	if ($count <= 0) {
		$count = intval(FUNC_SPAMCOUNT);
	}
	$matches = array();
	foreach($array as $idx) {
		if (preg_match_all(FUNC_SPAMREGEX, $vars[$idx], $matches) >= $count)
			return TRUE;
	}
	return FALSE;
}

// SPAM logging
function honeypot_write()
{
	global $get, $post, $vars;

	// Logging for SPAM Address
	// NOTE: Not recommended use Rental Server
	if ((FUNC_SPAMLOG === TRUE || FUNC_BLACKLIST === TRUE) && version_compare(PHP_VERSION, '4.2.0', '>=')) {
		error_log($_SERVER['REMOTE_ADDR'] . "\t" . $_SERVER['HTTP_USER_AGENT'] . "\n", 3, CACHE_DIR . 'blacklist.log');
	}

	// Logging for SPAM Report
	// NOTE: Not recommended use Rental Server
	if (FUNC_SPAMLOG === TRUE && version_compare(PHP_VERSION, '4.2.0', '>=')) {
		error_log("----" . date('Y-m-d H:i:s', time()) . "\n", 3, CACHE_DIR . 'honeypot.log');
		error_log("[ADDR]" . $_SERVER['REMOTE_ADDR'] . "\t" . $_SERVER['HTTP_USER_AGENT'] . "\n", 3, CACHE_DIR . 'honeypot.log');
		error_log("[GET]\n"  . var_export($get,  TRUE) . "\n", 3, CACHE_DIR . 'honeypot.log');
		error_log("[POST]\n" . var_export($post, TRUE) . "\n", 3, CACHE_DIR . 'honeypot.log');
		error_log("[VARS]\n" . var_export($vars, TRUE) . "\n", 3, CACHE_DIR . 'honeypot.log');
	}
}

// インクルードで余計なものはソースから削除する
function convert_filter($str)
{
	global $filter_rules;
	static $patternf, $replacef;

	if (!isset($patternf)) {
		$patternf = array_map(create_function('$a','return "/$a/";'), array_keys($filter_rules));
		$replacef = array_values($filter_rules);
		unset($filter_rules);
	}
	return preg_replace($patternf, $replacef, $str);
}

function get_fancy_uri()
{
	$script  = (SERVER_PORT == 443 ? 'https://' : 'http://'); // scheme
	$script .= SERVER_NAME; // host
	$script .= (SERVER_PORT == 80 ? '' : ':' . SERVER_PORT); // port

	// SCRIPT_NAME が'/'で始まっていない場合(cgiなど) REQUEST_URIを使ってみる
	$path    = SCRIPT_NAME;
	$script .= $path; // path

	return $script;
}

function mb_ereg_quote($str)
{
	return mb_ereg_replace('([.\\+*?\[^\]\$(){}=!<>|:])', '\\\1', $str);
}

// タグの追加
function open_uri_in_new_window($anchor, $which)
{
	global $use_open_uri_in_new_window,		// この関数を使うか否か
	       $open_uri_in_new_window_opis,		// 同一サーバー(Farm?)
	       $open_uri_in_new_window_opisi,		// 同一サーバー(Farm?)のInterWiki
	       $open_uri_in_new_window_opos,		// 外部サーバー
	       $open_uri_in_new_window_oposi;		// 外部サーバーのInterWiki
	global $_symbol_extanchor, $_symbol_innanchor;	// 新規ウィンドウを開くアイコン
	
	// この関数を使わない OR 呼び出し元が不正な場合はスルーする
	if (!$use_open_uri_in_new_window || !$which || !$_symbol_extanchor || !$_symbol_innanchor) {
		return $anchor;
	}

	// 外部形式のリンクをどうするか
	$frame = '';
	// 質問箱/115 対応
	/*
	if ($which == 'link_interwikiname') {
		$frame = (is_inside_uri($anchor) ? $open_uri_in_new_window_opisi:$open_uri_in_new_window_oposi);
		$symbol = (is_inside_uri($anchor) ? $_symbol_innanchor:$_symbol_extanchor);
		$aclass = (is_inside_uri($anchor) ? 'class="inn" ':'class="ext" ');
	} elseif ($which == 'link_url_interwiki') {
		$frame = (is_inside_uri($anchor) ? $open_uri_in_new_window_opisi:$open_uri_in_new_window_oposi);
		$symbol = (is_inside_uri($anchor) ? $_symbol_innanchor:$_symbol_extanchor);
		$aclass = (is_inside_uri($anchor) ? 'class="inn" ':'class="ext" ');
	} elseif ($which == 'link_url') {
		$frame = (is_inside_uri($anchor) ? $open_uri_in_new_window_opis:$open_uri_in_new_window_opos);
		$symbol = (is_inside_uri($anchor) ? $_symbol_innanchor:$_symbol_extanchor);
		$aclass = (is_inside_uri($anchor) ? 'class="inn" ':'class="ext" ');
	}
	*/
	switch (strtolower($which)) {
	case 'link_interwikiname':
	case 'link_url_interwiki':
		$frame  = (is_inside_uri($anchor) ? $open_uri_in_new_window_opisi : $open_uri_in_new_window_oposi);
		$symbol = (is_inside_uri($anchor) ? $_symbol_innanchor : $_symbol_extanchor);
		$aclass = (is_inside_uri($anchor) ? 'class="inn" ' : 'class="ext" ');
		break;
	case 'link_url':
		$frame  = (is_inside_uri($anchor) ? $open_uri_in_new_window_opis : $open_uri_in_new_window_opos);
		$symbol = (is_inside_uri($anchor) ? $_symbol_innanchor : $_symbol_extanchor);
		$aclass = (is_inside_uri($anchor) ? 'class="inn" ' : 'class="ext" ');
	}

	if ($frame == '')
		return $anchor;

	// 引数 $anchor は a タグの中にクラスはない
	$aclasspos = mb_strpos($anchor, '<a ', 0, mb_detect_encoding($anchor)) + 3; // 3 is strlen('<a ')
	// $insertpos = mb_strpos($anchor, '</a>', mb_detect_encoding($anchor));
	$insertpos = mb_strpos($anchor, '</a>', $aclasspos, mb_detect_encoding($anchor));
	preg_match('#href="([^"]+)"#', $anchor, $href);

	return (mb_substr($anchor, 0, $aclasspos) . $aclass .
		mb_substr($anchor, $aclasspos, $insertpos-$aclasspos)
	        . str_replace('$1', $href[1], str_replace('$2', $frame, $symbol)) . mb_substr($anchor, $insertpos));
}

function is_inside_uri($anchor)
{
	global $open_uri_in_new_window_servername;

	foreach ($open_uri_in_new_window_servername as $servername) {
		if (stristr($anchor, $servername)) {
			return true;
		}
	}
	return false;
}

function load_init_value($name,$must=0)
{
	$init_dir = array(INIT_DIR, SITE_INIT_DIR);
	$read_dir = array();
	$init_data = $name . '.ini.php';

	// Exclusion of repetition definition
	foreach($init_dir as $val) { $read_dir[$val] = ''; }

	foreach($read_dir as $key=>$val) {
		if (file_exists($key.$init_data)) {
			if ($must)
				require_once($key.$init_data);
			else
				include_once($key.$init_data);
			return TRUE;
		}
	}

	return FALSE;
}

function is_ignore_page($page)
{
	global $whatsnew,$whatsdeleted,$interwiki,$menubar,$sidebar,$headarea,$footarea;

	$ignore_regrex = '(Navigation$)|('.$whatsnew.'$)|('.$whatsdeleted.'$)|('.
		$interwiki.'$)|'.$menubar.'$)|('.$sidebar.'$)|('.$headarea.'$)|('.$footarea.'$)';
	return (ereg($ignore_regrex, $page)) ? TRUE : FALSE;
}

function is_localIP($ip)
{
	static $localIP = array('127.0.0.0/8','10.0.0.0/8','172.16.0.0/12','192.168.0.0/16');
	if (is_ipaddr($ip) === FALSE) return FALSE;
	return ip_scope_check($ip,$localIP);
}

function is_ipaddr($ip)
{
	$valid = ip2long($ip);
	return ($valid == -1 || $valid == FALSE) ? FALSE : $valid;
}

// IP の判定
function ip_scope_check($ip,$networks)
{
	// $l_ip = ip2long( ip2arrangement($ip) );
	$l_ip = ip2long($ip);
	foreach($networks as $network) {
		$range = explode('/', $network);
		// $l_network = ip2long( ip2arrangement($range[0]) );
		$l_network = ip2long( $range[0] );
		if (empty($range[1])) $range[1] = 0;
		$subnetmask = pow(2,32) - pow(2,32 - $range[1]);
		if (($l_ip & $subnetmask) == $l_network) return TRUE;
	}
	return FALSE;
}

// ex. 10 -> 10.0.0.0, 192.168 -> 192.168.0.0
function ip2arrangement($ip)
{
	$x = explode('.', $ip);
	if (count($x) == 4) return $ip;
	for($i=0;$i<4;$i++) { if (empty($x[$i])) $x[$i] =0; }
	return sprintf('%d.%d.%d.%d',$x[0],$x[1],$x[2],$x[3]);
}

// 予約されたドメイン
function is_ReservedTLD($host)
{
	// RFC2606
	static $ReservedTLD = array('example' =>'','invalid' =>'','localhost'=>'','test'=>'',);
	$x = array_reverse(explode('.', strtolower($host) ));
	return (isset($ReservedTLD[$x[0]])) ? TRUE : FALSE;
}

function path_check($url1,$url2)
{
	$u1 = parse_url(strtolower($url1));
	$u2 = parse_url(strtolower($url2));

	// http = https とする
	if (!empty($u1['scheme']) && $u1['scheme'] == 'https') $u1['scheme'] = 'http';
	if (!empty($u2['scheme']) && $u2['scheme'] == 'https') $u2['scheme'] = 'http';

	// path の手当て
	if (!empty($u1['path'])) {
		$u1['path'] = substr($u1['path'],0,strrpos($u1['path'],'/'));
	}
	if (!empty($u2['path'])) {
		$u2['path'] = substr($u2['path'],0,strrpos($u2['path'],'/'));
	}

	foreach(array('scheme','host','path') as $x) {
		$u1[$x] = (empty($u1[$x])) ? '' : $u1[$x];
		$u2[$x] = (empty($u2[$x])) ? '' : $u2[$x];
		if ($u1[$x] == $u2[$x]) continue;
		return FALSE;
	}
	return TRUE;
}

// get "GD" extension version
function get_gdversion()
{
	if (!extension_loaded('gd')) { return 0; }
	if (!function_exists('gd_info')) { return 0; }
	$gd_info = gd_info();
	$matches = array();
	preg_match('/\d/', $gd_info['GD Version'], $matches);
	return $matches[0];
}

// create thumbnail (required "GD" extension)
function make_thumbnail($ofile, $sfile, $maxw, $maxh, $refresh=FALSE, $zoom='10,90', $quality='75')
{
	static $gdversion = FALSE;
	if ($gdversion === FALSE) {
		$gdversion = get_gdversion();
	}

	if (!$refresh && file_exists($sfile)) return $sfile;
	if ($gdversion < 1 || !function_exists('imagecreate')) return $ofile; // Not Supported

	$imagecreate = ($gdversion >= 2)? 'imagecreatetruecolor' : 'imagecreate';
	$imageresize = ($gdversion >= 2)? 'imagecopyresampled' : 'imagecopyresized';

	$imagesiz = @getimagesize($ofile);
	if (!$imagesiz) return $ofile; // Not Picture

	$orgw = $imagesiz[0];
	$orgh = $imagesiz[1];
	if ($maxw >= $orgw && $maxh >= $orgh) return $ofile; // so big. why?

	list($minz, $maxz) = explode(",", $zoom);
	$zoom = min(($maxw/$orgw),($maxh/$orgh));
	if (!$zoom || $zoom < $minz/100 || $zoom > $maxz/100) return $ofile; // Invalid Zoom value
	$w = $orgw * $zoom;
	$h = $orgh * $zoom;

	// defined thumbnail file-type?(.jpg)
	$s_ext = '';
	$s_ext = preg_replace('/\.([^\.]+)$/', '$1', $sfile);

	// Create image.
	switch($imagesiz[2]) {
	case '1': // gif
		if (function_exists('imagecreatefromgif')) {
			$imsrc = imagecreatefromgif($ofile);
			$colortransparent = imagecolortransparent($imsrc);
			if ($s_ext != 'jpg' && $colortransparent > -1) {
				// Use transparent
				$imdst = $imagecreate($w, $h);
				imagepalettecopy($imdst, $imsrc);
				imagefill($imdst, 0, 0, $colortransparent);
				imagecolortransparent($imdst, $colortransparent);
				imagecopyresized($imdst, $imsrc, 0, 0, 0, 0, $w, $h, $orgw, $orgh);
			} else {
				// Unuse transparent
				$imdst = $imagecreate($w, $h);
				$imageresize($imdst, $imsrc, 0, 0, 0, 0, $w, $h, $orgw, $orgh);
				imagetruecolortopalette($dst_im, imagecolorstotal($imsrc));
			}
			touch($sfile);
			if ($s_ext == 'jpg') {
				imagejpeg($imdst, $sfile, $quality);
			} elseif (function_exists('imagegif')) {
				imagegif($imdst, $sfile);
			} else {
				imagepng($imdst, $sfile);
			}
			$ofile = $sfile;
		}
		break;
	case '2': // jpg
		$imsrc = imagecreatefromjpeg($ofile);
		$imdst = $imagecreate($w, $h);
		$imageresize($imdst, $imsrc, 0, 0, 0, 0, $w, $h, $orgw, $orgh);
		touch($sfile);
		imagejpeg($imdst, $sfile, $quality);
		$ofile = $sfile;
		break;
	case '3': // png
		$imsrc = imagecreatefrompng($ofile);
		if (imagecolorstotal($imsrc)) {
			// PaletteColor
			$colortransparent = imagecolortransparent($imsrc);
			if ($s_ext != 'jpg' && $colortransparent > -1) {
				// Use transparent
				$imdst = $imagecreate($w, $h);
				imagepalettecopy($imdst, $imsrc);
				imagefill($imdst, 0, 0, $colortransparent);
				imagecolortransparent($imdst, $colortransparent);
				imagecopyresized($imdst, $imsrc, 0, 0, 0, 0, $w, $h, $orgw, $orgh);
			} else {
				// Unuse transparent
				$imdst = $imagecreate($w, $h);
				$imageresize($imdst, $imsrc, 0, 0, 0, 0, $w, $h, $orgw, $orgh);
				imagetruecolortopalette($dst_im, imagecolorstotal($imsrc));
			}
		} else {
			// TrueColor
			$imdst = $imagecreate($w, $h);
			$imageresize($imdst, $imsrc, 0, 0, 0, 0, $w, $h, $orgw, $orgh);
		}
		touch($sfile);
		if ($s_ext == 'jpg') {
			imagejpeg($imdst, $sfile, $quality);
		} else {
			imagepng($imdst, $sfile);
		}
		$ofile = $sfile;
		break;
	default:
		break;
	}
	@imagedestroy($imdst);
	@imagedestroy($imsrc);
	return $ofile;
}
?>
