<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: tooltip.inc.php,v 0.6.2 2008/01/05 18:53:00 upk Exp $
//
/* 
*プラグイン tooltip
 ツールチップを表示

*Usage
 &tooltip(<term>);
 &tooltip(<term>){<glossary>};
// &tooltip(<term>,[<用語集>]);
// &tooltip(<term>,[<用語集>]){<glossary>};
 <term>にマウスカーソルを当てると、<glossary>が出現する。
*/
//========================================================
function plugin_tooltip_init()
{
		$messages = array(
		'_tooltip_messages' => array(
			'page_glossary' => 'Glossary',
			'defaults' => array(
				'glossary'=> 'Glossary',
			),
		),
	);
	set_plugin_messages($messages);
}

///////////////////////////////////////
// Plus! ajax Glossary for UTF-8
function plugin_tooltip_action()
{
	global $vars;

	$term = $vars['q'];
	if (trim($term) == '') { exit; }
	$glossary = plugin_tooltip_get_glossary($term, '', TRUE);
	if ($glossary == FALSE) { exit; }
	$s_glossary = convert_html($glossary);

	pkwk_common_headers();
	header('Content-type: text/xml');
	print '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
	print $s_glossary;
	exit;
}

//========================================================
function plugin_tooltip_inline()
{
	$args = func_get_args();
	$glossary  = array_pop($args);
	$term      = array_shift($args);
//	$glossary_page = count($args) ? array_shift($args) : '';
	$glossary_page = '';

	if ( $glossary == '' ){
		$glossary = plugin_tooltip_get_glossary($term,$glossary_page,FALSE);
		$debug .= "B=$glossary/";
		if ( $glossary === FALSE ) {
			$glossary = plugin_tooltip_get_page_title($term);
			if ( $glossary === FALSE ) $glossary = "";
		}
	}
	$s_glossary = htmlspecialchars($glossary);

	$page = strip_bracket($term);
	if ( is_page($page) ) {
		$url = get_page_uri($page);
		$passage = get_pg_passage($page,FALSE);
		return <<<EOD
<a href="$url" class="linktip" title="$s_glossary$passage">$term</a>
EOD;
	}
	else {
	return <<<EOD
<span class="tooltip" title="$s_glossary" onmouseover="javascript:this.style.backgroundColor='#ffe4e1';" onmouseout="javascript:this.style.backgroundColor='transparent';">$term</span>
EOD;
	}
}
//========================================================
function plugin_tooltip_get_page_title($term)
{
	$page = strip_bracket($term);
	if ( ! is_page($page) ) return FALSE;
	$src = get_source($page);
	$ct = 0;
	foreach ( $src as $line ) {
		if ( $ct ++ > 99 ) break;
		if ( preg_match('/^\*{1,3}(.*)\[#[A-Za-z][\w\-]+\].*$/', $line, $match) ){
			return trim($match[1]);
		}
		else if ( preg_match('/^\*{1,3}(.*)$/', $line, $match) ){
			return trim($match[1]);
		}
	}
	return FALSE;
}
//========================================================
// 用語集を変えた場合のキャッシュがうまく記述できない。
function plugin_tooltip_get_glossary($term,$g_page,$plain)
{
	global $_tooltip_messages;
	static $aglossary = '';

	if ( $aglossary == '' ) {
		$aglossary = array();
		$page = ( $g_page != '' )  ? $g_page : $_tooltip_messages['page_glossary'];
		if ( ! is_page($page) ) return FALSE;
		$src = get_source($page);
		foreach ( $_tooltip_messages['defaults'] as $t=>$d ){
			$aglossary[$t] = $d;
		}
		foreach ( $src as $line ){
			if ( preg_match('/^[:|]([^|]+)\|([^|]+)\|?$/', $line, $match) ){
				$dt = trim($match[1]);
				$dd = trim($match[2]);
				$aglossary[$dt] = $dd;
//				echo "[$dt=$dd]";
			}
		}
	}
	$out = $aglossary[trim($term)];
	if (!$plain) {
		$out = preg_replace('/&br;/', "\n", $out);
		$out = preg_replace('/&t;/', "\t", $out);
	}
//	echo "/out=$out/term=$term";
	if ( $out == '' ) return FALSE;
	return $out;
}
?>
