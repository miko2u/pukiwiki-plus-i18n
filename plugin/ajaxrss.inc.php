<?php
// PukiWiki Plus! - Yet another WikiWikiWeb clone
// $Id: ajaxrss.inc.php,v 0.3 2006/02/16 01:31:00 upk Exp $
//
// Warning: this plugin is experimental.
function plugin_ajaxrss_action()
{
	global $get;

	if ($get['t'] == 'js') {
		$output = plugin_ajaxrss_output_js();

		// Feeding start
		pkwk_common_headers();
//		header('Content-type: text/javascript');
		print $output;
	} else if ($get['t'] == 'url') {
		$output = plugin_ajaxrss_output_url(decode($get['q']));

		// Feeding start
		pkwk_common_headers();
		header('Content-type: application/xml');
		if (!preg_match('/\<\?xml/', $output, $matches)) {
			print '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		}
		print $output;
	} else {
		$output = plugin_ajaxrss_output_xml();

		// Feeding start
		pkwk_common_headers();
		header('Content-type: application/xml');
		if (!preg_match('/\<\?xml/', $output, $matches)) {
			print '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		}
		print $output;
	}

	exit;
}

function plugin_ajaxrss_output_url($url)
{
	if (!is_url($url,TRUE)) return '';
	$ret = http_request($url);
	if ($ret['rc'] != 200) return '';
	return $ret['data'];
}

function plugin_ajaxrss_output_xml()
{
	global $page_title;

	$lang = LANG;
	$page_title_utf8 = mb_convert_encoding($page_title, 'UTF-8', SOURCE_ENCODING);
	$self = get_script_absuri();
	$version = '0.91';

	$items = '';
	foreach (get_source('RSS') as $line) {
		if (preg_match('/\[(' . '(?:(?:https?|ftp|news):\/\/|\.\.?\/)' .
		    '[!~*\'();\/?:\@&=+\$,%#\w.-]*)\s([^\]]+)\]\s?([^\s]*)/',
		    $line, $matches)) {
			$title = $matches[2];
			$link = $self . '?cmd=ajaxrss&amp;t=url&amp;q=' . encode($matches[1]);
			$desc = $matches[3];
			$items .= <<<EOD
<item>
 <title>$title</title>
 <link>$link</link>
 <description>$desc</description>
</item>
EOD;
		}
	}

	$rssxml = '<!DOCTYPE rss PUBLIC "-//Netscape Communications//DTD RSS 0.91//EN"' .
	' "http://my.netscape.com/publish/formats/rss-0.91.dtd">' . "\n";

	$rssxml .= <<<EOD
<rss version="$version">
 <channel>
  <title>$page_title_utf8</title>
  <link>$self</link>
  <description>AjaxRSS Sample</description>
  <language>$lang</language>
$items
 </channel>
</rss>
EOD;

	return $rssxml;
}

function plugin_ajaxrss_output_js()
{
	global $script, $get;

	$s_elementid = 'ajaxrss' . htmlspecialchars($get['c']);

	$out .= <<<EOD
var render;

window.onload = function() {
	render = new RssRenderer();

	try {
		var list = new Rss();
		list.load('{$script}?cmd=ajaxrss');

		// フォーム作成
		var frm = document.createElement('form');
		frm.setAttribute('id' , 'rsslist');
		var sel = document.createElement('select');

		for( var i = 0 ; i < list.items.length ; i++ ){
			var opt = document.createElement('option');
			opt.setAttribute('value', list.items[i].link );
			opt.appendChild( document.createTextNode( list.items[i].title ) );
			sel.appendChild(opt);
		}
		frm.appendChild(sel);

		var btn = document.createElement('input');
		btn.setAttribute('type','button');
		btn.setAttribute('value','Show');
		frm.appendChild(btn);
		
		document.getElementById('{$s_elementid}').appendChild(frm);

		// RSS読み込みイベント用クロージャ
		btn.onclick = function(){
			var rss = new Rss();
			window.document.body.style.cursor = 'wait';
			try{
				rss.load(sel.value);
				render.write(rss, document.getElementById('{$s_elementid}'));
			}
			catch(e){
				window.alert(e.message);
			}
			window.document.body.style.cursor = 'auto';
		}
	}
	catch(e) {
		window.alert(e.message);
	}
}
EOD;
	return $out;
}

function plugin_ajaxrss_convert()
{
	global $script;
	static $ajaxcount = 0;
//	$num = func_num_args();
//	if ($num == 0 ) return FALSE;
//	list($url) = func_get_args();
	if ($ajaxcount == 0) {
		global $head_tags;
		$head_tags[] = ' <script type="text/javascript" charset="utf-8" src="' . SKIN_URI . 'ajax/msxml.js"></script>';
		$head_tags[] = ' <script type="text/javascript" charset="utf-8" src="' . SKIN_URI . 'ajax/xmlloader.js"></script>';
		$head_tags[] = ' <script type="text/javascript" charset="utf-8" src="' . SKIN_URI . 'ajax/rss.js"></script>';
	}
	$s_divid = 'ajaxrss' . $ajaxcount;
	$output = '<div id="' . $s_divid . '"><script type="text/javascript" charset="utf-8" src="' . $script . '?cmd=ajaxrss&amp;t=js&amp;c=' . $ajaxcount. '"></script></div>';
	++$ajaxcount;
	return $output;
}
?>
