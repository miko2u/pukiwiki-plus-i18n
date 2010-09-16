<?php
/**
 * js_init - JavaScript 初期化プラグイン
 *
 * @copyright   Copyright &copy; 2006, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: js_init.inc.php,v 0.1 2006/02/13 21:53:00 upk Exp $
 *
 */

function plugin_js_init_convert()
{
	global $language;

	$rc = '';
	$const = array('SKIN_DIR','IMAGE_DIR','DEFAULT_LANG');
	foreach( $const as $var){
		$rc .= 'var '.$var.'="'.constant($var).'";'."\n";
	}
	unset($const);

	$rc .= 'var LANG="'.$language.'";'."\n";
	return $rc;
}

?>
