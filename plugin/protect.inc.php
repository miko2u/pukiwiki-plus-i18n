<?php
/**
 * PukiWiki Plus! PROTECT Plugin
 *
 * @copyright   Copyright &copy; 2007-2009, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: protect.inc.php,v 0.4 2009/12/05 03:10:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License (GPL2)
 *
 */

defined('PLUGIN_PROTECT_MUST_PAGE') or define('PLUGIN_PROTECT_MUST_PAGE', 1);

function plugin_protect_convert()
{
	global $use_local_time, $language;
	global $head_tags, $foot_tags, $pkwk_dtd;
	global $vars;
	global $fixed_heading_edited, $autoglossary, $_symbol_paraedit, $_symbol_paraguiedit;
	global $_symbol_noexists;
	global $foot_explain, $note_hr;

	$fixed_heading_edited = $autoglossary = 0;
	$_symbol_paraedit = $_symbol_paraguiedit = '&nbsp;';

	if (func_num_args() == 1) {
		list($plugin) = func_get_args();
        } else {
		$plugin = '';
	}

	$body = protect_body($plugin);

	// Yetlist
	$noexists_pattern = '#<span class="noexists">([^<]*)<a[^>]+>' . preg_quote($_symbol_noexists, '#') . '</a></span>#';
	$body = preg_replace($noexists_pattern,'$1',$body);

	// List of footnotes
	ksort($foot_explain, SORT_NUMERIC);
	$notes = ! empty($foot_explain) ? $note_hr . join("\n", $foot_explain) : '';

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

	echo ' <title>Login</title>'."\n";

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

	if (exist_plugin_convert('tz')) echo do_plugin_convert('tz');

	echo $foot_tag;

	echo <<<EOD
</body>
</html>

EOD;

	die();
}

function protect_body($plugin)
{
	global $auth_api, $protect;

	$body = '';
	if ($plugin === 'login') $plugin = '';

	if (is_page($protect) && empty($plugin)) {
		$body .= convert_html(get_source($protect));
	} else {
		$plugin = (empty($plugin)) ? 'login' : $plugin;
		if (exist_plugin_convert($plugin)) $body .= do_plugin_convert($plugin);
	}

	if (empty($body)) {
 		if (PLUGIN_PROTECT_MUST_PAGE) die( 'The attestation setting is not done.' );
 		die();
	}
	return $body;
}

?>
