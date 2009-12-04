<?php
/**
 * PukiWiki Plus! PRINT Plugin
 *
 * @copyright   Copyright &copy; 2007-2009, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: print.inc.php,v 0.10 2009/12/05 03:36:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License (GPL2)
 *
 */

defined('PRINT_HEAD_BGCOLOR') or define('PRINT_HEAD_BGCOLOR', '#FFF0DD');
defined('PRINT_HEAD_BORDER')  or define('PRINT_HEAD_BORDER' , '#FFCC99');

function plugin_print_inline()
{
	global $vars;

	if (! print_display()) return '';

	// <div class="print" style="text-align:right"></div>
	return '<img src="'.IMAGE_URI.'plus/print.png" alt="Print Image" title="Print Image" class="ext" onclick="return open_uri(\''.
		'?cmd=print&amp;page='.urlencode($vars['page']).'\', \'_blank\');" />';
}

function plugin_print_action()
{
	global $defaultpage, $page_title, $newtitle;
	global $use_local_time, $language;
	global $head_tags, $foot_tags, $pkwk_dtd;
	global $vars;
	global $fixed_heading_edited, $autoglossary, $_symbol_paraedit, $_symbol_paraguiedit;
	global $_symbol_noexists;
	global $foot_explain, $note_hr;

	if (empty($vars['page']) || ! is_page($vars['page'])) return '';
	$page = $vars['page'];
	check_readable($page, false);

	$head = (isset($vars['nohead'])) ? 0 : 1;
	$foot = (isset($vars['nofoot'])) ? 0 : 1;
	$noa  = (isset($vars['noa']))    ? 1 : 0;
	$fixed_heading_edited = $autoglossary = 0;
	$_symbol_paraedit = $_symbol_paraguiedit = '&nbsp;';

	$body = convert_html(get_source($page));

	// Yetlist
	$noexists_pattern = '#<span class="noexists">([^<]*)<a[^>]+>' . preg_quote($_symbol_noexists, '#') . '</a></span>#';
	$body = preg_replace($noexists_pattern,'$1',$body);

	// List of footnotes
	ksort($foot_explain, SORT_NUMERIC);
	$notes = ! empty($foot_explain) ? $note_hr . join("\n", $foot_explain) : '';

	if ($noa) {
		$body = strip_a($body);
		$notes = strip_a($notes);
	}

	// Tags will be inserted into <head></head>
	$head_tag = ! empty($head_tags) ? join("\n", $head_tags) ."\n" : '';
	$foot_tag = ! empty($foot_tags) ? join("\n", $foot_tags) ."\n" : '';

	$css_charset = 'utf-8';
	switch(UI_LANG){
		case 'ja_JP': $css_charset = 'Shift_JIS'; break;
	}

	// Output header
	pkwk_common_headers();
	header('Cache-Control: no-cache');
	header('Pragma: no-cache');
	header('Content-Type: text/html; charset=' . CONTENT_CHARSET);
	header('ETag: ' . md5(MUTIME));

	// Output HTML DTD, <html>, and receive content-type
	$meta_content_type = (isset($pkwk_dtd)) ? pkwk_output_dtd($pkwk_dtd) : pkwk_output_dtd();

	$CONTENT_CHARSET = CONTENT_CHARSET;
	$SKIN_URI = SKIN_URI;
	$IMAGE_URI = IMAGE_URI;

	// Plus! not use $meta_content_type. because meta-content-type is most browser not used. umm...
	echo <<<EOD
<head>
 <meta http-equiv="content-type" content="application/xhtml+xml; charset=$CONTENT_CHARSET" />
 <meta http-equiv="content-style-type" content="text/css" />
 <meta http-equiv="content-script-type" content="text/javascript" />
 <meta name="robots" content="NOINDEX,NOFOLLOW" />

EOD;

	// $newtitle - TITLE: (convert_html)
	if ($newtitle != '') {
		$h1 = $newtitle.' - '.$page_title;
	} elseif ($page == $defaultpage) {
		$h1 = $page_title;
	} else {
		$h1 = $page.' - '.$page_title;
	}
	echo ' <title>'.$h1.'</title>'."\n";

	echo <<<EOD
 <link rel="stylesheet" href="{$SKIN_URI}default.css" type="text/css" media="screen" charset="$css_charset" />
 <link rel="stylesheet" href="{$SKIN_URI}print.css" type="text/css" media="print" charset="$css_charset" />
 <script type="text/javascript">
 <!--

EOD;
	if (exist_plugin_convert('js_init')) echo do_plugin_convert('js_init');

	echo <<<EOD
 // -->
 </script>
 <script type="text/javascript" src="{$SKIN_URI}lang/$language.js"></script>
 <script type="text/javascript" src="{$SKIN_URI}default.js"></script>

EOD;

	if (! $use_local_time) {
		echo <<<EOD
 <script type="text/javascript" src="{$SKIN_URI}tzCalculation_LocalTimeZone.js"></script>

EOD;
	}

	echo $head_tag;

	echo <<<EOD
</head>
<body>

EOD;


/*
	if ($head) {
		echo <<<EOD
<div id="header">
 <h1 class="title">$h1</h1>
</div>

EOD;
	}
*/

	if ($head) {
		// Last modification date (string) of the page
		$lastmodified = get_date('D, d M Y H:i:s T', get_filetime($page)).' '.get_pg_passage($page, FALSE);
		// <span style="font-size: large;line-height: 1;margin: 0px;padding: 0px;">$h1</span>
		$PRINT_HEAD_BGCOLOR = PRINT_HEAD_BGCOLOR;
		$PRINT_HEAD_BORDER = PRINT_HEAD_BORDER;
		echo <<<EOD
<div style="background-color: $PRINT_HEAD_BGCOLOR;border: 1px $PRINT_HEAD_BORDER solid;padding: 6px 8px;margin: 6px 1%;">
	<h1 class="title">$h1</h1>
	<p style="font-size:10px;text-align:right;">Last-Modified: $lastmodified</p>
</div>

EOD;
}

	echo <<<EOD
<div id="contents">
<table class="contents" width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
  <td class="ctable" valign="top">
    <div id="body">

EOD;

	echo $body;

	echo <<<EOD
    </div>
  </td>
</tr>
</table>
</div>

EOD;

	if ($notes) {
		echo <<<EOD
<div id="note">
$notes
</div>

EOD;
	}

	if ($foot) {
		echo print_foot_area();
	}

	if (exist_plugin_convert('tz')) echo do_plugin_convert('tz');

	echo $foot_tag;

	echo <<<EOD
</body>
</html>

EOD;

	die();
}

