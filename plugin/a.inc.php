<?php
/**
 * PukiWiki Plus! Anchor Plugin
 *
 * @copyright   Copyright &copy; 2008, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: a.inc.php,v 0.2 2008/02/24 18:47:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License (GPL2)
 *
 */
function plugin_a_inline()
{
	global $_symbol_innanchor, $_symbol_extanchor;

	list($href, $name, $opt) = func_get_args();

	$href = trim($href);
	if (empty($href)) return;
	$name = trim($name);
	if (empty($name)) $name= $href;
	$opt = trim($opt);
	if (! empty($opt)) $opt = ' '.$opt;

	$is_url = is_url($href);
	if (! $is_url && ! a_rel_check($href)) return $href;

	$is_ext = ($is_url && ! is_inside_uri($href));

	$symbol = $is_ext ? $_symbol_extanchor : $_symbol_innanchor;
	$r_href = (PKWK_USE_REDIRECT && $is_ext) ? get_cmd_uri('redirect','','','u=').rawurlencode($href) : htmlspecialchars($href);

	if (! $is_url) {
		return '<a href="'.$r_href.'"'.$opt.'>'.$name.'</a>';
	} else {
		return '<a class="inn" href="'.$r_href.'" rel="nofollow"'.$opt.'>'.$name.
			str_replace('$1', $r_href, str_replace('$2', '_blank', $symbol)).'</a>';
	}
}

function a_rel_check($href)
{
	// . | / | index.php | ?
	return preg_match('/^(\.|\/|index.php|\?)/i', $href);
}

?>
