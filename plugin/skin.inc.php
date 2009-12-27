<?php
/**
 * skin プラグイン
 *
 * @copyright   Copyright &copy; 2009, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: skin.inc.php,v 2.0 2009/12/26 13:29:00 upk Exp $
 *
 */
defined('PLUGIN_SKIN_USE')    or define('PLUGIN_SKIN_USE', 1);		// skin プラグインの機能の利用可否 (1-ok, 0-ng)
defined('PLUGIN_SKIN_EXPIRE') or define('PLUGIN_SKIN_EXPIRE', 0);	// 有効日数を定義(0 は、ブラウザ終了時消滅)

function plugin_skin_init()
{
	$msg = array(
		'_skin_msg' => array(
			'msg_select'		=> _('select a skin'),
			'err_not_use'		=> _('This plugin function cannot be used.'),
		)
	);
	set_plugin_messages($msg);
}

function plugin_skin_convert()
{
	global $vars, $skin_file, $_skin_msg;

	if (!PLUGIN_SKIN_USE) return $_skin_msg['err_not_use'];
	if (func_num_args() == 0) return skin_make_filelist();

	$argv = func_get_args();
	$parm = skin_set_parm($argv);

	if (count($parm['list']) > 1) {
		$skin_list = array();
		foreach($parm['list'] as $skin) {
			$skin_list[$skin] = '';
		}
		return skin_make_filelist($skin_list);
	}

	$val = explode('.', $parm['list'][0]);
	$val[1] = (empty($val[1])) ? $val[0] : $val[1];

	$skin_file = add_skindir($val[0]);

	if (! file_exists($skin_file) || ! is_readable($skin_file)) {
		die_message($skin_file.' (skin file) is not found.');
	}

	$expire = (PLUGIN_SKIN_EXPIRE > 0) ? time() + (60*60*24) * PLUGIN_SKIN_EXPIRE : PLUGIN_SKIN_EXPIRE;

	setcookie('skin_file', $skin_file, $expire, get_baseuri('abs'));
	$_COOKIE['skin_file'] = $skin_file;

	if ($val[0] == 'tdiary') {
		setcookie('tdiary_theme', $val[1], $expire, get_baseuri('abs'));
		$_COOKIE['tdiary_theme'] = $val[1];
	} else {
		setcookie('tdiary_theme', '', time()-3600); // tdiary じゃないので削除
	}

	header('Location: '.get_page_location_uri($vars['page']));
}

function plugin_skin_action()
{
	global $post, $_skin_msg;
	if (!PLUGIN_SKIN_USE) return array('msg'=>$_skin_msg['msg_select'],'body'=>$_skin_msg['err_not_use']);
	if (empty($post['skin']))
		return array('msg'=>$_skin_msg['msg_select'],'body'=>skin_make_filelist());
	plugin_skin_convert($post['skin']);
}

function plugin_skin_inline()
{
	global $_skin_msg;

	$theme = skin_get_plus_theme();
	$retval = $theme;
	if ($theme == 'tdiary') {
		if (isset($_COOKIE['tdiary_theme'])) {
			$retval .= ' ('.$_COOKIE['tdiary_theme'].')';
		} else {
			if (defined('TDIARY_THEME')) {
				$retval .= ' ('.TDIARY_THEME.')';
			}
		}
	}

	return $retval;
}

function skin_set_parm($argv)
{
	$parm = array();
	foreach($argv as $arg) {
		$val = explode('=', $arg);
		$val[1] = (empty($val[1])) ? htmlspecialchars($val[0]) : htmlspecialchars($val[1]);
		$parm['list'][] = $val[1];
	}
	return $parm;
}

function skin_get_plus_theme()
{
	global $skin_file;

	$skin = (isset($_COOKIE['skin_file'])) ? $_COOKIE['skin_file'] : $skin_file;
	$pos = strrpos($skin, '/');
	$skin = ($pos === false) ? $skin : substr($skin,$pos+1);
	$pos = strpos($skin,'.skin.php');
	return ($pos === false) ? $skin : substr($skin,0,$pos);
}

function skin_make_filelist($list='')
{
	global $vars, $script, $_skin_msg;

	$retval = <<<EOD
<form action="$script" method="post">
 <div>
  <select name="skin">

EOD;

	// 入力データの加工およびデータ取得
	if (empty($list)) {
		$list_plus = skin_search();
		$list_tdiary = skin_search_tdiary();
	} else {
		// 入力データを分離
		$list_plus = $list_tdiary = array();
                foreach($list as $skin=>$val) {
                        $val = explode('.', $skin);
                        $val[1] = (empty($val[1])) ? $val[0] : $val[1];
			if ($val[0] == 'tdiary') {
				$list_tdiary[ $val[1] ] = '';
			} else {
				$list_plus[ $val[1] ] = '';
			}
                }
	}

	// plus theme
	if (!empty($list_plus)) {
		$retval .= '   <optgroup label="plus-theme">'."\n";
		$theme = skin_get_plus_theme();
		foreach($list_plus as $skin=>$val) {
			switch ($skin) {
			case 'keitai':
			case 'tdiary':
				continue;
			default:
				$selected = (!empty($theme) && $theme == $skin) ? ' selected="selected"' : '';
				$retval .= '     <option value="'.$skin.'"'.$selected.'>'.$skin.'</option>'."\n";
			}
		}
		$retval .= '   </optgroup>'."\n";
	}

	// tDiary theme
	if (!empty($list_tdiary)) {
		$retval .= '   <optgroup label="tDiary-theme">'."\n";
		$theme = (isset($_COOKIE['tdiary_theme'])) ? $_COOKIE['tdiary_theme'] : '';
		foreach($list_tdiary as $skin=>$val) {
			$selected = (!empty($theme) && $theme == $skin) ? ' selected="selected"' : '';
			$retval .= '     <option value="tdiary.'.$skin.'"'.$selected.'>'.$skin.'</option>'."\n";
               	}
		$retval .= '   </optgroup>'."\n";
	}

	$retval .= <<<EOD
  </select>
  <input type="hidden" name="plugin" value="skin" />
  <input type="hidden" name="page" value="{$vars['page']}" />
  <input type="submit" value="{$_skin_msg['msg_select']}" />
 </div>
</form>

EOD;
	return $retval;
}

function skin_search()
{
        $retval = array();
        foreach(array(EXT_SKIN_DIR, SKIN_DIR, SKIN_URI, DATA_HOME.SKIN_DIR) as $dir) {
                $rc = skin_find_file($dir);
                if (!empty($rc)) $retval = array_merge($retval,$rc);
        }
        return $retval;
}

function skin_find_file($dir)
{
        $retval = $matches = array();

	if ($dp = opendir($dir)) {
        	while ($file = readdir($dp)) {
                	if ($file==='.' || $file==='..') continue;
                	if (filetype($dir.$file) === 'dir') {
                        	$rc = skin_find_file($dir.$file.'/');
                        	if (!empty($rc)) $retval = array_merge($retval,$rc);
				continue;
			}
			if (preg_match('/(.*)\.skin\.php$/i', $file, $matches)) $retval[$matches[1]] = '';
		}
		ksort($retval);
		closedir($dp);
	}
        return $retval;
}

function skin_search_tdiary()
{
	$retval = array();
	$dir = SKIN_URI.THEME_TDIARY_NAME;

	if ($dp = opendir($dir)) {
		while ($file = readdir($dp)) {
			if ($file==='.' || $file==='..') continue;
			if (filetype($dir.$file) === 'dir') $retval[$file] = '';
		}
		ksort($retval);
		closedir($dp);
	}
	return $retval;
}

?>
