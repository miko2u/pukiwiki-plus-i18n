<?php
/**
 * js_init - JavaScript 初期化プラグイン
 *
 * @copyright   Copyright &copy; 2006-2007, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: js_init.inc.php,v 0.3 2007/07/10 00:54:00 upk Exp $
 *
 */

function plugin_js_init_convert()
{
	global $language;

	$rc = '';
	//$const = array('SKIN_URI','IMAGE_URI','DEFAULT_LANG');
	//$const = array('SKIN_DIR','IMAGE_DIR','DEFAULT_LANG');
	//foreach( $const as $var){
	//	$rc .= 'var '.$var.'="'.constant($var).'";'."\n";
	//}

	$const = array('SKIN_DIR'=>'SKIN_URI','IMAGE_DIR'=>'IMAGE_URI','DEFAULT_LANG'=>'DEFAULT_LANG');
	foreach( $const as $key=>$val){
		$rc .= 'var '.$key.'="'.constant($val).'";'."\n";
	}
	unset($const);

	$rc .= 'var LANG="'.$language.'";'."\n";
	return $rc;
}

?>
