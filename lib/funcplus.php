<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: funcplus.php,v 0.1.5 2006/03/22 01:25:00 upk Exp $
//

// インクルードで余計なものはソースから削除する
function convert_filter($str)
{
	global $filter_rules;
	static $patternf,$replacef;

	if (!isset($patternf))
	{
		$patternf = array_map(create_function('$a','return "/$a/";'),array_keys($filter_rules));
		$replacef = array_values($filter_rules);
		unset($filter_rules);
	}
	return preg_replace($patternf,$replacef,$str);
}

function get_fancy_uri()
{
        $script  = (SERVER_PORT == 443 ? 'https://' : 'http://');       // scheme
        $script .= SERVER_NAME; // host
        $script .= (SERVER_PORT == 80 ? '' : ':' . SERVER_PORT); // port

        // SCRIPT_NAME が'/'で始まっていない場合(cgiなど) REQUEST_URIを使ってみる
        $path    = SCRIPT_NAME;
        $script .= $path;       // path

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

	if ($frame == '')
		return $anchor;

	// 引数 $anchor は a タグの中にクラスはない
	$aclasspos = mb_strpos($anchor, '<a ', mb_detect_encoding($anchor)) + 3; // 3 is strlen('<a ')
	$insertpos = mb_strpos($anchor, '</a>', mb_detect_encoding($anchor));
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
	global $defaultpage,$whatsnew,$whatsdeleted,$interwiki,$menubar,$sidebar,$headarea,$footarea;

	$ignore_regrex = '(Navigation$)|('.$defaultpage.'$)|('.$whatsnew.')|('.$whatsdeleted.'$)|('.
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
?>
