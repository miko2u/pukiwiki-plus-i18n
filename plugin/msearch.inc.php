<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: msearch.inc.php,v 0.3.2 2007/04/18 21:01:00 upk Exp $
// Original is sha
/* 
*プラグイン msearch
 複数サイトのPukiWikiの検索を実行して表示

*Usage
 ?plugin=msearch&word=<words>&site=<sites>&type=<type>&order=<order>
 <words>: 単語、スペース区切り
 <sites>: PukiWikiサイトのURL、スペース区切り、thisだと同じサイト内、(euc|sjis|utf8)
 <type>: AND, OR
 <order>: [-]past(経過時間順), [-]site(サイト順), [-]page(ページ名順)
*/

//========================================================
function plugin_msearch_init()
{
	$messages = array(
		'_msearch_messages' => array(
			'msg_access_error'  => _('It was not able to access any site. '),
			'body_access_error' => '',
			'order' => array(
				'past' => _('Elapsed Time Order'),
				'-past' => _('Elapsed Time Reverse Order'),
				'site' => _('Site Order'),
				'-site' => _('Site Reverse Order'),
				'page' => _('Page Name Order'),
				'-page' => _('Page Name Reverse Order'),
			),
		),
	);
	set_plugin_messages($messages);
}
//=========================================================
function plugin_msearch_action()
{
	global $vars, $_msearch_messages; //, $_title_result;
	$_title_result  = 'Search result of $1';

	$options = array(
		'word' => '',
		'site' => 'this',
		'type' => 'AND',
		'order' => 'past',
	);
	$debug = '';
	foreach ( $options as $key=>$val ){
		if ( array_key_exists($key, $vars) ) {
			$options[$key] = $vars[$key];
//			$debug .= "/$key=$vars[$key]";
		}
	}
	$outs = plugin_msearch_gets($options);
	$debug .= $outs['debug'];
	if ( ! count($outs) ){
		return array(
			'msg'  => $_msearch_messages['msg_access_error'], 
			'body' => $_msearch_messages['body_access_error'],
		);
	}
	list($body, $total, $found) = plugin_msearch_merge($outs,$options);
	$debug .= "/total=$total/ctfound=$found";
	$body .= plugin_msearch_form($options, $total, $found);
	$title = str_replace('$1', $options['word'], $_title_result);

	return array('msg'=>$title, 'body'=>$body);
//	return array('msg'=>$title, 'body'=>$body . $debug);
}
//=========================================================
function plugin_msearch_form($opts,$ctall,$ctfound)
{
//	global $_btn_and, $_btn_or, $_btn_search;
//	global $_msg_orresult, $_msg_andresult;
	$_btn_search    = _('Search');
	$_btn_and       = _('AND');
	$_btn_or        = _('OR');
	$_msg_andresult = _('In the page <strong> $2</strong>, <strong> $3</strong> pages that contain all the terms $1 were found.');
	$_msg_orresult  = _('In the page <strong> $2</strong>, <strong> $3</strong> pages that contain at least one of the terms $1 were found.');

	$s_word = htmlspecialchars($opts['word']);
	$s_site = htmlspecialchars($opts['site']);
	$s_order = htmlspecialchars($opts['order']);
	$s_and_checked = $s_or_checked = '';
	if ( $opts['type'] == 'OR' ){
		$s_or_checked  = "checked=\"checked\"";
		$head = $_msg_orresult;
	}
	else {
		$s_and_checked = "checked=\"checked\"";
		$head = $_msg_andresult;
	}
	$head = str_replace('$1', $opts['word'],    $head);
	$head = str_replace('$3', $ctall,           $head);
	$head = str_replace('$2', $ctfound,         $head);

	$ret =<<<EOD
$head
<form action="$script?plugin=msearch" method="post">
 <div>
  <input type="text" name="word" size="20" value="$s_word" />
  <input type="radio" name="type" value="AND" $s_and_checked />$_btn_and
  <input type="radio" name="type" value="OR" $s_or_checked />$_btn_or
  &nbsp;<input type="submit" value="{$_btn_search}" />
  <input type="hidden" name="site" size="20" value="$s_site" />
  <input type="hidden" name="order" value="$s_order" />
 </div>
</form>
EOD;
	return $ret;
}
//=========================================================
function plugin_msearch_gets($opts)
{
	global $script;
	static $encode_aliases = array('sjis'=>'SJIS','euc'=>'EUC-JP','utf8'=>'UTF-8');
	
	$outs = array();
	$sites = explode(',', $opts['site']);
	$type  = $opts['type'];
	foreach ( $sites as $site ){
		$outs['debug'] .= "/site=$site";
		if ( preg_match('/\(([\w\-]+)\)$/',$site,$matcha) ){
			$ki = $matcha[1];
			if ( array_key_exists($ki,$encode_aliases) ) $ki = $encode_aliases[$ki];
			$site = preg_replace('/\([\w\-]+\)$/','',$site);
		}
		else {
			$ki = SOURCE_ENCODING;
		}
		$words = mb_convert_encoding($opts['word'], $ki, SOURCE_ENCODING);
		$words = rawurlencode($words);
		if ( $site == 'this' ) $site = $script;
		if ( ! preg_match('/^http:\/\//i',$site, $matchb) ){
			$site = 'http://' . $site;
		}
		$location = "$site?cmd=search&word=$words&type=$type";

		$ret = http_request($location);
		if ( (integer)( $ret['rc'] / 100 ) != 2 ) {
			$outs['debug'] .= "/loc=$location/code={$ret['rc']}";
			continue;
		}
		$from_kanji = SOURCE_ENCODING;
		if ( preg_match('/charset=([\w\-]+)/', $ret['header'], $matchc) ) {
			$from_kanji = $matchc[1];
		}
		$html = mb_convert_encoding($ret['data'],SOURCE_ENCODING,$from_kanji);
		$html = preg_replace('/&amp;/','&',$html);
		$outs[$site] = $html;
		$outs['debug'] .= "/loc=$location/ki=$ki/{$html}";
//		$outs['debug'] .= "/loc=$location/header={$ret['header']}/from_kanji=$from_kanji";
	}
	return $outs;
}
//=========================================================
function plugin_msearch_merge($input,$opts)
{
	global $script, $_msearch_messages;
	$debug = '/';

	$type = $opts['type'];
	$order = $opts['order'] != '' ? $opts['order'] : 'past';
	$ary = array();
	$ct = $total = $found = 0;
//	$debug .= $opts['order'];

	$order_dir = 1;
	$order_kind = 'past';
	if ( preg_match('/^(-?)(\w+)$/', $order, $match) ){
		if ( $match[1] == '-' ) $order_dir = -1;
		$order_kind = $match[2];
	}
	$sort_order = SORT_NUMERIC;
	switch ( $order_kind ){
		case 'past':
		case 'site': $sort_order = SORT_NUMERIC; break;
		case 'page': $sort_order = SORT_STRING;  break;
	}

	$ctsite = 1;
	$head = '';
	$cnt = array();
	$cn  = array();
	foreach ( $input as $site=>$html ) {
		if ( $site == 'debug' ) continue;
		list($ary, $ct, $ctall, $ctfound) = plugin_msearch_parse_html($ary,$ct,$site,$html,$type);
		$total += $ctall;
		$found += $ctfound;
//		$debug .= $ary['debug'] . "/";
		$s = $site;
		if ( $site == 'this' ) $site = $script;
		if ( ! preg_match('/^http:\/\//i',$site, $match) ){
			$site = 'http://' . $site;
		}
		if ( $cnt[$site] == '' ) {
			$cts = ($ctsite-1) % 10;
			$cnt[$site] = "<strong class=\"word{$cts}\">[$ctsite]</strong>";
			$cn[$s]  = $ctsite;
			$head .=<<<EOD
<a href="$site">{$cnt[$site]} $site</a><br />\n
EOD;
			$ctsite ++;
		}
	}

	$st = array();
	foreach ( $ary as $c=>$a ) {
		if ( ! preg_match('/^\d+$/',$c,$mat) ) continue;
		switch ( $order_kind ){
			case 'past':  $st[$c] = $a['pastmin'];   break;
			case 'site':  $st[$c] = $cn[$a['site']]; break;
			case 'page':  $st[$c] = $a['name'];      break;
		}
	}
//	$debug .= "/order_dir=$order_dir/sort_order=$sort_order/order_kind=$order_kind";
	if ( $order_dir == 1 ) {
		asort($st,$sort_order);
	}
	else {
		arsort($st,$sort_order);
	}

	$body = "<hr width='90%' /><UL>\n";
	$body .= $_msearch_messages['order'][$order] . "<br />\n";
	foreach ( $st as $ct=>$pm ){
		$debug .= "/$ct=$pm";
		$a = $ary[$ct];
		$s = $a['site'];
		$u = $s . $a['opt'];
		$n = $a['name'];
		$p = "({$a['past']}){$a['str']}";
//		$body .="-[[{$cnt[$s]}>$s]] [[$n>$u]] $p\n";
		$body .=<<<EOD
<LI><a href="$s">{$cnt[$s]}</a> <a href="$u">$n</a> $p</LI>\n
EOD;
	}
	$body .= "</UL>\n";
//	return array($head . $body . $debug, $total, $found);
	return array($head . $body,          $total, $found);
}
//=========================================================
function plugin_msearch_parse_html($ary,$ct,$site,$html,$type)
{
//	global $_msg_orresult, $_msg_andresult;
	$_msg_andresult   = _('In the page <strong> $2</strong>, <strong> $3</strong> pages that contain all the terms $1 were found.');
	$_msg_orresult    = _('In the page <strong> $2</strong>, <strong> $3</strong> pages that contain at least one of the terms $1 were found.');

	static $reg = '';
	static $mins = array(
		'm' => 1,
		'h' => 60,
		'd' => 1440,
		'w' => 10080,
	);

	if ( $reg == '' ) {
		if ( $type == 'OR' ){
			$head = $_msg_orresult;
		}
		else {
			$head = $_msg_andresult;
		}
		$reg = str_replace('$1','', $head);
		$reg = str_replace('$2','(\d+)',$reg);
		$reg = str_replace('$3','(\d+)',$reg);
		$reg = str_replace('/', '\/', $reg);
	}
//	$ary['debug'] .= str_replace('s',' s',$reg);
	
//	if ( ! preg_match('/<div\s+id="body">(.+(?=<form ))<form\s+/s', $html, $match) ) {
	if ( ! preg_match('/<div\s+class="small">(.+(?=<form ))<form\s+/s', $html, $match) ) {
		return array($ary, $ct, 0, 0, 0);
	}
	$search_str = $match[1];
	$search_str = preg_replace('/<strong[^>]*>|<\/strong>/','',$search_str);

//	$ary['debug'] .= "/$search_str";
	
	if ( preg_match("/$reg/s", $html, $mat) ) {
		$ctall   = $mat[1];
		$ctfound = $mat[2];
		$ary['debug'] .= "/ctall=$ctall/ctfound=$ctfound";
	}
	
	if ( preg_match_all('/<li\s*[^>]*>(.+(?=<\/li>))<\/li>/', $search_str, $matches) ) {
//		$ary['debug'] .= join("/", $matches[1]);

		foreach ( $matches[0] as $line ) {
//			$ary['debug'] .= "/$line<br />\n";
			// For org:QA3/437
			// if ( preg_match('/<li\s*[^>]*>\s*<a\s+href=\"[^?]+(\?[^\"]+)\"[^>]*>(.+(?=<\/a>))<\/a>\((\d+)([mhdw])\)(.*(?=<\/li>))<\/li>/', $line, $mat) ) {
			if ( preg_match('/<li\s*[^>]*>\s*<a\s+href=\"[^?]+(\?[^\"]+)\"[^>]*>(.+(?=<\/a>))<\/a>\s*\((\d+)([mhdw])\)(.*(?=<\/li>))<\/li>/', $line, $mat) ) {
				$mat['opt']  = $mat[1];
				$mat['name'] = $mat[2];
				$mat['past'] = $mat[3] . $mat[4];
				$mat['pastmin'] = $mat[3] * $mins[$mat[4]];
				$mat['str']  = $mat[5];
				$mat['site']  = $site;
				$mat['ct']   = $ct;
				$ary[$ct++] = $mat;
//				$ary['debug'] .= "/name[{$ct}]={$mat[2]}({$mat[3]}{$mat[4]})<br />\n";
			}
		}
	}
	return array($ary, $ct, $ctall, $ctfound);
}
?>
