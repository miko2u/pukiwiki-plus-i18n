<?php
/**
 * 言語を判定しメッセージを表示
 *
 * @copyright	Copyright &copy; 2005-2006, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version	$Id: multilang.inc.php,v 0.5 2006/02/03 00:41:00 upk Exp $
 *
 */

// ja_JP, ko_KR, en_US, zh_TW
// They are used as delimiters at &multilang(link,ja_JP=Japanese,en_US=English,.....);
if (!defined('PLUGIN_MULTILANG_INLINE_BEFORE')) {
	define('PLUGIN_MULTILANG_INLINE_BEFORE', '[ ');
}
if (!defined('PLUGIN_MULTILANG_INLINE_DELIMITER')) {
	define('PLUGIN_MULTILANG_INLINE_DELIMITER', ' | ');
}
if (!defined('PLUGIN_MULTILANG_INLINE_AFTER')) {
	define('PLUGIN_MULTILANG_INLINE_AFTER',  ' ]');
}

function plugin_multilang_action()
{
	global $vars, $script;

	$page = isset($vars['page']) ? $vars['page'] : '';
	$lang = isset($vars['lang']) ? $vars['lang'] : '';

	$parsed_url = parse_url($script);
	$path = $parsed_url['path'];
	if (($pos = strrpos($path, '/')) !== FALSE) {
		$path = substr($path, 0, $pos + 1);
	}
	if ($lang) {
		setcookie('lang', $lang, 0, $path);
		$_COOKIE['lang'] = $lang; /* To effective promptly */
	} 

	if(exist_plugin_action('read')) {
		return plugin_read_action();
	} // if not? No way....
}

function plugin_multilang_inline()
{
	$args = func_get_args();
	$lang = array_shift($args);
	
	if ($lang == 'link') {
		array_pop($args); // drop {}
		return plugin_multilang_inline_link($args);
	} else {
	
		if (plugin_multilang_accept($lang)) {
			return array_pop($args);
		} else {
			return '';
		}
		
	}
}

function plugin_multilang_inline_link($args)
{
	global $vars, $script;

	$body = array();
	$page = $vars['page'];
	$url = "$script?page=$page&amp;cmd=multilang&amp;lang";
	$obj_l2c = new lang2country();
	foreach( $args as $arg ) {
		list($lang, $title) = split('=', $arg);
		list($lng,$country) = split('_', $lang);
		if (empty($country)) {
			$country = $obj_l2c->get_lang2country( strtolower($lng) );
		}

		$lang = htmlspecialchars($lang);
		$title = htmlspecialchars($title);

		if (! empty($country)) {
			$country = strtolower($country);
			$title = '<img src="' . IMAGE_URI . 'icon/flags/' . $country . '.png" title="'. $title . '">';
		}

		array_push($body, "<a href=\"$url=$lang\">$title</a>");
	}
	return PLUGIN_MULTILANG_INLINE_BEFORE . join(PLUGIN_MULTILANG_INLINE_DELIMITER, $body)
		. PLUGIN_MULTILANG_INLINE_AFTER;
}

function plugin_multilang_accept($lang)
{
	global $language_considering_setting_level;
	global $language;

	// FIXME: level 4
	if (isset($_COOKIE['lang'])) {
		$env = $_COOKIE['lang'];
		// 'none' shows everything. 
		if ($env == 'none') { return TRUE; }
	} else { 
		$env = ($language_considering_setting_level == 0) ? get_language(4) : $language;
		$l = accept_language::split_locale_str($env);
	}

	return $lang == $env || $lang == $l[1];
}

function plugin_multilang_convert()
{
	global $language_considering_setting_level;
	global $language;

	switch ( func_num_args() ) {
	case 1:
		list($lines) = func_get_args();
		$lang = DEFAULT_LANG; // pukiwiki.ini.php
		break;
	default:
		list($lang,$lines) = func_get_args();
	}

	if (plugin_multilang_accept($lang)) {
		$lines = preg_replace(array("[\\r|\\n]","[\\r]"), array("\n","\n"), $lines);
		return preg_replace(array("'<p>'si","'</p>'si"), array("",""), convert_html($lines) );
	} else {
		return '';
	}
}

?>
