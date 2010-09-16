<?php
/**
 * Skype プラグイン
 *
 * @copyright   Copyright &copy; 2006, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: skype.inc.php,v 0.2 2006/04/13 00:57:00 upk Exp $
 *
 */

if (!defined('SKYPE_MYSTATUS_URL')) {
	define('SKYPE_MYSTATUS_URL', 'http://mystatus.skype.com/');
}

function plugin_skype_convert()
{
	static $call = FALSE;

	$argv = func_get_args();
	$argc = func_num_args();

        $field = array('user','func','size');
	for($i=0; $i<$argc; $i++) {
		$$field[$i] = htmlspecialchars($argv[$i], ENT_QUOTES);
	}

	if (empty($user)) return '';
	$func = (skype::is_function($func)) ? $func : 'userinfo';
	$size_info = skype::size_info($size);

	$link = 'skype:'.$user.'?'.$func;
	$img = '<img src="'.SKYPE_MYSTATUS_URL.$size_info[0].'/'.$user.
		'" style="border: none;" width="'.$size_info[1].
		'" height="'.$size_info[2].'" alt="My status" />';

	$rc = '';

	if (!$call) {
		$call = TRUE;
		$rc = <<<EOD
<script type="text/javascript" src="http://download.skype.com/share/skypebuttons/js/skypeCheck.js"></script>

EOD;
	}

	$rc .= '<a href="skype:'.$user.'?'.$func.'">'.$img."</a>\n";
	return $rc;
}

class skype
{
	function is_function($func)
	{
		if (empty($func)) return FALSE;
		static $function = array('call','add','chat','userinfo','voicemaill','sendfile');
		$no = array_search($func, $function);
		return ($no === NULL || $no === FALSE) ? FALSE : TRUE;
	}

	function size_info($size)
	{
		$size = (empty($size)) ? 0 : $size;
		if ($size > 4) $size = 0;

		static $info = array(
			array('balloon'     , 150,50), // text + big + balloon
			array('bigclassic'  , 182,44), // text + big + button
			array('smallclassic', 114,20), // text + small
			array('mediumicon'  ,  26,26), // icon + big
			array('smallicon'   ,  16,16), // icon + small
		);
		return $info[$size];
	}
}

?>