function print_display()
{
	static $excludes_plugin = array('print');

	$pos = strpos($_SERVER['REQUEST_URI'], '?');
	if ($pos === false) return true;

	$query_string = explode('&',rawurldecode( substr($_SERVER['REQUEST_URI'], $pos+1) ));
	if (count($query_string) === 1) return true;

	foreach($query_string as $q) {
		$cmd = explode('=',$q);
		switch($cmd[0]) {
		case 'cmd':
		case 'plugin':
			foreach($excludes_plugin as $plugin_name) {
				if ($plugin_name == $cmd[1]) return false;
			}
			continue;
		}
	}

	return true;
}

function print_foot_area()
{
        global $hr, $modifier, $vars;
	$S_VERSION = S_VERSION;
	$PRINT_HEAD_BGCOLOR = PRINT_HEAD_BGCOLOR;
	$PRINT_HEAD_BORDER = PRINT_HEAD_BORDER;

	$rc = <<<EOD
<div style="background-color: $PRINT_HEAD_BGCOLOR;border: 1px $PRINT_HEAD_BORDER solid;padding: 6px 8px;margin: 6px 1%;">
<table id="footertable" border="0" cellspacing="0" cellpadding="0">
<tr>
 <td id="footerltable">

EOD;
        $rc .= print_qr_code($vars['page']);

        $rc .= <<<EOD
 </td>

 <td id="footerctable"><div id="sigunature">
  Founded by {$modifier}.
  <br />
  Powered by PukiWiki Plus! {$S_VERSION}.
 </div></td>

</tr>
</table>
</div>

EOD;

        return $rc;
}

function print_qr_code($page)
{
	if (! exist_plugin_inline('qrcode')) return '';

	$script = get_script_absuri();
	$r_page = rawurlencode($page);
	$a_script = $script;
	$a_script = str_replace("\\", "\\\\", $a_script);
	$a_script = str_replace(':', '\:', $a_script);
	$a_script = str_replace(';', '\;', $a_script);
	$a_script = str_replace(',', '\,', $a_script);
	$a_page = str_replace('%', '%25', $r_page);
	return plugin_qrcode_inline(1, $script.'?'.$a_page);
}

?>
